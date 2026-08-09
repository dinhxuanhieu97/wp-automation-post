#!/usr/bin/env python3
"""
Manual API test script - Chạy để test API endpoints cơ bản.
Không yêu cầu Celery hay DB bên ngoài (dùng SQLite in-memory).

Usage:
    python test_api_manual.py
"""
import os
import sys
from unittest.mock import MagicMock, patch

# Set SQLite for testing BEFORE importing app modules
os.environ["DATABASE_URL"] = "sqlite:///./test.db"
os.environ["REDIS_URL"] = "redis://localhost:6379/0"

# Mock Celery before importing app
mock_celery_app = MagicMock()
mock_celery_app.delay = MagicMock(return_value=MagicMock(id="mock-task-id"))

# Patch celery tasks before import
sys.modules['app.core.celery_app'] = MagicMock()
sys.modules['app.tasks'] = MagicMock()
sys.modules['app.tasks.article_pipeline'] = MagicMock()
sys.modules['app.tasks.article_pipeline'].process_article_pipeline = mock_celery_app
sys.modules['app.tasks.article_pipeline'].publish_to_wordpress = mock_celery_app

import json
from fastapi.testclient import TestClient
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from sqlalchemy.pool import StaticPool

# Setup test environment
from app.database import Base, get_db
from app.main import app

# Create in-memory test database
SQLALCHEMY_DATABASE_URL = "sqlite:///:memory:"
engine = create_engine(
    SQLALCHEMY_DATABASE_URL,
    connect_args={"check_same_thread": False},
    poolclass=StaticPool,
)
TestingSessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base.metadata.create_all(bind=engine)

def override_get_db():
    db = TestingSessionLocal()
    try:
        yield db
    finally:
        db.close()

app.dependency_overrides[get_db] = override_get_db

client = TestClient(app)

def print_section(title):
    print(f"\n{'='*60}")
    print(f"  {title}")
    print(f"{'='*60}")

def print_response(response, expected_status=None):
    status = f"✓" if expected_status and response.status_code == expected_status else "✗"
    print(f"{status} Status: {response.status_code}")
    try:
        data = response.json()
        print(f"  Response: {json.dumps(data, indent=2, ensure_ascii=False)[:500]}...")
    except:
        print(f"  Response: {response.text[:500]}")

def run_tests():
    all_passed = True
    
    # Test 1: Health check
    print_section("1. Health Check")
    response = client.get("/health")
    print_response(response, 200)
    if response.status_code != 200:
        all_passed = False
    
    # Test 2: Create article - Valid URL
    print_section("2. Create Article (Valid URL)")
    response = client.post(
        "/api/v1/articles/",
        json={"source_url": "https://example.com/test-article-1"}
    )
    print_response(response, 201)
    if response.status_code != 201:
        all_passed = False
    else:
        article_id = response.json()["id"]
        print(f"  Created article ID: {article_id}")
    
    # Test 3: Create article - Invalid URL
    print_section("3. Create Article (Invalid URL - should fail)")
    response = client.post(
        "/api/v1/articles/",
        json={"source_url": "not-a-valid-url"}
    )
    print_response(response, 422)
    if response.status_code != 422:
        all_passed = False
    
    # Test 4: Create article - Duplicate URL
    print_section("4. Create Article (Duplicate - should fail)")
    response = client.post(
        "/api/v1/articles/",
        json={"source_url": "https://example.com/test-article-1"}
    )
    print_response(response, 409)
    if response.status_code != 409:
        all_passed = False
    
    # Test 5: List articles
    print_section("5. List Articles")
    response = client.get("/api/v1/articles/")
    print_response(response, 200)
    if response.status_code != 200:
        all_passed = False
    else:
        articles = response.json()
        print(f"  Total articles: {len(articles)}")
    
    # Test 6: Get specific article
    print_section("6. Get Article (ID: 1)")
    response = client.get("/api/v1/articles/1")
    print_response(response, 200)
    if response.status_code != 200:
        all_passed = False
    
    # Test 7: Get non-existent article
    print_section("7. Get Article (Non-existent - should fail)")
    response = client.get("/api/v1/articles/99999")
    print_response(response, 404)
    if response.status_code != 404:
        all_passed = False
    
    # Test 8: Update article
    print_section("8. Update Article (ID: 1)")
    response = client.put(
        "/api/v1/articles/1",
        json={
            "rewritten_title": "Updated Title",
            "slug": "my-test-article",
            "meta_title": "SEO Meta Title",
            "meta_description": "SEO Meta Description"
        }
    )
    print_response(response, 200)
    if response.status_code != 200:
        all_passed = False
    else:
        data = response.json()
        print(f"  Updated title: {data.get('rewritten_title')}")
        print(f"  Slug: {data.get('slug')}")
    
    # Test 9: List with pagination
    print_section("9. List with Pagination (limit=1)")
    response = client.get("/api/v1/articles/?limit=1")
    print_response(response, 200)
    if response.status_code != 200:
        all_passed = False
    
    # Test 10: List with invalid pagination
    print_section("10. List with Invalid Pagination (should fail)")
    response = client.get("/api/v1/articles/?skip=-1")
    print_response(response, 422)
    if response.status_code != 422:
        all_passed = False
    
    # Test 11: Publish article (should fail - not in draft_created status)
    print_section("11. Publish Article (Wrong status - should fail)")
    response = client.post("/api/v1/articles/1/publish")
    print_response(response, 422)
    if response.status_code != 422:
        all_passed = False
    
    # Test 12: Delete article
    print_section("12. Delete Article (ID: 1)")
    response = client.delete("/api/v1/articles/1")
    print_response(response, 204)
    if response.status_code != 204:
        all_passed = False
    
    # Test 13: Delete non-existent article
    print_section("13. Delete Article (Non-existent - should fail)")
    response = client.delete("/api/v1/articles/99999")
    print_response(response, 404)
    if response.status_code != 404:
        all_passed = False
    
    # Summary
    print_section("SUMMARY")
    if all_passed:
        print("✓ All tests passed!")
        return 0
    else:
        print("✗ Some tests failed!")
        return 1

if __name__ == "__main__":
    sys.exit(run_tests())
