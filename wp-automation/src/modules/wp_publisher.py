# -*- coding: utf-8 -*-
"""
Module D: WP Uploader — WordPress REST API
==========================================
Đăng bài qua REST API với Application Password:

    post = await wp_publisher.publish(config, article, keyword_set)

Các bước:
1. Map category slug -> ID (GET /categories, cache; tự tạo nếu thiếu & được phép).
2. Upload ảnh .webp lên Media Library (alt text LSI), ảnh đầu làm featured image.
3. Tạo post `draft` (hoặc theo config) kèm:
   - meta Rank Math: rank_math_title, rank_math_description, rank_math_focus_keyword
     (nếu site chưa mở meta này qua REST — mặc định Rank Math KHÔNG mở — WP sẽ
     lặng lẽ bỏ qua; playwright_seo.py sẽ điền lại qua wp-admin, nên không mất dữ liệu).
   - tags (tự tạo tag chưa có).
4. Trả về dict post của WP (id, link, status...) + cờ `rank_math_meta_sent`.

Auth: WP_USERNAME + WP_APP_PASSWORD (Basic Auth) — KHÔNG dùng mật khẩu admin thật.
"""

from __future__ import annotations

import base64
import logging
import mimetypes
import os
from pathlib import Path
from typing import Any, Optional

import httpx

logger = logging.getLogger("wp_publisher")

_category_cache: dict[str, int] = {}
_tag_cache: dict[str, int] = {}


class WPClient:
    """Client REST API mỏng, tái sử dụng được cho các module khác."""

    def __init__(self, config: dict[str, Any], transport: Optional[httpx.AsyncBaseTransport] = None):
        self.api_base: str = config["site"]["wp_api_base"].rstrip("/")
        user = os.environ.get("WP_USERNAME", "")
        app_pw = os.environ.get("WP_APP_PASSWORD", "")
        if not user or not app_pw:
            raise RuntimeError("Thiếu WP_USERNAME / WP_APP_PASSWORD trong .env")
        token = base64.b64encode(f"{user}:{app_pw}".encode()).decode()
        self._client = httpx.AsyncClient(
            headers={
                "Authorization": f"Basic {token}",
                # LiteSpeed (Azdigi) strip header Authorization -> gửi kèm bản sao qua
                # X-Authorization; mu-plugin rest-auth-header.php trên site dựng lại.
                "X-Authorization": f"Basic {token}",
            },
            timeout=60, follow_redirects=True, transport=transport)

    async def __aenter__(self) -> "WPClient":
        return self

    async def __aexit__(self, *exc) -> None:
        await self._client.aclose()

    # ---------------- helpers ----------------

    async def get_json(self, path: str, **params) -> Any:
        r = await self._client.get(f"{self.api_base}{path}", params=params)
        r.raise_for_status()
        return r.json()

    async def post_json(self, path: str, payload: dict) -> Any:
        r = await self._client.post(f"{self.api_base}{path}", json=payload)
        r.raise_for_status()
        return r.json()

    # ---------------- taxonomy ----------------

    async def category_id(self, slug: str, name: str = "",
                          create_if_missing: bool = True) -> Optional[int]:
        if slug in _category_cache:
            return _category_cache[slug]
        found = await self.get_json("/categories", slug=slug, per_page=100)
        if found:
            _category_cache[slug] = found[0]["id"]
            return found[0]["id"]
        if not create_if_missing:
            return None
        created = await self.post_json("/categories", {"slug": slug, "name": name or slug})
        _category_cache[slug] = created["id"]
        logger.info("Đã tạo category mới: %s (id=%s)", slug, created["id"])
        return created["id"]

    async def tag_ids(self, tags: list[str]) -> list[int]:
        ids: list[int] = []
        for tag in tags[:6]:
            key = tag.strip().lower()
            if not key:
                continue
            if key in _tag_cache:
                ids.append(_tag_cache[key])
                continue
            found = await self.get_json("/tags", search=key, per_page=20)
            match = next((t for t in found if t["name"].lower() == key), None)
            if match is None:
                try:
                    match = await self.post_json("/tags", {"name": tag.strip()})
                except httpx.HTTPStatusError as e:
                    # term_exists (race) -> đọc lại
                    if e.response.status_code == 400 and "term_exists" in e.response.text:
                        data = e.response.json()
                        match = {"id": data.get("data", {}).get("term_id")}
                    else:
                        logger.warning("Tạo tag '%s' lỗi: %s", tag, e)
                        continue
            if match and match.get("id"):
                _tag_cache[key] = match["id"]
                ids.append(match["id"])
        return ids

    # ---------------- media ----------------

    async def upload_media(self, file_path: str, alt: str = "",
                           caption: str = "") -> Optional[dict]:
        p = Path(file_path)
        if not p.exists():
            logger.warning("Ảnh không tồn tại: %s", file_path)
            return None
        mime = mimetypes.guess_type(p.name)[0] or "image/webp"
        r = await self._client.post(
            f"{self.api_base}/media",
            content=p.read_bytes(),
            headers={
                "Content-Disposition": f'attachment; filename="{p.name}"',
                "Content-Type": mime,
            })
        r.raise_for_status()
        media = r.json()
        # cập nhật alt/caption (upload endpoint không nhận trực tiếp)
        if alt or caption:
            await self.post_json(f"/media/{media['id']}", {
                "alt_text": alt, "caption": caption, "title": alt})
        logger.info("Upload media #%s: %s", media["id"], p.name)
        return media

    # ---------------- post ----------------

    async def create_post(self, payload: dict) -> dict:
        return await self.post_json("/posts", payload)

    async def get_post(self, post_id: int) -> dict:
        return await self.get_json(f"/posts/{post_id}", context="edit")


