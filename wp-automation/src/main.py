# -*- coding: utf-8 -*-
"""
MAIN ORCHESTRATOR — WP Automation cho dienmaykimbien.com.vn
===========================================================
Bộ điều phối pipeline:

    [Module A] trends_scraper  ->  [Module B] ai_writer  ->  [Module C] media_optimizer
        -> [Module D] wp_publisher (REST API)  ->  [Module D+] playwright_seo (Rank Math/Yoast)

Lịch chạy: 5 "khung giờ vàng" mỗi ngày (config.publishing.golden_hours).
Mỗi khung giờ đăng 1 bài / danh mục  =>  5 khung x 1 bài = 5 bài/danh mục/ngày.

Cách chạy:
    python src/main.py                 # bật scheduler, chạy nền theo khung giờ
    python src/main.py --once          # chạy ngay 1 lượt rồi thoát (test)
    python src/main.py --once --categories dien-lanh,the-thao
    python src/main.py --dry-run       # chỉ quét keyword + viết bài, KHÔNG đăng
"""

from __future__ import annotations

import argparse
import asyncio
import json
import logging
import os
import sys
from datetime import datetime
from pathlib import Path
from typing import Any, Optional
from zoneinfo import ZoneInfo

from dotenv import load_dotenv

# --- đường dẫn gốc dự án ---------------------------------------------------
SRC_DIR = Path(__file__).resolve().parent
ROOT_DIR = SRC_DIR.parent
sys.path.insert(0, str(SRC_DIR))

load_dotenv(ROOT_DIR / ".env")

from modules.trends_scraper import TrendScraper  # noqa: E402

# Các module dưới đây được import "mềm" để pipeline vẫn chạy được từng phần
# trong lúc dự án đang phát triển dần từng module.
def _soft_import(name: str):
    try:
        return __import__(f"modules.{name}", fromlist=[name])
    except Exception as e:  # noqa: BLE001
        logging.getLogger("main").warning("Module %s chưa sẵn sàng (%s) — bước này sẽ bị bỏ qua.", name, e)
        return None


ai_writer = _soft_import("ai_writer")
media_optimizer = _soft_import("media_optimizer")
wp_publisher = _soft_import("wp_publisher")
playwright_seo = _soft_import("playwright_seo")


# ----------------------------------------------------------------------------
# Logging: app.log (chung) + seo_report.log (kết quả kiểm tra SEO)
# ----------------------------------------------------------------------------

def setup_logging(config: dict[str, Any]) -> logging.Logger:
    log_cfg = config.get("logging", {})
    (ROOT_DIR / "logs").mkdir(exist_ok=True)

    fmt = logging.Formatter("%(asctime)s [%(levelname)s] %(name)s: %(message)s")
    root = logging.getLogger()
    root.setLevel(getattr(logging, log_cfg.get("level", "INFO")))

    stream = logging.StreamHandler()
    stream.setFormatter(fmt)
    root.addHandler(stream)

    app_file = logging.FileHandler(ROOT_DIR / log_cfg.get("app_log_file", "logs/app.log"),
                                   encoding="utf-8")
    app_file.setFormatter(fmt)
    root.addHandler(app_file)

    # Logger riêng cho báo cáo SEO (Module D ghi vào đây khi điểm chưa Green)
    seo_logger = logging.getLogger("seo_report")
    seo_handler = logging.FileHandler(ROOT_DIR / log_cfg.get("seo_report_file", "logs/seo_report.log"),
                                      encoding="utf-8")
    seo_handler.setFormatter(fmt)
    seo_logger.addHandler(seo_handler)
    seo_logger.propagate = True
    return logging.getLogger("main")


def load_config() -> dict[str, Any]:
    return json.loads((SRC_DIR / "config.json").read_text(encoding="utf-8"))


# ----------------------------------------------------------------------------
# Pipeline cho MỘT bài viết
# ----------------------------------------------------------------------------

async def process_one_post(config: dict, keyword_set: dict, dry_run: bool = False) -> Optional[dict]:
    """keyword_set (từ Module A) -> bài viết hoàn chỉnh đã đăng lên WP."""
    log = logging.getLogger("pipeline")
    focus = keyword_set["focus_keyword"]
    log.info(">>> Bắt đầu bài viết: [%s] %s", keyword_set["category"], focus)

    # ----- Module B: viết content chuẩn SEO -----
    if ai_writer is None:
        log.warning("Bỏ qua (ai_writer chưa có). Keyword set: %s", json.dumps(keyword_set, ensure_ascii=False))
        return None
    article = await ai_writer.generate_article(config, keyword_set)
    if not article:
        log.error("LLM không tạo được bài cho '%s'", focus)
        return None

    # ----- Module C: ảnh minh họa -----
    if media_optimizer is not None:
        try:
            article["images"] = await media_optimizer.prepare_images(config, keyword_set)
        except Exception as e:  # noqa: BLE001
            log.warning("Media lỗi (%s) — đăng bài không kèm ảnh mới.", e)
            article.setdefault("images", [])

    if dry_run:
        out = ROOT_DIR / "output" / f"{keyword_set['category']}-{datetime.now():%Y%m%d-%H%M%S}.json"
        out.parent.mkdir(exist_ok=True)
        out.write_text(json.dumps(article, ensure_ascii=False, indent=2), encoding="utf-8")
        log.info("[DRY-RUN] Đã lưu bài viết -> %s", out)
        return article

    # ----- Module D: đăng qua REST API -----
    if wp_publisher is None:
        log.warning("wp_publisher chưa có — dừng ở bước tạo content.")
        return article
    post = await wp_publisher.publish(config, article, keyword_set)
    if not post:
        log.error("Đăng bài thất bại: %s", focus)
        return None
    log.info("Đã tạo bài #%s (%s) trạng thái %s", post.get("id"), post.get("link"), post.get("status"))

    # ----- Module D+: Playwright cấu hình SEO plugin & publish nếu Green -----
    if playwright_seo is not None:
        try:
            await playwright_seo.optimize_and_publish(config, post, article, keyword_set)
        except Exception as e:  # noqa: BLE001
            logging.getLogger("seo_report").error(
                "Post #%s (%s): Playwright SEO config lỗi: %s", post.get("id"), focus, e)
    return post


