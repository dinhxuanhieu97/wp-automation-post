# -*- coding: utf-8 -*-
"""Test provider `claude_cli` (gói Max qua `claude -p`) trong ai_writer.

Dùng fake CLI (script bash giả lập binary `claude`) — không tốn hạn mức thật.
Test cuối gọi CLI thật với prompt 1 câu, tự skip nếu CLI chưa login.
"""
import asyncio
import json
import os
import shutil
import stat
import sys
from pathlib import Path

import pytest

SRC = Path(__file__).resolve().parents[1] / "src"
sys.path.insert(0, str(SRC))

from modules import ai_writer  # noqa: E402
from tests.test_ai_writer import make_good_article  # noqa: E402

CONFIG = json.loads((SRC / "config.json").read_text(encoding="utf-8"))


def _fake_cli(tmp_path: Path, script_body: str) -> str:
    """Tạo binary giả tên `claude` trả về nội dung định sẵn."""
    p = tmp_path / "claude"
    p.write_text("#!/bin/bash\n" + script_body, encoding="utf-8")
    p.chmod(p.stat().st_mode | stat.S_IEXEC)
    return str(p)


def test_config_chain_has_claude_cli_first():
    chain = CONFIG["content"]["llm_chain"]
    assert chain[0]["provider"] == "claude_cli"
    assert [s["provider"] for s in chain] == ["claude_cli", "anthropic", "openai"]


def test_cli_success(tmp_path, monkeypatch):
    art = json.dumps(make_good_article(), ensure_ascii=False)
    payload = tmp_path / "payload.json"
    payload.write_text(art, encoding="utf-8")
    # fake CLI: đọc hết stdin (như CLI thật), in bài viết ra stdout
    fake = _fake_cli(tmp_path, f'cat > /dev/null\ncat "{payload}"\n')
    monkeypatch.setenv("CLAUDE_CLI_BIN", fake)

    out = asyncio.run(ai_writer._call_claude_cli("sonnet", "system x", "user y"))
    assert json.loads(out)["title"].startswith("Máy lạnh inverter")


def test_cli_passes_args_and_stdin(tmp_path, monkeypatch):
    log = tmp_path / "args.log"
    fake = _fake_cli(tmp_path, f'echo "$@" > "{log}"\ncat >> "{log}"\necho OK\n')
    monkeypatch.setenv("CLAUDE_CLI_BIN", fake)

    asyncio.run(ai_writer._call_claude_cli("sonnet", "SYS_PROMPT_ABC", "USER_PROMPT_XYZ"))
    logged = log.read_text(encoding="utf-8")
    assert "-p" in logged and "--output-format text" in logged
    assert "--system-prompt SYS_PROMPT_ABC" in logged
    assert "--model sonnet" in logged
    assert "USER_PROMPT_XYZ" in logged            # user prompt đi qua stdin


def test_cli_no_model_flag_when_empty(tmp_path, monkeypatch):
    log = tmp_path / "args.log"
    fake = _fake_cli(tmp_path, f'echo "$@" > "{log}"\ncat > /dev/null\necho OK\n')
    monkeypatch.setenv("CLAUDE_CLI_BIN", fake)
    asyncio.run(ai_writer._call_claude_cli("", "s", "u"))
    assert "--model" not in log.read_text(encoding="utf-8")


def test_cli_error_raises(tmp_path, monkeypatch):
    fake = _fake_cli(tmp_path, 'cat > /dev/null\necho "Not logged in" >&2\nexit 1\n')
    monkeypatch.setenv("CLAUDE_CLI_BIN", fake)
    with pytest.raises(RuntimeError, match="exit=1"):
        asyncio.run(ai_writer._call_claude_cli("sonnet", "s", "u"))


def test_cli_missing_binary_raises(monkeypatch):
    monkeypatch.setenv("CLAUDE_CLI_BIN", "claude-khong-ton-tai-xyz")
    with pytest.raises(RuntimeError, match="Không tìm thấy CLI"):
        asyncio.run(ai_writer._call_claude_cli("sonnet", "s", "u"))


def test_call_llm_falls_back_from_cli_to_api(tmp_path, monkeypatch):
    """CLI hỏng -> chain phải rơi xuống anthropic API."""
    fake = _fake_cli(tmp_path, 'cat > /dev/null\nexit 1\n')
    monkeypatch.setenv("CLAUDE_CLI_BIN", fake)
    monkeypatch.setenv("ANTHROPIC_API_KEY", "sk-ant-test")

    called = {}

    async def fake_anthropic(model, system, user, max_tokens=8000):
        called["model"] = model
        return '{"ok": true}'

    monkeypatch.setattr(ai_writer, "_call_anthropic", fake_anthropic)
    # rebuild bảng dispatch vì nó giữ tham chiếu hàm cũ
    monkeypatch.setitem(ai_writer._PROVIDER_FN, "anthropic", fake_anthropic)

    out = asyncio.run(ai_writer.call_llm(CONFIG, "s", "u"))
    assert out == '{"ok": true}'
    assert called["model"] == "claude-sonnet-4-5"


def test_call_llm_all_fail(tmp_path, monkeypatch):
    fake = _fake_cli(tmp_path, 'cat > /dev/null\nexit 1\n')
    monkeypatch.setenv("CLAUDE_CLI_BIN", fake)
    monkeypatch.delenv("ANTHROPIC_API_KEY", raising=False)
    monkeypatch.delenv("OPENAI_API_KEY", raising=False)
    with pytest.raises(RuntimeError, match="Tất cả LLM provider"):
        asyncio.run(ai_writer.call_llm(CONFIG, "s", "u"))


# ----------------------------------------------------------------------------
# Live: CLI thật (skip nếu chưa cài / chưa login) — prompt 1 câu, chi phí ~0
# ----------------------------------------------------------------------------

@pytest.mark.skipif(shutil.which(os.environ.get("CLAUDE_CLI_BIN", "claude")) is None,
                    reason="Chưa cài Claude Code CLI")
def test_live_claude_cli_smoke():
    try:
        out = asyncio.run(ai_writer._call_claude_cli(
            "haiku", "Bạn là máy echo. Chỉ trả về đúng chuỗi được yêu cầu.",
            'Trả về đúng chuỗi JSON sau, không thêm gì khác: {"ping": "pong"}',
            timeout_sec=120))
    except RuntimeError as e:
        pytest.skip(f"CLI chưa sẵn sàng/chưa login: {e}")
    assert ai_writer.parse_llm_json(out) == {"ping": "pong"}
