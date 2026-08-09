from typing import List
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from sqlalchemy.exc import IntegrityError
from app.database import get_db
from app.models import Article, ProcessingLog
from app.schemas import ArticleCreate, ArticleResponse, ArticleUpdate
from app.core.exceptions import DuplicateArticleException, NotFoundException, ValidationException
from app.services.crawler import is_valid_url

router = APIRouter(prefix="/articles", tags=["articles"])


@router.post("/", response_model=ArticleResponse, status_code=status.HTTP_201_CREATED)
def create_article(article: ArticleCreate, db: Session = Depends(get_db)):
    """Tạo bài viết mới từ URL nguồn, bắt đầu pipeline xử lý.
    
    - **source_url**: URL bài viết nguồn (phải bắt đầu bằng http:// hoặc https://)
    """
    # Validate URL format
    if not is_valid_url(article.source_url):
        raise ValidationException("Invalid URL format", field="source_url")
    
    # Check for duplicate URL
    existing = db.query(Article).filter(Article.source_url == article.source_url).first()
    if existing:
        raise DuplicateArticleException(article.source_url)
    
    try:
        db_article = Article(
            source_url=article.source_url,
            status="pending"
        )
        db.add(db_article)
        db.commit()
        db.refresh(db_article)
        
        # Trigger Celery task để xử lý pipeline
        from app.tasks.article_pipeline import process_article_pipeline
        process_article_pipeline.delay(db_article.id)
        
        return db_article
        
    except IntegrityError:
        db.rollback()
        raise DuplicateArticleException(article.source_url)


@router.get("/", response_model=List[ArticleResponse])
def list_articles(
    skip: int = 0,
    limit: int = 100,
    status: str = None,
    db: Session = Depends(get_db)
):
    """Danh sách bài viết với filter theo status.
    
    - **skip**: Số bài bỏ qua (offset)
    - **limit**: Số bài tối đa trả về (max 100)
    - **status**: Filter theo status (pending, crawling, rewritten, draft_created, published, failed)
    """
    # Validate pagination
    if skip < 0:
        raise ValidationException("skip must be >= 0", field="skip")
    if limit < 1 or limit > 100:
        raise ValidationException("limit must be between 1 and 100", field="limit")
    
    # Validate status if provided
    valid_statuses = ["pending", "crawling", "extracting", "rewriting", "seo_auditing",
                      "generating_thumbnail", "uploading_thumbnail", "creating_post",
                      "draft_created", "published", "failed"]
    if status and status not in valid_statuses:
        raise ValidationException(
            f"Invalid status. Valid values: {', '.join(valid_statuses)}",
            field="status"
        )
    
    query = db.query(Article)
    if status:
        query = query.filter(Article.status == status)
    articles = query.order_by(Article.created_at.desc()).offset(skip).limit(limit).all()
    return articles


@router.get("/{article_id}", response_model=ArticleResponse)
def get_article(article_id: int, db: Session = Depends(get_db)):
    """Chi tiết bài viết kèm logs.
    
    - **article_id**: ID của bài viết
    """
    article = db.query(Article).filter(Article.id == article_id).first()
    if not article:
        raise NotFoundException("Article", article_id)
    return article


@router.put("/{article_id}", response_model=ArticleResponse)
def update_article(
    article_id: int,
    article_update: ArticleUpdate,
    db: Session = Depends(get_db)
):
    """Cập nhật nội dung bài viết trước khi publish.
    
    - **article_id**: ID của bài viết
    - **rewritten_title**: Tiêu đề mới (max 500 chars)
    - **slug**: URL slug (chỉ a-z, 0-9, -)
    - **meta_title**: Meta title SEO (max 70 chars, khuyến nghị <60)
    - **meta_description**: Meta description (max 160 chars, khuyến nghị <155)
    """
    article = db.query(Article).filter(Article.id == article_id).first()
    if not article:
        raise NotFoundException("Article", article_id)
    
    for field, value in article_update.dict(exclude_unset=True).items():
        setattr(article, field, value)
    
    db.commit()
    db.refresh(article)
    return article


@router.post("/{article_id}/publish", response_model=ArticleResponse)
def publish_article(article_id: int, db: Session = Depends(get_db)):
    """Publish bài viết lên WordPress (từ draft sang published).
    
    Yêu cầu: Article phải ở status "draft_created"
    """
    article = db.query(Article).filter(Article.id == article_id).first()
    if not article:
        raise NotFoundException("Article", article_id)
    
    if article.status != "draft_created":
        raise ValidationException(
            f"Article must be in 'draft_created' status, current: {article.status}",
            field="status"
        )
    
    # Trigger publish task
    from app.tasks.article_pipeline import publish_to_wordpress
    publish_to_wordpress.delay(article_id)
    
    return article


@router.delete("/{article_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_article(article_id: int, db: Session = Depends(get_db)):
    """Xóa bài viết.
    
    - **article_id**: ID của bài viết cần xóa
    """
    article = db.query(Article).filter(Article.id == article_id).first()
    if not article:
        raise NotFoundException("Article", article_id)
    
    db.delete(article)
    db.commit()
    return None
