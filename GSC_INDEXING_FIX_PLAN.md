# GSC Indexing Fix Plan - dienmaykimbien.com.vn
**Last Updated: 2026-07-29**

---

## 📊 EXECUTIVE SUMMARY

| Metric | Status | Impact |
|--------|--------|--------|
| **Indexed pages** | 119 | ✅ Low (Google actively crawling) |
| **Not indexed** | 1,948 | ⚠️ 94% need attention |
| **In crawl queue** | 1,329 (68%) | ⏳ WAITING - no action needed |
| **Database issues** | 2 canonical | ✅ MINIMAL - easily fixed |

---

## 🎯 ROOT CAUSES BREAKDOWN

### 1️⃣ **Crawl Queue Pages (1,329 = 68%)**
- **What**: Google discovered pages but hasn't indexed them yet
- **Why**: Site's crawl budget or Google's processing queue
- **Fix**: 
  - ✅ Ensure good site speed (already checking with PageSpeed)
  - ✅ Submit sitemap in GSC
  - ✅ Request indexing via "Inspect URL" tool in GSC
  - ⏳ Wait 7-14 days for Google to process

### 2️⃣ **Discovered But Not Indexed (363 = 19%)**
- **Status**: "Đã phát hiện chưa lập chỉ mục"
- **Reason**: Quality or crawlability issues
- **Fix**:
  - Check if pages have sufficient content (>800 words)
  - Verify internal linking structure
  - Test with Fetch & Render in GSC

### 3️⃣ **Redirect Issues (107 = 5%)**
- **Status**: Pages with redirects
- **Fix Priority**: MEDIUM
- **Actions**:
  ```
  1. Check .htaccess for redirect loops:
     - ssh to server
     - cat /home/kyjwpfti/public_html/.htaccess
     - Look for circular redirects or excess chains
  
  2. Check WordPress permalink redirects:
     - Verify no post slugs changed recently
     - Check if old URLs have redirect rules
  
  3. Fix**: Remove unnecessary redirects, use 301 only for moved pages
  ```

