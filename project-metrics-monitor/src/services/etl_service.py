from __future__ import annotations

import logging
from datetime import datetime, timedelta, timezone
from typing import Any

from src.models.entities import EtlResult, ExtractedBundle, RepositoryPayload
from src.utils.dates import parse_iso_date, parse_iso_datetime, to_iso, utc_now_iso


class ETLService:
    def __init__(
        self,
        github_client: Any,
        dataset_builder: Any,
        loader: Any,
        control_repository: Any,
        export_service: Any,
        logger: logging.Logger,
    ) -> None:
        self.github_client = github_client
        self.dataset_builder = dataset_builder
        self.loader = loader
        self.control_repository = control_repository
        self.export_service = export_service
        self.logger = logger

    def run(
        self,
        owner: str,
        repos: list[str],
        since: str,
        dry_run: bool,
        export_csv: bool,
        export_parquet: bool,
        public_export: bool,
    ) -> EtlResult:
        self.loader.initialize()
        repositories: dict[str, RepositoryPayload] = {}
        since_dt = self._parse_since_datetime(since)
        for repo in repos:
            source_name = f"github:{owner}/{repo}"
            effective_since = self._resolve_incremental_since(source_name, since_dt)
            self.logger.info(
                "extracting_repository",
                extra={"extra_fields": {"owner": owner, "repo": repo, "since": effective_since}},
            )
            repo_payload = self._extract_repository(owner, repo, effective_since)
            repositories[repo] = repo_payload
        dataset = self.dataset_builder.build(ExtractedBundle(owner=owner, repositories=repositories))
        table_counts = {name: int(frame.shape[0]) for name, frame in dataset.items()}
        loaded_at = utc_now_iso()
        if not dry_run:
            self.loader.load_dataset(dataset)
            for repo in repos:
                self.control_repository.update_last_loaded_at(f"github:{owner}/{repo}", loaded_at)
            self.export_service.export_all(export_csv=export_csv, export_parquet=export_parquet)
            if public_export:
                self.export_service.export_public(export_csv=export_csv, export_parquet=export_parquet)
        return EtlResult(loaded_at=loaded_at, table_counts=table_counts, dry_run=dry_run)

    def _resolve_incremental_since(self, source_name: str, cli_since_dt: datetime) -> str:
        last_loaded_at = self.control_repository.get_last_loaded_at(source_name)
        if not last_loaded_at:
            return to_iso(cli_since_dt) or cli_since_dt.isoformat()
        last_loaded_dt = parse_iso_datetime(last_loaded_at) or cli_since_dt
        threshold = max(cli_since_dt, last_loaded_dt + timedelta(seconds=1))
        return to_iso(threshold) or threshold.isoformat()

    def _parse_since_datetime(self, since: str) -> datetime:
        value = since.strip()
        dt = parse_iso_datetime(value)
        if dt is not None:
            return dt
        return parse_iso_date(value).replace(hour=0, minute=0, second=0, microsecond=0, tzinfo=timezone.utc)

    def _extract_repository(self, owner: str, repo: str, since_iso: str) -> RepositoryPayload:
        repo_data = self.github_client.get_repository(owner, repo)
        commits = self._safe_call("commits", owner, repo, lambda: self.github_client.get_commits(owner, repo, since_iso))
        pull_requests = self._safe_call(
            "pull_requests", owner, repo, lambda: self.github_client.get_pull_requests(owner, repo, since_iso)
        )
        issues = self._safe_call("issues", owner, repo, lambda: self.github_client.get_issues(owner, repo, since_iso))
        releases = self._safe_call(
            "releases", owner, repo, lambda: self.github_client.get_releases(owner, repo, since_iso)
        )
        workflow_runs = self._safe_call(
            "workflow_runs", owner, repo, lambda: self.github_client.get_workflow_runs(owner, repo, since_iso)
        )
        contributors = self._safe_call(
            "contributors", owner, repo, lambda: self.github_client.get_contributors(owner, repo, since_iso)
        )
        return RepositoryPayload(
            repo=repo_data,
            commits=commits,
            pull_requests=pull_requests,
            issues=issues,
            releases=releases,
            workflow_runs=workflow_runs,
            contributors=contributors,
        )

    def _safe_call(self, source: str, owner: str, repo: str, fn: Any) -> list[dict[str, Any]]:
        try:
            value = fn()
            return value if isinstance(value, list) else list(value)
        except Exception as exc:
            self.logger.warning(
                "github_extract_warning",
                extra={
                    "extra_fields": {
                        "source": source,
                        "owner": owner,
                        "repo": repo,
                        "error": str(exc),
                    }
                },
            )
            return []
