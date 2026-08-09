# 🔧 Kế Hoạch Dự Phòng - Nếu Google Không Index Theo Targets

**Mục đích**: Hành động nếu GSC Coverage không tăng theo dự kiến  
**Được cập nhật**: 2026-07-29

---

## 🚨 Tình Huống 1: Ngày 3-4, Vẫn Không Có Growth (0 posts indexed thêm)

**Dấu hiệu**: 
- Indexed vẫn ở 119
- Crawl queue không giảm
- GSC "Sitemaps" tab vẫn chưa show "Processed"

### Hành động ngay lập tức:

#### **Step 1: Kiểm tra Sitemap Status trong GSC** (2 phút)
```
1. Mở GSC → Sitemaps
2. Click sitemap_index.xml
3. Kiểm tra status:
   ✅ "Submitted and processed" = Bình thường, chờ thêm
   ⚠️ "Submitted, processing" = Chờ, có thể 24-48h
   🔴 "Error" = Problem, xem chi tiết
```

#### **Step 2: Thử Request Indexing Thủ Công** (5 phút)
```
1. GSC → URL Inspection
2. Nhập: https://dienmaykimbien.com.vn/vi-tri-dat-may-giat-theo-phong-thuy/
3. Click "Request Indexing"
4. Chờ 24h để xem có được index không

Nếu được index → Problem là sitemap, không phải site
Nếu không được → Problem là site configuration
```

#### **Step 3: Kiểm tra Robots.txt Lại** (2 phút)
```bash
curl https://dienmaykimbien.com.vn/robots.txt

Nếu thấy:
- "Disallow: /" → XÓA NGAY (là bug serious)
- "User-agent: *" → Bình thường
- "Sitemap:" → Bình thường
```

---

## 🟠 Tình Huống 2: Ngày 7, Chỉ Indexed +30 Posts (Quá Chậm)

**Mục tiêu Week 1**: 300+ indexed  
**Thực tế**: Chỉ 149 (119 + 30)  
**Kết luận**: Google crawl quá chậm

### Hành động:

#### **A. Tăng Crawl Budget (Chính Thức)**

Rank Math settings:
```
1. WP Admin → Rank Math → General Settings
2. Search Appearance → Advanced
3. Kiểm tra "Site Speed" → Nếu < 50 (PageSpeed) → Tối ưu ngay
4. Kiểm tra "Mobile Usability" → Fix any errors
```

#### **B. Tối ưu Core Web Vitals** (24-48h work)
```
Priority fixes cho Speed:
1. Enable GZIP Compression (LiteSpeed Cache)
2. Enable Image Lazy Loading
3. Minify CSS/JS
4. Reduce server response time

Expected impact: 
- Day 1-2: Speed score 50 → 70+
- Day 3-4: Indexing +50-100/day
```

#### **C. Manual Indexing Blitz** (Day 7-8)
```
Request indexing for 30 URLs (not just 15):
- Top 30 posts by traffic
- Newest 10 posts  
- Category pages (9 pages)

This accelerates initial indexing while waiting for auto crawl
```

#### **D. Check robots.txt Crawl Delays** (5 min)
```
WordPress → Settings → Reading → Search Visibility
Hạn chế: wp-admin, wp-login, search pages

Check if accidentally blocking:
- /post-sitemap1.xml → Should NOT be blocked
- /category/ pages → Should NOT be blocked
- /page/ (numbered pagination) → OK to block
```

---

## 🔴 Tình Huống 3: Ngày 14, Stuck at 200 Indexed (Totally Blocked)

**Mục tiêu**: 600+ indexed  
**Thực tế**: Vẫn ≈150-200  
**Kết luận**: Có vấn đề cơ bản

### Root Cause Investigation:

#### **Check 1: Crawl Errors in GSC** (5 min)
```
GSC → Crawl Stats → Review Errors:
- Soft 404 errors → Page content too short? Fix content
- Server errors (5xx) → LiteSpeed/PHP problem → Contact host
- "Robots.txt blocked" → Fix robots.txt
- DNS errors → Contact hosting

Action: Fix top error type affecting 50%+ of posts
```

#### **Check 2: robots.txt Blocking** (2 min)
```bash
# Check if accidentally blocking posts
curl https://dienmaykimbien.com.vn/robots.txt | grep -i "disallow"

Should see ONLY:
- Disallow: /wp-admin/
- Disallow: /wp-login.php

Should NOT see:
- Disallow: /  ← CRITICAL BUG if present
- Disallow: /*.xml ← Blocks sitemaps
- Disallow: /post- ← Blocks all posts
```

#### **Check 3: Canonical Tags** (5 min)
```
If canonical tags point to DIFFERENT domain:
Example: Post at dienmaykimbien.com.vn 
         but canonical says dienmaykimbien.com

Solution:
1. WP Admin → Rank Math → Search Appearance
2. Verify canonical URL format = exact domain
3. Regenerate sitemaps after fix
```

#### **Check 4: Redirect Chains** (3 min)
```bash
# Check for redirect chains
curl -L -I https://dienmaykimbien.com.vn/post-slug/

If see multiple 301/302 redirects:
Example: 
/post → /post-slug/ → /post-slug/index.html → final URL

Problem: Uses crawl budget for redirects

Solution: Fix .htaccess to direct to final URL
```

