# -*- coding: utf-8 -*-
"""
Module A: Keyword Analyzer & Trend Scraper
==========================================
Quét Google Suggest + Google Trends (geo: VN) bằng Playwright,
map trend vào 6 danh mục cốt lõi của Điện Máy Kim Biên và xuất JSON:

    {
        "category": "dien-lanh",
        "focus_keyword": "máy lạnh inverter tiết kiệm điện",
        "lsi_keywords": ["máy lạnh inverter giá rẻ", ...],
        "search_intent": "transactional" | "informational",
        "trending_angle": "Mùa hè nắng nóng 40 độ"
    }

Chiến lược 2 lớp (chống gãy pipeline):
  1. Playwright: gõ từ khóa vào google.com.vn và đọc autocomplete (giống người thật).
  2. Fallback: gọi endpoint JSON `suggestqueries.google.com` qua httpx nếu browser lỗi.

Google Trends dùng trang https://trends.google.com/trending?geo=VN (realtime trends).
"""

from __future__ import annotations

import asyncio
import json
import logging
import random
import re
import unicodedata
from dataclasses import dataclass, field, asdict
from pathlib import Path
from typing import Any, Optional
from urllib.parse import quote

import httpx
from playwright.async_api import async_playwright, Browser, BrowserContext, Page, TimeoutError as PWTimeout

logger = logging.getLogger("trends_scraper")

# ----------------------------------------------------------------------------
# Hằng số & tiện ích ngôn ngữ tiếng Việt
# ----------------------------------------------------------------------------

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 Edg/125.0.0.0",
]

# Từ khóa nhận diện search intent (rule-based cho tiếng Việt)
TRANSACTIONAL_MARKERS = [
    "giá", "mua", "bán", "khuyến mãi", "giảm giá", "tốt nhất", "nên mua",
    "chính hãng", "trả góp", "rẻ", "so sánh", "review", "đánh giá", "top",
]
INFORMATIONAL_MARKERS = [
    "cách", "là gì", "tại sao", "vì sao", "hướng dẫn", "sửa", "lỗi",
    "vệ sinh", "bảo dưỡng", "sử dụng", "mẹo", "bao nhiêu", "có nên",
]


def strip_accents(text: str) -> str:
    """Bỏ dấu tiếng Việt để so khớp keyword không phân biệt dấu."""
    text = text.replace("đ", "d").replace("Đ", "D")
    return unicodedata.normalize("NFKD", text).encode("ascii", "ignore").decode("ascii")


def norm(text: str) -> str:
    """Chuẩn hóa: lowercase + bỏ dấu + gọn khoảng trắng."""
    return re.sub(r"\s+", " ", strip_accents(text.lower())).strip()


def jaccard(a: str, b: str) -> float:
    """Độ tương đồng token (0..1) giữa 2 keyword đã chuẩn hóa."""
    sa, sb = set(norm(a).split()), set(norm(b).split())
    if not sa or not sb:
        return 0.0
    return len(sa & sb) / len(sa | sb)


def classify_intent(keyword: str, lsi_keywords: list[str]) -> str:
    """Phân loại search intent dựa trên marker tiếng Việt trong keyword + LSI."""
    corpus = norm(keyword) + " " + " ".join(norm(k) for k in lsi_keywords)
    t_score = sum(1 for m in TRANSACTIONAL_MARKERS if norm(m) in corpus)
    i_score = sum(1 for m in INFORMATIONAL_MARKERS if norm(m) in corpus)
    return "transactional" if t_score >= i_score else "informational"


# ----------------------------------------------------------------------------
# Dataclass kết quả
# ----------------------------------------------------------------------------

@dataclass
class KeywordResult:
    category: str
    category_name: str
    focus_keyword: str
    lsi_keywords: list[str] = field(default_factory=list)
    search_intent: str = "informational"
    trending_angle: str = ""
    source: str = "google_suggest"  # google_suggest | google_trends
    score: float = 0.0

    def to_dict(self) -> dict[str, Any]:
        return asdict(self)


# ----------------------------------------------------------------------------
# Trend Scraper chính
# ----------------------------------------------------------------------------

