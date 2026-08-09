# -*- coding: utf-8 -*-
"""Test Module B (ai_writer) + sitemap_links.

Chạy:  pytest tests/ -v
Test LLM thật bị skip nếu không có API key; toàn bộ logic còn lại
được test bằng mock LLM (không tốn token, chạy được trong CI).
"""
import asyncio
import json
import re
import sys
from pathlib import Path

import pytest

SRC = Path(__file__).resolve().parents[1] / "src"
sys.path.insert(0, str(SRC))

from modules import ai_writer  # noqa: E402
from modules.sitemap_links import SitemapLinkIndex, tokens_of, slug_to_phrase  # noqa: E402

CONFIG = json.loads((SRC / "config.json").read_text(encoding="utf-8"))

KEYWORD_SET = {
    "category": "dien-lanh",
    "category_name": "Điện lạnh",
    "focus_keyword": "máy lạnh inverter tiết kiệm điện",
    "lsi_keywords": ["máy lạnh inverter giá rẻ", "máy lạnh tiết kiệm điện nhất",
                     "điều hòa inverter loại nào tốt"],
    "search_intent": "transactional",
    "trending_angle": "Mùa hè nắng nóng 40 độ",
}


# ----------------------------------------------------------------------------
# Helper: tạo bài viết mẫu ĐẠT CHUẨN từ LLM giả
# ----------------------------------------------------------------------------

def make_good_article(n_links: int = 3) -> dict:
    para = ("Máy lạnh inverter hiện đại giúp gia đình Việt giảm đáng kể tiền điện "
            "mỗi tháng nhờ máy nén biến tần thông minh vận hành êm ái và bền bỉ. " * 8)
    links = "\n\n".join(
        f"Nội dung tham khảo về chủ đề điện lạnh máy lạnh rất hữu ích cho bạn đọc. "
        f"Xem thêm [mẹo tiết kiệm điện số {i}](https://dienmaykimbien.com.vn/link-{i}/) nhé. " + para
        for i in range(n_links))
    body = f"""{para}

## Máy lạnh inverter là gì và vì sao tiết kiệm điện?

{para}

### Nguyên lý máy nén biến tần

{para}

## So sánh máy lạnh inverter và máy lạnh thường

| Tiêu chí | Inverter | Non-inverter |
|---|---|---|
| Điện năng | Tiết kiệm 30-50% | Tốn điện hơn |
| Giá bán | Cao hơn 2-3 triệu | Rẻ hơn |
| Độ ồn | Êm (~19dB) | Ồn hơn |
| Độ bền | Cao nếu điện ổn định | Bền, dễ sửa |

{links}

## Kinh nghiệm chọn mua máy lạnh inverter theo diện tích phòng

{para}

## Kết luận

Chọn đúng máy lạnh inverter giúp bạn tiết kiệm điện lâu dài. Ghé Điện Máy Kim Biên để được tư vấn tận tình."""
    sapo = ("Máy lạnh inverter tiết kiệm điện đang là lựa chọn hàng đầu của các gia đình Việt "
            "trong mùa hè nắng nóng 40 độ. Bài viết này giúp bạn hiểu rõ nguyên lý biến tần, "
            "so sánh chi tiết với máy lạnh thường và nắm trọn kinh nghiệm chọn mua theo diện tích "
            "phòng, ngân sách thực tế. Chúng tôi đã lắp đặt hàng nghìn bộ máy cho khách hàng "
            "TP.HCM nên hiểu rõ loại nào bền, loại nào hay lỗi. Đọc xong bạn sẽ tự tin chọn được "
            "chiếc máy phù hợp nhất, tránh mua nhầm model tốn điện, và biết cách dùng để hóa đơn "
            "điện giảm rõ rệt ngay tháng đầu tiên sử dụng thiết bị mới trong nhà mình.")
    return {
        "title": "Máy lạnh inverter tiết kiệm điện: 7 kinh nghiệm chọn 2026",
        "slug": "may-lanh-inverter-tiet-kiem-dien",
        "sapo": sapo,
        "content_markdown": body,
        "faq": [
            {"question": "Máy lạnh inverter tiết kiệm bao nhiêu điện?",
             "answer": "Trung bình 30-50% so với máy thường, tùy cách sử dụng và diện tích phòng. Dùng đúng cách, hóa đơn điện giảm rõ sau 1 tháng."},
            {"question": "Máy lạnh inverter có bền không?",
             "answer": "Bền nếu nguồn điện ổn định và vệ sinh định kỳ 3-6 tháng/lần. Nên lắp thêm ổn áp ở khu vực điện yếu để bảo vệ bo mạch."},
            {"question": "Nên mua máy lạnh inverter hãng nào?",
             "answer": "Daikin, Panasonic bền và êm; Casper, TCL hợp ngân sách. Chọn theo diện tích phòng và chế độ bảo hành tại khu vực của bạn."},
        ],
        "rank_math": {
            "focus_keyword": "máy lạnh inverter tiết kiệm điện",
            "seo_title": "Máy Lạnh Inverter Tiết Kiệm Điện: Top 7 Kinh Nghiệm 2026",
            "meta_description": "Máy lạnh inverter tiết kiệm điện 30-50% nếu chọn đúng. Xem ngay 7 kinh nghiệm chọn mua chuẩn 2026 — tư vấn miễn phí tại Điện Máy Kim Biên!",
        },
        "tags": ["máy lạnh inverter", "tiết kiệm điện", "điện lạnh"],
    }


