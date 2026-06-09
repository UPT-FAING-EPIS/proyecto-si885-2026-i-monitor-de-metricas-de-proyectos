from __future__ import annotations

import json
import os
import subprocess
import sys
import threading
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import parse_qs, urlparse


def _env_flag(name: str, default: bool) -> bool:
    value = os.getenv(name)
    if value is None:
        return default
    return value.strip().lower() in {"1", "true", "yes", "on"}


def _build_etl_command() -> list[str]:
    owner = os.environ["GITHUB_OWNER"].strip()
    repos = os.environ["GITHUB_REPOS"].strip()
    since = os.getenv("ETL_SINCE", "2026-01-01T00:00:00Z").strip()
    db_path = os.getenv("DB_PATH", "runtime_data/project_metrics.db").strip()
    export_dir = os.getenv("EXPORT_DIR", "runtime_data/exports").strip()

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
    if _env_flag("ETL_EXPORT_PARQUET", False):
        command.append("--export-parquet")
    if _env_flag("ETL_PUBLIC_EXPORT", True):
        command.append("--public-export")
    if _env_flag("ETL_NO_TOKEN", False):
        command.append("--no-token")
    return command


def _ensure_dirs() -> Path:
    project_root = Path(__file__).resolve().parent.parent
    export_dir_raw = os.getenv("EXPORT_DIR", "runtime_data/exports").strip()
    export_dir = (project_root / export_dir_raw).resolve()
    export_dir.mkdir(parents=True, exist_ok=True)
    return export_dir


def _run_etl() -> dict[str, object]:
    command = _build_etl_command()
    completed = subprocess.run(command, check=True, capture_output=True, text=True)
    output = (completed.stdout or "").strip()
    if not output:
        return {"status": "ok"}
    try:
        return json.loads(output)
    except json.JSONDecodeError:
        return {"status": "ok", "stdout": output[-2000:]}


class _Handler(SimpleHTTPRequestHandler):
    server_version = "project-metrics-monitor"

    def do_GET(self) -> None:
        parsed = urlparse(self.path)
        if parsed.path.rstrip("/") == "/run":
            query = parse_qs(parsed.query)
            allow = (query.get("allow") or ["false"])[0].strip().lower() in {
                "1",
                "true",
                "yes",
                "on",
            }
            if not allow:
                self.send_response(400)
                self.send_header("Content-Type", "application/json; charset=utf-8")
                self.end_headers()
                self.wfile.write(
                    json.dumps(
                        {
                            "error": "missing_allow_flag",
                            "message": "Usa /run?allow=true para ejecutar el ETL manualmente.",
                        },
                        ensure_ascii=True,
                    ).encode("utf-8")
                )
                return

            def _background() -> None:
                try:
                    _run_etl()
                except Exception:
                    return

            threading.Thread(target=_background, daemon=True).start()
            self.send_response(202)
            self.send_header("Content-Type", "application/json; charset=utf-8")
            self.end_headers()
            self.wfile.write(json.dumps({"status": "accepted"}, ensure_ascii=True).encode("utf-8"))
            return
        super().do_GET()

    def log_message(self, format: str, *args: object) -> None:
        return


def main() -> None:
    export_dir = _ensure_dirs()
    if _env_flag("ETL_RUN_ON_START", True):
        _run_etl()

    port = int(os.getenv("PORT", "10000"))

    def handler(*args: object, **kwargs: object) -> _Handler:
        return _Handler(*args, directory=str(export_dir), **kwargs)

    httpd = ThreadingHTTPServer(("0.0.0.0", port), handler)
    httpd.serve_forever()


if __name__ == "__main__":
    main()
