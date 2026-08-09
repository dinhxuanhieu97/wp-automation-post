# CLAUDE.md — Dự án tự động đăng bài WordPress (Multi-site)

Ghi chú bộ nhớ cho Claude. File này tự động nạp đầu mỗi phiên làm việc trong thư mục này —
đây là NGUỒN DUY NHẤT, không còn file `CLAUDE-multi-site.md` riêng (đã gộp vào đây để tránh
tình trạng thông tin lệch nhau giữa 2 file).

Quản lý 2 site: **Điện Máy Kim Biên** (có pipeline tự động `wp-automation/`) và **Navycons**
(quy trình thủ công theo persona, chưa có script tự động).

---

## 0. ⚠️ QUY TẮC TUYỆT ĐỐI: KHÔNG TỰ Ý PUBLISH — LUÔN ĐỂ DRAFT

**Claude KHÔNG BAO GIỜ được set `status: "publish"` cho bất kỳ bài viết nào, trên bất kỳ site
nào, kể cả khi đã đạt điểm Rank Math ≥80.** Quy tắc này áp dụng cho MỌI site (Điện Máy Kim
Biên, Navycons) và ghi đè lên mọi hướng dẫn khác trong file này hoặc trong lịch sử phiên làm
việc trước đó (kể cả những lần trước Claude từng tự publish — đó là hành vi CŨ, SAI, không
còn áp dụng).

Quy trình đúng cho MỌI bài viết:
1. Viết bài, tối ưu SEO, verify Rank Math ≥80 như checklist mục 4.3.
2. Tạo/giữ bài ở trạng thái **`draft`** (mặc định của REST API và của `wp-automation` đã
   đúng sẵn — không cần đổi gì).
3. Báo cho user biết bài đã sẵn sàng (kèm link edit `wp-admin/post.php?post={id}&action=edit`
   hoặc link xem trước), và dừng lại — **KHÔNG** tự gọi `POST {"status":"publish"}`.
4. User sẽ tự review và tự bấm "Xuất bản" trên wp-admin khi họ muốn.

Ngoại lệ duy nhất: nếu user **chủ động yêu cầu rõ ràng** "publish giúp tôi" / "đăng luôn" /
"xuất bản bài này" trong tin nhắn hiện tại thì mới được publish bài đó — không tự suy diễn từ
các yêu cầu chung chung như "viết bài", "hoàn thành bài", "làm xong 3 bài này".

---

## 1. Tổng quan multi-site (Site Detection)

Khi user nói bất kỳ cụm từ nào dưới đây, tự động nhận diện đúng site và đọc phần tương ứng
bên dưới TRƯỚC khi viết bài:

### Điện Máy Kim Biên
- Trigger: "dienmaykimbien", "điện máy kim biên", "web công nghiệp", "site thiết bị điện"
- Đọc mục 2 bên dưới.
- Config: `multi-site-config.json` → `sites[0]`

### Navycons
- Trigger: "navycons", "pool villa", "biệt thự", "villa", "web navycons"
- Đọc mục 3 bên dưới, đặc biệt persona SEO Content Expert ở mục 3.4 — **bắt buộc** trước khi
  viết bất kỳ bài nào cho Navycons.
- Config: `multi-site-config.json` → `sites[1]`

Credentials (username + application password) của cả 2 site lưu trong `multi-site-config.json`
(đã `.gitignore`, không commit). Khi user gửi application password mới, sửa field
`wp_app_password` tương ứng trong file đó.

**Quy tắc chung khi viết bài cho MỌI site** (chuẩn nội dung, checklist Rank Math, bug flip
status) nằm ở mục 4 — áp dụng đồng thời với phần đặc thù site.

---

## 2. Site: Điện Máy Kim Biên

### 2.1 Tổng quan

Hệ thống tự động: quét keyword (Google Trends/Suggest) → viết bài chuẩn SEO E-E-A-T →
lấy ảnh → đăng lên `dienmaykimbien.com.vn` qua WordPress REST API (mặc định `draft`).