# ----------------------------------------------------------------------------
# Entry point
# ----------------------------------------------------------------------------

async def publish(config: dict, article: dict, keyword_set: dict,
                  transport: Optional[httpx.AsyncBaseTransport] = None) -> Optional[dict]:
    """Upload media + tạo post. Trả về dict post WP hoặc None nếu lỗi."""
    status = (os.environ.get("POST_STATUS_OVERRIDE")
              or config.get("site", {}).get("default_post_status", "draft"))
    rm = article.get("rank_math", {})

    try:
        async with WPClient(config, transport=transport) as wp:
            # 1) category
            cat_cfg = next((c for c in config.get("categories", [])
                            if c["slug"] == keyword_set.get("category")), {})
            cat_id = cat_cfg.get("wp_category_id")
            if not cat_id:
                cat_id = await wp.category_id(
                    keyword_set.get("category", "tin-tuc"),
                    name=keyword_set.get("category_name", ""))

            # 2) media
            featured_id = None
            content_html = article.get("content_html", "")
            for i, img in enumerate(article.get("images", []) or []):
                media = None
                try:
                    media = await wp.upload_media(img["path"], img.get("alt", ""),
                                                  img.get("caption", ""))
                except Exception as e:  # noqa: BLE001
                    logger.warning("Upload ảnh '%s' lỗi: %s", img.get("filename"), e)
                if media is None:
                    continue
                if i == 0:
                    featured_id = media["id"]
                else:  # ảnh phụ chèn sau H2 đầu tiên
                    img_tag = (f'<figure><img src="{media["source_url"]}" '
                               f'alt="{img.get("alt", "")}" loading="lazy"/></figure>')
                    idx = content_html.find("</h2>")
                    content_html = (content_html[: idx + 5] + img_tag + content_html[idx + 5:]
                                    if idx >= 0 else content_html + img_tag)

            # 3) post + Rank Math meta (best-effort qua REST)
            payload = {
                "title": article["title"],
                "slug": article.get("slug", ""),
                "content": content_html,
                "excerpt": article.get("sapo", "")[:300],
                "status": status,
                "categories": [cat_id] if cat_id else [],
                "tags": await wp.tag_ids(article.get("tags", [])),
                "meta": {
                    "rank_math_title": rm.get("seo_title", ""),
                    "rank_math_description": rm.get("meta_description", ""),
                    "rank_math_focus_keyword": rm.get("focus_keyword", ""),
                },
            }
            if featured_id:
                payload["featured_media"] = featured_id

            post = await wp.create_post(payload)

            # 4) xác minh meta có được nhận qua REST không
            meta_sent = False
            try:
                fresh = await wp.get_post(post["id"])
                meta_sent = bool(
                    fresh.get("meta", {}).get("rank_math_focus_keyword"))
            except Exception:  # noqa: BLE001
                pass
            post["rank_math_meta_sent"] = meta_sent
            if not meta_sent:
                logger.info("Rank Math meta chưa vào qua REST — playwright_seo sẽ điền qua wp-admin.")

            logger.info("Tạo post #%s [%s] '%s' -> %s",
                        post.get("id"), post.get("status"),
                        article["title"], post.get("link"))
            return post

    except httpx.HTTPStatusError as e:
        logger.error("WP REST lỗi %s: %s", e.response.status_code, e.response.text[:300])
    except Exception as e:  # noqa: BLE001
        logger.error("Publish lỗi: %s", e)
    return None
