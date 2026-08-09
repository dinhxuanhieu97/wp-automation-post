# -*- coding: utf-8 -*-
"""
Module C: Media Handling
========================
Tìm ảnh minh họa (Pexels → Unsplash → placeholder tự vẽ), tối ưu chuẩn web:

    images = await media_optimizer.prepare_images(config, keyword_set)
    # -> [{"path": ".../may-lanh-inverter.webp", "alt": "...", "filename": ...,
    #      "caption": ..., "source": "pexels|unsplash|placeholder"}]

Chuẩn đầu ra:
- Định dạng .webp, chiều rộng tối đa 1200px (giữ tỉ lệ), quality ~82.
- Tên file = slug của focus keyword (`slug-tu-khoa.webp`, ảnh 2 trở đi thêm -2, -3...).
- Alt text tự động chứa từ khóa LSI (mỗi ảnh một biến thể, không lặp).
"""

from __future__ import annotations

import io
import logging
import os
import random
from pathlib import Path
from typing import Any, Optional

import httpx
from PIL import Image, ImageDraw
from slugify import slugify

logger = logging.getLogger("media_optimizer")

PEXELS_SEARCH_URL = "https://api.pexels.com/v1/search"
UNSPLASH_SEARCH_URL = "https://api.unsplash.com/search/photos"

# Từ khóa tiếng Việt -> query tiếng Anh cho kho ảnh quốc tế (ảnh stock VN hiếm)
EN_QUERY_HINTS = {
    "may lanh": "air conditioner modern living room",
    "dieu hoa": "air conditioner modern living room",
    "tu lanh": "refrigerator modern kitchen",
    "tu dong": "freezer appliance store",
    "quat": "electric fan home interior",
    "tivi": "smart tv living room",
    "loa": "soundbar speaker living room",
    "bep tu": "induction cooktop modern kitchen",
    "noi chien": "air fryer kitchen counter",
    "may rua bat": "dishwasher modern kitchen",
    "may giat": "washing machine laundry room",
    "may loc": "air purifier home interior",
    "hut bui": "vacuum cleaner home",
    "world cup": "football fans watching tv living room",
    "bong da": "football fans watching tv living room",
}


def _en_query(focus_keyword: str) -> str:
    from modules.sitemap_links import strip_accents
    nk = strip_accents(focus_keyword.lower())
    # chọn hint khớp DÀI nhất — "world cup" (9 ký tự) thắng "tivi" (4 ký tự)
    matches = [(len(vi), en) for vi, en in EN_QUERY_HINTS.items() if vi in nk]
    if matches:
        return max(matches)[1]
    return "modern home appliance interior"


# ----------------------------------------------------------------------------
# Nguồn ảnh
# ----------------------------------------------------------------------------

async def _search_pexels(client: httpx.AsyncClient, query: str, n: int) -> list[str]:
    key = os.environ.get("PEXELS_API_KEY")
    if not key:
        return []
    try:
        r = await client.get(PEXELS_SEARCH_URL, params={
            "query": query, "per_page": n * 2, "orientation": "landscape"},
            headers={"Authorization": key}, timeout=20)
        r.raise_for_status()
        photos = r.json().get("photos", [])
        random.shuffle(photos)
        return [p["src"]["large2x"] for p in photos[:n]]
    except Exception as e:  # noqa: BLE001
        logger.warning("Pexels lỗi: %s", e)
        return []


async def _search_unsplash(client: httpx.AsyncClient, query: str, n: int) -> list[str]:
    key = os.environ.get("UNSPLASH_ACCESS_KEY")
    if not key:
        return []
    try:
        r = await client.get(UNSPLASH_SEARCH_URL, params={
            "query": query, "per_page": n * 2, "orientation": "landscape"},
            headers={"Authorization": f"Client-ID {key}"}, timeout=20)
        r.raise_for_status()
        results = r.json().get("results", [])
        random.shuffle(results)
        return [p["urls"]["regular"] for p in results[:n]]
    except Exception as e:  # noqa: BLE001
        logger.warning("Unsplash lỗi: %s", e)
        return []


def _make_placeholder(width: int = 1200, height: int = 675) -> Image.Image:
    """Ảnh gradient thương hiệu khi không có API key/ảnh phù hợp
    (tránh đăng bài trắng ảnh; nên thay bằng ảnh thật khi duyệt draft)."""
    img = Image.new("RGB", (width, height))
    draw = ImageDraw.Draw(img)
    top, bottom = (13, 71, 161), (2, 136, 209)  # xanh điện máy
    for y in range(height):
        t = y / height
        color = tuple(int(a + (b - a) * t) for a, b in zip(top, bottom))
        draw.line([(0, y), (width, y)], fill=color)
    draw.ellipse([width * 0.75, -height * 0.3, width * 1.2, height * 0.5],
                 fill=(255, 255, 255, 30))
    return img


# ----------------------------------------------------------------------------
# Tối ưu ảnh
# ----------------------------------------------------------------------------

