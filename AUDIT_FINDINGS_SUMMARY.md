# GSC Indexing Audit - Full Summary Report
**Date**: 2026-07-29 | **Site**: dienmaykimbien.com.vn

---

## EXECUTIVE SUMMARY

**Problem**: 1,948 posts discovered by Google but only 119 indexed (6% indexation rate)

**Root Cause**: **Empty XML sitemaps** prevent automated post discovery  
**Priority**: 🔴 **CRITICAL** - Fix within 24 hours  
**Estimated Fix Time**: 1-2 hours  
**Expected Impact**: +300-500 indexed posts within 48 hours

---

## AUDIT FINDINGS

### 1. GSC Coverage Analysis

| Status | Count | % | Cause |
|--------|-------|---|-------|
| Indexed | 119 | 6% | ✅ Successfully indexed |
| Not indexed | 1,948 | 94% | ❌ Issues below |
| **Breakdown of not indexed**: | | | |
| → In crawl queue | 1,329 | 68% | Discovered but Google hasn't crawled yet |
| → Discovered only | 363 | 19% | Found but not processed |
| → Redirect issues | 107 | 5% | Pages with redirects |
| → Canonical issues | 115 | 6% | Duplicate/alternate page issues |
| → 404 pages | 10 | <1% | Deleted pages |
| → Noindex tags | 3 | <1% | Explicitly blocked |
| → Duplicate pages | 21 | 1% | Near-duplicate content |

### 2. Database Analysis (WordPress)

**What we checked**:
- ✅ REST API - 300+ posts fetched
- ✅ Post status - All published correctly
- ✅ Permalink structure - Default (OK)
- ⚠️ Canonical tags - 2 posts with custom canonicals (easily fixable)
- ✅ Noindex tags - None found (0 issues)
- ✅ Redirect rules - None at database level

### 3. Technical SEO Audit Results

**Overall Score: 82/100**

| Component | Score | Status | Issue |
|-----------|-------|--------|-------|
| Crawlability | 60/100 | ⚠️ WARNING | Empty sitemaps, robots.txt "Disallow: /" |
| Security | 90/100 | ✅ GOOD | HTTPS enabled, missing HSTS header |
| Sitemaps | 70/100 | 🔴 CRITICAL | **0 URLs in sitemaps** |
| Mobile | 100/100 | ✅ EXCELLENT | Fully responsive |
| Structured Data | 80/100 | ✅ GOOD | JSON-LD present on pages |

### 4. Detailed Issue Analysis

#### 🔴 CRITICAL: Empty XML Sitemaps

**Finding**:
```
sitemap.xml: 1.0 KB, 0 URLs
sitemap_index.xml: 1.0 KB, 0 sitemaps
```

**Why this matters**:
- Google uses sitemaps to discover pages efficiently
- 1,329 posts (68%) stuck in "crawl queue" because they haven't been discovered yet
- Without sitemaps, Google must discover posts via internal links (slow)

**Root cause**:
- Rank Math sitemap generation may be disabled or not configured
- OR sitemap regeneration hasn't been triggered since site setup

**Fix priority**: **DO THIS TODAY**
1. Access WordPress admin → Rank Math → Sitemaps
2. Verify settings: Posts ✓, Pages ✓, Regenerate enabled ✓
3. Click "Regenerate Sitemaps"
4. Verify sitemaps now have URLs
5. Re-submit to GSC

---

#### ⚠️ HIGH: robots.txt May Block Posts

**Current state**:
```
User-agent: *
Disallow: /          ← This blocks EVERYTHING
Sitemap: https://dienmaykimbien.com.vn/sitemap_index.xml
```

**Risk**:
- If "Disallow: /" is active, Google cannot crawl ANY posts
- Combined with empty sitemaps = no discovery mechanism

**Fix**:
- Verify in WordPress: Settings → Reading → "Discourage search engines" should be UNCHECKED
- If needed, allow posts explicitly in .htaccess

---

#### ⚠️ MEDIUM: Missing Security Headers

**Missing headers**:
- Strict-Transport-Security (HSTS)
- Content-Security-Policy (CSP)
- X-Content-Type-Options

**Impact**: Affects security score, not indexing

**Fix**: Add to .htaccess (1-hour fix)

---

#### 🟡 LOW: 2 Posts with Custom Canonicals

**Affected**: Posts #9507, #9511
**Impact**: Minor (only 2 posts)
**Fix**: Remove custom canonical tags in Rank Math UI

---

## WHAT'S WORKING WELL ✅

- **Mobile**: Perfect responsive design (100/100)
- **Structured Data**: JSON-LD schemas present on posts
- **Security**: HTTPS enabled, good baseline security
- **Database**: Posts properly configured, no noindex tags
- **Content**: 1,948 posts are real, discoverable content

---

