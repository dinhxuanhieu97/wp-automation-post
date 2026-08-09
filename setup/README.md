# Setup Tool — Hướng dẫn cài đặt cho một site WordPress mới

Hướng dẫn này giúp bạn cắm pipeline tự động (`wp-automation/`) vào **một site WordPress
mới** (khác `dienmaykimbien.com.vn`/`navycons.com` đã cấu hình sẵn), từ REST API auth cho
tới chạy thử bài viết đầu tiên. Đọc [`../CLAUDE.md`](../CLAUDE.md) để hiểu ngữ cảnh vận
hành đầy đủ sau khi setup xong.

## Yêu cầu hệ thống

- WordPress đã cài **Rank Math** SEO (bắt buộc nếu muốn dùng checklist SEO trong CLAUDE.md).
- Quyền truy cập file server (cPanel File Manager, FTP, hoặc SSH) để upload mu-plugin.
- Python 3.10+ và PHP CLI (`php -v`) trên máy chạy tool.
- macOS/Linux/WSL có `curl`.

## Bước 1 — Tạo Application Password

1. Đăng nhập `<site>/wp-admin` bằng tài khoản admin.
2. Vào **Người dùng** → **Hồ sơ của tôi** → cuộn tới **Application Passwords**.
3. Đặt tên bất kỳ (ví dụ `wp-automation-tool`) → **Add New**.
4. Copy password 16 ký tự (dạng `abcd 1234 efgh 5678 ijkl 9012`) — chỉ hiện **một lần**.

> ⚠️ Username cần dùng là **username đăng nhập wp-admin thật** (ví dụ `admin`), KHÔNG phải
> tên bạn vừa đặt cho Application Password ở bước 3. Đây là lỗi hay gặp nhất — xem CLAUDE.md
> mục 2.3.

## Bước 2 — Cài mu-plugin (bắt buộc trên host dùng LiteSpeed)

Nhiều host LiteSpeed (Azdigi, một số shared hosting VN) tự động strip header
`Authorization`, khiến REST API luôn trả 401 dù credential đúng. `rest-auth-header.php` vá
lỗi này bằng cách đọc lại từ header `X-Authorization` (tool đã tự gửi kèm header này).

1. Upload `wp-content/mu-plugins/rest-auth-header.php` (trong repo này) lên
   `<document_root>/wp-content/mu-plugins/rest-auth-header.php` trên server đích.
   mu-plugin không cần kích hoạt thủ công — WordPress tự nạp mọi file trong `mu-plugins/`.
2. Nếu server dùng Apache/LiteSpeed và bạn có quyền sửa `.htaccess`, đảm bảo có 2 dòng sau
   ở đầu file (đã có sẵn trong `.htaccess` ở repo này, tham khảo nếu cần dựng site mới):
   ```apache
   CGIPassAuth On
   SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
   ```
3. **(Tuỳ chọn)** Nếu bạn cần tool ghi được các field SEO của Rank Math qua REST (focus
   keyword, SEO title, meta description...), upload thêm
   `wp-content/mu-plugins/rank-math-rest-meta.php`. Không cần nếu chỉ đăng bài không set
   Rank Math meta qua REST.

## Bước 3 — Test REST API auth

Dùng `setup/user.php` (chỉ cần PHP CLI, không phụ thuộc gì khác) để xác minh Application
Password + mu-plugin hoạt động trước khi đụng tới `wp-automation/`:

```bash
php setup/user.php https://site-cua-ban.com admin "abcd 1234 efgh 5678 ijkl 9012"
```

Kỳ vọng: `HTTP 200` và dòng `✅ Auth OK — id=... username=... name=...`.

Nếu vẫn 401 sau khi đã cài mu-plugin, script sẽ in gợi ý khắc phục — phần lớn rơi vào 1
trong 3 nguyên nhân: sai username, mu-plugin chưa upload đúng chỗ, hoặc domain đang test
không thuộc đúng hosting account (xem bài học ở CLAUDE.md mục 5).

Có thể test tương đương bằng `curl` nếu không có PHP:

```bash
curl -i https://site-cua-ban.com/wp-json/wp/v2/users/me \
  -H "Authorization: Basic $(echo -n 'admin:abcd 1234 efgh 5678 ijkl 9012' | base64)" \
  -H "X-Authorization: Basic $(echo -n 'admin:abcd 1234 efgh 5678 ijkl 9012' | base64)"
```

## Bước 4 — Cấu hình `wp-automation/.env`

```bash
cd wp-automation
cp .env.example .env
```

Điền tối thiểu:

- `WP_SITE_URL`, `WP_USERNAME`, `WP_APP_PASSWORD` (giá trị vừa test thành công ở Bước 3).
- `PEXELS_API_KEY` (miễn phí tại pexels.com/api) — dùng cho ảnh minh hoạ.
- Nếu dùng LLM qua API thay vì `claude_cli`: `ANTHROPIC_API_KEY` hoặc `OPENAI_API_KEY`.

Cấu hình danh mục/chuẩn nội dung ở `wp-automation/src/config.json` — sửa danh mục cho khớp
site mới (xem CLAUDE.md mục 2.4 để biết cách lấy category ID thật qua
`GET /wp-json/wp/v2/categories`).

## Bước 5 — Cài dependencies

```bash
python3 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
playwright install chromium
```

## Bước 6 — Chạy thử

```bash
# Xem trước, KHÔNG đăng bài (an toàn tuyệt đối, ghi ra output/):
python src/main.py --once --dry-run --categories <slug-danh-muc> --posts 1

# Đăng thật — luôn ở trạng thái draft, không tự publish:
python src/main.py --once --categories <slug-danh-muc> --posts 1
```

Mở `wp-admin/post.php?post={id}&action=edit` trên site để kiểm tra bài vừa tạo và điểm
Rank Math trước khi tự tay bấm "Xuất bản".

## Bảo mật

- **Không commit** `.env`, Application Password, hay bất kỳ credential nào — đã có sẵn rule
  trong `.gitignore` gốc của repo.
- Nếu lỡ để lộ Application Password, thu hồi ngay trong **Hồ sơ của tôi → Application
  Passwords** trên wp-admin rồi tạo cái mới.
- Xem quy tắc **"không tự publish"** ở mục 0 của [`../CLAUDE.md`](../CLAUDE.md) — áp dụng
  cho mọi site, mọi lúc.