**Thư mục dự án THẬT: `wp-automation/`** — đây là bản đang phát triển, hoàn chỉnh.
- `wordpress-auto-publisher/` và `AutoPostSystems/` là bản CŨ/bỏ dở — KHÔNG dùng.

Cấu trúc `wp-automation/`:
- `src/main.py` — orchestrator + scheduler (5 khung giờ vàng).
- `src/modules/`: `trends_scraper` (A), `ai_writer` (B), `media_optimizer` (C),
  `wp_publisher` (D, REST API), `playwright_seo` (D+, điền Rank Math qua wp-admin),
  `sitemap_links` (kho internal link từ sitemap, 1047 URL).
- `src/config.json` — site, danh mục, chuẩn nội dung, khung giờ.
- `.env` — credentials (KHÔNG commit). `output/` — bài dry-run. `images/` — ảnh Gemini.

### 2.2 Site & hosting

- WordPress + **Rank Math** SEO. Web server **LiteSpeed** (Azdigi), **PHP 7.4**.
- cPanel user `kyjwpfti`, home `/home/kyjwpfti`, docroot **`public_html`**.
- WP admin users: `admin` (id 1, "Hieu Minh") và `Huynhtrananhthu`.

### 2.3 ⚠️ REST AUTH — đã fix, ĐỪNG phá

Có 2 lỗi đã xử lý, cần giữ nguyên:

1. **Username đúng là `admin`.** `admin-auto-post-tools` chỉ là TÊN (label) của
   Application Password, KHÔNG phải username. `.env` → `WP_USERNAME=admin`.
2. **LiteSpeed strip header `Authorization`.** Đã vá bằng:
   - mu-plugin trên server: `public_html/wp-content/mu-plugins/rest-auth-header.php`
     (đọc header `X-Authorization` → dựng lại PHP_AUTH_USER/PW). Xoá file = hỏng auth.
   - `wp_publisher.py` gửi kèm header `X-Authorization` (ngoài `Authorization`).
   - `.htaccess` có `CGIPassAuth On` + `SetEnvIf` (giữ, vô hại).

Test auth nhanh: `GET /wp-json/wp/v2/users/me` với header `X-Authorization: Basic <base64(admin:app_pw)>` → mong đợi 200.

### 2.4 Danh mục (đã map ID thật của site)

Config đã đổi từ danh mục điện máy gia dụng (giả định, KHÔNG có thật) sang danh mục
công nghiệp CÓ THẬT:

| slug | id | tên |
|---|---|---|
| thiet-bi-dien | 1 | Thiết Bị Điện |
| co-khi | 32 | Cơ Khí |
| thiet-bi-cong-nghiep | 31 | Thiết Bị Công Nghiệp |
| bao-ho-lao-dong | 33 | Bảo hộ lao động |
| nong-nghiep | 63 | Nông Nghiệp |

Site còn có (chưa đưa vào rotation): phong-thuy(40), showbiz(64), the-thao(65), ngay-le(57).

### 2.5 LLM viết bài

- Chuỗi provider (config `content.llm_chain`): **claude_cli** → anthropic → openai.
- `claude_cli` = dùng gói Claude Code (`claude login` 1 lần trên Mac), 0đ phí API. LÀ CÁCH CHÍNH.
- ANTHROPIC_API_KEY / OPENAI_API_KEY trong `.env` hiện là **placeholder** — đừng dựa vào fallback API.

### 2.6 Ảnh

- Folder `wp-automation/images/`. Đặt tên: `{slug}__featured`, `{slug}__1`, `{slug}__2`…
- Nguồn: Gemini (thủ công, prompt trong `images/PROMPTS_*.txt`) ưu tiên; thiếu thì Pexels.
- `media_optimizer.py` ĐÃ wire **Gemini-first**: đọc ảnh local trong `images/{slug}__featured|__1|__2`
  trước, thiếu mới lấy Pexels→Unsplash→placeholder. `config.media.images_per_post=3` (featured+2 thân bài).