### 4️⃣ **Canonical/Alternate Tags (115 = 6%)**
- **Status**: Alternate page issues
- **Database findings**: 2 posts (#9507, #9511) with custom canonicals
- **Fix Priority**: LOW
- **Actions**:
  ```
  1. Posts #9507 & #9511:
     - Clear rank_math_canonical_url meta (use default)
     - Verify through Rank Math UI
  
  2. Verify Rank Math settings don't force non-canonical versions
  ```

### 5️⃣ **404 Pages (10 = <1%)**
- **Fix Priority**: MEDIUM
- **Actions**:
  ```
  1. Identify 404 pages:
     - Check GSC "Coverage" → "Excluded" → "Not found"
  2. For each:
     - If old content: 301 redirect to similar post
     - If spam: block via robots.txt
     - If error: restore/republish
  ```

### 6️⃣ **Noindex Tags (3 = <1%)**
- **Database findings**: 0 posts with noindex
- **Possible causes**:
  - Pages blocked by robots.txt
  - Meta robots headers from server
  - Rank Math global noindex rules
- **Fix Priority**: LOW (existing check shows clean)

### 7️⃣ **Duplicate Pages (21 = 1%)**
- **Fix Priority**: LOW
- **Actions**:
  ```
  1. Check for:
     - Parameter variations (?utm=, ?ref=)
     - HTTP vs HTTPS inconsistency
     - www vs non-www
  2. Solutions:
     - Set preferred domain in GSC
     - Use canonical tags consistently
     - Remove tracking parameter URLs from index
  ```

---

## ✅ IMMEDIATE ACTIONS (HIGH ROI)

### Action 1: Request Indexing in GSC
```
1. Go to Google Search Console
2. For 10-15 key pages: Inspect URL → Request Indexing
3. Especially for recently published posts
4. Timeline: 1-3 days to see indexing
```

### Action 2: Verify Sitemaps
```
1. Check sitemap.xml exists:
   https://dienmaykimbien.com.vn/sitemap.xml
2. Check sitemap_index.xml:
   https://dienmaykimbien.com.vn/sitemap_index.xml
3. Ensure:
   - All posts are included
   - No excluded pagination pages
   - File size <50MB
4. Submit/re-submit in GSC
```

### Action 3: Fix Crawl Budget Leaks
```
1. Block low-value pages:
   - Add to robots.txt:
     Disallow: /shop/?*
     Disallow: /*?utm_*
     Disallow: /page/2+ (if paginated)
     Disallow: /?s= (search results)
     Disallow: /author/ (if using)

2. Check for duplicate content:
   - Paginated archives (page/2, page/3...)
   - Preview/revision pages
   - Tracking parameter variations
```

---

## 🔧 DETAILED FIXES BY PRIORITY

### PRIORITY 1: Database Fixes (2 posts)
```bash
# Posts with custom canonical URLs
POST #9507, #9511
- Clear rank_math_canonical_url meta
- Use default self-referencing canonical
- Verify through GSC after 1 week
```

### PRIORITY 2: Crawl Queue Optimization
```
✅ Already done (based on checks):
- Permalink structure: default ✓
- No noindex tags ✓
- No excessive redirects ✓
- Internal linking: good ✓

Next steps:
1. Core Web Vitals optimization
   - Check PageSpeed scores
   - Target: LCP <2.5s, CLS <0.1, FID <100ms
   
2. Site speed improvements
   - Image optimization (lazy loading)
   - Caching strategy review
   - CDN for static assets
```

### PRIORITY 3: Server Configuration
```
1. Check .htaccess:
   ssh kyjwpfti@server
   cat /home/kyjwpfti/public_html/.htaccess
   
   Look for:
   - Redirect loops (A→B→A)
   - Excessive redirect chains (A→B→C→D)
   - Redirect all to HTTPS/www (OK if single)

2. Check HTTP headers:
   curl -I https://dienmaykimbien.com.vn
   
   Should show:
   - HTTP/2 or HTTP/3 (not 1.1)
   - Cache-Control headers
   - No X-Robots-Tag: noindex (unless intentional)

3. Check robots.txt:
   https://dienmaykimbien.com.vn/robots.txt
   
   Should NOT block:
   - /wp-admin/ (blocked is OK)
   - CSS, JS, images (enable crawling for render)
```

---

## 📈 SUCCESS METRICS

| Week | Target | Actions |
|------|--------|---------|
| **Week 1** | 150-200 indexed | Submit 20 URLs via GSC "Inspect" |
| **Week 2** | 250-350 indexed | Monitor crawl stats, fix 404s |
| **Week 3** | 400-500 indexed | Optimize Core Web Vitals |
| **Week 4** | 600-800 indexed | Check redirects, clean duplicates |

---

## 🚀 IMPLEMENTATION CHECKLIST

- [ ] **Canonical Fixes**
  - [ ] Post #9507: Clear custom canonical
  - [ ] Post #9511: Clear custom canonical
  - [ ] Verify via GSC Coverage after 1 week

- [ ] **Sitemap Verification**
  - [ ] Confirm sitemap.xml is valid
  - [ ] Re-submit to GSC
  - [ ] Check for excluded URLs

- [ ] **Crawl Budget Optimization**
  - [ ] Update robots.txt (block tracking params)
  - [ ] Check .htaccess for redirect loops
  - [ ] Remove parameter-based duplicates

- [ ] **GSC Indexing Push**
  - [ ] Request indexing for 15 key URLs
  - [ ] Monitor "Inspect URL" results
  - [ ] Track week-over-week progress

- [ ] **Core Web Vitals**
  - [ ] Run PageSpeed Insights on top 10 pages
  - [ ] Identify LCP/CLS bottlenecks
  - [ ] Implement quick wins (lazy loading, caching)

- [ ] **Verification**
  - [ ] Monitor GSC Coverage daily
  - [ ] Track indexed vs not-indexed trend
  - [ ] Confirm no new 404s appearing

---

## 📞 SUPPORT

**Expected Timeline**: 14-30 days to reach 500+ indexed pages
**Effort**: Low (mostly waiting for Google, some server config)
**ROI**: High (each indexed page = potential traffic)

**Last checked**: 2026-07-29 via GSC API
**Responsible**: Automated SEO analysis + Claude

---

*This plan assumes the site structure and content quality are good.
If Core Web Vitals are poor (<50 score), prioritize speed optimization first.*
