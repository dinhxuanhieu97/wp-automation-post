# -*- coding: utf-8 -*-
"""
Module B: SEO Content Generator — chuẩn E-E-A-T + Rank Math
===========================================================
Entry point (main.py gọi):

    article = await ai_writer.generate_article(config, keyword_set)

`keyword_set` là output của Module A (trends_scraper):
    {category, category_name, focus_keyword, lsi_keywords, search_intent, trending_angle}

Output `article`:
    {
        "title": "H1 <= 65 ký tự, focus keyword ở nửa đầu",
        "slug": "slug-khong-dau",
        "sapo": "100-150 từ, keyword trong 50 từ đầu",
        "content_markdown": "...",      # thân bài đầy đủ (H2/H3, bảng, FAQ, internal links)
        "content_html": "...",          # đã convert để đăng WP
        "faq": [{"question": ..., "answer": ...} x3],
        "faq_schema": {...},            # JSON-LD FAQPage (Rank Math/thủ công đều dùng được)
        "internal_links": [{"url", "anchor"}],
        "rank_math": {
            "focus_keyword": "...",
            "seo_title": "<= 60 ký tự, tối ưu CTR",
            "meta_description": "<= 155 ký tự, có CTA"
        },
        "tags": [...],
        "validation": {"passed": bool, "issues": [...]}
    }

Pipeline: nạp sitemap -> chọn ứng viên internal link -> prompt E-E-A-T ->
LLM (Anthropic, fallback OpenAI) -> parse JSON -> hậu kiểm (validator) ->
nếu thiếu internal link thì tự chèn ngữ cảnh -> retry 1 lần nếu fail nặng.
"""

from __future__ import annotations

import json
import logging
import os
import re
from typing import Any, Optional

from slugify import slugify

from modules.sitemap_links import SitemapLinkIndex, tokens_of, strip_accents

logger = logging.getLogger("ai_writer")

# ============================================================================
# 1. PROMPT SYSTEM — E-E-A-T, NLP-friendly, tiếng Việt
# ============================================================================

SYSTEM_PROMPT = """Bạn là chuyên gia Content SEO tiếng Việt với 10 năm kinh nghiệm trong ngành ĐIỆN MÁY - ĐIỆN LẠNH - GIA DỤNG, viết cho website Điện Máy Kim Biên (dienmaykimbien.com.vn) — nhà bán lẻ thiết bị điện, điện máy uy tín tại TP.HCM.

NGUYÊN TẮC E-E-A-T (bắt buộc thể hiện trong bài):
- Experience: viết như người ĐÃ TRỰC TIẾP dùng/lắp đặt/sửa sản phẩm; thêm chi tiết thực tế (số liệu điện năng, nhiệt độ, chi phí VNĐ, thời gian sử dụng).
- Expertise: giải thích thông số kỹ thuật chính xác (BTU, inverter, công suất W, dung tích lít...), so sánh có căn cứ.
- Authoritativeness: giọng tự tin, dẫn tiêu chuẩn/quy ước ngành khi phù hợp (nhãn năng lượng, tiêu chuẩn Việt Nam).
- Trustworthiness: trung thực về nhược điểm sản phẩm, không hứa hẹn quá đà, khuyến nghị an toàn điện.

NGUYÊN TẮC NLP & SEO:
- Câu chủ động, ngắn (dưới 25 từ), mỗi đoạn 2-4 câu. Trả lời trực tiếp câu hỏi ngay đầu mỗi mục (answer-first).
- Dùng focus keyword và biến thể LSI TỰ NHIÊN, mật độ ~0.8-1.2%, tuyệt đối không nhồi nhét.
- Từ nối mạch lạc, có danh sách gạch đầu dòng khi liệt kê, bảng khi so sánh.
- Viết cho người Việt: đơn vị VNĐ, ví dụ khí hậu nóng ẩm Việt Nam, thói quen tiêu dùng Việt.

CẤM: bịa số liệu nghiên cứu cụ thể không thể kiểm chứng, bịa tên chuyên gia, copy văn mẫu sáo rỗng ("trong thời đại công nghệ 4.0..."), mở bài vòng vo."""