- `.env`: `PEXELS_API_KEY` (free tại pexels.com/api). `UNSPLASH_ACCESS_KEY` là nguồn dự phòng (tùy chọn).

### 2.7 Cách chạy (trên Mac)

```bash
cd ~/Downloads/DienMayKimBien/wp-automation
source venv/bin/activate           # lần đầu: python3 -m venv venv; pip install -r requirements.txt; playwright install chromium
python src/main.py --once --dry-run --categories thiet-bi-dien --posts 1   # xem trước, không đăng → output/
python src/main.py --once --categories thiet-bi-dien --posts 1             # đăng draft thật
python src/main.py --once          # cả 5 danh mục
python src/main.py                 # bật scheduler 5 khung giờ vàng
```

⚠️ `.env` đang có `POST_STATUS_OVERRIDE=schedule` (status KHÔNG hợp lệ) → đổi thành `draft`
(hoặc để trống; config mặc định đã là draft).

### 2.8 Trạng thái đã làm

- REST auth thông; đã đăng thử draft test **post #9700** (Thiết Bị Điện, Rank Math meta OK).
- Bài mẫu dry-run: `output/dryrun_thiet-bi-dien_may-phat-dien.json` (+ .html), đạt validator 100%.

---

## 3. Site: Navycons

### 3.1 Site & hosting

- URL ĐÚNG: **https://navycons.com** (KHÔNG PHẢI `.vn` — xem bài học ở mục 5).
- Credentials: `admin` / xem `multi-site-config.json` → `sites[1].wp_app_password`.
- Hosting: cPanel account `vureaegb` trên `vdc-whm-prohosting-07.vinahost.org`,
  document root `/home/vureaegb/navycons.com/`.
