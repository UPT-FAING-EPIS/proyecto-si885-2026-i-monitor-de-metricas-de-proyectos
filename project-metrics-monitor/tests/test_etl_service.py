from __future__ import annotations

from pathlib import Path

from src.load.sqlite_loader import SQLiteLoader
from src.repositories.control_repository import EtlControlRepository
from src.services.etl_service import ETLService
from src.services.export_service import ExportService
from src.transform.dataset_builder import DatasetBuilder
from src.utils.logging_utils import get_logger
from verify import OfflineSampleGitHubClient


def build_service(tmp_path: Path) -> tuple[ETLService, SQLiteLoader]:
    project_root = Path(__file__).resolve().parent.parent
    db_path = tmp_path / "metrics.db"
    loader = SQLiteLoader(
        db_path=db_path,
        schema_path=project_root / "database" / "schema.sql",
        views_path=project_root / "database" / "views.sql",
        seed_path=project_root / "database" / "seed.sql",
    )
    service = ETLService(
        github_client=OfflineSampleGitHubClient(),
        dataset_builder=DatasetBuilder(),
        loader=loader,
        control_repository=EtlControlRepository(loader.connect),
        export_service=ExportService(db_path, tmp_path / "exports"),
        logger=get_logger("test"),
    )
    return service, loader


def test_etl_service_persists_and_exports(tmp_path: Path) -> None:
    service, loader = build_service(tmp_path)
    result = service.run(
        owner="sample-org",
        repos=["sample-repo"],
        since="2026-01-01",
        dry_run=False,
        export_csv=True,
        export_parquet=False,
        public_export=True,
    )

    etl_control_total = int(
        loader.fetch_dataframe("SELECT COUNT(*) AS total FROM etl_control").iloc[0]["total"]
    )
    assert result.dry_run is False
    assert etl_control_total == 1
    assert (tmp_path / "exports" / "vw_repo_summary.csv").exists()
    assert (tmp_path / "exports" / "public" / "vw_public_repo_summary.csv").exists()


def test_etl_service_dry_run_skips_persistence(tmp_path: Path) -> None:
    service, loader = build_service(tmp_path)
    result = service.run(
        owner="sample-org",
        repos=["sample-repo"],
        since="2026-01-01",
        dry_run=True,
        export_csv=False,
        export_parquet=False,
        public_export=False,
    )

    etl_control_total = int(
        loader.fetch_dataframe("SELECT COUNT(*) AS total FROM etl_control").iloc[0]["total"]
    )
    assert result.dry_run is True
    assert etl_control_total == 0
