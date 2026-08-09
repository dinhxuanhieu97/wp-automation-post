# -*- coding: utf-8 -*-
"""
Sitemap Link Index — nguồn Internal Link cho ai_writer
======================================================
Tải `sitemap_index.xml` (Rank Math) của dienmaykimbien.com.vn, gom toàn bộ URL
bài viết / trang / danh mục, và cung cấp API tìm "cơ hội internal link" theo
ngữ cảnh bài viết:

    index = SitemapLinkIndex(config)
    await index.load()                       # có cache TTL, không spam site
    cands = index.find_candidates("máy lạnh inverter tiết kiệm điện", top_n=8)
    # -> [{"url": ..., "slug": ..., "anchor_hint": "vị trí đặt tủ lạnh hợp phong thủy",
    #      "type": "post", "score": 3.2}, ...]

Cách chấm điểm: token của slug (đã bỏ dấu) giao với token của keyword/chủ đề.
URL post được ưu tiên hơn category; trang chủ & trang chính sách bị loại.
"""

from __future__ import annotations

import asyncio
import json
import logging
import re
import time
import unicodedata
from pathlib import Path
from typing import Any, Optional
from urllib.parse import urlparse

import httpx
from lxml import etree

logger = logging.getLogger("sitemap_links")

SM_NS = {"sm": "http://www.sitemaps.org/schemas/sitemap/0.9"}

# Stopword tiếng Việt (dạng không dấu) — loại khỏi token slug khi so khớp
STOPWORDS = {
    "va", "la", "cua", "cho", "nen", "khi", "voi", "tai", "nha", "nam",
    "cach", "nhung", "dieu", "gi", "co", "den", "tu", "ve", "hop", "theo",
    "bang", "bao", "nhieu", "the", "nao", "toi", "trong", "tren", "duoi",
}

EXCLUDE_SLUGS = {
    "", "tin-tuc", "lien-he", "gioi-thieu", "chinh-sach-bao-mat",
    "dieu-khoan-su-dung", "sitemap", "gio-hang", "thanh-toan", "tai-khoan",
}


def strip_accents(text: str) -> str:
    text = text.replace("đ", "d").replace("Đ", "D")
    return unicodedata.normalize("NFKD", text).encode("ascii", "ignore").decode("ascii")


def tokens_of(text: str) -> set[str]:
    """Token không dấu, bỏ stopword, dài >= 2 ký tự."""
    raw = re.split(r"[^a-z0-9]+", strip_accents(text.lower()))
    return {t for t in raw if len(t) >= 2 and t not in STOPWORDS}


def slug_to_phrase(slug: str) -> str:
    """'vi-tri-dat-tu-lanh-hop-phong-thuy' -> 'vi tri dat tu lanh hop phong thuy'"""
    return slug.strip("/").replace("-", " ")