def make_link_candidates() -> list[dict]:
    return [
        {"url": f"https://dienmaykimbien.com.vn/bai-viet-{i}/", "slug": f"bai-viet-{i}",
         "type": "post", "anchor_hint": f"meo tiet kiem dien may lanh so {i}",
         "score": 3.0, "matched": ["may", "lanh"]}
        for i in range(6)
    ]


class MockLLM:
    """LLM giả: trả về danh sách response định sẵn theo từng lần gọi."""

    def __init__(self, responses):
        self.responses = list(responses)
        self.calls = 0

    async def __call__(self, config, system, user):
        self.calls += 1
        return self.responses.pop(0) if self.responses else json.dumps(make_good_article(),
                                                                       ensure_ascii=False)


# ----------------------------------------------------------------------------
# 1. Unit: tiện ích ngôn ngữ & parse
# ----------------------------------------------------------------------------

def test_tokens_and_slug_phrase():
    assert tokens_of("Máy Lạnh Inverter") == {"may", "lanh", "inverter"}
    assert slug_to_phrase("vi-tri-dat-tu-lanh") == "vi tri dat tu lanh"
    # stopword bị loại
    assert "cua" not in tokens_of("cửa của nhà")


def test_parse_llm_json_variants():
    obj = {"title": "abc", "x": [1, 2]}
    raw = json.dumps(obj, ensure_ascii=False)
    assert ai_writer.parse_llm_json(raw) == obj
    assert ai_writer.parse_llm_json(f"```json\n{raw}\n```") == obj
    assert ai_writer.parse_llm_json(f"Đây là kết quả:\n{raw}\nHết.") == obj
    with pytest.raises(Exception):
        ai_writer.parse_llm_json("không phải json")


# ----------------------------------------------------------------------------
# 2. Unit: validator
# ----------------------------------------------------------------------------

def test_validator_passes_good_article():
    issues = ai_writer.validate_article(make_good_article(), KEYWORD_SET, CONFIG)
    assert issues == [], f"Bài chuẩn nhưng validator báo lỗi: {issues}"


def test_validator_catches_violations():
    bad = make_good_article()
    bad["title"] = "Tiêu đề siêu dài " * 8                      # > 65 ký tự, không keyword
    bad["sapo"] = "Ngắn quá."                                   # thiếu từ + thiếu keyword
    bad["content_markdown"] = "## Một H2 duy nhất\n\nNội dung ngắn."  # thiếu H2/bảng/link/từ
    bad["faq"] = bad["faq"][:1]                                  # thiếu FAQ
    bad["rank_math"]["meta_description"] = "x" * 200             # quá dài
    issues = ai_writer.validate_article(bad, KEYWORD_SET, CONFIG)
    joined = " | ".join(issues)
    for expected in ("title dài", "sapo", "H2", "bảng", "internal link", "FAQ",
                     "meta_description", "thân bài"):
        assert expected in joined, f"Validator bỏ sót lỗi '{expected}'. Issues: {issues}"