class TrendScraper:
    """Quét Google Suggest + Google Trends bằng Playwright (async)."""

    def __init__(self, config: dict[str, Any]):
        self.config = config
        self.scraper_cfg = config.get("scraper", {})
        self.categories: list[dict] = config.get("categories", [])
        self.headless: bool = self.scraper_cfg.get("headless", True)
        self.locale: str = self.scraper_cfg.get("locale", "vi-VN")
        self.google_domain: str = self.scraper_cfg.get("google_domain", "google.com.vn")
        self.suggest_variants: list[str] = self.scraper_cfg.get(
            "suggest_variants", ["", " giá", " tốt nhất", " 2026"]
        )
        self.max_kw: int = self.scraper_cfg.get("max_keywords_per_category", 10)
        self.delay_range: tuple = tuple(self.scraper_cfg.get("request_delay_range_sec", [2, 5]))

        self._browser: Optional[Browser] = None
        self._context: Optional[BrowserContext] = None
        self._pw = None

    # ------------------------------------------------------------------
    # Vòng đời browser
    # ------------------------------------------------------------------

    async def __aenter__(self) -> "TrendScraper":
        await self.start()
        return self

    async def __aexit__(self, *exc) -> None:
        await self.close()

    async def start(self) -> None:
        self._pw = await async_playwright().start()
        self._browser = await self._pw.chromium.launch(
            headless=self.headless,
            args=["--disable-blink-features=AutomationControlled", "--no-sandbox"],
        )
        self._context = await self._browser.new_context(
            locale=self.locale,
            user_agent=random.choice(USER_AGENTS),
            viewport={"width": 1366, "height": 850},
            timezone_id="Asia/Ho_Chi_Minh",
            geolocation={"latitude": 10.7769, "longitude": 106.7009},  # TP.HCM
            permissions=["geolocation"],
        )
        # Ẩn dấu hiệu webdriver
        await self._context.add_init_script(
            "Object.defineProperty(navigator, 'webdriver', {get: () => undefined});"
        )
        logger.info("Playwright browser started (headless=%s)", self.headless)

    async def close(self) -> None:
        for closer in (self._context, self._browser):
            try:
                if closer:
                    await closer.close()
            except Exception:  # noqa: BLE001
                pass
        if self._pw:
            await self._pw.stop()
        logger.info("Playwright browser closed")

    async def _human_delay(self) -> None:
        await asyncio.sleep(random.uniform(*self.delay_range))

    # ------------------------------------------------------------------
    # Google Suggest — lớp 1: Playwright gõ thật trên google.com.vn
    # ------------------------------------------------------------------

    async def suggest_via_browser(self, query: str) -> list[str]:
        """Gõ `query` vào ô tìm kiếm Google và đọc danh sách autocomplete."""
        page: Page = await self._context.new_page()
        try:
            await page.goto(f"https://www.{self.google_domain}/?hl=vi&gl=vn",
                            wait_until="domcontentloaded", timeout=30_000)
            # Đóng popup consent nếu có
            for sel in ("button:has-text('Chấp nhận tất cả')", "button:has-text('Accept all')",
                        "#L2AGLb"):
                try:
                    await page.click(sel, timeout=2_000)
                    break
                except PWTimeout:
                    continue

            box = page.locator("textarea[name='q'], input[name='q']").first
            await box.click(timeout=10_000)
            await box.press_sequentially(query, delay=random.randint(60, 140))
            await page.wait_for_timeout(1_200)

            items = await page.locator("ul[role='listbox'] li span").all_inner_texts()
            suggestions = []
            for t in items:
                t = re.sub(r"\s+", " ", t).strip()
                if t and len(t) > 3 and norm(query.split()[0]) in norm(t):
                    suggestions.append(t)
            # unique giữ thứ tự
            seen: set[str] = set()
            out = [s for s in suggestions if not (norm(s) in seen or seen.add(norm(s)))]
            logger.debug("Browser suggest '%s' -> %d items", query, len(out))
            return out
        except Exception as e:  # noqa: BLE001
            logger.warning("Browser suggest failed for '%s': %s", query, e)
            return []
        finally:
            await page.close()

    # ------------------------------------------------------------------
    # Google Suggest — lớp 2 (fallback): endpoint JSON qua httpx
    # ------------------------------------------------------------------

    @staticmethod
    async def suggest_via_api(query: str) -> list[str]:
        """Fallback: gọi suggestqueries.google.com (client=firefox trả JSON sạch)."""
        url = (
            "https://suggestqueries.google.com/complete/search"
            f"?client=firefox&hl=vi&gl=vn&q={quote(query)}"
        )
        try:
            async with httpx.AsyncClient(timeout=15) as client:
                r = await client.get(url, headers={"User-Agent": random.choice(USER_AGENTS)})
                r.raise_for_status()
                data = json.loads(r.content.decode("utf-8", errors="ignore"))
                return [s for s in data[1] if isinstance(s, str)]
        except Exception as e:  # noqa: BLE001
            logger.warning("API suggest failed for '%s': %s", query, e)
            return []

    async def get_suggestions(self, query: str) -> list[str]:
        """Suggest với chiến lược browser-first, API-fallback."""
        suggestions = await self.suggest_via_browser(query)
        if not suggestions:
            suggestions = await self.suggest_via_api(query)
        return suggestions

    # ------------------------------------------------------------------
    # Google Trends realtime (geo=VN)
    # ------------------------------------------------------------------

    async def fetch_trending_now(self) -> list[str]:
        """Cào danh sách chủ đề đang trending tại VN từ trends.google.com."""
        hours = self.scraper_cfg.get("trends_hours", 24)
        url = f"https://trends.google.com/trending?geo={self.scraper_cfg.get('trends_geo', 'VN')}&hours={hours}&hl=vi"
        page: Page = await self._context.new_page()
        trends: list[str] = []
        try:
            await page.goto(url, wait_until="networkidle", timeout=45_000)
            await page.wait_for_timeout(2_000)
            # Bảng trend: mỗi hàng có ô tên trend (cột đầu tiên)
            rows = page.locator("table tbody tr")
            count = await rows.count()
            for i in range(min(count, 50)):
                cell_text = await rows.nth(i).locator("td").nth(1).inner_text()
                title = cell_text.strip().split("\n")[0].strip()
                if title:
                    trends.append(title)
            logger.info("Google Trends VN: %d trending topics", len(trends))
        except Exception as e:  # noqa: BLE001
            logger.warning("Google Trends scrape failed: %s", e)
        finally:
            await page.close()
        return trends

    # ------------------------------------------------------------------
    # Ghép trend ↔ danh mục
    # ------------------------------------------------------------------

    def match_trends_to_category(self, trends: list[str], category: dict) -> list[str]:
        """Trả về các trend khớp với seed keywords của danh mục (so khớp không dấu)."""
        seeds = [norm(s) for s in category.get("seed_keywords", [])]
        # thêm từng từ đơn của seed để bắt trend rộng hơn (vd 'world cup')
        seed_tokens = {tok for s in seeds for tok in s.split() if len(tok) >= 3}
        matched = []
        for t in trends:
            nt = norm(t)
            if any(s in nt or nt in s for s in seeds) or \
               sum(1 for tok in seed_tokens if tok in nt) >= 2:
                matched.append(t)
        return matched

    @staticmethod
    def pick_trending_angle(category: dict, matched_trends: list[str]) -> str:
        """Chọn góc trending: ưu tiên trend thật, fallback về hook cấu hình sẵn."""
        if matched_trends:
            return matched_trends[0]
        hooks = category.get("trending_hooks", [])
        return random.choice(hooks) if hooks else ""

    # ------------------------------------------------------------------
    # Chấm điểm & chọn focus keyword
    # ------------------------------------------------------------------

    @staticmethod
    def score_keyword(kw: str, seed: str, is_trend_related: bool) -> float:
        """Heuristic: dài vừa phải (long-tail 3-7 từ), chứa modifier thương mại,
        liên quan trend thì cộng điểm."""
        words = kw.split()
        score = 0.0
        if 3 <= len(words) <= 7:
            score += 3.0
        elif len(words) > 7:
            score += 1.0
        nk = norm(kw)
        score += sum(0.8 for m in TRANSACTIONAL_MARKERS if norm(m) in nk)
        score += sum(0.5 for m in INFORMATIONAL_MARKERS if norm(m) in nk)
        if norm(seed) in nk:
            score += 1.5
        if is_trend_related:
            score += 2.5
        if re.search(r"\b20(2[5-9])\b", kw):  # có năm -> fresh content
            score += 1.0
        return round(score, 2)

    # ------------------------------------------------------------------
    # Pipeline chính cho 1 danh mục
    # ------------------------------------------------------------------

    async def analyze_category(self, category: dict, trends: list[str]) -> list[KeywordResult]:
        """Trả về tối đa `posts_per_category_per_day` bộ keyword cho 1 danh mục."""
        n_posts = self.config.get("publishing", {}).get("posts_per_category_per_day", 5)
        matched_trends = self.match_trends_to_category(trends, category)
        angle = self.pick_trending_angle(category, matched_trends)

        # Seed = seed cấu hình + trend khớp (trend được ưu tiên)
        seeds: list[str] = matched_trends[:3] + list(category.get("seed_keywords", []))
        candidates: dict[str, dict] = {}

        for seed in seeds[:8]:
            is_trend = seed in matched_trends
            for variant in self.suggest_variants:
                q = f"{seed}{variant}".strip()
                suggestions = await self.get_suggestions(q)
                await self._human_delay()
                for s in suggestions[: self.max_kw]:
                    key = norm(s)
                    if key in candidates:
                        continue
                    candidates[key] = {
                        "keyword": s,
                        "seed": seed,
                        "score": self.score_keyword(s, seed, is_trend),
                    }
            if len(candidates) >= self.max_kw * 4:
                break

        if not candidates:
            logger.warning("No suggestions for category %s — dùng seed keywords làm fallback",
                           category["slug"])
            candidates = {
                norm(s): {"keyword": s, "seed": s, "score": 1.0}
                for s in category.get("seed_keywords", [])
            }

        ranked = sorted(candidates.values(), key=lambda c: c["score"], reverse=True)

        results: list[KeywordResult] = []
        used: set[str] = set()
        for cand in ranked:
            if len(results) >= n_posts:
                break
            focus = cand["keyword"]
            nf = norm(focus)
            # tránh 2 bài trùng focus keyword gần giống nhau (substring hoặc Jaccard >= 0.7)
            if any(nf in u or u in nf or jaccard(nf, u) >= 0.7 for u in used):
                continue
            used.add(nf)
            lsi = [
                c["keyword"] for c in ranked
                if c["keyword"] != focus and norm(cand["seed"]) in norm(c["keyword"])
            ][:8]
            if len(lsi) < 4:  # bổ sung LSI từ pool chung
                extra = [c["keyword"] for c in ranked if c["keyword"] != focus and c["keyword"] not in lsi]
                lsi += extra[: 8 - len(lsi)]
            results.append(KeywordResult(
                category=category["slug"],
                category_name=category.get("name", category["slug"]),
                focus_keyword=focus,
                lsi_keywords=lsi,
                search_intent=classify_intent(focus, lsi),
                trending_angle=angle,
                source="google_trends" if cand["seed"] in matched_trends else "google_suggest",
                score=cand["score"],
            ))
        logger.info("Category %-20s -> %d keyword sets (angle: %s)",
                    category["slug"], len(results), angle or "-")
        return results

    # ------------------------------------------------------------------
    # Entry point
    # ------------------------------------------------------------------

    async def run(self, only_categories: Optional[list[str]] = None) -> list[dict]:
        """Chạy toàn bộ: fetch trends 1 lần, sau đó phân tích từng danh mục."""
        trends = await self.fetch_trending_now()
        all_results: list[dict] = []
        for cat in self.categories:
            if only_categories and cat["slug"] not in only_categories:
                continue
            try:
                results = await self.analyze_category(cat, trends)
                all_results.extend(r.to_dict() for r in results)
            except Exception as e:  # noqa: BLE001
                logger.error("Category %s failed: %s", cat.get("slug"), e)
        return all_results


