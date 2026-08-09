# -*- coding: utf-8 -*-
"""
Module D+: Playwright SEO Config — Rank Math trong wp-admin
===========================================================
Sau khi wp_publisher tạo post qua REST API:

    await playwright_seo.optimize_and_publish(config, post, article, keyword_set)

Các bước (headless Chromium):
1. Đăng nhập /wp-login.php bằng WP_ADMIN_USERNAME / WP_ADMIN_PASSWORD.
2. Mở editor bài viết: /wp-admin/post.php?post=<ID>&action=edit (Gutenberg).
3. Mở panel Rank Math, điền Focus Keyword; mở tab Edit Snippet điền
   SEO Title + Meta Description (nếu REST đã ghi meta thành công thì chỉ xác minh).
4. Đọc điểm Rank Math. Điểm >= min_seo_score_to_publish (mặc định 80/100,
   ngưỡng "màu xanh" của Rank Math) -> bấm Publish/Update.
   Chưa đạt -> giữ draft + ghi logs/seo_report.log + chụp screenshot debug.

Selector Rank Math hay đổi theo version -> mỗi thao tác có NHIỀU selector dự
phòng; khi mọi selector fail sẽ chụp màn hình vào logs/screenshots/ để debug.
"""

from __future__ import annotations

import logging
import os
import re
from datetime import datetime
from pathlib import Path
from typing import Any, Optional

from playwright.async_api import async_playwright, Page, TimeoutError as PWTimeout

logger = logging.getLogger("playwright_seo")
seo_report = logging.getLogger("seo_report")

ROOT_DIR = Path(__file__).resolve().parents[2]
SCREENSHOT_DIR = ROOT_DIR / "logs" / "screenshots"

# --- Bộ selector dự phòng (Rank Math ~v1.0.2xx, Gutenberg) ---------------------
SEL = {
    "login_user": ["#user_login"],
    "login_pass": ["#user_pass"],
    "login_submit": ["#wp-submit"],
    "gutenberg_close_welcome": [
        ".edit-post-welcome-guide button[aria-label='Close']",
        ".components-modal__header button",
    ],
    "rank_math_open": [
        "button.rank-math-toolbar-score",
        ".rank-math-rm-icon",
        "button[aria-label='Rank Math SEO']",
    ],
    "focus_keyword_input": [
        ".rank-math-focus-keyword input",
        "#rank-math-focus-keyword input",
        "input[placeholder*='Focus Keyword' i]",
        "input[placeholder*='từ khóa' i]",
    ],
    "edit_snippet_btn": [
        "button.rank-math-edit-snippet",
        "button:has-text('Edit Snippet')",
        "button:has-text('Chỉnh sửa snippet')",
    ],
    "snippet_title": [
        ".serp-title input", "input.serp-title",
        "[class*='serp'] input[name*='title' i]",
    ],
    "snippet_description": [
        ".serp-description textarea", "textarea.serp-description",
        "[class*='serp'] textarea",
    ],
    "snippet_close": [
        ".rank-math-modal .components-modal__header button",
        "button[aria-label='Close']",
    ],
    "score_badge": [
        ".rank-math-toolbar-score",
        ".rank-math-seo-score",
    ],
    "publish_btn": [
        ".editor-post-publish-button__button",
        "button.editor-post-publish-button",
    ],
    "publish_confirm": [
        ".editor-post-publish-panel button.editor-post-publish-button",
        ".editor-post-publish-panel__header-publish-button button",
    ],
    "update_btn": [
        "button.editor-post-publish-button__button:has-text('Update')",
        "button:has-text('Cập nhật')",
    ],
}


async def _first(page: Page, keys: str, timeout: int = 4000):
    """Trả về locator đầu tiên khớp trong bộ selector dự phòng, None nếu hết."""
    for sel in SEL[keys]:
        loc = page.locator(sel).first
        try:
            await loc.wait_for(state="visible", timeout=timeout)
            return loc
        except PWTimeout:
            continue
    return None


async def _screenshot(page: Page, label: str) -> str:
    SCREENSHOT_DIR.mkdir(parents=True, exist_ok=True)
    path = SCREENSHOT_DIR / f"{label}-{datetime.now():%Y%m%d-%H%M%S}.png"
    try:
        await page.screenshot(path=str(path), full_page=False)
    except Exception:  # noqa: BLE001
        return ""
    return str(path)


async def _fill(page: Page, keys: str, value: str) -> bool:
    loc = await _first(page, keys)
    if loc is None:
        return False
    await loc.click()
    await loc.fill("")
    await loc.type(value, delay=15)
    return True


async def _read_score(page: Page) -> Optional[int]:
    """Đọc điểm Rank Math (0-100) từ toolbar badge."""
    loc = await _first(page, "score_badge", timeout=8000)
    if loc is None:
        return None
    text = (await loc.inner_text()).strip()
    m = re.search(r"(\d{1,3})\s*/\s*100", text) or re.search(r"\b(\d{1,3})\b", text)
    return int(m.group(1)) if m else None


# ----------------------------------------------------------------------------
# Entry point
# ----------------------------------------------------------------------------

