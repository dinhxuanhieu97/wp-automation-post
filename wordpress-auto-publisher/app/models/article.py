from datetime import datetime
from sqlalchemy import Column, Integer, String, Text, DateTime, Boolean, ARRAY
from app.database import Base


class Article(Base):
    __tablename__ = "articles"
    
    id = Column(Integer, primary_key=True, index=True)
    
    # Source data
    source_url = Column(String(1000), nullable=False)
    source_title = Column(String(500))
    source_content = Column(Text)
    
    # Rewritten content
    rewritten_title = Column(String(500))
    rewritten_content = Column(Text)
    
    # SEO metadata
    keywords = Column(String(1000))
    slug = Column(String(200))
    meta_title = Column(String(200))
    meta_description = Column(String(160))
    
    # FAQ (stored as JSON string or separate table - keeping simple here)
    faq_content = Column(Text)
    
    # Image
    thumbnail_prompt = Column(Text)
    thumbnail_local_path = Column(String(500))
    thumbnail_url = Column(String(1000))
    wp_media_id = Column(Integer)
    
    # WordPress
    wp_post_id = Column(Integer)
    wp_post_url = Column(String(1000))
    
    # Processing status
    status = Column(String(50), default="pending", index=True)
    # pending, crawling, extracting, rewriting, seo_auditing, 
    # generating_thumbnail, uploading_thumbnail, creating_post, 
    # draft_created, published, failed
    
    error_message = Column(Text)
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    published_at = Column(DateTime)
