"""Celery tasks cho article processing pipeline."""
import asyncio
from datetime import datetime
from celery import chain
from app.core.celery_app import celery_app
from app.database import SessionLocal
from app.models import Article, ProcessingLog
from app.services.crawler import crawl_with_fallback
from app.services.ai.rewriter import AIRewriter
from app.services.image.generator import ImageGenerator
from app.services.wordpress.client import WordPressClient


def create_log(article_id: int, step: str, status: str, message: str = ""):
    """Helper để tạo processing log."""
    db = SessionLocal()
    try:
        log = ProcessingLog(
            article_id=article_id,
            step=step,
            status=status,
            message=message
        )
        db.add(log)
        db.commit()
    finally:
        db.close()


def update_article_status(article_id: int, status: str, error_message: str = None):
    """Helper để update article status."""
    db = SessionLocal()
    try:
        article = db.query(Article).filter(Article.id == article_id).first()
        if article:
            article.status = status
            if error_message:
                article.error_message = error_message
            article.updated_at = datetime.utcnow()
            db.commit()
    finally:
        db.close()


@celery_app.task(bind=True, max_retries=3)
def crawl_task(self, article_id: int):
    """Task 1: Crawl và extract nội dung."""
    create_log(article_id, "crawl", "started")
    
    db = SessionLocal()
    try:
        article = db.query(Article).filter(Article.id == article_id).first()
        if not article:
            raise ValueError(f"Article {article_id} not found")
        
        # Crawl with fallback (requests first, then playwright if needed)
        result = crawl_with_fallback(article.source_url)
        
        # Debug logging
        print(f"[DEBUG] Crawl result for article {article_id}:")
        print(f"  - success: {result.get('success')}")
        print(f"  - title: {result.get('title', 'N/A')[:100] if result.get('title') else 'EMPTY'}")
        print(f"  - content length: {len(result.get('content', ''))}")
        print(f"  - error: {result.get('error', 'N/A')}")
        
        if not result["success"]:
            raise Exception(result.get("error", "Crawl failed"))
        
        # Save extracted content
        article.source_title = result.get("title", "")
        article.source_content = result.get("content", "")
        article.status = "extracted"
        db.commit()
        
        create_log(article_id, "crawl", "completed", f"Title: {result.get('title', 'N/A')}")
        return article_id
        
    except Exception as exc:
        create_log(article_id, "crawl", "failed", str(exc))
        update_article_status(article_id, "failed", str(exc))
        raise self.retry(exc=exc, countdown=60)
    finally:
        db.close()


@celery_app.task(bind=True, max_retries=3)
def rewrite_task(self, article_id: int):
    """Task 2: AI rewrite bài viết."""
    create_log(article_id, "rewrite", "started")
    
    db = SessionLocal()
    try:
        article = db.query(Article).filter(Article.id == article_id).first()
        if not article or not article.source_content:
            raise ValueError("No content to rewrite")
        
        rewriter = AIRewriter()
        
        # Rewrite content
        result = rewriter.rewrite_article(
            title=article.source_title,
            content=article.source_content
        )
        
        if not result.get("success"):
            raise Exception(result.get("error", "Rewrite failed"))
        
        article.rewritten_title = result.get("rewritten_title", "")
        article.rewritten_content = result.get("rewritten_content", "")
        article.status = "rewritten"
        db.commit()
        
        # Generate SEO metadata
        keywords = result.get("suggested_keywords", [])
        try:
            seo_result = rewriter.generate_seo_metadata(
                title=article.rewritten_title,
                content=article.rewritten_content[:2000],
                keywords=keywords
            )
            
            if seo_result.get("success"):
                article.keywords = ", ".join(keywords)
                article.slug = seo_result.get("slug", "")
                article.meta_title = seo_result.get("meta_title", "")
                article.meta_description = seo_result.get("meta_description", "")
                article.faq_content = str(seo_result.get("faq", []))
            else:
                # Generate fallback SEO
                article.keywords = ", ".join(keywords) if keywords else ""
                article.slug = article.rewritten_title.lower().replace(" ", "-")[:200]
                article.meta_title = article.rewritten_title[:200]
                article.meta_description = article.rewritten_content[:160] if article.rewritten_content else ""
        except Exception as seo_err:
            # Fallback SEO on error
            article.keywords = ", ".join(keywords) if keywords else ""
            article.slug = article.rewritten_title.lower().replace(" ", "-")[:200] if article.rewritten_title else ""
            article.meta_title = article.rewritten_title[:200] if article.rewritten_title else ""
            article.meta_description = article.rewritten_content[:160] if article.rewritten_content else ""
        
        db.commit()
        
        create_log(article_id, "rewrite", "completed")
        return article_id
        
    except Exception as exc:
        create_log(article_id, "rewrite", "failed", str(exc))
        update_article_status(article_id, "failed", str(exc))
        raise self.retry(exc=exc, countdown=60)
    finally:
        db.close()