def test_validator_meta_missing_keyword():
    art = make_good_article()
    art["rank_math"]["meta_description"] = "Mua hàng ngay hôm nay giảm giá cực sốc!"
    issues = ai_writer.validate_article(art, KEYWORD_SET, CONFIG)
    assert any("meta_description thiếu focus keyword" in i for i in issues)


# ----------------------------------------------------------------------------
# 3. Unit: internal link enforcement
# ----------------------------------------------------------------------------

def test_enforce_links_keeps_existing():
    art = make_good_article(n_links=4)
    body, links = ai_writer.enforce_internal_links(
        art["content_markdown"], make_link_candidates(), 3, "dienmaykimbien.com.vn")
    assert len(links) >= 3
    assert body == art["content_markdown"]  # đủ link -> không sửa gì


def test_enforce_links_auto_inserts():
    art = make_good_article(n_links=0)  # LLM "quên" chèn link
    body, links = ai_writer.enforce_internal_links(
        art["content_markdown"], make_link_candidates(), 3, "dienmaykimbien.com.vn")
    assert len(links) >= 3, f"Chỉ vá được {len(links)} link"
    for l in links:
        assert l["url"] in body
    # link không được chèn vào heading hoặc bảng
    for line in body.split("\n"):
        if line.startswith(("#", "|")):
            assert "https://dienmaykimbien.com.vn/bai-viet-" not in line


def test_enforce_links_ignores_external():
    body = "Đoạn văn có [link ngoài](https://google.com/abc) " + "nội dung " * 30
    out, links = ai_writer.enforce_internal_links(
        body, make_link_candidates()[:1], 1, "dienmaykimbien.com.vn")
    # link ngoài không được tính, phải chèn thêm 1 link nội bộ
    assert len(links) == 1 and "dienmaykimbien.com.vn" in links[0]["url"]


# ----------------------------------------------------------------------------
# 4. Unit: FAQ schema + HTML
# ----------------------------------------------------------------------------

def test_faq_schema_structure():
    faq = make_good_article()["faq"]
    html, schema = ai_writer.build_faq_blocks(faq)
    assert schema["@type"] == "FAQPage" and len(schema["mainEntity"]) == 3
    assert schema["mainEntity"][0]["acceptedAnswer"]["text"].startswith("Trung bình")
    assert html.count("<h3>") == 3


def test_markdown_to_html_table():
    html = ai_writer.markdown_to_html("| A | B |\n|---|---|\n| 1 | 2 |")
    assert "<table>" in html and "<td>1</td>" in html


# ----------------------------------------------------------------------------
# 5. Integration: generate_article end-to-end với mock LLM
# ----------------------------------------------------------------------------

@pytest.fixture()
def offline_index(tmp_path):
    """SitemapLinkIndex nạp từ cache giả — không gọi mạng."""
    idx = SitemapLinkIndex(CONFIG, cache_dir=tmp_path)
    idx.entries = [
        {"url": c["url"], "slug": c["slug"], "type": "post",
         "tokens": sorted(tokens_of(c["anchor_hint"]))}
        for c in make_link_candidates()
    ] + [{"url": "https://dienmaykimbien.com.vn/cach-chon-tivi-xem-world-cup-2026/",
          "slug": "cach-chon-tivi-xem-world-cup-2026", "type": "post",
          "tokens": sorted(tokens_of("cach chon tivi xem world cup 2026"))}]
    return idx


