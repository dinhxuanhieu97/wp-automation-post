# -*- coding: utf-8 -*-
"""Test Module C (media_optimizer) + Module D (wp_publisher) + pipeline end-to-end.

WP REST API được giả lập bằng httpx.MockTransport — không đụng site thật.
Riêng test live chỉ GET dữ liệu public (categories) — read-only, an toàn.
"""
import asyncio
import io
import json
import re
import sys
from pathlib import Path

import httpx
import pytest
from PIL import Image

SRC = Path(__file__).resolve().parents[1] / "src"
sys.path.insert(0, str(SRC))

from modules import media_optimizer, wp_publisher  # noqa: E402
from tests.test_ai_writer import make_good_article, KEYWORD_SET  # noqa: E402

CONFIG = json.loads((SRC / "config.json").read_text(encoding="utf-8"))


# ============================================================================
# Module C — media_optimizer
# ============================================================================

def test_optimize_to_webp_resizes(tmp_path):
    big = Image.new("RGB", (2400, 1600), (200, 30, 30))
    out = media_optimizer.optimize_to_webp(big, tmp_path / "test.webp", max_width=1200)
    img = Image.open(out)
    assert img.format == "WEBP"
    assert img.width == 1200 and img.height == 800   # giữ tỉ lệ


def test_optimize_to_webp_small_image_not_upscaled(tmp_path):
    small = Image.new("RGB", (800, 600))
    out = media_optimizer.optimize_to_webp(small, tmp_path / "s.webp")
    assert Image.open(out).width == 800


def test_optimize_accepts_raw_bytes(tmp_path):
    buf = io.BytesIO()
    Image.new("RGB", (1500, 1000)).save(buf, "JPEG")
    out = media_optimizer.optimize_to_webp(buf.getvalue(), tmp_path / "b.webp")
    assert Image.open(out).width == 1200


def test_alt_texts_unique_and_contains_lsi():
    alts = media_optimizer.build_alt_texts(KEYWORD_SET, 3)
    assert len(alts) == 3 and len(set(alts)) == 3
    assert alts[0].lower() == KEYWORD_SET["focus_keyword"]
    assert alts[1].lower() in [k.lower() for k in KEYWORD_SET["lsi_keywords"]]


def test_en_query_mapping():
    assert "air conditioner" in media_optimizer._en_query("máy lạnh inverter")
    assert "football" in media_optimizer._en_query("tivi xem world cup 2026")
    assert media_optimizer._en_query("abcxyz") == "modern home appliance interior"


def test_prepare_images_placeholder_when_no_keys(tmp_path, monkeypatch):
    monkeypatch.delenv("PEXELS_API_KEY", raising=False)
    monkeypatch.delenv("UNSPLASH_ACCESS_KEY", raising=False)
    images = asyncio.run(media_optimizer.prepare_images(CONFIG, KEYWORD_SET, out_dir=tmp_path))
    assert len(images) == CONFIG["media"]["images_per_post"]
    for i, im in enumerate(images):
        assert im["source"] == "placeholder"
        assert Path(im["path"]).exists()
        img = Image.open(im["path"])
        assert img.format == "WEBP" and img.width <= 1200
    # tên file chuẩn slug
    assert images[0]["filename"] == "may-lanh-inverter-tiet-kiem-dien.webp"
    assert images[1]["filename"].endswith("-2.webp")


# ============================================================================
# Module D — wp_publisher với Mock WP REST API
# ============================================================================

class FakeWP:
    """Mock WP REST API tối giản, ghi nhớ mọi request để assert."""

    def __init__(self, accept_meta: bool = True, fail_media: bool = False):
        self.accept_meta = accept_meta
        self.fail_media = fail_media
        self.posts: dict[int, dict] = {}
        self.media: list[dict] = []
        self.requests: list[tuple[str, str]] = []
        self._id = 100

    def handler(self, request: httpx.Request) -> httpx.Response:
        path = request.url.path
        self.requests.append((request.method, path))

        if path.endswith("/categories") and request.method == "GET":
            slug = request.url.params.get("slug", "")
            return httpx.Response(200, json=[{"id": 7, "slug": slug}] if slug else [])
        if path.endswith("/tags") and request.method == "GET":
            return httpx.Response(200, json=[])
        if path.endswith("/tags") and request.method == "POST":
            self._id += 1
            body = json.loads(request.content)
            return httpx.Response(201, json={"id": self._id, "name": body["name"]})
        if path.endswith("/media") and request.method == "POST":
            if self.fail_media:
                return httpx.Response(500, json={"message": "upload broken"})
            self._id += 1
            m = {"id": self._id, "source_url": f"https://cdn.fake/{self._id}.webp"}
            self.media.append(m)
            return httpx.Response(201, json=m)
        if re.search(r"/media/\d+$", path) and request.method == "POST":
            return httpx.Response(200, json={"id": int(path.rsplit("/", 1)[1])})
        if path.endswith("/posts") and request.method == "POST":
            self._id += 1
            body = json.loads(request.content)
            meta = body.get("meta", {}) if self.accept_meta else {}
            post = {"id": self._id, "status": body.get("status"),
                    "link": f"https://dienmaykimbien.com.vn/?p={self._id}",
                    "title": {"raw": body.get("title")}, "meta": meta,
                    "_payload": body}
            self.posts[self._id] = post
            return httpx.Response(201, json=post)
        m = re.search(r"/posts/(\d+)$", path)
        if m and request.method == "GET":
            return httpx.Response(200, json=self.posts[int(m.group(1))])
        return httpx.Response(404, json={"message": f"no route {path}"})


