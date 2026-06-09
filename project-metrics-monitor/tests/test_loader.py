from __future__ import annotations

from pathlib import Path

from src.load.sqlite_loader import SQLiteLoader
from src.transform.dataset_builder import DatasetBuilder
from tests.test_transform import build_bundle


def test_sqlite_loader_initializes_and_loads_dataset(tmp_path: Path) -> None:
    project_root = Path(__file__).resolve().parent.parent
    db_path = tmp_path / "metrics.db"
    loader = SQLiteLoader(
        db_path=db_path,
        schema_path=project_root / "database" / "schema.sql",
        views_path=project_root / "database" / "views.sql",
        seed_path=project_root / "database" / "seed.sql",
    )

    loader.initialize()
    loader.load_dataset(DatasetBuilder().build(build_bundle()))

    repo_count = int(
        loader.fetch_dataframe("SELECT COUNT(*) AS total FROM dim_repo").iloc[0]["total"]
    )
    throughput_count = int(
        loader.fetch_dataframe("SELECT COUNT(*) AS total FROM vw_throughput").iloc[0]["total"]
    )
    assert repo_count == 1
    assert throughput_count >= 1