# ----------------------------------------------------------------------------
# CLI: python -m modules.trends_scraper [--categories dien-lanh,the-thao] [--out file]
# ----------------------------------------------------------------------------

async def _main() -> None:
    import argparse

    parser = argparse.ArgumentParser(description="Quét trend & keyword cho Điện Máy Kim Biên")
    parser.add_argument("--config", default=str(Path(__file__).resolve().parents[1] / "config.json"))
    parser.add_argument("--categories", default="", help="Danh sách slug, phân cách bởi dấu phẩy")
    parser.add_argument("--out", default="", help="File JSON đầu ra (mặc định: in ra stdout)")
    parser.add_argument("--no-headless", action="store_true", help="Hiện cửa sổ browser khi debug")
    args = parser.parse_args()

    logging.basicConfig(level=logging.INFO,
                        format="%(asctime)s [%(levelname)s] %(name)s: %(message)s")

    config = json.loads(Path(args.config).read_text(encoding="utf-8"))
    if args.no_headless:
        config.setdefault("scraper", {})["headless"] = False

    only = [c.strip() for c in args.categories.split(",") if c.strip()] or None

    async with TrendScraper(config) as scraper:
        results = await scraper.run(only_categories=only)

    payload = json.dumps(results, ensure_ascii=False, indent=2)
    if args.out:
        Path(args.out).write_text(payload, encoding="utf-8")
        logger.info("Đã ghi %d keyword sets -> %s", len(results), args.out)
    else:
        print(payload)


if __name__ == "__main__":
    asyncio.run(_main())