USER_PROMPT_TEMPLATE = """Viết một bài viết chuẩn SEO hoàn chỉnh bằng tiếng Việt theo yêu cầu sau.

## THÔNG TIN BÀI VIẾT
- Focus keyword: "{focus_keyword}"
- LSI keywords (dùng tự nhiên, không bắt buộc đủ): {lsi_keywords}
- Search intent: {search_intent} ({intent_hint})
- Góc trending cần lồng ghép: "{trending_angle}"
- Danh mục: {category_name}
- Độ dài thân bài: {min_words}-{max_words} từ.

## CẤU TRÚC BẮT BUỘC
1. **title (H1)**: DƯỚI {title_max} ký tự, giật tít hấp dẫn kiểu báo chí, focus keyword nằm ở NỬA ĐẦU tiêu đề. Có thể dùng số, năm, hoặc lợi ích cụ thể.
2. **sapo**: 100-150 từ, giải quyết NGAY vấn đề người đọc đang tìm, focus keyword xuất hiện trong 50 từ đầu tiên.
3. **Thân bài**: tối thiểu {h2_min} thẻ H2 (markdown `##`), mỗi H2 có thể chứa H3 (`###`). Trình tự logic theo search intent.
4. **Bảng bắt buộc**: đúng 1 bảng markdown so sánh thông số kỹ thuật HOẶC ưu/nhược điểm (tối thiểu 3 cột hoặc 2 cột x 4 dòng).
5. **Internal links**: chèn TỐI THIỂU {links_min} link từ danh sách dưới đây vào thân bài, dạng markdown `[anchor text](url)`. Anchor text phải TỰ NHIÊN theo ngữ cảnh câu (viết lại có dấu, KHÔNG dùng nguyên cụm không dấu), KHÔNG dùng "click vào đây"/"xem tại đây". Chỉ dùng URL trong danh sách:
{link_candidates}
6. **faq**: đúng {faq_count} câu hỏi thường gặp thực tế người mua hay hỏi, câu trả lời 40-80 từ, trực tiếp.
7. **Kết bài**: 2-3 câu, có lời kêu gọi hành động nhẹ nhàng hướng về Điện Máy Kim Biên.

## SEO METADATA (Rank Math)
- seo_title: <= 60 ký tự, chứa focus keyword, tối ưu CTR (con số/năm/từ gợi cảm xúc), có thể khác H1.
- meta_description: <= 155 ký tự, chứa focus keyword + lời kêu gọi hành động (CTA).
- slug: không dấu, 3-6 từ, chứa từ chính của keyword.

## ĐỊNH DẠNG ĐẦU RA
Trả về DUY NHẤT một JSON object hợp lệ (không markdown fence, không giải thích):
{{
  "title": "...",
  "slug": "...",
  "sapo": "...",
  "content_markdown": "... (KHÔNG lặp lại title; bắt đầu từ sapo rồi đến ## H2 đầu tiên; bao gồm cả internal links và bảng; KHÔNG gồm FAQ - FAQ để riêng)",
  "faq": [{{"question": "...", "answer": "..."}}],
  "rank_math": {{"focus_keyword": "{focus_keyword}", "seo_title": "...", "meta_description": "..."}},
  "tags": ["3-6 tag ngắn tiếng Việt"]
}}"""

INTENT_HINTS = {
    "transactional": "người đọc muốn CHỌN MUA — tập trung tiêu chí chọn, so sánh model/giá, gợi ý theo ngân sách",
    "informational": "người đọc muốn HIỂU/LÀM — tập trung giải thích, hướng dẫn từng bước, mẹo thực tế",
}

RETRY_SUFFIX = """

## SỬA LỖI (lần trước bài chưa đạt)
Bài trước vi phạm: {issues}
Hãy viết lại và tuân thủ nghiêm ngặt TẤT CẢ yêu cầu, đặc biệt các mục vừa nêu."""


# ============================================================================
# 2. LLM CLIENT — claude_cli (gói Max, không tốn phí API) / Anthropic / OpenAI
# ============================================================================

