from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.api.v1.articles import router as articles_router
from app.api.errors import setup_exception_handlers
from app.database import engine, Base

# Create database tables (in production, use Alembic migrations)
Base.metadata.create_all(bind=engine)

app = FastAPI(
    title="WordPress Auto Publisher API",
    description="Hệ thống tự động crawl, rewrite và đăng bài WordPress",
    version="1.0.0",
)

# Setup exception handlers
setup_exception_handlers(app)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Include routers
app.include_router(articles_router, prefix="/api/v1")


@app.get("/")
def root():
    return {
        "message": "WordPress Auto Publisher API",
        "docs": "/docs",
        "version": "1.0.0"
    }


@app.get("/health")
def health_check():
    return {"status": "healthy"}