def test_generate_article_happy_path(monkeypatch, offline_index):
    mock = MockLLM([json.dumps(make_good_article(), ensure_ascii=False)])
    monkeypatch.setattr(ai_writer, "call_llm", mock)
    article = asyncio.run(ai_writer.generate_article(CONFIG, KEYWORD_SET, offline_index))
    assert article is not None
    assert article["validation"]["passed"] is True
    assert mock.calls == 1                       # không retry
    assert len(article["internal_links"]) >= 3
    assert article["slug"] == "may-lanh-inverter-tiet-kiem-dien"
    assert "<table>" in article["content_html"]
    assert '"@type": "FAQPage"' in article["content_html"]
    assert article["rank_math"]["focus_keyword"] == KEYWORD_SET["focus_keyword"]
    assert len(article["rank_math"]["meta_description"]) <= 155


def test_generate_article_retry_on_bad_first_attempt(monkeypatch, offline_index):
    bad = make_good_article()
    bad["faq"] = []           # vi phạm -> phải retry
    bad["sapo"] = "ngắn"
    mock = MockLLM([json.dumps(bad, ensure_ascii=False),
                    json.dumps(make_good_article(), ensure_ascii=False)])
    monkeypatch.setattr(ai_writer, "call_llm", mock)
    article = asyncio.run(ai_writer.generate_article(CONFIG, KEYWORD_SET, offline_index))
    assert mock.calls == 2                       # có retry đúng 1 lần
    assert article["validation"]["passed"] is True


def test_generate_article_patches_missing_links(monkeypatch, offline_index):
    art = make_good_article(n_links=0)           # LLM quên link cả 2 lần
    mock = MockLLM([json.dumps(art, ensure_ascii=False),
                    json.dumps(art, ensure_ascii=False)])
    monkeypatch.setattr(ai_writer, "call_llm", mock)
    article = asyncio.run(ai_writer.generate_article(CONFIG, KEYWORD_SET, offline_index))
    # enforce_internal_links phải tự vá đủ 3 link dù LLM không chèn
    assert len(article["internal_links"]) >= 3
    assert article["validation"]["passed"] is True


def test_generate_article_slug_vietnamese_safe(monkeypatch, offline_index):
    art = make_good_article()
    art["slug"] = "Máy Lạnh Đây Nhé!!!"          # LLM trả slug có dấu
    mock = MockLLM([json.dumps(art, ensure_ascii=False)])
    monkeypatch.setattr(ai_writer, "call_llm", mock)
    article = asyncio.run(ai_writer.generate_article(CONFIG, KEYWORD_SET, offline_index))
    assert re.fullmatch(r"[a-z0-9-]+", article["slug"]), article["slug"]


def test_generate_article_llm_totally_fails(monkeypatch, offline_index):
    async def dead_llm(config, system, user):
        raise RuntimeError("no provider")
    monkeypatch.setattr(ai_writer, "call_llm", dead_llm)
    article = asyncio.run(ai_writer.generate_article(CONFIG, KEYWORD_SET, offline_index))
    assert article is None                       # trả None, không raise


# ----------------------------------------------------------------------------
# 6. Live: sitemap thật (skip nếu offline) & LLM thật (skip nếu thiếu key)
# ----------------------------------------------------------------------------

def test_live_sitemap_index(tmp_path):
    idx = SitemapLinkIndex(CONFIG, cache_dir=tmp_path)
    n = asyncio.run(idx.load(force_refresh=True))
    if n == 0:
        pytest.skip("Site không truy cập được từ môi trường này")
    assert n > 500
    cands = idx.find_candidates("tivi xem world cup 2026", top_n=5)
    assert cands and "world-cup" in cands[0]["slug"]
    # cache hoạt động
    idx2 = SitemapLinkIndex(CONFIG, cache_dir=tmp_path)
    assert asyncio.run(idx2.load()) == n


@pytest.mark.skipif(
    not (__import__("os").environ.get("ANTHROPIC_API_KEY")
         or __import__("os").environ.get("OPENAI_API_KEY")),
    reason="Không có API key LLM trong môi trường")
def test_live_llm_generate():
    article = asyncio.run(ai_writer.generate_article(CONFIG, KEYWORD_SET))
    assert article is not None
    assert article["validation"]["passed"], article["validation"]["issues"]