async def _call_claude_cli(model: str, system: str, user: str,
                           timeout_sec: int = 600) -> str:
    """Gọi Claude qua CLI `claude -p` (Claude Code headless / Agent SDK).

    Xác thực bằng tài khoản Claude Pro/Max đã đăng nhập trên máy
    (`claude login` một lần) — usage trừ vào hạn mức subscription,
    KHÔNG cần ANTHROPIC_API_KEY, không phát sinh phí API.

    - Binary mặc định `claude`, đổi qua env CLAUDE_CLI_BIN nếu cài chỗ khác.
    - `model` nhận alias ("sonnet", "haiku", "opus") hoặc model ID đầy đủ;
      để trống thì dùng model mặc định của Claude Code.
    """
    import asyncio as aio
    import shutil

    binary = os.environ.get("CLAUDE_CLI_BIN", "claude")
    if shutil.which(binary) is None:
        raise RuntimeError(
            f"Không tìm thấy CLI '{binary}'. Cài Claude Code (npm install -g "
            f"@anthropic-ai/claude-code) và chạy `claude login` bằng tài khoản Max.")

    cmd = [binary, "-p", "--output-format", "text", "--system-prompt", system]
    if model:
        cmd += ["--model", model]

    proc = await aio.create_subprocess_exec(
        *cmd,
        stdin=aio.subprocess.PIPE, stdout=aio.subprocess.PIPE, stderr=aio.subprocess.PIPE)
    try:
        stdout, stderr = await aio.wait_for(
            proc.communicate(input=user.encode("utf-8")), timeout=timeout_sec)
    except aio.TimeoutError:
        proc.kill()
        raise RuntimeError(f"claude CLI timeout sau {timeout_sec}s")

    out = stdout.decode("utf-8", errors="replace").strip()
    if proc.returncode != 0 or not out:
        err = stderr.decode("utf-8", errors="replace").strip()[:500]
        raise RuntimeError(
            f"claude CLI exit={proc.returncode}: {err or 'không có output'} "
            f"(đã `claude login` chưa? hạn mức gói Max còn không?)")
    return out


async def _call_anthropic(model: str, system: str, user: str, max_tokens: int = 8000) -> str:
    from anthropic import AsyncAnthropic
    client = AsyncAnthropic(api_key=os.environ["ANTHROPIC_API_KEY"])
    msg = await client.messages.create(
        model=model, max_tokens=max_tokens, system=system,
        messages=[{"role": "user", "content": user}], temperature=0.7,
    )
    return "".join(b.text for b in msg.content if getattr(b, "type", "") == "text")


async def _call_openai(model: str, system: str, user: str, max_tokens: int = 8000) -> str:
    from openai import AsyncOpenAI
    client = AsyncOpenAI(api_key=os.environ["OPENAI_API_KEY"])
    resp = await client.chat.completions.create(
        model=model, max_tokens=max_tokens, temperature=0.7,
        response_format={"type": "json_object"},
        messages=[{"role": "system", "content": system}, {"role": "user", "content": user}],
    )
    return resp.choices[0].message.content


_PROVIDER_FN = {
    "claude_cli": _call_claude_cli,   # gói Pro/Max qua `claude -p` — không tốn phí API
    "anthropic": _call_anthropic,     # API key, tính phí theo token
    "openai": _call_openai,           # API key, tính phí theo token
}
_PROVIDER_KEY = {"anthropic": "ANTHROPIC_API_KEY", "openai": "OPENAI_API_KEY"}


async def call_llm(config: dict, system: str, user: str) -> str:
    """Gọi lần lượt chuỗi provider trong config, tự fallback khi lỗi/thiếu key.

    Chuỗi provider đọc từ config.content.llm_chain:
        [{"provider": "claude_cli", "model": "sonnet"}, ...]
    Nếu không có llm_chain thì dùng cặp llm_provider/llm_fallback_provider (cũ).
    """
    c = config.get("content", {})
    chain = c.get("llm_chain") or [
        {"provider": c.get("llm_provider", "anthropic"),
         "model": c.get("llm_model", "claude-sonnet-4-5")},
        {"provider": c.get("llm_fallback_provider", "openai"),
         "model": c.get("llm_fallback_model", "gpt-4o")},
    ]
    last_err: Optional[Exception] = None
    for step in chain:
        provider, model = step.get("provider", ""), step.get("model", "")
        fn = _PROVIDER_FN.get(provider)
        if fn is None:
            logger.warning("Provider không hỗ trợ: %s — bỏ qua", provider)
            continue
        key_name = _PROVIDER_KEY.get(provider)
        if key_name and not os.environ.get(key_name):
            logger.warning("Thiếu %s — bỏ qua provider %s", key_name, provider)
            continue
        try:
            return await fn(model, system, user)
        except Exception as e:  # noqa: BLE001
            logger.warning("LLM %s/%s lỗi: %s", provider, model, e)
            last_err = e
    raise RuntimeError(f"Tất cả LLM provider đều lỗi hoặc thiếu API key/CLI. Lỗi cuối: {last_err}")


