# WP Automation — Điện Máy Kim Biên

Hệ thống tự động nghiên cứu từ khóa → viết bài chuẩn SEO E-E-A-T → đăng lên
`dienmaykimbien.com.vn` (5 bài/danh mục/ngày, chia đều 5 khung giờ vàng).

## Kiến trúc

```
                    ┌──────────────── main.py (APScheduler, 5 khung giờ vàng) ────────────────┐
                    │                                                                          │
  [A] trends_scraper.py ──> [B] ai_writer.py ──> [C] media_optimizer.py ──> [D] wp_publisher.py ──> [D+] playwright_seo.py
  Playwright + Google        Claude/OpenAI API    Pexels/DALL-E → .webp       WP REST API           Playwright /wp-admin
  Suggest & Trends (VN)      E-E-A-T prompt        1200px, alt text LSI       Draft + Category      Rank Math/Yoast:
  → JSON keyword sets        H1/Sapo/H2-H3/         slug-tu-khoa.webp          + Tags + Media        focus kw, SEO title,
                             Table/FAQ/Internal                                                     meta desc → Green?
                             links từ sitemap                                                       → Publish : log lỗi
```

### Lựa chọn công nghệ

| Thành phần | Chọn | Lý do |
|---|---|---|
| Ngôn ngữ | Python 3.10+ `asyncio` | Playwright async + httpx cùng event loop, dễ mở rộng |
| Scraping | Playwright (Chromium) | Google Suggest/Trends render bằng JS; giả lập người dùng VN (locale vi-VN, timezone Asia/Ho_Chi_Minh, geolocation TP.HCM) |
| Chống gãy | 2 lớp: browser-first → API fallback | Nếu Google chặn browser, tự động chuyển sang endpoint `suggestqueries.google.com` — pipeline không bao giờ đứng |
| LLM | **claude_cli** (gói Pro/Max, 0đ phí API) → fallback Anthropic API → OpenAI | Chuỗi provider trong `config.json` (`content.llm_chain`); `claude_cli` gọi `claude -p` xác thực bằng subscription — cần cài Claude Code + `claude login` 1 lần |
| Đăng bài | WP REST API + Application Password | An toàn hơn login form; Playwright chỉ dùng cho phần REST API không làm được (điền Rank Math/Yoast, đọc điểm SEO) |
| Lập lịch | APScheduler `AsyncIOScheduler` | CronTrigger theo timezone VN, misfire_grace_time chống miss job |

## Cấu trúc thư mục

```
wp-automation/
├── src/
│   ├── modules/
│   │   ├── trends_scraper.py    # [A] ✅ hoàn thành — đã test
│   │   ├── sitemap_links.py     # [B] ✅ inventory 1047 URL từ Rank Math sitemap — đã test live
│   │   ├── ai_writer.py         # [B] ✅ E-E-A-T prompt + internal link + validator — 16 test pass
│   │   ├── media_optimizer.py   # [C] ✅ Pexels/Unsplash → webp 1200px, alt LSI — đã test
│   │   ├── wp_publisher.py      # [D] ✅ REST API + Rank Math meta best-effort — đã test (mock WP)
│   │   └── playwright_seo.py    # [D+] ✅ login wp-admin, điền Rank Math, publish nếu >= 80đ
│   ├── config.json              # site, 6 danh mục, khung giờ vàng, chuẩn content
│   └── main.py                  # ✅ orchestrator + scheduler (import "mềm" các module chưa có)
├── .env.example                 # → copy thành .env và điền key
├── requirements.txt
├── logs/                        # app.log + seo_report.log
└── output/                      # keyword sets + bài viết dry-run (audit)
```

## Cài đặt & chạy

```bash
cd wp-automation
python3 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
playwright install chromium
cp .env.example .env   # rồi điền API keys + WP credentials

# Test riêng Module A (in JSON ra màn hình):
cd src
python -m modules.trends_scraper --categories dien-lanh,the-thao

# Chạy 1 lượt đầy đủ, không đăng (bài lưu vào /output):
python main.py --once --dry-run

# Chạy thật theo lịch 5 khung giờ vàng:
python main.py
```

## Output mẫu Module A (đã chạy thử thật, 19/07/2026)

```json
{
  "category": "the-thao",
  "focus_keyword": "world cup 2026 chiếu trên kênh nào",
  "lsi_keywords": ["world cup 2026 giá vé", "lịch world cup 2026", "..."],
  "search_intent": "transactional",
  "trending_angle": "World Cup 2026",
  "source": "google_suggest",
  "score": 6.3
}
```

## Trạng thái test (chạy trong môi trường dev, 19/07/2026)

- `pytest tests/` → **38 pass / 1 skip** (skip = test Anthropic API thật, chỉ cần khi dùng fallback).
- Provider `claude_cli` đã verify LIVE: sinh trọn 1 bài 1.631 từ đạt chuẩn validator 100%
  (5 internal link ngữ cảnh vào URL thật của site, bảng so sánh, 3 FAQ, meta 126 ký tự có CTA).
- Đã verify live (read-only): sitemap Rank Math 1047 URL; REST API `/categories` mở.
- WP REST được mock hoàn toàn trong test — chưa có bài nào được tạo trên site thật.
- Chạy thật lần đầu nên dùng: `python main.py --once --dry-run` → duyệt bài trong `/output`
  → rồi mới `--once` (đăng draft) → cuối cùng bật scheduler.

## Ghi chú vận hành

- `main.py` import "mềm" các module B/C/D: module nào chưa viết xong thì pipeline
  bỏ qua bước đó và log cảnh báo — có thể chạy/kiểm thử từng phần ngay từ bây giờ.
- Điểm SEO chưa Green → bài giữ ở `draft` và lỗi ghi vào `logs/seo_report.log`.
- Delay ngẫu nhiên 2–5s giữa các request + gõ phím tốc độ người thật để giảm rủi ro bị Google chặn.
- Không commit `.env` (chứa WP admin password + API keys).