async def optimize_and_publish(config: dict, post: dict, article: dict,
                               keyword_set: dict) -> dict[str, Any]:
    """Trả về {"score": int|None, "published": bool, "action": str}."""
    site = config["site"]
    pub = config.get("publishing", {})
    min_score = pub.get("min_seo_score_to_publish", 80)
    admin_url = site.get("wp_admin_url", site["base_url"].rstrip("/") + "/wp-admin")
    login_url = site["base_url"].rstrip("/") + "/wp-login.php"
    user = os.environ.get("WP_ADMIN_USERNAME") or os.environ.get("WP_USERNAME", "")
    password = os.environ.get("WP_ADMIN_PASSWORD", "")
    rm = article.get("rank_math", {})
    focus = rm.get("focus_keyword", keyword_set.get("focus_keyword", ""))
    post_id = post.get("id")
    result: dict[str, Any] = {"score": None, "published": False, "action": "none"}

    if not user or not password:
        seo_report.warning("Post #%s: thiếu WP_ADMIN_USERNAME/PASSWORD — bỏ qua bước Rank Math UI.", post_id)
        return result

    async with async_playwright() as pw:
        browser = await pw.chromium.launch(
            headless=config.get("scraper", {}).get("headless", True),
            args=["--no-sandbox"])
        context = await browser.new_context(
            locale="vi-VN", viewport={"width": 1500, "height": 950})
        page = await context.new_page()
        try:
            # ---- 1. Login ----
            await page.goto(login_url, wait_until="domcontentloaded", timeout=45_000)
            u = await _first(page, "login_user", 10_000)
            if u is None:  # có thể đã đăng nhập sẵn qua cookie
                logger.info("Không thấy form login — thử vào thẳng wp-admin.")
            else:
                await u.fill(user)
                p = await _first(page, "login_pass")
                await p.fill(password)
                btn = await _first(page, "login_submit")
                await btn.click()
                await page.wait_for_load_state("domcontentloaded")
                if "wp-login.php" in page.url and "loggedout" not in page.url:
                    body = await page.content()
                    if "login_error" in body or "incorrect" in body.lower():
                        raise RuntimeError("Đăng nhập wp-admin thất bại (sai user/pass hoặc bị captcha/2FA)")

            # ---- 2. Mở editor ----
            await page.goto(f"{admin_url}/post.php?post={post_id}&action=edit",
                            wait_until="domcontentloaded", timeout=60_000)
            await page.wait_for_timeout(4_000)  # chờ Gutenberg khởi động
            close_btn = await _first(page, "gutenberg_close_welcome", 2_000)
            if close_btn:
                await close_btn.click()

            # ---- 3. Rank Math panel ----
            opener = await _first(page, "rank_math_open", 10_000)
            if opener is None:
                shot = await _screenshot(page, f"post{post_id}-no-rankmath")
                raise RuntimeError(f"Không tìm thấy nút Rank Math (screenshot: {shot})")
            await opener.click()
            await page.wait_for_timeout(1_500)

            if not await _fill(page, "focus_keyword_input", focus):
                shot = await _screenshot(page, f"post{post_id}-no-focus-input")
                raise RuntimeError(f"Không điền được Focus Keyword (screenshot: {shot})")
            await page.keyboard.press("Enter")   # xác nhận keyword pill
            await page.wait_for_timeout(1_000)

            # Edit Snippet: SEO title + meta description
            snip = await _first(page, "edit_snippet_btn", 5_000)
            if snip:
                await snip.click()
                await page.wait_for_timeout(800)
                await _fill(page, "snippet_title", rm.get("seo_title", ""))
                await _fill(page, "snippet_description", rm.get("meta_description", ""))
                closer = await _first(page, "snippet_close", 3_000)
                if closer:
                    await closer.click()
            else:
                logger.warning("Không thấy nút Edit Snippet — dựa vào meta REST/template.")

            await page.wait_for_timeout(5_000)  # chờ Rank Math chấm điểm lại

            # ---- 4. Đọc điểm & quyết định ----
            score = await _read_score(page)
            result["score"] = score
            logger.info("Post #%s điểm Rank Math: %s", post_id, score)

            if score is not None and score >= min_score and pub.get("publish_if_green", True):
                btn = await _first(page, "publish_btn", 8_000)
                if btn:
                    await btn.click()
                    confirm = await _first(page, "publish_confirm", 5_000)
                    if confirm:
                        await confirm.click()
                    await page.wait_for_timeout(3_000)
                    result.update(published=True, action="published")
                    logger.info("Post #%s PUBLISHED (score %s >= %s)", post_id, score, min_score)
            else:
                # lưu draft với meta vừa điền (Ctrl+S của Gutenberg)
                await page.keyboard.press("Control+s")
                await page.wait_for_timeout(2_500)
                result["action"] = "kept_draft"
                shot = await _screenshot(page, f"post{post_id}-score-{score}")
                seo_report.warning(
                    "Post #%s ('%s'): điểm SEO %s < %s -> GIỮ DRAFT. Focus kw: '%s'. Screenshot: %s",
                    post_id, article.get("title", ""), score, min_score, focus, shot)

        except Exception as e:  # noqa: BLE001
            shot = await _screenshot(page, f"post{post_id}-error")
            seo_report.error("Post #%s: Playwright SEO lỗi: %s (screenshot: %s)",
                             post_id, e, shot)
            result["action"] = "error"
        finally:
            await context.close()
            await browser.close()

    return result