# ----------------------------------------------------------------------------
# Một "lượt chạy" (1 khung giờ vàng): mỗi danh mục 1 bài
# ----------------------------------------------------------------------------

async def run_batch(only_categories: Optional[list[str]] = None,
                    posts_per_category: int = 1,
                    dry_run: bool = False) -> None:
    config = load_config()
    log = logging.getLogger("main")
    tz = ZoneInfo(config["site"].get("timezone", "Asia/Ho_Chi_Minh"))
    log.info("===== BATCH START %s =====", datetime.now(tz).isoformat(timespec="seconds"))

    # Module A: quét trend + keyword cho tất cả danh mục (1 lần / batch)
    async with TrendScraper(config) as scraper:
        keyword_sets = await scraper.run(only_categories=only_categories)

    if not keyword_sets:
        log.error("Không thu được keyword nào — dừng batch.")
        return

    # Lưu lại kết quả Module A để audit
    kw_file = ROOT_DIR / "output" / f"keywords-{datetime.now(tz):%Y%m%d-%H%M%S}.json"
    kw_file.parent.mkdir(exist_ok=True)
    kw_file.write_text(json.dumps(keyword_sets, ensure_ascii=False, indent=2), encoding="utf-8")
    log.info("Module A: %d keyword sets -> %s", len(keyword_sets), kw_file.name)

    # Nhóm theo danh mục, lấy N bài đầu mỗi danh mục cho khung giờ này
    by_cat: dict[str, list[dict]] = {}
    for ks in keyword_sets:
        by_cat.setdefault(ks["category"], []).append(ks)

    for cat, sets in by_cat.items():
        for ks in sets[:posts_per_category]:
            try:
                await process_one_post(load_config(), ks, dry_run=dry_run)
            except Exception as e:  # noqa: BLE001
                log.exception("Pipeline lỗi với '%s': %s", ks.get("focus_keyword"), e)
            await asyncio.sleep(5)  # giãn nhịp giữa các bài

    log.info("===== BATCH DONE =====")


# ----------------------------------------------------------------------------
# Scheduler: 5 khung giờ vàng / ngày
# ----------------------------------------------------------------------------

def start_scheduler(dry_run: bool = False) -> None:
    from apscheduler.schedulers.asyncio import AsyncIOScheduler
    from apscheduler.triggers.cron import CronTrigger

    config = load_config()
    log = logging.getLogger("main")
    tz = ZoneInfo(config["site"].get("timezone", "Asia/Ho_Chi_Minh"))
    golden_hours: list[str] = config.get("publishing", {}).get(
        "golden_hours", ["06:30", "09:00", "11:30", "15:00", "20:00"])

    scheduler = AsyncIOScheduler(timezone=tz)
    for slot in golden_hours:
        hh, mm = slot.split(":")
        scheduler.add_job(
            run_batch,
            CronTrigger(hour=int(hh), minute=int(mm), timezone=tz),
            kwargs={"posts_per_category": 1, "dry_run": dry_run},
            id=f"batch-{slot}",
            max_instances=1,
            coalesce=True,
            misfire_grace_time=1800,
        )
        log.info("Đã lên lịch batch lúc %s (%s) — 1 bài/danh mục", slot, tz.key)

    log.info("Scheduler đang chạy. Tổng: %d khung giờ x 1 bài/danh mục = %d bài/danh mục/ngày. Ctrl+C để dừng.",
             len(golden_hours), len(golden_hours))

    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)
    scheduler.start()
    try:
        loop.run_forever()
    except (KeyboardInterrupt, SystemExit):
        scheduler.shutdown()
        log.info("Scheduler stopped.")


# ----------------------------------------------------------------------------
# CLI
# ----------------------------------------------------------------------------

def main() -> None:
    parser = argparse.ArgumentParser(description="WP Automation — Điện Máy Kim Biên")
    parser.add_argument("--once", action="store_true",
                        help="Chạy ngay 1 lượt rồi thoát (không bật scheduler)")
    parser.add_argument("--dry-run", action="store_true",
                        help="Không đăng lên WP, chỉ lưu bài viết vào /output")
    parser.add_argument("--categories", default="",
                        help="Chỉ chạy các danh mục này (slug, cách nhau dấu phẩy)")
    parser.add_argument("--posts", type=int, default=1,
                        help="Số bài / danh mục trong lượt chạy --once (mặc định 1)")
    args = parser.parse_args()

    config = load_config()
    log = setup_logging(config)

    run_once = args.once or os.getenv("RUN_ONCE", "").lower() == "true"
    only = [c.strip() for c in args.categories.split(",") if c.strip()] or None

    if run_once:
        log.info("Chế độ RUN ONCE (dry_run=%s)", args.dry_run)
        asyncio.run(run_batch(only_categories=only,
                              posts_per_category=args.posts,
                              dry_run=args.dry_run))
    else:
        start_scheduler(dry_run=args.dry_run)


if __name__ == "__main__":
    main()