#### **Check 5: Security/IP Blocks** (2 min)
```
Check if Googlebot is blocked:
1. cPanel → Security → IP Blocklist
2. Verify Google IPs not blocked
3. Check WAF (Web Application Firewall) rules

Google IPs to whitelist:
- 66.249.0.0/16 (Googlebot)
- 2001:4860:4801::/32 (IPv6)
```

---

## 🛠️ Heavy-Duty Fixes (Nếu Tất Cả Đều Thất Bại)

### Fix 1: Rebuild Sitemaps Completely
```bash
# Via SSH (if wp-cli available)
wp rankmath sitemap regenerate

# If that fails, manually:
1. Delete all post-sitemap*.xml files
2. Go to Rank Math settings
3. Disable sitemaps, save
4. Enable sitemaps again, save
5. Wait 10 minutes for regeneration
```

### Fix 2: Force Resubmit Sitemap to GSC
```
1. GSC → Sitemaps → sitemap_index.xml
2. Click delete
3. Wait 2 minutes
4. Click "Add/test sitemap"
5. Enter: https://dienmaykimbien.com.vn/sitemap_index.xml
6. Click "Submit"
```

### Fix 3: Contact LiteSpeed Support
```
Problem: Server-side issue (LiteSpeed caching?)
Email: support@litespeed.io

Describe:
- 1,069 posts in sitemaps
- Google won't crawl/index
- robots.txt is correct
- No errors in GSC Crawl Stats

Symptoms to check:
- HTACCESS conflicts with LiteSpeed cache
- X-LiteSpeed-Cache headers blocking Googlebot
- PHP memory limits too low
```

### Fix 4: Check WordPress Health
```
WP Admin → Tools → Site Health
Look for:
- REST API issues
- Database connection issues
- Plugin conflicts
- PHP version compatibility
```

---

## ✅ Decision Tree - Nên Làm Gì Trước Tiên?

```
Day 1-3: Zero growth?
└─> Step 1: Kiểm tra Sitemap status GSC
    └─> "Processed" → Go to Day 4 check
    └─> "Processing" → Wait 24h more
    └─> "Error" → Read error detail

Day 4-7: Only +30 indexed?
└─> Step 2: Optimize Core Web Vitals
    (Most common cause of slow indexing)
    └─> Speed < 50? → Tối ưu images/cache
    └─> Done? → Wait 3-4 days, recheck

Day 8-14: Still stuck?
└─> Step 3: Heavy investigation
    ├─> Check robots.txt (blocking?)
    ├─> Check redirects (chains?)
    ├─> Check canonicals (wrong domain?)
    ├─> Check crawl errors (in GSC)
    └─> Fix #1 issue, wait 48h

Day 15+: No improvement?
└─> Step 4: Contact support
    ├─> LiteSpeed support (server issue)
    ├─> Rank Math support (plugin issue)
    └─> Google Search Central (indexing policy)
```

---

## 📋 Checklist Tự Kiểm Tra (Hằng Ngày)

**Each morning:**
- [ ] Open GSC Coverage tab
- [ ] Note: Indexed count (should be +50 min/day)
- [ ] Note: Crawl queue count (should be -50 min/day)
- [ ] Check for new errors in "Crawl Stats"
- [ ] If no growth: escalate to next step above

---

## 🎯 Success Thresholds (Khi nào dừng lo lắng)

✅ **Safe Zone** (Indexing working):
- Day 2-3: Any growth at all = OK
- Day 5: 200+ indexed = on track
- Day 7: 300+ indexed = good
- Day 14: 600+ indexed = excellent
- Day 30: 1,000+ indexed = mission accomplished

⚠️ **Warning Zone** (May need investigation):
- Day 4: 0 new indexed = check sitemap GSC status
- Day 7: <150 total indexed = optimize Core Web Vitals
- Day 14: <400 total indexed = investigate crawl errors

🔴 **Critical Zone** (Needs immediate action):
- Day 3: GSC says "Error" on sitemap = fix immediately
- Day 5: robots.txt blocking "/" = fix immediately
- Day 10: 0 growth + crawl errors = escalate to support

---

## 📞 Support Contacts

**Rank Math Support**: https://rankmath.com/kb/
- Search: "sitemap not generating"
- Search: "pages not indexing"

**LiteSpeed Support**: support@litespeed.io
- Mention: Googlebot crawl issues
- Attach: GSC crawl stats screenshot

**Google Search Central**: https://support.google.com/webmasters
- Forum: Search Console Help Community
- Topic: "Large site indexing"

**WordPress Support**: https://wordpress.org/support/
- Plugin conflicts check
- REST API troubleshooting

---

## 💡 Pro Tips for Faster Indexing

**Things that speed up Google indexing:**
1. ✅ High PageSpeed score (>70)
2. ✅ Regular content updates
3. ✅ Strong internal linking
4. ✅ External backlinks (domain authority)
5. ✅ Low bounce rate (good UX)
6. ✅ Mobile-friendly design
7. ✅ Structured data (schema markup)

**Things that slow down indexing:**
1. ❌ Low PageSpeed score (<50)
2. ❌ Too many redirects
3. ❌ Excessive crawl errors
4. ❌ Blocking Googlebot in robots.txt
5. ❌ Bad canonicals (pointing elsewhere)
6. ❌ Thin content (<500 words)
7. ❌ Too many duplicate pages

---

**Last Updated**: 2026-07-29  
**Next Review**: 2026-07-31 (after 48 hours of monitoring)