def parse_llm_json(text: str) -> dict:
    """Parse JSON từ LLM, chịu được ```json fence và text thừa 2 đầu."""
    text = text.strip()
    text = re.sub(r"^```(?:json)?\s*", "", text)
    text = re.sub(r"\s*```$", "", text)
    try:
        return json.loads(text)
    except json.JSONDecodeError:
        # cắt từ '{' đầu tiên tới '}' cuối cùng
        m = re.search(r"\{.*\}", text, re.DOTALL)
        if m:
            return json.loads(m.group(0))
        raise


# ============================================================================
# 3. VALIDATOR — hậu kiểm chuẩn Senior SEO
# ============================================================================

def _word_count(text: str) -> int:
    return len(re.findall(r"\S+", text))


def _kw_in(text: str, keyword: str) -> bool:
    """Keyword xuất hiện trong text? So khớp không dấu, cho phép khác biệt nhỏ."""
    nt, nk = strip_accents(text.lower()), strip_accents(keyword.lower())
    if nk in nt:
        return True
    kw_tokens = tokens_of(keyword)
    return bool(kw_tokens) and len(kw_tokens & tokens_of(text)) >= max(1, len(kw_tokens) - 1)


def validate_article(article: dict, keyword_set: dict, config: dict) -> list[str]:
    """Trả về danh sách vi phạm (rỗng = đạt)."""
    c = config.get("content", {})
    issues: list[str] = []
    focus = keyword_set["focus_keyword"]

    title = article.get("title", "")
    if not title:
        issues.append("thiếu title")
    else:
        if len(title) > c.get("title_max_chars", 65):
            issues.append(f"title dài {len(title)} ký tự (> {c.get('title_max_chars', 65)})")
        if not _kw_in(title[: max(len(title) * 2 // 3, 20)], focus):
            issues.append("focus keyword không nằm ở nửa đầu title")

    sapo = article.get("sapo", "")
    wc_sapo = _word_count(sapo)
    if not 70 <= wc_sapo <= 200:
        issues.append(f"sapo {wc_sapo} từ (chuẩn 100-150)")
    first50 = " ".join(re.findall(r"\S+", sapo)[:50])
    if not _kw_in(first50, focus):
        issues.append("focus keyword không có trong 50 từ đầu sapo")

    body = article.get("content_markdown", "")
    h2_count = len(re.findall(r"(?m)^##\s+", body))
    if h2_count < c.get("h2_min", 3):
        issues.append(f"chỉ có {h2_count} H2 (cần >= {c.get('h2_min', 3)})")
    if not re.search(r"(?m)^\|.+\|\s*$\n^\|[\s:|-]+\|", body):
        issues.append("thiếu bảng markdown so sánh")

    wc_body = _word_count(body)
    if wc_body < c.get("min_words", 1200) * 0.75:
        issues.append(f"thân bài {wc_body} từ (mục tiêu >= {c.get('min_words', 1200)})")

    n_links = len(re.findall(r"\[[^\]]+\]\(https?://[^)]+\)", body))
    if n_links < c.get("internal_links_min", 3):
        issues.append(f"chỉ có {n_links} internal link (cần >= {c.get('internal_links_min', 3)})")

    faq = article.get("faq", [])
    if len(faq) != c.get("faq_count", 3):
        issues.append(f"FAQ có {len(faq)} câu (cần đúng {c.get('faq_count', 3)})")

    rm = article.get("rank_math", {})
    seo_title = rm.get("seo_title", "")
    meta = rm.get("meta_description", "")
    if not seo_title or len(seo_title) > 70:
        issues.append(f"seo_title dài {len(seo_title)} ký tự (> 70) hoặc thiếu")
    if not meta or len(meta) > c.get("meta_description_max_chars", 155):
        issues.append(f"meta_description dài {len(meta)} ký tự (> {c.get('meta_description_max_chars', 155)}) hoặc thiếu")
    elif not _kw_in(meta, focus):
        issues.append("meta_description thiếu focus keyword")

    return issues


# ============================================================================
# 4. INTERNAL LINK — kiểm soát & tự vá
# ============================================================================

def extract_links(body: str) -> list[dict]:
    return [{"anchor": a, "url": u}
            for a, u in re.findall(r"\[([^\]]+)\]\((https?://[^)]+)\)", body)]


def enforce_internal_links(body: str, candidates: list[dict], min_links: int,
                           base_host: str) -> tuple[str, list[dict]]:
    """Đảm bảo >= min_links link nội bộ. Nếu LLM chèn thiếu, tự chèn thêm vào
    cuối đoạn văn phù hợp nhất (đoạn có nhiều token trùng slug nhất)."""
    links = [l for l in extract_links(body) if base_host in l["url"]]
    used_urls = {l["url"] for l in links}
    remaining = [c for c in candidates if c["url"] not in used_urls]

    if len(links) >= min_links or not remaining:
        return body, links

    paragraphs = body.split("\n\n")
    for cand in remaining:
        if len(links) >= min_links:
            break
        cand_tokens = set(cand["anchor_hint"].split())
        # tìm đoạn văn thường (không heading/bảng/list) liên quan nhất
        best_i, best_score = -1, 0
        fallback_i, fallback_len = -1, 0
        for i, p in enumerate(paragraphs):
            if p.startswith(("#", "|", "-", "*", ">")) or len(p) < 80:
                continue
            if len(p) > fallback_len:
                fallback_i, fallback_len = i, len(p)
            score = len(cand_tokens & tokens_of(p))
            if score > best_score:
                best_i, best_score = i, score
        if best_i < 0:
            best_i = fallback_i  # không đoạn nào trùng token -> đoạn dài nhất
        if best_i < 0:
            continue
        anchor = cand["anchor_hint"]
        sentence = f" Bạn có thể tham khảo thêm bài viết [{anchor}]({cand['url']}) để nắm chi tiết hơn."
        paragraphs[best_i] = paragraphs[best_i].rstrip() + sentence
        links.append({"anchor": anchor, "url": cand["url"]})
        logger.info("Tự chèn internal link bổ sung: %s", cand["url"])

    return "\n\n".join(paragraphs), links


_TABLE_RE = re.compile(r"(?m)^\|.+\|\s*$\n^\|[\s:|-]+\|")


def enforce_table(body: str, article: dict, keyword_set: dict) -> str:
    """Đảm bảo body LUÔN có đúng 1 bảng markdown hợp lệ.

    LLM đôi khi bỏ sót bảng dù prompt đã yêu cầu (mục 4 CẤU TRÚC BẮT BUỘC),
    và trước đây validator chỉ log warning rồi vẫn publish draft có lỗi
    "thiếu bảng markdown so sánh" — bug khiến hàng loạt bài (Phong Thủy,
    Dúi...) lên site không có bảng. Hàm này tự vá y hệt cơ chế
    `enforce_internal_links`: nếu không tìm thấy bảng, tự dựng 1 bảng
    "Tóm tắt nhanh" từ FAQ (ưu tiên, vì luôn có sẵn nội dung thật) hoặc từ
    keyword_set nếu chưa có FAQ, rồi chèn ngay sau H2 đầu tiên.
    """
    if _TABLE_RE.search(body):
        return body

    faq = article.get("faq") or []
    rows: list[tuple[str, str]] = []
    if len(faq) >= 2:
        for item in faq[:4]:
            q = (item.get("question") or "").strip()
            a = (item.get("answer") or "").strip()
            if not q or not a:
                continue
            a_short = " ".join(a.split()[:18]) + ("…" if len(a.split()) > 18 else "")
            rows.append((q, a_short))
        header = ("Câu hỏi", "Trả lời nhanh")
    if not rows:
        focus = keyword_set.get("focus_keyword", "")
        rows = [
            ("Chủ đề", focus or "—"),
            ("Danh mục", keyword_set.get("category_name", "—")),
            ("Đối tượng phù hợp", "Người tiêu dùng phổ thông tại Việt Nam"),
            ("Cập nhật", "Nội dung mới nhất theo năm hiện tại"),
        ]
        header = ("Tiêu chí", "Thông tin")

    table_lines = [
        f"| {header[0]} | {header[1]} |",
        "|---|---|",
    ] + [f"| {q} | {a} |" for q, a in rows]
    table_md = "\n".join(table_lines)

    logger.warning(
        "Bài '%s': LLM không sinh bảng markdown, tự chèn bảng '%s' để đạt chuẩn.",
        article.get("title", keyword_set.get("focus_keyword", "?")), header[0])

    # Chèn ngay sau H2 đầu tiên (và đoạn văn ngay sau nó, nếu có) để bảng
    # không bị kẹt giữa 1 đoạn văn.
    paragraphs = body.split("\n\n")
    insert_at = 0
    for i, p in enumerate(paragraphs):
        if p.lstrip().startswith("## "):
            insert_at = i + 1
            if insert_at < len(paragraphs) and not paragraphs[insert_at].lstrip().startswith(("#", "|")):
                insert_at += 1
            break
    paragraphs.insert(insert_at, table_md)
    return "\n\n".join(paragraphs)


# ============================================================================
# 5. HẬU XỬ LÝ — markdown -> HTML, FAQ schema
# ============================================================================

def markdown_to_html(md_text: str) -> str:
    try:
        import markdown as md
        return md.markdown(md_text, extensions=["tables", "sane_lists"])
    except ImportError:
        logger.warning("Thiếu thư viện `markdown` — đăng dạng markdown thô.")
        return md_text


def build_faq_blocks(faq: list[dict]) -> tuple[str, dict]:
    """Trả về (html_faq, json_ld_schema) cho FAQ."""
    html_parts = ["<h2>Câu hỏi thường gặp</h2>"]
    for item in faq:
        html_parts.append(f"<h3>{item['question']}</h3>\n<p>{item['answer']}</p>")
    schema = {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": item["question"],
                "acceptedAnswer": {"@type": "Answer", "text": item["answer"]},
            }
            for item in faq
        ],
    }
    return "\n".join(html_parts), schema


# ============================================================================
# 6. ENTRY POINT
# ============================================================================

async def generate_article(config: dict, keyword_set: dict,
                           link_index: Optional[SitemapLinkIndex] = None) -> Optional[dict]:
    """Sinh bài viết hoàn chỉnh cho 1 keyword_set. Trả về None nếu thất bại."""
    c = config.get("content", {})
    focus = keyword_set["focus_keyword"]

    # ---- Internal link candidates từ sitemap ----
    if link_index is None:
        link_index = SitemapLinkIndex(config)
        await link_index.load()
    topic_text = " ".join([focus] + keyword_set.get("lsi_keywords", [])
                          + [keyword_set.get("trending_angle", "")])
    candidates = link_index.find_candidates(topic_text, top_n=8)
    if len(candidates) < c.get("internal_links_min", 3):
        # nới lỏng: lấy thêm theo tên danh mục
        extra = link_index.find_candidates(keyword_set.get("category_name", ""), top_n=5,
                                           exclude_urls={x["url"] for x in candidates})
        candidates += extra
    link_lines = "\n".join(
        f"   - {x['url']}  (chủ đề: {x['anchor_hint']})" for x in candidates) or "   (không có)"

    user_prompt = USER_PROMPT_TEMPLATE.format(
        focus_keyword=focus,
        lsi_keywords=", ".join(keyword_set.get("lsi_keywords", [])[:8]),
        search_intent=keyword_set.get("search_intent", "informational"),
        intent_hint=INTENT_HINTS.get(keyword_set.get("search_intent", ""), ""),
        trending_angle=keyword_set.get("trending_angle", "") or "không có",
        category_name=keyword_set.get("category_name", ""),
        min_words=c.get("min_words", 1200), max_words=c.get("max_words", 1800),
        title_max=c.get("title_max_chars", 65),
        h2_min=c.get("h2_min", 3),
        links_min=c.get("internal_links_min", 3),
        faq_count=c.get("faq_count", 3),
        link_candidates=link_lines,
    )

    base_host = re.sub(r"^https?://", "", config["site"]["base_url"]).strip("/")
    article: Optional[dict] = None
    issues: list[str] = []

    for attempt in (1, 2):  # tối đa 1 lần retry với prompt sửa lỗi
        prompt = user_prompt if attempt == 1 else user_prompt + RETRY_SUFFIX.format(
            issues="; ".join(issues))
        try:
            raw = await call_llm(config, SYSTEM_PROMPT, prompt)
            article = parse_llm_json(raw)
        except Exception as e:  # noqa: BLE001
            logger.error("Lần %d: LLM/parse lỗi cho '%s': %s", attempt, focus, e)
            continue

        # Vá internal link trước khi validate
        body, links = enforce_internal_links(
            article.get("content_markdown", ""), candidates,
            c.get("internal_links_min", 3), base_host)
        article["content_markdown"] = body
        article["internal_links"] = links

        # Vá bảng markdown trước khi validate (fallback nếu LLM bỏ sót,
        # xem docstring enforce_table — fix bug bài publish thiếu bảng).
        article["content_markdown"] = enforce_table(
            article["content_markdown"], article, keyword_set)

        issues = validate_article(article, keyword_set, config)
        if not issues:
            break
        logger.warning("Lần %d: bài '%s' còn %d lỗi: %s", attempt, focus, len(issues), issues)

    if article is None:
        return None

    # ---- Chuẩn hóa slug (không dấu, an toàn) ----
    article["slug"] = slugify(article.get("slug") or article.get("title", focus))[:80]

    # ---- FAQ html + schema, ghép HTML hoàn chỉnh ----
    faq = article.get("faq", [])
    faq_html, faq_schema = build_faq_blocks(faq) if faq else ("", {})
    body_html = markdown_to_html(article["content_markdown"])
    sapo_html = f"<p><strong>{article.get('sapo', '')}</strong></p>" if article.get("sapo") else ""
    schema_html = (f'<script type="application/ld+json">'
                   f'{json.dumps(faq_schema, ensure_ascii=False)}</script>' if faq_schema else "")
    article["content_html"] = "\n".join(x for x in (sapo_html, body_html, faq_html, schema_html) if x)
    article["faq_schema"] = faq_schema

    # ---- Đính kèm meta cho các module sau ----
    article["keyword_set"] = keyword_set
    article["validation"] = {"passed": not issues, "issues": issues}
    if issues:
        logging.getLogger("seo_report").warning(
            "Bài '%s' PHÁT HÀNH DẠNG DRAFT — chưa đạt chuẩn: %s", focus, "; ".join(issues))
    logger.info("Hoàn tất bài '%s' (%d từ, %d links, đạt chuẩn: %s)",
                article.get("title", "?"), _word_count(article["content_markdown"]),
                len(article.get("internal_links", [])), not issues)
    return article


# ----------------------------------------------------------------------------
# CLI test: python -m modules.ai_writer --keyword "máy lạnh inverter" [--mock]
# ----------------------------------------------------------------------------

async def _main() -> None:
    import argparse
    from pathlib import Path

    parser = argparse.ArgumentParser()
    parser.add_argument("--keyword", default="máy lạnh inverter tiết kiệm điện")
    parser.add_argument("--category", default="dien-lanh")
    parser.add_argument("--out", default="")
    args = parser.parse_args()

    logging.basicConfig(level=logging.INFO,
                        format="%(asctime)s [%(levelname)s] %(name)s: %(message)s")
    config = json.loads(
        (Path(__file__).resolve().parents[1] / "config.json").read_text(encoding="utf-8"))
    keyword_set = {
        "category": args.category, "category_name": args.category,
        "focus_keyword": args.keyword,
        "lsi_keywords": [f"{args.keyword} giá rẻ", f"{args.keyword} loại nào tốt"],
        "search_intent": "transactional", "trending_angle": "Mùa hè nắng nóng 40 độ",
    }
    article = await generate_article(config, keyword_set)
    if article and args.out:
        Path(args.out).write_text(json.dumps(article, ensure_ascii=False, indent=2),
                                  encoding="utf-8")
        print(f"Saved -> {args.out}")
    elif article:
        print(json.dumps({k: article[k] for k in
                          ("title", "slug", "rank_math", "internal_links", "validation")},
                         ensure_ascii=False, indent=2))


if __name__ == "__main__":
    import asyncio
    asyncio.run(_main())
