from __future__ import annotations

import os
import subprocess
import sys
import time


def _env_flag(name: str, default: bool) -> bool:
    value = os.getenv(name)
    if value is None:
        return default
    return value.strip().lower() in {"1", "true", "yes", "on"}


def _build_command() -> list[str]:
    owner = os.environ["GITHUB_OWNER"].strip()
    repos = os.environ["GITHUB_REPOS"].strip()
    since = os.getenv("ETL_SINCE", "2026-01-01T00:00:00Z").strip()
    db_path = os.getenv("DB_PATH", "storage/project_metrics.db").strip()
    export_dir = os.getenv("EXPORT_DIR", "storage/exports").strip()

    command = [
        sys.executable,
        "-m",
        "src.run",
        "--owner",
        owner,
        "--repos",
        repos,
        "--since",
        since,
        "--db-path",
        db_path,
        "--export-dir",
        export_dir,
    ]
    if _env_flag("ETL_EXPORT_CSV", True):
        command.append("--export-csv")
    if _env_flag("ETL_EXPORT_PARQUET", True):
        command.append("--export-parquet")
    if _env_flag("ETL_PUBLIC_EXPORT", True):
        command.append("--public-export")
    if _env_flag("ETL_NO_TOKEN", False):
        command.append("--no-token")
    return command


def main() -> None:
    interval_hours = float(os.getenv("ETL_INTERVAL_HOURS", "12"))
    retry_minutes = float(os.getenv("ETL_RETRY_MINUTES", "15"))
    run_on_start = _env_flag("ETL_RUN_ON_START", True)
    wait_seconds = max(int(interval_hours * 3600), 60)
    retry_seconds = max(int(retry_minutes * 60), 60)
    command = _build_command()

    if not run_on_start:
        time.sleep(wait_seconds)

    while True:
        try:
            subprocess.run(command, check=True)
            time.sleep(wait_seconds)
        except subprocess.CalledProcessError as exc:
            print(f"render_worker_run_failed exit_code={exc.returncode}", flush=True)
            time.sleep(retry_seconds)


if __name__ == "__main__":
    main()
