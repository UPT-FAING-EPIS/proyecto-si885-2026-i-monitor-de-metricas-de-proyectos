from __future__ import annotations

from argparse import Namespace
from pathlib import Path

import pytest

from src.utils.config import build_settings, load_dotenv
from src.utils.dates import parse_iso_date
from src.utils.validators import validate_owner, validate_repos, validate_since


def test_load_dotenv_and_build_settings(tmp_path: Path, monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.delenv("GITHUB_TOKEN", raising=False)
    (tmp_path / ".env").write_text("GITHUB_TOKEN=test-token\n", encoding="utf-8")
    load_dotenv(tmp_path / ".env")
    args = Namespace(
        owner="microsoft",
        repos="vscode,terminal",
        since="2026-01-01",
        db_path="database/project_metrics.db",
        export_dir="exports",
        dry_run=False,
        no_token=False,
        export_csv=True,
        export_parquet=True,
        public_export=False,
    )

    settings = build_settings(args, tmp_path)

    assert settings.owner == "microsoft"
    assert settings.repos == ["vscode", "terminal"]
    assert settings.github_token == "test-token"
    assert settings.export_csv is True
    assert settings.export_parquet is True
    assert settings.db_path.name == "project_metrics.db"
    assert settings.export_dir.name == "exports"


def test_build_settings_without_token_when_no_token_flag(tmp_path: Path) -> None:
    args = Namespace(
        owner="microsoft",
        repos="vscode",
        since="2026-01-01",
        db_path="database/project_metrics.db",
        export_dir="exports",
        dry_run=True,
        no_token=True,
        export_csv=False,
        export_parquet=False,
        public_export=False,
    )

    settings = build_settings(args, tmp_path)
    assert settings.github_token is None
    assert settings.dry_run is True


def test_validators_accept_expected_values() -> None:
    assert validate_owner("microsoft") == "microsoft"
    assert validate_repos("vscode,terminal") == ["vscode", "terminal"]
    assert validate_since("2026-01-01") == "2026-01-01"
    assert validate_since("2026-01-01T00:00:00Z").startswith("2026-01-01")
    assert parse_iso_date("2026-01-01").year == 2026


def test_validators_reject_bad_values() -> None:
    with pytest.raises(ValueError):
        validate_owner("bad owner")
    with pytest.raises(ValueError):
        validate_repos("")