class SitemapLinkIndex:
    """Inventory URL nội bộ từ Rank Math sitemap, có cache file JSON (TTL)."""

    def __init__(self, config: dict[str, Any], cache_dir: Optional[Path] = None):
        site = config.get("site", {})
        self.sitemap_index_url: str = site.get(
            "sitemap_index", "https://dienmaykimbien.com.vn/sitemap_index.xml")
        self.base_host: str = urlparse(self.sitemap_index_url).netloc
        self.cache_ttl_sec: int = config.get("scraper", {}).get("sitemap_cache_ttl_sec", 6 * 3600)
        root = cache_dir or (Path(__file__).resolve().parents[2] / "output")
        root.mkdir(parents=True, exist_ok=True)
        self.cache_file = root / "sitemap_cache.json"
        self.entries: list[dict] = []  # {url, slug, type, tokens: [..]}

    # ------------------------------------------------------------------
    # Tải & parse
    # ------------------------------------------------------------------

    @staticmethod
    def _parse_locs(xml_bytes: bytes) -> list[str]:
        """Trả về mọi <loc> trong sitemap/sitemapindex (nhẫn nại với namespace)."""
        try:
            root = etree.fromstring(xml_bytes)
        except etree.XMLSyntaxError:
            # Một số host chèn BOM/khoảng trắng đầu file
            root = etree.fromstring(xml_bytes.strip())
        locs = root.findall(".//sm:loc", SM_NS)
        if not locs:  # sitemap không namespace
            locs = root.findall(".//loc")
        return [l.text.strip() for l in locs if l.text]

    @staticmethod
    def _sitemap_type(sitemap_url: str) -> str:
        name = urlparse(sitemap_url).path.lower()
        for t in ("post", "page", "category", "product", "local"):
            if t in name:
                return t
        return "other"

    async def _fetch(self, client: httpx.AsyncClient, url: str) -> Optional[bytes]:
        try:
            r = await client.get(url, timeout=30, follow_redirects=True,
                                 headers={"User-Agent": "Mozilla/5.0 (compatible; DMKB-Automation/1.0)"})
            r.raise_for_status()
            return r.content
        except Exception as e:  # noqa: BLE001
            logger.warning("Fetch sitemap failed %s: %s", url, e)
            return None

    async def load(self, force_refresh: bool = False) -> int:
        """Nạp inventory (ưu tiên cache còn hạn). Trả về số URL."""
        if not force_refresh and self._load_cache():
            return len(self.entries)

        async with httpx.AsyncClient() as client:
            index_xml = await self._fetch(client, self.sitemap_index_url)
            if index_xml is None:
                # offline / lỗi mạng -> dùng cache cũ bất kể TTL
                if self._load_cache(ignore_ttl=True):
                    logger.warning("Dùng cache sitemap cũ (site không truy cập được).")
                    return len(self.entries)
                return 0

            child_urls = self._parse_locs(index_xml)
            # Nếu URL cấp 1 đã là trang (sitemap đơn), coi chính nó là nguồn
            sitemap_children = [u for u in child_urls if u.endswith(".xml")] or [self.sitemap_index_url]

            results = await asyncio.gather(
                *(self._fetch(client, u) for u in sitemap_children))

        entries: list[dict] = []
        seen: set[str] = set()
        for sm_url, xml in zip(sitemap_children, results):
            if xml is None:
                continue
            sm_type = self._sitemap_type(sm_url)
            for page_url in self._parse_locs(xml):
                p = urlparse(page_url)
                if p.netloc != self.base_host:
                    continue
                slug = p.path.strip("/").split("/")[-1]
                if slug in EXCLUDE_SLUGS or page_url in seen:
                    continue
                seen.add(page_url)
                entries.append({
                    "url": page_url,
                    "slug": slug,
                    "type": sm_type,
                    "tokens": sorted(tokens_of(slug_to_phrase(slug))),
                })
        self.entries = entries
        self._save_cache()
        logger.info("Sitemap index: %d URL nội bộ (từ %d sitemap con)",
                    len(entries), len(sitemap_children))
        return len(entries)

    # ------------------------------------------------------------------
    # Cache
    # ------------------------------------------------------------------

    def _save_cache(self) -> None:
        payload = {"fetched_at": time.time(), "entries": self.entries}
        self.cache_file.write_text(json.dumps(payload, ensure_ascii=False), encoding="utf-8")

    def _load_cache(self, ignore_ttl: bool = False) -> bool:
        if not self.cache_file.exists():
            return False
        try:
            payload = json.loads(self.cache_file.read_text(encoding="utf-8"))
            age = time.time() - payload.get("fetched_at", 0)
            if not ignore_ttl and age > self.cache_ttl_sec:
                return False
            self.entries = payload.get("entries", [])
            return bool(self.entries)
        except Exception:  # noqa: BLE001
            return False

    # ------------------------------------------------------------------
    # Tìm cơ hội internal link
    # ------------------------------------------------------------------

    def find_candidates(self, topic_text: str, top_n: int = 8,
                        exclude_urls: Optional[set[str]] = None) -> list[dict]:
        """Chấm điểm mọi URL theo độ giao token với `topic_text` (keyword + LSI).

        Trả về top_n ứng viên: url, slug, anchor_hint (cụm từ tự nhiên từ slug),
        type, score. Post được cộng điểm nhẹ so với category/page.
        """
        topic_tokens = tokens_of(topic_text)
        if not topic_tokens:
            return []
        exclude_urls = exclude_urls or set()
        scored: list[dict] = []
        for e in self.entries:
            if e["url"] in exclude_urls:
                continue
            overlap = topic_tokens.intersection(e["tokens"])
            if not overlap:
                continue
            score = len(overlap) + (0.5 if e["type"] == "post" else 0.0)
            # slug càng "khớp trọn" topic càng tốt
            score += len(overlap) / max(len(e["tokens"]), 1)
            scored.append({
                "url": e["url"],
                "slug": e["slug"],
                "type": e["type"],
                "anchor_hint": slug_to_phrase(e["slug"]),
                "score": round(score, 2),
                "matched": sorted(overlap),
            })
        scored.sort(key=lambda x: x["score"], reverse=True)
        return scored[:top_n]


# ----------------------------------------------------------------------------
# CLI test nhanh: python -m modules.sitemap_links "máy lạnh inverter"
# ----------------------------------------------------------------------------

async def _main() -> None:
    import sys
    logging.basicConfig(level=logging.INFO)
    config = json.loads(
        (Path(__file__).resolve().parents[1] / "config.json").read_text(encoding="utf-8"))
    topic = sys.argv[1] if len(sys.argv) > 1 else "máy lạnh inverter tiết kiệm điện"
    index = SitemapLinkIndex(config)
    n = await index.load()
    print(f"Đã nạp {n} URL nội bộ")
    for c in index.find_candidates(topic):
        print(f"  {c['score']:>5}  [{c['type']}] {c['url']}  (anchor: {c['anchor_hint']})")


if __name__ == "__main__":
    asyncio.run(_main())