@pytest.fixture()
def article_with_images(tmp_path):
    art = make_good_article()
    art["content_html"] = "<p>sapo</p><h2>Mục 1</h2><p>nội dung</p>"
    imgs = []
    for i in range(2):
        p = tmp_path / f"img-{i}.webp"
        media_optimizer.optimize_to_webp(Image.new("RGB", (1200, 800)), p)
        imgs.append({"path": str(p), "filename": p.name, "alt": f"alt {i}", "caption": ""})
    art["images"] = imgs
    return art


@pytest.fixture(autouse=True)
def wp_creds(monkeypatch):
    monkeypatch.setenv("WP_USERNAME", "testuser")
    monkeypatch.setenv("WP_APP_PASSWORD", "aaaa bbbb cccc dddd")
    monkeypatch.delenv("POST_STATUS_OVERRIDE", raising=False)
    # cache module-level phải sạch giữa các test
    wp_publisher._category_cache.clear()
    wp_publisher._tag_cache.clear()


def test_publish_happy_path(article_with_images):
    fake = FakeWP()
    post = asyncio.run(wp_publisher.publish(
        CONFIG, article_with_images, KEYWORD_SET,
        transport=httpx.MockTransport(fake.handler)))
    assert post is not None and post["status"] == "draft"
    payload = fake.posts[post["id"]]["_payload"]
    assert payload["categories"] == [7]
    assert payload["meta"]["rank_math_focus_keyword"] == KEYWORD_SET["focus_keyword"]
    assert payload["slug"] == "may-lanh-inverter-tiet-kiem-dien"
    assert payload["featured_media"] == fake.media[0]["id"]
    # ảnh phụ được chèn vào content sau </h2>
    assert "cdn.fake" in payload["content"]
    assert post["rank_math_meta_sent"] is True
    assert len(fake.media) == 2


def test_publish_meta_rejected_flag(article_with_images):
    fake = FakeWP(accept_meta=False)
    post = asyncio.run(wp_publisher.publish(
        CONFIG, article_with_images, KEYWORD_SET,
        transport=httpx.MockTransport(fake.handler)))
    assert post is not None
    assert post["rank_math_meta_sent"] is False   # -> playwright_seo sẽ điền tay


def test_publish_survives_media_failure(article_with_images):
    fake = FakeWP(fail_media=True)
    post = asyncio.run(wp_publisher.publish(
        CONFIG, article_with_images, KEYWORD_SET,
        transport=httpx.MockTransport(fake.handler)))
    assert post is not None                       # bài vẫn đăng, không featured image
    assert "featured_media" not in fake.posts[post["id"]]["_payload"]


def test_publish_missing_credentials(article_with_images, monkeypatch):
    monkeypatch.delenv("WP_USERNAME", raising=False)
    monkeypatch.delenv("WP_APP_PASSWORD", raising=False)
    post = asyncio.run(wp_publisher.publish(CONFIG, article_with_images, KEYWORD_SET))
    assert post is None                           # trả None, không crash pipeline


def test_publish_status_override(article_with_images, monkeypatch):
    monkeypatch.setenv("POST_STATUS_OVERRIDE", "publish")
    fake = FakeWP()
    post = asyncio.run(wp_publisher.publish(
        CONFIG, article_with_images, KEYWORD_SET,
        transport=httpx.MockTransport(fake.handler)))
    assert post["status"] == "publish"


# ============================================================================
# Pipeline end-to-end: keyword_set -> ai_writer(mock LLM) -> media -> publish(mock WP)
# ============================================================================

def test_full_pipeline_end_to_end(tmp_path, monkeypatch):
    from modules import ai_writer
    from tests.test_ai_writer import MockLLM
    from modules.sitemap_links import SitemapLinkIndex, tokens_of

    # 1. ai_writer với mock LLM + sitemap offline
    idx = SitemapLinkIndex(CONFIG, cache_dir=tmp_path)
    idx.entries = [{"url": f"https://dienmaykimbien.com.vn/bai-{i}/", "slug": f"bai-{i}",
                    "type": "post", "tokens": sorted(tokens_of("may lanh tiet kiem dien"))}
                   for i in range(5)]
    monkeypatch.setattr(ai_writer, "call_llm",
                        MockLLM([json.dumps(make_good_article(), ensure_ascii=False)]))
    article = asyncio.run(ai_writer.generate_article(CONFIG, KEYWORD_SET, idx))
    assert article["validation"]["passed"]

    # 2. media (placeholder, không cần API key)
    monkeypatch.delenv("PEXELS_API_KEY", raising=False)
    monkeypatch.delenv("UNSPLASH_ACCESS_KEY", raising=False)
    article["images"] = asyncio.run(
        media_optimizer.prepare_images(CONFIG, KEYWORD_SET, out_dir=tmp_path / "m"))

    # 3. publish với mock WP
    fake = FakeWP()
    post = asyncio.run(wp_publisher.publish(
        CONFIG, article, KEYWORD_SET, transport=httpx.MockTransport(fake.handler)))
    assert post is not None and post["status"] == "draft"
    payload = fake.posts[post["id"]]["_payload"]
    assert "FAQPage" in payload["content"]            # schema đi theo bài
    assert payload["meta"]["rank_math_title"]
    assert len(payload["tags"]) >= 3


# ============================================================================
# Live read-only: REST API site thật có mở không? (không ghi gì)
# ============================================================================

def test_live_rest_api_categories_readonly():
    try:
        r = httpx.get(CONFIG["site"]["wp_api_base"] + "/categories",
                      params={"per_page": 20}, timeout=20, follow_redirects=True)
    except Exception:
        pytest.skip("Site không truy cập được")
    if r.status_code != 200:
        pytest.skip(f"REST categories trả {r.status_code} (site có thể chặn REST public)")
    slugs = {c["slug"] for c in r.json()}
    assert "the-thao" in slugs or len(slugs) > 0
