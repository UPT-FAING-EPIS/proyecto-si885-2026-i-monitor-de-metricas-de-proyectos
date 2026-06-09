from __future__ import annotations

from contextlib import closing
import sqlite3
from pathlib import Path

import pandas as pd


class ExportService:
    def __init__(self, db_path: Path, export_dir: Path) -> None:
        self.db_path = db_path
        self.export_dir = export_dir

    def export_all(self, export_csv: bool, export_parquet: bool) -> list[Path]:
        self.export_dir.mkdir(parents=True, exist_ok=True)
        exported_files: list[Path] = []
        objects = [
            "dim_date",
            "dim_repo",
            "dim_author",
            "dim_label",
            "fact_commits",
            "fact_prs",
            "fact_issues",
            "fact_releases",
            "fact_workflows",
            "fact_contributors",
            "vw_throughput",
            "vw_commits_daily",
            "vw_pr_status_summary",
            "vw_issue_status_summary",
            "vw_release_cadence",
            "vw_flow_trends_weekly",
            "vw_lead_time",
            "vw_cycle_time",
            "vw_aging",
            "vw_repo_summary",
            "vw_author_activity",
            "vw_release_summary",
            "vw_quality_metrics",
        ]
        with closing(sqlite3.connect(self.db_path)) as connection:
            for object_name in objects:
                frame = pd.read_sql_query(f"SELECT * FROM {object_name}", connection)
                if export_csv:
                    csv_path = self.export_dir / f"{object_name}.csv"
                    frame.to_csv(csv_path, index=False)
                    exported_files.append(csv_path)
                if export_parquet:
                    parquet_path = self.export_dir / f"{object_name}.parquet"
                    frame.to_parquet(parquet_path, index=False)
                    exported_files.append(parquet_path)
        return exported_files

    def export_public(self, export_csv: bool, export_parquet: bool) -> list[Path]:
        public_dir = self.export_dir / "public"
        public_dir.mkdir(parents=True, exist_ok=True)
        exported_files: list[Path] = []
        objects = [
            "dim_date",
            "dim_repo",
            "dim_label",
            "vw_public_author",
            "vw_public_commits",
            "vw_public_prs",
            "vw_public_issues",
            "vw_public_repo_summary",
            "vw_public_quality_metrics",
            "vw_public_throughput",
        ]
        with closing(sqlite3.connect(self.db_path)) as connection:
            for object_name in objects:
                frame = pd.read_sql_query(f"SELECT * FROM {object_name}", connection)
                if export_csv:
                    csv_path = public_dir / f"{object_name}.csv"
                    frame.to_csv(csv_path, index=False)
                    exported_files.append(csv_path)
                if export_parquet:
                    parquet_path = public_dir / f"{object_name}.parquet"
                    frame.to_parquet(parquet_path, index=False)
                    exported_files.append(parquet_path)
        return exported_files