def optimize_to_webp(raw: bytes | Image.Image, out_path: Path,
                     max_width: int = 1200, quality: int = 82) -> Path:
    """Resize (giữ tỉ lệ) về <= max_width, lưu .webp."""
    img = raw if isinstance(raw, Image.Image) else Image.open(io.BytesIO(raw))
    if img.mode not in ("RGB", "RGBA"):
        img = img.convert("RGB")
    if img.width > max_width:
        new_h = round(img.height * max_width / img.width)
        img = img.resize((max_width, new_h), Image.LANCZOS)
    out_path.parent.mkdir(parents=True, exist_ok=True)
    img.save(out_path, "WEBP", quality=quality, method=6)
    return out_path


def build_alt_texts(keyword_set: dict, n: int) -> list[str]:
    """Mỗi ảnh một alt khác nhau: ảnh 1 dùng focus keyword, các ảnh sau dùng LSI."""
    focus = keyword_set["focus_keyword"]
    lsi = [k for k in keyword_set.get("lsi_keywords", []) if k.lower() != focus.lower()]
    alts = [focus.capitalize()]
    for i in range(1, n):
        alts.append((lsi[(i - 1) % len(lsi)] if lsi else f"{focus} {i + 1}").capitalize())
    return alts


# ----------------------------------------------------------------------------
# Entry point
# ----------------------------------------------------------------------------

def _local_gemini_images(images_dir: Path, slug: str) -> list[Path]:
    """Ảnh Gemini local theo thứ tự featured -> __1 -> __2 ... (nếu có).

    Quy ước tên file: {slug}__featured.(png|jpg|jpeg|webp), {slug}__1.*, ...
    """
    if not images_dir.exists():
        return []
    exts = (".png", ".jpg", ".jpeg", ".webp")
    found: list[Path] = []
    for ext in exts:  # featured trước
        p = images_dir / f"{slug}__featured{ext}"
        if p.exists():
            found.append(p)
            break
    i = 1  # rồi tới ảnh thân bài __1, __2, ...
    while True:
        hit = next((images_dir / f"{slug}__{i}{ext}" for ext in exts
                    if (images_dir / f"{slug}__{i}{ext}").exists()), None)
        if hit is None:
            break
        found.append(hit)
        i += 1
    return found


async def prepare_images(config: dict, keyword_set: dict,
                         out_dir: Optional[Path] = None) -> list[dict[str, Any]]:
    media_cfg = config.get("media", {})
    n = media_cfg.get("images_per_post", 2)
    max_w = media_cfg.get("max_width_px", 1200)
    quality = media_cfg.get("webp_quality", 82)
    focus = keyword_set["focus_keyword"]
    base_slug = slugify(focus)[:60]
    out_dir = out_dir or (Path(__file__).resolve().parents[2] / "output" / "media")

    # (1) ƯU TIÊN ảnh Gemini local trong images/  (khớp theo slug)
    images_dir = Path(media_cfg.get("gemini_images_dir")
                      or (Path(__file__).resolve().parents[2] / "images"))
    local = _local_gemini_images(images_dir, base_slug)
    if local:
        logger.info("Tìm thấy %d ảnh Gemini local cho '%s' (ưu tiên dùng).", len(local), base_slug)

    # (2) THIẾU bao nhiêu thì lấy Pexels -> Unsplash bù
    need_remote = max(0, n - len(local))
    urls: list[str] = []
    async with httpx.AsyncClient(follow_redirects=True) as client:
        remote_source = "placeholder"
        if need_remote > 0:
            query = _en_query(focus)
            if media_cfg.get("image_source", "pexels") == "pexels":
                urls = await _search_pexels(client, query, need_remote)
                remote_source = "pexels" if urls else remote_source
            if not urls:
                urls = await _search_unsplash(client, query, need_remote)
                remote_source = "unsplash" if urls else remote_source

        images: list[dict] = []
        alts = build_alt_texts(keyword_set, n)
        ri = 0  # con trỏ ảnh remote
        for i in range(n):
            filename = f"{base_slug}.webp" if i == 0 else f"{base_slug}-{i + 1}.webp"
            out_path = out_dir / filename
            try:
                if i < len(local):
                    optimize_to_webp(Image.open(local[i]), out_path, max_w, quality)
                    img_source = "gemini"
                elif ri < len(urls):
                    r = await client.get(urls[ri], timeout=30)
                    r.raise_for_status()
                    optimize_to_webp(r.content, out_path, max_w, quality)
                    img_source = remote_source
                    ri += 1
                else:
                    optimize_to_webp(_make_placeholder(), out_path, max_w, quality)
                    img_source = "placeholder"
            except Exception as e:  # noqa: BLE001
                logger.warning("Tải/tối ưu ảnh %d lỗi (%s) — dùng placeholder.", i, e)
                optimize_to_webp(_make_placeholder(), out_path, max_w, quality)
                img_source = "placeholder"
            images.append({
                "path": str(out_path),
                "filename": filename,
                "alt": alts[i],
                "caption": alts[i],
                "source": img_source,
            })
    logger.info("Chuẩn bị %d ảnh cho '%s' (nguồn: %s)",
                len(images), focus, ", ".join({im['source'] for im in images}))
    return images
