import re
from datetime import datetime
from typing import Optional, List
from pydantic import BaseModel, HttpUrl, field_validator, constr


class ArticleCreate(BaseModel):
    source_url: str
    
    @field_validator('source_url')
    @classmethod
    def validate_source_url(cls, v: str) -> str:
        if not v:
            raise ValueError('URL is required')
        
        # Basic URL validation
        url_pattern = re.compile(
            r'^https?://'  # http:// or https://
            r'(?:(?:[A-Z0-9](?:[A-Z0-9-]{0,61}[A-Z0-9])?\.)+[A-Z]{2,6}\.?|'  # domain
            r'localhost|'  # localhost
            r'\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})'  # or ip
            r'(?::\d+)?'  # optional port
            r'(?:/?|[/?]\S+)$', re.IGNORECASE
        )
        
        if not url_pattern.match(v):
            raise ValueError('Invalid URL format')
        
        # Check for blocked domains (optional)
        blocked_domains = ['localhost', '127.0.0.1', '0.0.0.0']
        for blocked in blocked_domains:
            if blocked in v:
                raise ValueError(f'URL contains blocked domain: {blocked}')
        
        return v.strip()


class ArticleUpdate(BaseModel):
    rewritten_title: Optional[constr(max_length=500)] = None
    rewritten_content: Optional[str] = None
    keywords: Optional[constr(max_length=1000)] = None
    slug: Optional[constr(max_length=200, pattern=r'^[a-z0-9-]+$')] = None
    meta_title: Optional[constr(max_length=200)] = None
    meta_description: Optional[constr(max_length=160)] = None
    faq_content: Optional[str] = None
    
    @field_validator('slug')
    @classmethod
    def validate_slug(cls, v: Optional[str]) -> Optional[str]:
        if v is None:
            return v
        # Convert to lowercase and replace spaces with hyphens
        v = v.lower().strip().replace(' ', '-')
        # Remove special characters
        v = re.sub(r'[^a-z0-9-]', '', v)
        # Remove multiple consecutive hyphens
        v = re.sub(r'-+', '-', v)
        # Remove leading/trailing hyphens
        v = v.strip('-')
        return v
    
    @field_validator('meta_title')
    @classmethod
    def validate_meta_title(cls, v: Optional[str]) -> Optional[str]:
        if v and len(v) > 60:
            # Warning but still allow (Google displays ~60 chars)
            pass
        return v
    
    @field_validator('meta_description')
    @classmethod
    def validate_meta_description(cls, v: Optional[str]) -> Optional[str]:
        if v and len(v) > 155:
            # Warning but still allow (Google displays ~155-160 chars)
            pass
        return v


class ProcessingLogResponse(BaseModel):
    id: int
    step: str
    status: str
    message: Optional[str]
    created_at: datetime
    
    class Config:
        from_attributes = True


class ArticleResponse(BaseModel):
    id: int
    source_url: str
    source_title: Optional[str]
    source_content: Optional[str]
    
    rewritten_title: Optional[str]
    rewritten_content: Optional[str]
    
    keywords: Optional[str]
    slug: Optional[str]
    meta_title: Optional[str]
    meta_description: Optional[str]
    faq_content: Optional[str]
    
    thumbnail_url: Optional[str]
    wp_post_id: Optional[int]
    wp_post_url: Optional[str]
    
    status: str
    error_message: Optional[str]
    
    created_at: datetime
    updated_at: datetime
    published_at: Optional[datetime]
    
    logs: List[ProcessingLogResponse] = []
    
    class Config:
        from_attributes = True
