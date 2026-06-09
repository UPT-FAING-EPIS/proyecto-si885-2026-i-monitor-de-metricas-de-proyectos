from __future__ import annotations

import json
from dataclasses import asdict
from pathlib import Path

from src.cli.parser import build_parser
from src.extract.github_client import GitHubClient
from src.load.sqlite_loader import SQLiteLoader
from src.repositories.control_repository import EtlControlRepository
from src.services.etl_service import ETLService
from src.services.export_service import ExportService
from src.transform.dataset_builder import DatasetBuilder
from src.utils.config import build_settings
from src.utils.logging_utils import get_logger


def main() -> None:
    project_root = Path(__file__).resolve().parent.parent
    parser = build_parser()
    args = parser.parse_args()
    settings = build_settings(args, project_root)
    logger = get_logger("project_metrics_monitor")
    if settings.github_token is None:
        logger.warning(
            "github_token_missing",
            extra={
                "extra_fields": {
                    "message": "Se usaran endpoints publicos con rate limit reducido.",
                }
            },
        )
    loader = SQLiteLoader(
        db_path=settings.db_path,
        schema_path=project_root / "database" / "schema.sql",
        views_path=project_root / "database" / "views.sql",
        seed_path=project_root / "database" / "seed.sql",
    )
    service = ETLService(
        github_client=GitHubClient(token=settings.github_token, logger=logger),
        dataset_builder=DatasetBuilder(),
        loader=loader,
        control_repository=EtlControlRepository(loader.connect),
        export_service=ExportService(settings.db_path, settings.export_dir),
        logger=logger,
    )
    result = service.run(
        owner=settings.owner,
        repos=settings.repos,
        since=settings.since,
        dry_run=settings.dry_run,
        export_csv=settings.export_csv,
        export_parquet=settings.export_parquet,
        public_export=settings.public_export,
    )
    print(json.dumps(asdict(result), ensure_ascii=True, indent=2))


if __name__ == "__main__":
    main()
