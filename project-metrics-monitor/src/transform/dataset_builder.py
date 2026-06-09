from __future__ import annotations

from datetime import UTC, datetime
from typing import Any

import pandas as pd

from src.models.entities import ExtractedBundle
from src.utils.dates import parse_iso_datetime, to_date_id, to_iso
from src.utils.hash_utils import hash_identity


class DatasetBuilder:
    def build(self, bundle: ExtractedBundle) -> dict[str, pd.DataFrame]:
        repo_rows: list[dict[str, Any]] = []
        author_rows: dict[str, dict[str, Any]] = {
            "unknown": {
                "author_id": "unknown",
                "login": "unknown",
                "display_name": "Unknown",
                "author_type": "System",
                "anonymized_login": "unknown",
            }
        }
        label_rows: dict[str, dict[str, Any]] = {
            "label:unknown": {
                "label_id": "label:unknown",
                "label_name": "unknown",
                "color": "999999",
                "description": "Fallback label",
            }
        }
        commit_rows: list[dict[str, Any]] = []
        pr_rows: list[dict[str, Any]] = []
        issue_rows: list[dict[str, Any]] = []
        release_rows: list[dict[str, Any]] = []
        workflow_rows: list[dict[str, Any]] = []
        contributor_rows: list[dict[str, Any]] = []
        issue_label_rows: list[dict[str, Any]] = []
        pr_label_rows: list[dict[str, Any]] = []
        date_values: dict[int, datetime] = {}
        now_utc = datetime.now(UTC)

        for repo_name, payload in bundle.repositories.items():
            repo = payload.repo
            repo_rows.append(
                {
                    "repo_id": repo["id"],
                    "owner": bundle.owner,
                    "repo_name": repo_name,
                    "full_name": repo["full_name"],
                    "description": repo.get("description"),
                    "language": repo.get("language"),
                    "stars": repo.get("stargazers_count", 0),
                    "forks": repo.get("forks_count", 0),
                    "open_issues": repo.get("open_issues_count", 0),
                    "default_branch": repo.get("default_branch"),
                    "archived": int(bool(repo.get("archived"))),
                    "visibility": repo.get("visibility", "public"),
                    "created_at": repo.get("created_at"),
                    "updated_at": repo.get("updated_at"),
                }
            )

            for commit in payload.commits:
                author = self._extract_author(commit.get("author"), commit["commit"].get("author"))
                author_rows[author["author_id"]] = author
                commit_date = parse_iso_datetime(commit["commit"]["author"]["date"])
                date_id = to_date_id(commit_date)
                if date_id and commit_date:
                    date_values[date_id] = commit_date
                commit_rows.append(
                    {
                        "commit_id": commit["sha"],
                        "repo_id": repo["id"],
                        "author_id": author["author_id"],
                        "date_id": date_id,
                        "commit_date": to_iso(commit_date),
                        "message": commit["commit"]["message"].splitlines()[0][:500],
                        "additions": commit.get("stats", {}).get("additions", 0),
                        "deletions": commit.get("stats", {}).get("deletions", 0),
                    }
                )

            for pr in payload.pull_requests:
                author = self._extract_author(pr.get("author"))
                author_rows[author["author_id"]] = author
                created_at = parse_iso_datetime(pr.get("createdAt"))
                merged_at = parse_iso_datetime(pr.get("mergedAt"))
                closed_at = parse_iso_datetime(pr.get("closedAt"))
                created_date_id = to_date_id(created_at)
                merged_date_id = to_date_id(merged_at)
                if created_date_id and created_at:
                    date_values[created_date_id] = created_at
                if merged_date_id and merged_at:
                    date_values[merged_date_id] = merged_at
                requested_changes_count = 0
                first_review_at: datetime | None = None
                for review in pr.get("reviews", {}).get("nodes", []):
                    review_author = self._extract_author(review.get("author"))
                    author_rows[review_author["author_id"]] = review_author
                    submitted_at = parse_iso_datetime(review.get("submittedAt"))
                    if submitted_at and (first_review_at is None or submitted_at < first_review_at):
                        first_review_at = submitted_at
                    if review.get("state") == "CHANGES_REQUESTED":
                        requested_changes_count += 1
                lead_time_hours = None
                if created_at and merged_at:
                    lead_time_hours = round((merged_at - created_at).total_seconds() / 3600, 2)
                review_time_hours = None
                if created_at and first_review_at:
                    review_time_hours = round(
                        (first_review_at - created_at).total_seconds() / 3600, 2
                    )
                pr_rows.append(
                    {
                        "pr_id": pr["id"],
                        "pr_number": pr["number"],
                        "repo_id": repo["id"],
                        "author_id": author["author_id"],
                        "created_date_id": created_date_id,
                        "merged_date_id": merged_date_id,
                        "created_at": to_iso(created_at),
                        "merged_at": to_iso(merged_at),
                        "closed_at": to_iso(closed_at),
                        "state": pr.get("state"),
                        "lead_time_hours": lead_time_hours,
                        "review_time_hours": review_time_hours,
                        "commits_count": pr.get("commits", {}).get("totalCount", 0),
                        "changed_files": pr.get("changedFiles", 0),
                        "additions": pr.get("additions", 0),
                        "deletions": pr.get("deletions", 0),
                        "requested_changes_count": requested_changes_count,
                    }
                )
                for label in pr.get("labels", {}).get("nodes", []):
                    label_id = label["id"]
                    label_rows[label_id] = self._extract_label(label)
                    pr_label_rows.append({"pr_id": pr["id"], "label_id": label_id})

            for issue in payload.issues:
                author = self._extract_author(issue.get("author"))
                author_rows[author["author_id"]] = author
                created_at = parse_iso_datetime(issue.get("createdAt"))
                closed_at = parse_iso_datetime(issue.get("closedAt"))
                created_date_id = to_date_id(created_at)
                closed_date_id = to_date_id(closed_at)
                if created_date_id and created_at:
                    date_values[created_date_id] = created_at
                if closed_date_id and closed_at:
                    date_values[closed_date_id] = closed_at
                cycle_time_hours = None
                if created_at and closed_at:
                    cycle_time_hours = round((closed_at - created_at).total_seconds() / 3600, 2)
                current_age_hours = None
                if created_at and not closed_at:
                    current_age_hours = round((now_utc - created_at).total_seconds() / 3600, 2)
                issue_labels = issue.get("labels", {}).get("nodes", [])
                is_bug = any(
                    label["name"].lower() in {"bug", "type: bug"} for label in issue_labels
                )
                issue_rows.append(
                    {
                        "issue_id": issue["id"],
                        "issue_number": issue["number"],
                        "repo_id": repo["id"],
                        "author_id": author["author_id"],
                        "created_date_id": created_date_id,
                        "closed_date_id": closed_date_id,
                        "created_at": to_iso(created_at),
                        "closed_at": to_iso(closed_at),
                        "cycle_time_hours": cycle_time_hours,
                        "current_age_hours": current_age_hours,
                        "state": issue.get("state"),
                        "is_bug": int(is_bug),
                    }
                )
                if not issue_labels:
                    issue_label_rows.append({"issue_id": issue["id"], "label_id": "label:unknown"})
                for label in issue_labels:
                    label_id = label["id"]
                    label_rows[label_id] = self._extract_label(label)
                    issue_label_rows.append({"issue_id": issue["id"], "label_id": label_id})

            for release in payload.releases:
                release_date = parse_iso_datetime(release.get("published_at"))
                date_id = to_date_id(release_date)
                if date_id and release_date:
                    date_values[date_id] = release_date
                release_rows.append(
                    {
                        "release_id": release["id"],
                        "repo_id": repo["id"],
                        "date_id": date_id,
                        "release_date": to_iso(release_date),
                        "tag_name": release.get("tag_name"),
                        "name": release.get("name"),
                        "draft": int(bool(release.get("draft"))),
                        "prerelease": int(bool(release.get("prerelease"))),
                    }
                )

            for run in payload.workflow_runs:
                run_date = parse_iso_datetime(run.get("created_at"))
                date_id = to_date_id(run_date)
                if date_id and run_date:
                    date_values[date_id] = run_date
                workflow_rows.append(
                    {
                        "workflow_id": run["id"],
                        "repo_id": repo["id"],
                        "date_id": date_id,
                        "run_date": to_iso(run_date),
                        "status": run.get("status"),
                        "conclusion": run.get("conclusion"),
                        "workflow_name": run.get("name"),
                        "branch": run.get("head_branch"),
                        "event": run.get("event"),
                        "duration_seconds": self._duration_seconds(run),
                    }
                )

            for contributor in getattr(payload, "contributors", []) or []:
                author = self._extract_author(contributor.get("author") or contributor.get("user"))
                author_rows[author["author_id"]] = author
                first_seen_at = parse_iso_datetime(contributor.get("first_seen_at"))
                last_seen_at = parse_iso_datetime(contributor.get("last_seen_at"))
                first_seen_date_id = to_date_id(first_seen_at)
                last_seen_date_id = to_date_id(last_seen_at)
                if first_seen_date_id and first_seen_at:
                    date_values[first_seen_date_id] = first_seen_at
                if last_seen_date_id and last_seen_at:
                    date_values[last_seen_date_id] = last_seen_at
                contributor_rows.append(
                    {
                        "contributor_id": hash_identity(f"{repo['id']}:{author['author_id']}"),
                        "repo_id": repo["id"],
                        "author_id": author["author_id"],
                        "contributions": int(contributor.get("contributions") or 0),
                        "first_seen_date_id": first_seen_date_id,
                        "last_seen_date_id": last_seen_date_id,
                        "first_seen_at": to_iso(first_seen_at),
                        "last_seen_at": to_iso(last_seen_at),
                    }
                )

        date_rows = [self._date_row(date_id, dt) for date_id, dt in sorted(date_values.items())]

        return {
            "dim_date": pd.DataFrame(date_rows),
            "dim_repo": pd.DataFrame(repo_rows),
            "dim_author": pd.DataFrame(author_rows.values()),
            "dim_label": pd.DataFrame(label_rows.values()),
            "fact_commits": pd.DataFrame(commit_rows),
            "fact_prs": pd.DataFrame(pr_rows),
            "fact_issues": pd.DataFrame(issue_rows),
            "fact_releases": pd.DataFrame(release_rows),
            "fact_workflows": pd.DataFrame(workflow_rows),
            "fact_contributors": pd.DataFrame(contributor_rows),
            "bridge_issue_labels": pd.DataFrame(issue_label_rows),
            "bridge_pr_labels": pd.DataFrame(pr_label_rows),
        }

    def _extract_author(
        self,
        actor: dict[str, Any] | None,
        fallback: dict[str, Any] | None = None,
    ) -> dict[str, Any]:
        actor = actor or {}
        fallback = fallback or {}
        login = actor.get("login") or fallback.get("name") or "unknown"
        author_id = actor.get("id") or f"user:{login.lower()}"
        display_name = actor.get("name") or fallback.get("name") or login
        author_type = actor.get("__typename") or actor.get("type") or "User"
        return {
            "author_id": author_id,
            "login": login,
            "display_name": display_name,
            "author_type": author_type,
            "anonymized_login": hash_identity(login),
        }

    def _extract_label(self, label: dict[str, Any]) -> dict[str, Any]:
        return {
            "label_id": label["id"],
            "label_name": label["name"],
            "color": label.get("color"),
            "description": label.get("description"),
        }

    def _date_row(self, date_id: int, value: datetime) -> dict[str, Any]:
        iso_week = int(value.strftime("%V"))
        quarter = ((value.month - 1) // 3) + 1
        return {
            "date_id": date_id,
            "full_date": value.date().isoformat(),
            "year": value.year,
            "quarter": quarter,
            "month": value.month,
            "month_name": value.strftime("%B"),
            "week_of_year": iso_week,
            "day_of_month": value.day,
            "day_of_week": value.isoweekday(),
        }

    def _duration_seconds(self, run: dict[str, Any]) -> int | None:
        created_at = parse_iso_datetime(run.get("run_started_at") or run.get("created_at"))
        updated_at = parse_iso_datetime(run.get("updated_at"))
        if not created_at or not updated_at:
            return None
        return int((updated_at - created_at).total_seconds())
