from __future__ import annotations

import argparse
import os
from dataclasses import dataclass
from pathlib import Path

from src.utils.validators import validate_owner, validate_repos, validate_since


@dataclass(slots=True)
class Settings:
    owner: str
    repos: list[str]
    since: str
    db_path: Path
    export_dir: Path
    github_token: str | None
    dry_run: bool
    export_csv: bool
    export_parquet: bool
    public_export: bool


def load_dotenv(dotenv_path: Path) -> None:
    if not dotenv_path.exists():
        return
    for raw_line in dotenv_path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip())


def build_settings(args: argparse.Namespace, project_root: Path) -> Settings:
    load_dotenv(project_root / ".env")
    token = None if args.no_token else os.getenv("GITHUB_TOKEN")
    return Settings(
        owner=validate_owner(args.owner),
        repos=validate_repos(args.repos),
        since=validate_since(args.since),
        db_path=(project_root / args.db_path).resolve(),
        export_dir=(project_root / args.export_dir).resolve(),
        github_token=token,
        dry_run=bool(args.dry_run),
        export_csv=bool(args.export_csv),
        export_parquet=bool(args.export_parquet),
        public_export=bool(args.public_export),
    )
