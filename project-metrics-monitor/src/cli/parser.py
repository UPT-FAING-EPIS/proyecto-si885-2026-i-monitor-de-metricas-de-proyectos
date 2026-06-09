from __future__ import annotations

import argparse
from datetime import UTC, datetime, timedelta


def build_parser() -> argparse.ArgumentParser:
    default_since = (datetime.now(UTC) - timedelta(days=30)).date().isoformat()
    parser = argparse.ArgumentParser(
        prog="python -m src.run",
        description="Monitor de Metricas de Proyectos - ETL GitHub a SQLite para Power BI",
    )
    parser.add_argument("--owner", required=True, help="Owner u organizacion de GitHub.")
    parser.add_argument(
        "--repos",
        required=True,
        help="Lista separada por comas de repositorios. Ejemplo: vscode,terminal",
    )
    parser.add_argument(
        "--since",
        default=default_since,
        help=(
            "Fecha o timestamp ISO-8601 para extraccion incremental. "
            "Ejemplo: 2026-01-01 o 2026-01-01T00:00:00Z"
        ),
    )
    parser.add_argument(
        "--db-path",
        default="database/project_metrics.db",
        help="Ruta del archivo SQLite destino.",
    )
    parser.add_argument(
        "--export-dir",
        default="exports",
        help="Directorio donde se guardan las exportaciones CSV y Parquet.",
    )
    parser.add_argument("--dry-run", action="store_true", help="Ejecuta sin persistir datos.")
    parser.add_argument(
        "--no-token",
        action="store_true",
        help="Fuerza el uso de endpoints publicos sin GITHUB_TOKEN.",
    )
    parser.add_argument(
        "--export-csv",
        action="store_true",
        help="Exporta tablas y vistas a archivos CSV.",
    )
    parser.add_argument(
        "--export-parquet",
        action="store_true",
        help="Exporta tablas y vistas a archivos Parquet.",
    )
    parser.add_argument(
        "--public-export",
        action="store_true",
        help="Exporta un dataset publico anonimo (sin PII ni mensajes de commit).",
    )
    return parser
