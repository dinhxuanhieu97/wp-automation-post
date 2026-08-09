# 📊 GSC Coverage Monitoring - Daily Checklist
**Campaign**: Điện Máy Kim Biên Indexing Recovery  
**Start Date**: 2026-07-29  
**Monitoring Period**: 30 days

---

## 📱 How to Access

1. Go to: https://search.google.com/search-console
2. Select property: **dienmaykimbien.com.vn**
3. Left menu → **Coverage**
4. Review the table showing indexing status

---

## ✅ Daily Monitoring (5 minutes)

### Morning Check (Every Day)

| Metric | Current | Target | Status |
|---|---|---|---|
| **Indexed** | 119 | 400+ | 🔴 Watching |
| **Discovered - not indexed** | 1,829 | 1,200- | 🔴 Watching |
| **In crawl queue** | 1,329 | 500- | 🟡 Monitor |
| **Redirect issues** | 107 | <50 | 🟡 Monitor |
| **Canonicals** | 115 | <50 | 🟡 Monitor |

### What to Look For

✅ **Good Signs** (indexing working):
- Indexed count increases daily (even +10-20 is good)
- "Discovered - not indexed" decreases
- "In crawl queue" gradually shrinks
- No new errors appearing

⚠️ **Warning Signs** (something wrong):
- Indexed count stays flat for 3+ days
- New 404 errors appearing
- New "soft 404" errors
- robots.txt or crawl errors reported

---

## 📅 Weekly Targets

### Week 1 (Jul 29 - Aug 4)
- **Target**: 200-300 indexed posts
- **Action if missed**: Request indexing for 15-20 key posts via "Inspect URL"
- **Check**: Verify no crawl errors blocking posts

### Week 2 (Aug 5 - 11)
- **Target**: 400-600 indexed posts
- **Action if missed**: Check Core Web Vitals score, speed issues
- **Check**: Review GSC Mobile Usability report

### Week 3-4 (Aug 12 - 25)
- **Target**: 800-1,200 indexed posts
- **Action if missed**: Investigate if canonicals/redirects causing issues
- **Check**: Verify internal linking quality

---

## 🎯 If Indexing NOT Growing

### Day 2-3 (No growth yet)
- ✅ Normal - Google is processing crawl queue
- 🔍 Check sitemap_index.xml status in GSC (should show "Success")
- 📞 No action needed yet

### Day 4-5 (Still no growth)
- ⚠️ Manual indexing request needed
- **Steps**:
  1. Go to GSC → URL Inspection
  2. Enter: `https://dienmaykimbien.com.vn/[post-slug]/`
  3. Click "Request Indexing" button
  4. Repeat for 10-15 top posts
  5. Monitor for 24-48 hours

### Week 2 (No growth after 7 days)
- 🔴 Escalation needed - investigate root cause
- **Check list**:
  - [ ] robots.txt still allows crawl?
  - [ ] No IP blocks in security settings?
  - [ ] Core Web Vitals score <50?
  - [ ] Too many 404 errors?
  - [ ] Too many redirects?

---

## 📋 Manual Indexing Request (If Needed)

**Top 15 Posts to Request** (by importance):

1. https://dienmaykimbien.com.vn/vi-tri-dat-may-giat-theo-phong-thuy/
2. https://dienmaykimbien.com.vn/huong-dat-ban-tho-chuan-phong-thuy/
3. https://dienmaykimbien.com.vn/huong-dat-dieu-hoa-theo-phong-thuy/
4. https://dienmaykimbien.com.vn/giay-bao-ho-chong-dinh/
5. https://dienmaykimbien.com.vn/may-nen-khi-cong-nghiep/
6. https://dienmaykimbien.com.vn/may-han-dien-tu-mini/
7. https://dienmaykimbien.com.vn/chon-aptomat-chong-giat-gia-dinh/
8. https://dienmaykimbien.com.vn/may-phat-dien-gia-dinh-loai-nao-tot/
9. https://dienmaykimbien.com.vn/vi-tri-dat-tu-lanh-hop-phong-thuy/
10. https://dienmaykimbien.com.vn/kieng-ky-phong-thuy-khi-bo-tri-do-dien/
11. https://dienmaykimbien.com.vn/can-lam-gi-khi-bi-dui-can/
12. https://dienmaykimbien.com.vn/lich-thi-dau-doi-tuyen-viet-nam-asean-cup-2026/
13. https://dienmaykimbien.com.vn/bai-viet-phong-thuy-1/
14. https://dienmaykimbien.com.vn/tin-tuc/
15. *(Add 5 more top posts by traffic)*

---

## 📊 Logging Template

Copy & paste this daily:

```
DATE: [YYYY-MM-DD]
TIME: [HH:MM UTC]

INDEXED: [number] (was: [previous])
DISCOVERED: [number] (was: [previous])
CRAWL_QUEUE: [number] (was: [previous])
GROWTH: +[number] posts since yesterday

NOTES:
- [Any changes/errors noticed]
- [Action taken if any]

STATUS: ✅ On track / ⚠️ Slow / 🔴 No growth
```

---

## 🔔 Success Indicators

✅ **Campaign Successful** when:
- Day 7: 300+ indexed (from 119 = +181 = on track)
- Day 14: 600+ indexed (from 119 = +481 = excellent)
- Day 30: 1,000+ indexed (from 119 = +881 = target met)

**Expected daily growth**: 50-100 posts/day after crawl queue processing starts

---

## 📞 Quick Reference Links

- **GSC Dashboard**: https://search.google.com/search-console?resource_id=sc-domain%3Adienmaykimbien.com.vn
- **Coverage Report**: https://search.google.com/search-console?resource_id=sc-domain%3Adienmaykimbien.com.vn#coverage
- **URL Inspection**: https://search.google.com/search-console?resource_id=sc-domain%3Adienmaykimbien.com.vn#inspection
- **Sitemaps**: https://search.google.com/search-console?resource_id=sc-domain%3Adienmaykimbien.com.vn#sitemaps
- **Mobile Usability**: https://search.google.com/search-console?resource_id=sc-domain%3Adienmaykimbien.com.vn#mobile-usability

---

## 💡 Pro Tips

1. **Bookmark GSC Coverage page** - You'll visit it daily
2. **Screenshot baseline** - Take a screenshot today to compare
3. **Set calendar reminder** - Email yourself daily at 9 AM
4. **Check after major changes** - Any content updates → check GSC 24h later
5. **Don't obsess** - Indexing takes time; checking every hour won't help

---

**Created**: 2026-07-29  
**Review Frequency**: Daily (5 min check)  
**Next Review Point**: 2026-07-31 (after 48 hours)