- WordPress + **Rank Math** SEO.
- Status: ✅ HOẠT ĐỘNG — đã publish nhiều bài draft thành công (post #4514, #4527, #4532...).

### 3.2 ⚠️ REST AUTH & Rank Math meta — đã fix, ĐỪNG phá

- Domain đúng là `navycons.com`, không phải `.vn` (bài học mục 5).
- Rank Math **không expose** các field `rank_math_*` qua REST theo mặc định. Đã vá bằng
  mu-plugin `wp-content/mu-plugins/rank-math-rest-meta.php` trên server, dùng
  `register_post_meta()` với `show_in_rest => true` cho các key: `rank_math_focus_keyword`,
  `rank_math_title`, `rank_math_description`, `rank_math_pillar_content`,
  `rank_math_canonical_url`, `rank_math_robots`, `rank_math_facebook_title`,
  `rank_math_facebook_description`. Xoá file này = REST không ghi được field SEO nữa.

### 3.3 Danh mục

| slug | id | tên |
|---|---|---|
| tin-tuc-su-kien | 1 | Tin tức & sự kiện |
| chia-se-kinh-nghiem | 19 | Chia sẻ kinh nghiệm |

Chỉ có 2 danh mục thật trên site — không bịa thêm.

### 3.4 ⚠️ Persona bắt buộc khi viết bài cho Navycons (SEO Content Expert)

Trước khi bắt đầu viết BẤT KỲ bài nào cho Navycons, phải đọc kỹ, ghi nhớ và tuân thủ tuyệt
đối bộ quy tắc dưới đây (áp dụng cùng với checklist Rank Math dùng chung ở mục 4). Nếu có
Skills/Tools/Connectors phù hợp để phân tích chủ đề/từ khóa trước khi viết (VD: research
trend, kiểm tra bài đã có trên site để tránh trùng nội dung, tra internal link thật), hãy
dùng trước khi soạn bài.

**Vai trò:** Chuyên gia viết nội dung chuẩn SEO (SEO Content Expert) cho website Navycons.

**Bối cảnh thương hiệu (Brand Context)**

- **Tổng quan:** Navycons – Design & Build là doanh nghiệp xây dựng và kiến trúc uy tín, trụ
  sở chính tại TP.HCM, thành lập bởi nhóm kiến trúc sư và kỹ sư tài năng.
- **Dịch vụ cốt lõi:** Tư vấn thiết kế, thi công trọn gói, trang trí nội ngoại thất, quản lý
  dự án.
- **Danh mục công trình:** Biệt thự cao cấp, nhà phố, chung cư, văn phòng cho thuê, hạ tầng
  công nghiệp.
- **Triết lý & USP:** Lấy khách hàng làm trọng tâm, cam kết chất lượng, đảm bảo tiến độ,
  không ngừng ứng dụng công nghệ hiện đại. Minh bạch về năng lực, giá cả dịch vụ, định hướng
  vươn tầm quốc tế.
- **Văn phong (Tone of Voice):** Chuyên nghiệp, đáng tin cậy, cung cấp kiến thức thực tế.
  Dùng thuật ngữ chuyên ngành nhưng giải thích dễ hiểu để chủ đầu tư dễ tiếp nhận. **Tuyệt
  đối không viết lan man ngoài phạm vi thiết kế - xây dựng.**

**Internal link:** Chèn tối thiểu 3 liên kết chéo tới các dịch vụ/bài viết liên quan của
Navycons trong ngữ cảnh tự nhiên nhất — **chỉ dùng bài/URL có thật** (tra bằng
`GET /wp-json/wp/v2/posts?search=<từ khóa>`), không bịa tên dự án. KHÔNG có các dự án
"Spring Villa/Pool Villa Skypiea/Villa M12" — đó là tên hư cấu từ session cũ, không tồn tại
trên site thật.

Tiêu chuẩn SEO chi tiết (độ dài, H2, bảng, FAQ, title/meta, ảnh...) dùng chung với mục 4 —
không lặp lại ở đây.

### 3.5 Cách cấp/đổi credential

1. Đăng nhập `<site>/wp-admin` với user admin.
2. Vào **Người dùng** → **Hồ sơ của tôi** → tìm section **Application Passwords**.
3. Xoá cái cũ (nếu có), tạo mới với tên `claude-auto-publisher`.
4. Sao chép password (16 ký tự, 6 nhóm cách nhau bằng space) và gửi cho Claude.
5. Cập nhật field `wp_app_password` tương ứng trong `multi-site-config.json`.

---

## 4. ⚠️ Quy tắc DÙNG CHUNG cho MỌI site (Rank Math + REST)

### 4.1 Bug flip status draft → publish

**Đã gặp nhiều lần** trên cả 2 site: bất kỳ `POST /posts/{id}` nào vào bài đang `draft` có
thể tự động flip status thành `publish`, dù payload không hề chứa field `status` (đổi slug,
set excerpt, set title đều từng kích hoạt bug này). Nguyên nhân chưa rõ (nghi ngờ hook của
Rank Math hoặc plugin khác).

**Nguyên tắc bắt buộc:** sau MỌI lần update REST vào một bài draft, phải gọi lại
`GET /posts/{id}` (hoặc đọc response của chính POST đó) kiểm tra `status`, nếu thấy `publish`
thì gửi thêm `POST {"status":"draft"}` ngay lập tức, và verify bằng
`curl -o /dev/null -w "%{http_code}"` vào URL công khai của bài (mong đợi 404/301, không phải 200).

### 4.2 Chuẩn nội dung

- **Độ dài bắt buộc: 1200–1300 từ/bài — KHÔNG được vượt quá 1300 từ**, trừ khi user yêu cầu
  rõ ràng viết thêm số lượng từ. Giới hạn cứng, áp dụng cho MỌI site.
- ≥3 H2, đúng 1 bảng so sánh, đúng 3 FAQ, ≥3 internal link (backlink chéo, lấy từ
  `sitemap_links` hoặc `GET /wp-json/wp/v2/posts?search=`, không bịa URL).
- Title ≤65 ký tự (focus keyword ở nửa đầu), meta description ≤155 ký tự có CTA, sapo
  100–150 từ.

### 4.3 Checklist Rank Math (để đạt ≥80/100 ổn định, rút ra từ thực tế đăng bài)

- **Ưu tiên focus keyword ngắn (2-3 từ) thay vì dài (4-5 từ).** Rank Math tính mật độ từ khóa
  = (số lần cụm từ khóa xuất hiện CHÍNH XÁC) ÷ (tổng số từ) × 100 — KHÔNG nhân theo độ dài cụm
  từ. Keyword 5 từ lặp tự nhiên 4 lần/bài 1300 từ chỉ ra ~0.31% → bị chấm "mật độ thấp". Nếu
  bắt buộc dùng keyword dài, phải chủ động lặp nguyên cụm **8-10 lần** trong bài 1200-1500 từ
  mới đạt ~0.6-0.8%.
- **Với keyword dài (≥3 từ), đừng chỉ dùng biến thể rút gọn** (VD chỉ viết "vật tư" thay vì
  "vật tư xây dựng") — Rank Math đếm CHÍNH XÁC cụm từ đầy đủ, biến thể rút gọn không tính vào
  density. Lặp NGUYÊN CỤM đầy đủ trong H2, câu đầu sapo, và rải đều thân bài.
- **Focus keyword phải xuất hiện trong 10% ĐẦU nội dung** (không chỉ "gần đầu" chung chung) —
  Rank Math chấm riêng mục này. Cách chắc ăn: đặt nguyên cụm focus keyword (bôi đậm `<strong>`)
  ngay trong câu đầu tiên của bài.
- **Title SEO nên chứa ít nhất 1 con số** (năm 2026, số tiêu chí, top N…) — thiếu số bị chấm
  fail mục "Khả năng đọc tiêu đề".
- **Title SEO phải chứa 1 "power word" tiếng Anh** (danh sách ~1100 từ hardcode trong Rank
  Math, KHÔNG có bản dịch tiếng Việt — từ Việt như "bí quyết", "hoàn hảo" không được nhận
  diện). ⚠️ **"premium" ĐÃ TEST THỰC TẾ và KHÔNG nằm trong danh sách** (dù từng tưởng vậy) —
  đừng dùng. Từ đã verify CÓ trong danh sách (an toàn để dùng): `luxury`, `best`, `free`,
  `new`, `unique`, `perfect`, `expert`, `proven`, `guaranteed`, `essential`, `ultimate`,
  `solid`, `safe`, `reliable`, `quality`, `complete`. **KHÔNG dùng nếu chưa verify**:
  `premium`, `trusted`, `durable`, `top`. Luôn kiểm tra lại trước khi dùng từ mới (danh sách
  đổi theo phiên bản plugin) bằng JS trong console trang edit bài:
  `window.rankMath.assessor.powerWords.includes("tên_từ")`.
- **Title Readability còn 1 lỗi RIÊNG BIỆT: "doesn't contain a positive or negative sentiment**
  **word"** — khác lỗi power word ở trên, và Rank Math KHÔNG expose danh sách sentiment word
  qua `window.rankMath.assessor` nên không tra cứu tĩnh được. Thực tế: một số từ trong danh
  sách power word (VD: `best`) đồng thời thỏa mãn CẢ 2 lỗi cùng lúc — không cần thêm từ riêng.
  Quy trình bắt buộc: sau khi soạn title, **luôn mở bài trong wp-admin (Rank Math SEO box →**
  **General → Edit Snippet), gõ thử title, và quan sát điểm số + checklist cập nhật**
  **REAL-TIME** trước khi chốt — không đoán mù.
- **Đoạn văn ngắn:** không để đoạn nào dài quá ~100-120 từ / 4-5 câu, tách nhỏ nếu dài hơn —
  đoạn dài bị chấm fail mục "Khả năng đọc nội dung".
- Ảnh có alt chứa keyword, tên file không dấu gạch ngang (VD: `pool-villa-thiet-ke.jpg`),
  ≥1 outbound link **dofollow** (`rel="noopener"`, KHÔNG kèm `nofollow`) tới nguồn uy tín,
  ≥1 internal link thật, mật độ keyword ngắn 0.5-2.5%.
- **Luôn set field `excerpt` (Mô tả ngắn) khi tạo bài, KHÔNG để trống.** Nếu để trống, theme
  gọi `the_excerpt()` sẽ tự cắt ~55 từ đầu content, dễ cắt ngang câu/từ. Viết riêng 1-2 câu
  hoàn chỉnh, ~25-35 từ — khác với `rank_math_description` (dùng cho snippet Google, có CTA).
  Set qua REST: `POST /posts/{id} {"excerpt": "..."}`. **Nhớ áp dụng nguyên tắc chống bug flip**
  **status ở mục 4.1 sau bước này** (excerpt từng kích hoạt bug đó).
- **QUY TRÌNH BẮT BUỘC trước khi báo hoàn thành bất kỳ bài nào: verify điểm Rank Math THẬT**
  **qua wp-admin** (set field `meta` REST thành công KHÔNG đồng nghĩa đạt ≥80 điểm). Mở
  `wp-admin/post.php?post={id}&action=edit`, cuộn tới box "Rank Math SEO" → tab General, đọc
  điểm số cạnh Focus Keyword và mở từng mục có nhãn "x Errors" để xem chi tiết. Bỏ qua gợi ý
  "Use Content AI to optimise the Post" (nudge bán tính năng trả phí, không phải lỗi thật).
  Nếu điểm <80, sửa nội dung/title rồi lặp lại đến khi đạt ≥80 mới coi là hoàn thành.

### 4.4 Ảnh thumbnail/featured image (đè tiêu đề, tránh bản quyền) — CHUẨN TỪ NAY

**KHÔNG bao giờ** tải ảnh trực tiếp từ Pinterest hoặc nguồn không rõ giấy phép thương mại để
đăng lên site khách hàng — phần lớn là nội dung bên thứ ba, rủi ro vi phạm bản quyền. Nếu user
gửi link Pinterest, chỉ dùng để tham khảo phong cách, KHÔNG tải ảnh từ đó.

**Thứ tự ưu tiên nguồn ảnh:**
1. **Ảnh minh hoạ AI tự sinh** (Gamma `generate_image` hoặc công cụ tương đương) — không dính
   bản quyền, kiểm soát đúng chủ đề/phong cách. Lưu ý: tốn credit (Gamma ~70 credit/ảnh), có
   thể hết giữa chừng (lỗi 402 Insufficient credits) — khi đó dừng lại, hỏi user muốn nạp thêm
   credit hay chuyển sang phương án 2, không tự ý bỏ dở.
2. **Ảnh thật miễn phí bản quyền thương mại từ Pexels API** (key trong `.env`/
   `multi-site-config.json`) — dùng khi hết credit AI hoặc chủ đề cần ảnh thật (VD: tin tức thể
   thao, nhân vật có thật). Ưu tiên chọn ảnh có khoảng trống bố cục (bầu trời, nền mờ, một bên
   ảnh trống) để đặt chữ đè lên không che mất chủ thể. Nếu 1 bài cần ảnh minh hoạ cho nhiều chủ
   đề con khác nhau (VD: mỗi bài ứng với 1 con giáp/1 đội bóng khác nhau), PHẢI xem trước từng
   ảnh nguồn (Read tool) để đảm bảo đúng chủ đề — không dùng đại một ảnh chung chung.

**Đè tiêu đề lên ảnh thumbnail (bắt buộc áp dụng cho MỌI bài từ nay):**
- Dùng Python Pillow (`PIL`), font `DejaVuSans-Bold.ttf` (hỗ trợ tốt dấu tiếng Việt, có sẵn
  trong sandbox tại `/usr/share/fonts/truetype/dejavu/`).
- Layout chuẩn: crop ảnh về tỉ lệ 16:9 (khuyến nghị 1200×675), phủ gradient đen mờ dần ở ~55%
  phần dưới ảnh để chữ dễ đọc, viết 2 dòng canh giữa — dòng 1 to đậm (chủ đề chính, màu vàng
  gold khoảng `#FFD778`, có viền đen/shadow quanh chữ để nổi trên mọi nền), dòng 2 nhỏ hơn là
  phụ đề (màu trắng, cùng kiểu viền).
- Export JPEG quality=90. Đặt tên file có dấu gạch ngang theo keyword, không dấu tiếng Việt
  (VD: `tu-vi-tuoi-suu-2026.jpg`, `lich-ban-ket-viet-nam-malaysia-2026.jpg`).
- Khi upload media qua REST, luôn set `alt_text` chứa focus keyword.
- Dùng ảnh đã đè chữ làm **cả `featured_media` VÀ ảnh đầu bài** (figure ngay sau đoạn sapo) để
  nhất quán giữa thumbnail và trong bài.

---

## 5. ⚠️ Bài học quan trọng: luôn xác minh domain trước khi debug auth

**Sự cố đã xảy ra:** Debug REST auth cho Navycons thất bại liên tục (401) dù đã thử nhiều
credential khác nhau + đặt đúng mu-plugin + đúng document root. Nguyên nhân thật sự: domain
đang test **không hề tồn tại trong cPanel account đang thao tác** — cPanel account `vureaegb`
chỉ quản lý 2 domain: `edutex.net` (main) và **`navycons.com`** (domain thật của Navycons).
Domain sai được dùng để test trước đó hoàn toàn không liên quan đến hosting account này.

**Cách phát hiện:** Vào cPanel → Domains → xem cột "Domain" liệt kê tất cả domain thuộc
account, đối chiếu với domain đang cố gắng truy cập.

**Bài học cho tương lai:** Khi REST auth thất bại nhất quán dù đã đúng credential + đúng
vị trí file trên server, luôn nghi ngờ **domain đang test có thực sự trỏ đến server/hosting
account đang thao tác hay không** — kiểm tra qua cPanel Domains list TRƯỚC khi debug sâu hơn
vào auth/mu-plugin.

---

## 6. File liên quan

- `multi-site-config.json` — Site config + credentials cho cả 2 site (file chính, gitignore).
- `wp-automation/` — pipeline tự động của Điện Máy Kim Biên (xem mục 2).
- `pool-villa-biet-thu-san-vuon.md` — bài mẫu Navycons (đã publish thử, post #4514).

---

## 7. Phân tích Google (GSC/GA) — chưa nối

Chưa có connector GA4/GSC. Lựa chọn: (A) connector **Ahrefs** (cần authorize trong claude.ai
Connectors; có dữ liệu GSC + Site Audit + Rank Tracker), hoặc (B) export CSV từ GSC/GA rồi
upload để phân tích (nhanh, 0 phí). Mục tiêu: ra content roadmap nạp vào tool.

---

## 8. Ưu tiên xử lý

- Khi cùng lúc có request viết cho 2 site: xử lý **Điện Máy Kim Biên** trước (pipeline tự
  động sẵn có), sau đó **Navycons** (viết thủ công theo persona mục 3.4).
- Khi user chưa chỉ rõ site: ưu tiên hỏi lại thay vì đoán, trừ khi context đã rõ (VD user vừa
  nói về pool villa/vật tư xây dựng → tự động chọn Navycons).
