from __future__ import annotations

import argparse
import sqlite3
from contextlib import closing
from pathlib import Path

from src.extract.github_client import GitHubClient
from src.load.sqlite_loader import SQLiteLoader
from src.repositories.control_repository import EtlControlRepository
from src.services.etl_service import ETLService
from src.services.export_service import ExportService
from src.transform.dataset_builder import DatasetBuilder
from src.utils.dates import utc_now_iso
from src.utils.logging_utils import get_logger


class OfflineSampleGitHubClient:
    def __init__(self) -> None:
        now = utc_now_iso()
        self.repo = {
            "id": 1001,
            "full_name": "sample-org/sample-repo",
            "description": "Repositorio de muestra para verificacion local",
            "language": "Python",
            "stargazers_count": 10,
            "forks_count": 2,
            "open_issues_count": 1,
            "default_branch": "main",
            "archived": False,
            "visibility": "public",
            "created_at": now,
            "updated_at": now,
        }

    def get_repository(self, owner: str, repo: str) -> dict:
        return self.repo | {"full_name": f"{owner}/{repo}"}

    def get_commits(self, owner: str, repo: str, since: str) -> list[dict]:
        return [
            {
                "sha": "abc123",
                "author": {"id": "user:alice", "login": "alice", "type": "User"},
                "commit": {
                    "author": {"date": "2026-01-10T10:00:00Z", "name": "Alice"},
                    "message": "feat: initial sample pipeline",
                },
                "stats": {"additions": 50, "deletions": 5},
            }
        ]

    def get_pull_requests(self, owner: str, repo: str, since: str) -> list[dict]:
        return [
            {
                "id": "PR_kwDOAAA",
                "number": 1,
                "state": "MERGED",
                "createdAt": "2026-01-11T10:00:00Z",
                "mergedAt": "2026-01-12T16:00:00Z",
                "closedAt": "2026-01-12T16:00:00Z",
                "changedFiles": 4,
                "additions": 100,
                "deletions": 20,
                "commits": {"totalCount": 3},
                "author": {"id": "user:bob", "login": "bob", "name": "Bob"},
                "reviews": {
                    "nodes": [
                        {
                            "state": "CHANGES_REQUESTED",
                            "submittedAt": "2026-01-11T12:00:00Z",
                            "author": {"id": "user:carol", "login": "carol", "name": "Carol"},
                        }
                    ]
                },
                "labels": {
                    "nodes": [
                        {
                            "id": "label:enhancement",
                            "name": "enhancement",
                            "color": "a2eeef",
                            "description": "Feature",
                        }
                    ]
                },
            }
        ]

    def get_issues(self, owner: str, repo: str, since: str) -> list[dict]:
        return [
            {
                "id": "I_kwDOAAA",
                "number": 10,
                "state": "CLOSED",
                "createdAt": "2026-01-09T08:00:00Z",
                "closedAt": "2026-01-10T09:00:00Z",
                "author": {"id": "user:alice", "login": "alice", "name": "Alice"},
                "labels": {
                    "nodes": [
                        {"id": "label:bug", "name": "bug", "color": "d73a4a", "description": "Bug"}
                    ]
                },
            }
        ]

    def get_releases(self, owner: str, repo: str, since: str) -> list[dict]:
        return [
            {
                "id": 501,
                "published_at": "2026-01-15T10:00:00Z",
                "tag_name": "v1.0.0",
                "name": "Initial release",
                "draft": False,
                "prerelease": False,
            }
        ]

    def get_workflow_runs(self, owner: str, repo: str, since: str) -> list[dict]:
        return [
            {
                "id": 9001,
                "created_at": "2026-01-13T10:00:00Z",
                "updated_at": "2026-01-13T10:15:00Z",
                "run_started_at": "2026-01-13T10:01:00Z",
                "status": "completed",
                "conclusion": "success",
                "name": "CI",
                "head_branch": "main",
                "event": "push",
            }
        ]

    def get_contributors(self, owner: str, repo: str, since: str) -> list[dict]:
        return [
            {
                "author": {"id": "user:alice", "login": "alice", "name": "Alice"},
                "contributions": 5,
                "first_seen_at": "2026-01-10T10:00:00Z",
                "last_seen_at": "2026-01-12T16:00:00Z",
            }
        ]