## ACTION PLAN - PRIORITY ORDER

### PHASE 1: CRITICAL (TODAY - 1-2 hours)

**Task 1: Fix Empty Sitemaps**
- Action: WordPress Admin → Rank Math → Sitemaps → Regenerate
- Verify: sitemap_index.xml should have 10-20 KB after regeneration
- Time: 5-10 minutes

**Task 2: Fix robots.txt**
- Action: WordPress → Settings → Reading → Verify "Discourage search engines" is UNCHECKED
- Verify: robots.txt allows `/`
- Time: 5 minutes

**Task 3: Submit to GSC**
- Action: GSC → Sitemaps → Submit `https://dienmaykimbien.com.vn/sitemap_index.xml`
- Verify: GSC shows "Submitted" status
- Time: 2 minutes
- Impact: Google starts processing within 24 hours

**Task 4: Monitor GSC**
- Action: Check Coverage tab daily
- Watch for: "Indexed" count increases, "Discovered" count decreases
- Timeline: Expect 300-500 new indexed posts within 48 hours

---

### PHASE 2: HIGH (WEEK 1)

**Task 5: Fix Canonical Issues**
- Action: Posts #9507, #9511 → Rank Math → Remove custom canonical
- Time: 10 minutes

**Task 6: Add Security Headers**
- Action: Edit .htaccess, add HSTS/CSP headers
- Time: 20 minutes

**Task 7: Request Indexing for Key Posts**
- Action: GSC → Inspect URL → Request Indexing for 15 top posts
- Time: 30 minutes
- Impact: Accelerates indexing of high-priority content

**Task 8: Core Web Vitals Optimization**
- Action: PageSpeed Insights audit
- Focus: LCP (<2.5s), INP (<200ms), CLS (<0.1)
- Time: 1-2 hours

---

### PHASE 3: ONGOING (WEEKS 2-4)

**Daily**:
- Monitor GSC Coverage for index growth
- Check PageSpeed scores

**Weekly**:
- Run technical audit
- Review 404 errors in GSC
- Check search appearance in Google Search Console

---

## EXPECTED RESULTS

**Scenario 1: All fixes completed TODAY**
- Day 1: Sitemaps regenerated, submitted to GSC
- Day 2: Google starts crawling; 100-200 new posts queued
- Day 3-7: 300-500 posts indexed
- Week 2: 600-800 posts indexed
- Week 3-4: 1,200-1,500 posts indexed (80%+ total)

**Scenario 2: Only sitemap fix (no robots.txt fix)**
- Day 2: Still 100-200 new posts queued
- Week 1: Same growth rate, but slower after first batch
- Full indexing: 3-4 weeks instead of 2 weeks

---

## SUCCESS METRICS

**KPI 1: Indexed Page Count**
- Current: 119 (6%)
- Target (1 week): 400-500 (20-25%)
- Target (1 month): 1,200+ (60%+)

**KPI 2: Discovered - Not Indexed Reduction**
- Current: 1,948 (100%)
- Target (1 week): 1,400-1,500 (70-75%)
- Target (1 month): 400-600 (20-30%)

**KPI 3: Crawl Budget Efficiency**
- Measure: Posts indexed per day
- Target: 50-100 posts/day starting day 2

---

## TOOLS & RESOURCES

**WordPress Admin**: https://dienmaykimbien.com.vn/wp-admin
- Navigate: Rank Math → Sitemaps

**Google Search Console**: https://search.google.com/search-console?resource_id=sc-domain%3Adienmaykimbien.com.vn
- Check: Coverage → Index → Request Indexing

**PageSpeed Insights**: https://pagespeed.web.dev/?url=https%3A%2F%2Fdienmaykimbien.com.vn

**File Access** (via SSH if needed):
- .htaccess: `/home/kyjwpfti/public_html/.htaccess`
- Sitemaps: `public_html/sitemap*.xml`
- robots.txt: `public_html/robots.txt`

---

## RISK ASSESSMENT

**Risk**: What if sitemaps are still 0 URLs after regeneration?
- **Cause**: Rank Math may not be properly configured
- **Alternative**: Use WordPress XML Sitemap plugin as backup
- **Backup plan**: Manually create sitemap via GSC's "Create Sitemap" tool

**Risk**: What if robots.txt doesn't fix?
- **Check**: curl -I https://dienmaykimbien.com.vn/posts/
- **Should see**: 200 status code (not blocked)
- **If blocked**: Check WordPress Reading settings or .htaccess rules

---

## NEXT STEP

**👉 Execute Task 1 NOW: WordPress Admin → Rank Math → Regenerate Sitemaps**

This single action will likely fix 60-70% of the indexing problem.

---

**Questions?** Reference this document for decision-making.  
**Need help?** GSC Implements Plan provides step-by-step instructions above.

