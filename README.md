# DienMayKimBien — Multi-site WordPress Content Automation

Dự án tự động hoá nghiên cứu từ khóa, viết bài chuẩn SEO và đăng (ở trạng thái **draft**) lên
2 website WordPress:

- **Điện Máy Kim Biên** (`dienmaykimbien.com.vn`) — có pipeline tự động hoàn chỉnh trong
  [`wp-automation/`](./wp-automation).
- **Navycons** (`navycons.com`) — quy trình viết bài thủ công theo persona SEO Content Expert
  (chưa có script tự động).

> 📌 Toàn bộ ngữ cảnh vận hành chi tiết (credentials, cấu trúc danh mục, checklist SEO,
> các bug đã gặp và cách fix, quy trình publish...) nằm trong [`CLAUDE.md`](./CLAUDE.md) —
> đây là tài liệu tham chiếu chính, đọc trước khi thao tác trên bất kỳ site nào.

## ⚠️ Quy tắc quan trọng nhất

**Không bài viết nào được tự động publish.** Mọi bài do pipeline hoặc AI tạo ra đều dừng ở
trạng thái `draft` để chủ site tự review và bấm "Xuất bản" trên wp-admin. Xem chi tiết ở
mục 0 của `CLAUDE.md`.

## Cấu trúc thư mục

```
.
├── CLAUDE.md                  # Tài liệu vận hành đầy đủ cho cả 2 site (nguồn tham chiếu chính)
├── multi-site-config.json     # Config + credentials 2 site (KHÔNG commit, xem .gitignore)
├── wp-automation/              # Pipeline tự động của Điện Máy Kim Biên (bản THẬT, đang dùng)
│   ├── src/
│   │   ├── main.py             # Orchestrator + scheduler (5 khung giờ vàng/ngày)
│   │   ├── modules/             # trends_scraper, ai_writer, media_optimizer, wp_publisher,
│   │   │                        # playwright_seo, sitemap_links
│   │   └── config.json          # Site, danh mục, chuẩn nội dung, khung giờ
│   ├── requirements.txt
│   └── README.md                # Chi tiết kiến trúc + hướng dẫn chạy pipeline
├── wp-content/                 # mu-plugin (fix REST auth) + theme đang dùng trên site
├── template/                    # Asset/template tham khảo
├── wordpress-auto-publisher/    # ⚠️ Bản CŨ/bỏ dở — KHÔNG dùng, giữ lại để tham khảo
└── AutoPostSystems/              # ⚠️ Bản CŨ/bỏ dở — KHÔNG dùng, giữ lại để tham khảo
```

## Bắt đầu nhanh

```bash
cd wp-automation
python3 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
playwright install chromium
cp .env.example .env   # điền API keys + WP application password (không commit file này)

# Xem trước, không đăng bài:
python src/main.py --once --dry-run --categories thiet-bi-dien --posts 1

# Đăng thật (mặc định draft):
python src/main.py --once --categories thiet-bi-dien --posts 1
```

Chi tiết đầy đủ hơn (kiến trúc, biến môi trường, danh mục, lịch chạy) xem
[`wp-automation/README.md`](./wp-automation/README.md) và mục 2 của `CLAUDE.md`.

## Bảo mật

Các file sau chứa thông tin nhạy cảm và **không được commit** (đã khai báo trong
`.gitignore`):

- `multi-site-config.json` — credentials của cả 2 site.
- `wp-config.php` — thông tin kết nối database WordPress.
- `*.env` (mọi vị trí, trừ `*.env.example`) — API keys và application password.

Nếu vô tình commit nhầm một trong các file trên, phải thu hồi/đổi lại credential đó ngay
(application password trên wp-admin, API key trên nhà cung cấp) chứ không chỉ xoá khỏi commit
tiếp theo.
