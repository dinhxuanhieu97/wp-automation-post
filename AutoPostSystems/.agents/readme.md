# AI AGENT TASK SPEC — XÂY DỰNG HỆ THỐNG ĐĂNG BÀI WORDPRESS TỰ ĐỘNG

## 0. Vai trò của AI Agent

Bạn là Senior Full-stack Engineer + AI Automation Architect.

Nhiệm vụ của bạn là xây dựng một hệ thống đăng bài tự động cho WordPress theo quy trình:

1. Nhập URL bài viết từ website nguồn.
2. Crawl và trích xuất nội dung chính.
3. Kiểm tra trùng lặp / rủi ro copy.
4. Dùng AI viết lại bài theo chuẩn SEO.
5. Tạo keyword, slug, meta title, meta description, FAQ.
6. Tạo prompt hình ảnh minh họa và thumbnail 1240x1240.
7. Tạo hoặc upload thumbnail.
8. Đẩy bài viết lên WordPress dưới dạng draft.
9. Lưu log toàn bộ quá trình.
10. Cung cấp API/admin dashboard để người dùng kiểm tra, chỉnh sửa và publish.

Lưu ý quan trọng:

- Không copy nguyên văn bài viết gốc.
- Không spin nội dung máy móc.
- Không giữ cấu trúc bài gốc quá sát.
- Bài viết mới phải có giá trị bổ sung: giải thích, ví dụ, góc nhìn, FAQ, hướng dẫn thực tế.
- Ban đầu chỉ publish dưới dạng `draft`, không tự động publish trực tiếp.

---

# 1. Mục tiêu hệ thống

Xây dựng MVP hệ thống tự động tạo bài WordPress từ URL nguồn.

## MVP bắt buộc có

- Backend API.
- Crawler nhập URL và extract nội dung.
- AI Agent rewrite bài SEO.
- AI Agent audit SEO.
- Sinh keyword, slug, meta description.
- Sinh prompt thumbnail.
- Tạo thumbnail kích thước 1240x1240.
- Upload thumbnail lên WordPress Media.
- Tạo bài WordPress dạng draft.
- Lưu database.
- Có trạng thái xử lý từng bài.
- Có logs lỗi.

## Chưa cần làm ở MVP

- Chưa cần auto crawl hàng loạt nhiều website.
- Chưa cần Google Search Console.
- Chưa cần tự publish.
- Chưa cần dashboard quá đẹp.
- Chưa cần content cluster nâng cao.

---

# 2. Tech Stack đề xuất

Ưu tiên triển khai theo stack sau:

## Backend

- .NET Web API hoặc Python FastAPI.
- Nếu dùng .NET:
  - ASP.NET Core Web API
  - EF Core
  - PostgreSQL hoặc MySQL
  - BackgroundService hoặc Hangfire
- Nếu dùng Python:
  - FastAPI
  - SQLAlchemy
  - PostgreSQL hoặc MySQL
  - Celery/RQ nếu cần queue

## Crawler

- Requests + BeautifulSoup cho website HTML tĩnh.
- Playwright cho website render bằng JavaScript.
- Readability/trafilatura để extract nội dung chính nếu dùng Python.

## AI

- OpenAI hoặc Gemini API.
- Một agent để rewrite.
- Một agent để audit SEO.
- Một agent để tạo prompt ảnh.

## WordPress

- WordPress REST API.
- Dùng Application Password để xác thực.
- Endpoint:
  - `/wp-json/wp/v2/media`
  - `/wp-json/wp/v2/posts`
  - `/wp-json/wp/v2/categories`
  - `/wp-json/wp/v2/tags`

---

# 3. Cấu trúc thư mục đề xuất

Nếu dùng .NET:

```txt
wordpress-auto-publisher/
├── src/
│   ├── Api/
│   │   ├── Controllers/
│   │   ├── Program.cs
│   │   └── appsettings.json
│   ├── Application/
│   │   ├── Services/
│   │   ├── Jobs/
│   │   ├── DTOs/
│   │   └── Interfaces/
│   ├── Domain/
│   │   ├── Entities/
│   │   └── Enums/
│   ├── Infrastructure/
│   │   ├── Db/
│   │   ├── Ai/
│   │   ├── Crawler/
│   │   ├── WordPress/
│   │   └── Storage/
│   └── Worker/
├── docs/
│   ├── api-spec.md
│   ├── database-schema.md
│   └── workflow.md
└── README.md
```