def build_service(project_root: Path, db_path: Path, use_offline_sample: bool) -> ETLService:
    logger = get_logger("verify")
    loader = SQLiteLoader(
        db_path=db_path,
        schema_path=project_root / "database" / "schema.sql",
        views_path=project_root / "database" / "views.sql",
        seed_path=project_root / "database" / "seed.sql",
    )
    client = (
        OfflineSampleGitHubClient()
        if use_offline_sample
        else GitHubClient(token=None, logger=logger)
    )
    return ETLService(
        github_client=client,
        dataset_builder=DatasetBuilder(),
        loader=loader,
        control_repository=EtlControlRepository(loader.connect),
        export_service=ExportService(db_path, project_root / "exports"),
        logger=logger,
    )


def print_table_counts(db_path: Path) -> None:
    tables = [
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
        "bridge_issue_labels",
        "bridge_pr_labels",
        "etl_control",
    ]
    with closing(sqlite3.connect(db_path)) as connection:
        print("Conteos por tabla")
        for table in tables:
            count = connection.execute(f"SELECT COUNT(*) FROM {table}").fetchone()[0]
            print(f"- {table}: {count}")


def run_validation_queries(db_path: Path) -> None:
    validations = {
        "Repositorios resumen": (
            "SELECT repo_name, commits, prs_merged, issues_closed, releases " "FROM vw_repo_summary"
        ),
        "Throughput semanal": "SELECT repo_name, year_week, throughput_total FROM vw_throughput",
        "Calidad": "SELECT repo_name, bug_ratio, workflow_failure_rate FROM vw_quality_metrics",
    }
    with closing(sqlite3.connect(db_path)) as connection:
        for title, query in validations.items():
            rows = connection.execute(query).fetchall()
            print(title)
            for row in rows:
                print(f"- {row}")


def verify_referential_integrity(db_path: Path) -> None:
    checks = {
        "fact_commits.repo_id": """
            SELECT COUNT(*) FROM fact_commits fc
            LEFT JOIN dim_repo dr ON dr.repo_id = fc.repo_id
            WHERE dr.repo_id IS NULL
        """,
        "fact_prs.author_id": """
            SELECT COUNT(*) FROM fact_prs fp
            LEFT JOIN dim_author da ON da.author_id = fp.author_id
            WHERE da.author_id IS NULL
        """,
        "fact_issues.created_date_id": """
            SELECT COUNT(*) FROM fact_issues fi
            LEFT JOIN dim_date dd ON dd.date_id = fi.created_date_id
            WHERE fi.created_date_id IS NOT NULL AND dd.date_id IS NULL
        """,
    }
    with closing(sqlite3.connect(db_path)) as connection:
        print("Integridad referencial")
        for name, query in checks.items():
            invalid_count = connection.execute(query).fetchone()[0]
            print(f"- {name}: {invalid_count}")
            if invalid_count != 0:
                raise SystemExit(f"Integridad rota en {name}")


def main() -> None:
    project_root = Path(__file__).resolve().parent
    parser = argparse.ArgumentParser(
        description="Verificacion integral del Monitor de Metricas de Proyectos"
    )
    parser.add_argument("--db-path", default="database/verify_project_metrics.db")
    parser.add_argument("--owner", default="sample-org")
    parser.add_argument("--repos", default="sample-repo")
    parser.add_argument("--since", default="2026-01-01")
    parser.add_argument("--offline-sample", action="store_true")
    args = parser.parse_args()

    db_path = (project_root / args.db_path).resolve()
    service = build_service(project_root, db_path, use_offline_sample=args.offline_sample)
    result = service.run(
        owner=args.owner,
        repos=[repo.strip() for repo in args.repos.split(",") if repo.strip()],
        since=args.since,
        dry_run=False,
        export_csv=False,
        export_parquet=False,
        public_export=False,
    )
    print(f"Carga completada en {result.loaded_at}")
    print_table_counts(db_path)
    run_validation_queries(db_path)
    verify_referential_integrity(db_path)


if __name__ == "__main__":
    main()
