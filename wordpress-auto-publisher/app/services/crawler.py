"""Crawler service để crawl và extract nội dung từ URL."""
import trafilatura
import requests
from typing import Optional, Dict
from urllib.parse import urlparse


def crawl_url(url: str, use_playwright: bool = False) -> Dict[str, Optional[str]]:
    """
    Crawl URL và trích xuất nội dung chính.
    
    Args:
        url: URL cần crawl
        use_playwright: Nếu True, dùng Playwright cho trang JavaScript
    
    Returns:
        Dict với keys: title, content, author, date, url, success
    """
    try:
        if use_playwright:
            return _crawl_with_playwright(url)
        else:
            return _crawl_with_requests(url)
    except Exception as e:
        return {
            "title": None,
            "content": None,
            "error": f"Crawl failed: {str(e)}",
            "success": False
        }


def _crawl_with_requests(url: str) -> Dict[str, Optional[str]]:
    """Crawl using requests + trafilatura (for static pages)."""
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "Accept-Language": "en-US,en;q=0.5",
        "Accept-Encoding": "gzip, deflate",
        "Connection": "keep-alive",
    }
    
    response = requests.get(url, headers=headers, timeout=30)
    response.raise_for_status()
    
    # Extract content using trafilatura
    result = trafilatura.extract(
        response.text,
        output_format="json",
        include_comments=False,
        include_tables=True,
        deduplicate=True,
        url=url
    )
    
    if result:
        data = result if isinstance(result, dict) else {}
        return {
            "title": data.get("title", ""),
            "content": data.get("text", ""),
            "author": data.get("author", ""),
            "date": data.get("date", ""),
            "url": url,
            "success": True
        }
    else:
        return {
            "title": "",
            "content": "",
            "error": "Could not extract content with trafilatura",
            "success": False
        }


def _crawl_with_playwright(url: str) -> Dict[str, Optional[str]]:
    """Crawl using Playwright (for JavaScript-rendered pages)."""
    try:
        from playwright.sync_api import sync_playwright
        
        with sync_playwright() as p:
            browser = p.chromium.launch(headless=True)
            page = browser.new_page(
                user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
            )
            
            page.goto(url, wait_until="networkidle", timeout=30000)
            
            # Wait for content to load
            page.wait_for_load_state("domcontentloaded")
            
            # Get rendered HTML
            html = page.content()
            
            browser.close()
            
        # Extract using trafilatura from rendered HTML
        result = trafilatura.extract(
            html,
            output_format="json",
            include_comments=False,
            include_tables=True,
            deduplicate=True,
            url=url
        )
        
        if result:
            data = result if isinstance(result, dict) else {}
            return {
                "title": data.get("title") or "",
                "content": data.get("text") or "",
                "author": data.get("author") or "",
                "date": data.get("date") or "",
                "url": url,
                "success": True
            }
        else:
            return {
                "title": "",
                "content": "",
                "error": "Could not extract content after rendering",
                "success": False
            }
            
    except ImportError:
        return {
            "title": "",
            "content": "",
            "error": "Playwright not installed. Install with: pip install playwright",
            "success": False
        }


def _crawl_with_beautifulsoup(url: str, html: str) -> Dict[str, Optional[str]]:
    """Fallback extraction using BeautifulSoup for difficult sites."""
    from bs4 import BeautifulSoup
    
    soup = BeautifulSoup(html, 'html.parser')
    
    # Try to find main content
    title = soup.title.string if soup.title else ""
    
    # Common content selectors
    content_selectors = [
        'article', 'main', '.content', '.entry-content', '.post-content',
        '[role="main"]', '#content', '.single-content', '.post', '.entry'
    ]
    
    content = ""
    for selector in content_selectors:
        element = soup.select_one(selector)
        if element:
            # Extract text from paragraphs
            paragraphs = element.find_all('p')
            content = '\n\n'.join(p.get_text(strip=True) for p in paragraphs if len(p.get_text(strip=True)) > 50)
            if len(content) > 200:
                break
    
    # If no content found, try all paragraphs
    if not content:
        paragraphs = soup.find_all('p')
        content = '\n\n'.join(p.get_text(strip=True) for p in paragraphs if len(p.get_text(strip=True)) > 50)
    
    if content:
        return {
            "title": title or "",
            "content": content[:10000],  # Limit content length
            "author": "",
            "date": "",
            "url": url,
            "success": True
        }
    else:
        return {
            "title": title or "",
            "content": "",
            "error": "Could not extract content with BeautifulSoup",
            "success": False
        }


def crawl_with_fallback(url: str) -> Dict[str, Optional[str]]:
    """
    Try multiple extraction methods:
    1. requests + trafilatura (fastest)
    2. requests + beautifulsoup (if trafilatura fails)
    3. playwright + trafilatura (for JS-rendered pages)
    4. playwright + beautifulsoup (last resort)
    """
    import requests
    
    # Method 1: requests + trafilatura
    result = _crawl_with_requests(url)
    if result.get("success") and len(result.get("content", "")) > 200:
        return result
    
    # Method 2: requests + beautifulsoup
    if not result.get("success") or len(result.get("content", "")) < 200:
        try:
            headers = {
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
            }
            response = requests.get(url, headers=headers, timeout=30)
            result = _crawl_with_beautifulsoup(url, response.text)
            if result.get("success") and len(result.get("content", "")) > 200:
                return result
        except Exception as e:
            pass
    
    # Method 3: playwright + trafilatura
    result = _crawl_with_playwright(url)
    if result.get("success") and len(result.get("content", "")) > 200:
        return result
    
    # Method 4: playwright + beautifulsoup (last resort)
    try:
        from playwright.sync_api import sync_playwright
        with sync_playwright() as p:
            browser = p.chromium.launch(headless=True)
            page = browser.new_page()
            page.goto(url, wait_until="domcontentloaded", timeout=30000)
            html = page.content()
            browser.close()
        result = _crawl_with_beautifulsoup(url, html)
        if result.get("content"):
            result["success"] = True
            return result
    except:
        pass
    
    return result  # Return best effort result


def is_valid_url(url: str) -> bool:
    """Kiểm tra URL hợp lệ."""
    try:
        result = urlparse(url)
        return all([result.scheme, result.netloc])
    except:
        return False