@celery_app.task(bind=True, max_retries=3)
def generate_thumbnail_task(self, article_id: int):
    """Task 3: Generate thumbnail."""
    create_log(article_id, "generate_thumbnail", "started")
    
    db = SessionLocal()
    article = None
    try:
        article = db.query(Article).filter(Article.id == article_id).first()
        if not article:
            raise ValueError("Article not found")
        
        # Generate prompt
        rewriter = AIRewriter()
        prompt = rewriter.generate_image_prompt(
            title=article.rewritten_title or article.source_title,
            content_summary=article.rewritten_content or article.source_content
        )
        article.thumbnail_prompt = prompt
        db.commit()
        
        # Generate image
        generator = ImageGenerator()
        image_path = generator.generate_thumbnail(prompt)
        
        if image_path:
            article.thumbnail_local_path = image_path
            article.status = "thumbnail_generated"
            db.commit()
            create_log(article_id, "generate_thumbnail", "completed", f"Path: {image_path}")
        else:
            raise Exception("Failed to generate image")
        
        return article_id
        
    except Exception as exc:
        # Skip thumbnail generation, go directly to WordPress
        create_log(article_id, "generate_thumbnail", "skipped", f"Error: {str(exc)}. Proceeding to WordPress.")
        article.status = "thumbnail_skipped"
        db.commit()
        # Trigger WordPress upload directly
        upload_to_wordpress_task.delay(article_id)
        return article_id
    finally:
        db.close()


@celery_app.task(bind=True, max_retries=3)
def upload_to_wordpress_task(self, article_id: int):
    """Task 4: Upload thumbnail và tạo draft post."""
    create_log(article_id, "upload_to_wordpress", "started")
    
    db = SessionLocal()
    try:
        article = db.query(Article).filter(Article.id == article_id).first()
        if not article:
            raise ValueError("Article not found")
        
        wp_client = WordPressClient()
        
        # Test connection first
        if not wp_client.test_connection():
            raise Exception("Cannot connect to WordPress")
        
        # Upload thumbnail if exists
        media_id = None
        if article.thumbnail_local_path:
            loop = asyncio.new_event_loop()
            asyncio.set_event_loop(loop)
            
            try:
                media_result = loop.run_until_complete(
                    wp_client.upload_media(
                        article.thumbnail_local_path,
                        alt_text=article.rewritten_title or article.source_title,
                        title=article.rewritten_title or article.source_title
                    )
                )
                media_id = media_result.get("id")
                article.wp_media_id = media_id
            finally:
                loop.close()
        
        # Create draft post
        content = article.rewritten_content or article.source_content
        
        # Add FAQ to content if exists
        if article.faq_content:
            try:
                import json
                faq_list = json.loads(article.faq_content.replace("'", "\""))
                if faq_list:
                    faq_html = "\n\n<h2>Câu hỏi thường gặp (FAQ)</h2>\n"
                    for item in faq_list:
                        faq_html += f"<h3>{item.get('question', '')}</h3>\n"
                        faq_html += f"<p>{item.get('answer', '')}</p>\n"
                    content += faq_html
            except:
                pass
        
        loop = asyncio.new_event_loop()
        asyncio.set_event_loop(loop)
        
        try:
            post_result = loop.run_until_complete(
                wp_client.create_post(
                    title=article.rewritten_title or article.source_title,
                    content=content,
                    status="draft",
                    featured_media=media_id
                )
            )
            
            article.wp_post_id = post_result.get("id")
            article.wp_post_url = post_result.get("link", "")
            article.status = "draft_created"
            db.commit()
            
            create_log(
                article_id, 
                "upload_to_wordpress", 
                "completed", 
                f"Post ID: {article.wp_post_id}"
            )
            
        finally:
            loop.close()
        
        return article_id
        
    except Exception as exc:
        create_log(article_id, "upload_to_wordpress", "failed", str(exc))
        update_article_status(article_id, "failed", str(exc))
        raise self.retry(exc=exc, countdown=60)
    finally:
        db.close()


@celery_app.task
def process_article_pipeline(article_id: int):
    """
    Chain tất cả các tasks lại với nhau.
    Usage: process_article_pipeline.delay(article_id)
    """
    chain(
        crawl_task.s(article_id),
        rewrite_task.s(),
        generate_thumbnail_task.s(),
        upload_to_wordpress_task.s()
    ).apply_async()


@celery_app.task(bind=True, max_retries=2)
def publish_to_wordpress(self, article_id: int):
    """Task để publish draft thành published."""
    create_log(article_id, "publish", "started")
    
    db = SessionLocal()
    try:
        article = db.query(Article).filter(Article.id == article_id).first()
        if not article or not article.wp_post_id:
            raise ValueError("No WordPress post to publish")
        
        wp_client = WordPressClient()
        
        loop = asyncio.new_event_loop()
        asyncio.set_event_loop(loop)
        
        try:
            result = loop.run_until_complete(
                wp_client.update_post(
                    post_id=article.wp_post_id,
                    status="publish"
                )
            )
            
            article.status = "published"
            article.published_at = datetime.utcnow()
            db.commit()
            
            create_log(article_id, "publish", "completed", f"URL: {result.get('link', '')}")
            
        finally:
            loop.close()
        
        return article_id
        
    except Exception as exc:
        create_log(article_id, "publish", "failed", str(exc))
        raise self.retry(exc=exc, countdown=30)
    finally:
        db.close()
