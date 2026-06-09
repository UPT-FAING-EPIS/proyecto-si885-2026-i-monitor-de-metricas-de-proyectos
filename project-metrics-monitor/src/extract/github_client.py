from __future__ import annotations

import logging
import time
from collections.abc import Iterator
from datetime import UTC, datetime
from typing import Any

import requests

from src.utils.dates import parse_iso_date, parse_iso_datetime, to_iso
from src.utils.logging_utils import get_logger


class GitHubClient:
    rest_base_url = "https://api.github.com"
    graphql_url = "https://api.github.com/graphql"

    def __init__(
        self,
        token: str | None,
        logger: logging.Logger | None = None,
        timeout_seconds: int = 60,
        max_retries: int = 5,
    ) -> None:
        self.token = token
        self.timeout_seconds = timeout_seconds
        self.max_retries = max_retries
        self.logger = logger or get_logger(self.__class__.__name__)
        self.session = requests.Session()
        self.session.headers.update(
            {
                "Accept": "application/vnd.github+json",
                "X-GitHub-Api-Version": "2022-11-28",
                "User-Agent": "project-metrics-monitor",
            }
        )
        if token:
            self.session.headers["Authorization"] = f"Bearer {token}"

    def _request(
        self,
        method: str,
        url: str,
        *,
        params: dict[str, Any] | None = None,
        json_body: dict[str, Any] | None = None,
    ) -> requests.Response:
        last_error: Exception | None = None
        for attempt in range(1, self.max_retries + 1):
            try:
                response = self.session.request(
                    method=method,
                    url=url,
                    params=params,
                    json=json_body,
                    timeout=self.timeout_seconds,
                )
                if response.status_code in {429, 502, 503, 504}:
                    self._backoff_sleep(attempt, response)
                    continue
                if (
                    response.status_code == 403
                    and response.headers.get("X-RateLimit-Remaining") == "0"
                ):
                    self._sleep_for_rate_limit(response)
                    continue
                response.raise_for_status()
                return response
            except requests.RequestException as exc:
                last_error = exc
                self._backoff_sleep(attempt, getattr(exc, "response", None))
        raise RuntimeError(f"Fallo al consultar GitHub: {last_error}") from last_error

    def _backoff_sleep(self, attempt: int, response: requests.Response | None) -> None:
        wait_seconds = min(2**attempt, 30)
        self.logger.warning(
            "retrying_github_request",
            extra={"extra_fields": {"attempt": attempt, "wait_seconds": wait_seconds}},
        )
        if response is not None and response.headers.get("Retry-After"):
            wait_seconds = int(response.headers["Retry-After"])
        time.sleep(wait_seconds)

    def _sleep_for_rate_limit(self, response: requests.Response) -> None:
        reset_at = response.headers.get("X-RateLimit-Reset")
        wait_seconds = 60
        if reset_at:
            reset_epoch = int(reset_at)
            wait_seconds = max(reset_epoch - int(datetime.now(UTC).timestamp()) + 1, 1)
        self.logger.warning(
            "github_rate_limit_wait",
            extra={"extra_fields": {"wait_seconds": wait_seconds}},
        )
        time.sleep(wait_seconds)

    def _rest_paginated(
        self, endpoint: str, params: dict[str, Any] | None = None
    ) -> Iterator[dict[str, Any]]:
        page = 1
        base_params = dict(params or {})
        while True:
            current_params = {**base_params, "per_page": 100, "page": page}
            response = self._request(
                "GET", f"{self.rest_base_url}{endpoint}", params=current_params
            )
            items = response.json()
            if not items:
                break
            yield from items
            if len(items) < 100:
                break
            page += 1

    def _graphql(self, query: str, variables: dict[str, Any]) -> dict[str, Any]:
        if not self.token:
            raise RuntimeError("GraphQL requiere autenticacion (GITHUB_TOKEN).")
        response = self._request(
            "POST", self.graphql_url, json_body={"query": query, "variables": variables}
        )
        payload = response.json()
        if payload.get("errors"):
            raise RuntimeError(f"GraphQL errors: {payload['errors']}")
        return payload["data"]

    def _since_datetime(self, since: str) -> datetime:
        value = since.strip()
        dt = parse_iso_datetime(value)
        if dt is not None:
            return dt
        return parse_iso_date(value).replace(hour=0, minute=0, second=0, microsecond=0, tzinfo=UTC)

    def _warn_no_token(self, capability: str, owner: str, repo: str) -> None:
        self.logger.warning(
            "github_no_token_fallback",
            extra={"extra_fields": {"capability": capability, "owner": owner, "repo": repo}},
        )

    def get_repository(self, owner: str, repo: str) -> dict[str, Any]:
        endpoint = f"/repos/{owner}/{repo}"
        return self._request("GET", f"{self.rest_base_url}{endpoint}").json()

    def get_commits(self, owner: str, repo: str, since: str) -> list[dict[str, Any]]:
        endpoint = f"/repos/{owner}/{repo}/commits"
        since_dt = self._since_datetime(since)
        commits = list(self._rest_paginated(endpoint, params={"since": to_iso(since_dt)}))
        enriched: list[dict[str, Any]] = []
        for commit in commits:
            sha = commit["sha"]
            detail = self._request(
                "GET", f"{self.rest_base_url}/repos/{owner}/{repo}/commits/{sha}"
            ).json()
            enriched.append(detail)
        return enriched

    def get_releases(self, owner: str, repo: str, since: str) -> list[dict[str, Any]]:
        releases = list(self._rest_paginated(f"/repos/{owner}/{repo}/releases"))
        threshold = self._since_datetime(since)
        return [
            release
            for release in releases
            if parse_iso_datetime(release.get("published_at"))
            and parse_iso_datetime(release.get("published_at")) > threshold
        ]

    def get_workflow_runs(self, owner: str, repo: str, since: str) -> list[dict[str, Any]]:
        endpoint = f"/repos/{owner}/{repo}/actions/runs"
        page = 1
        runs: list[dict[str, Any]] = []
        threshold = self._since_datetime(since)
        while True:
            response = self._request(
                "GET",
                f"{self.rest_base_url}{endpoint}",
                params={"per_page": 100, "page": page},
            ).json()
            items = response.get("workflow_runs", [])
            if not items:
                break
            for item in items:
                created_at = parse_iso_datetime(item.get("created_at"))
                if created_at and created_at > threshold:
                    runs.append(item)
            if len(items) < 100:
                break
            page += 1
        return runs

    def get_pull_requests(self, owner: str, repo: str, since: str) -> list[dict[str, Any]]:
        if not self.token:
            self._warn_no_token("pull_requests", owner, repo)
            return self._get_pull_requests_rest(owner, repo, since)
        query = """
        query($owner: String!, $repo: String!, $cursor: String) {
          repository(owner: $owner, name: $repo) {
            pullRequests(first: 50, after: $cursor, orderBy: {field: UPDATED_AT, direction: DESC}) {
              pageInfo { hasNextPage endCursor }
              nodes {
                id
                number
                title
                state
                createdAt
                updatedAt
                mergedAt
                closedAt
                changedFiles
                additions
                deletions
                commits { totalCount }
                author { login ... on User { id name } }
                reviews(first: 50) {
                  nodes {
                    state
                    submittedAt
                    author { login ... on User { id name } }
                  }
                }
                labels(first: 50) {
                  nodes {
                    id
                    name
                    color
                    description
                  }
                }
              }
            }
          }
        }
        """
        results: list[dict[str, Any]] = []
        threshold = self._since_datetime(since)
        cursor: str | None = None
        while True:
            data = self._graphql(query, {"owner": owner, "repo": repo, "cursor": cursor})
            payload = data["repository"]["pullRequests"]
            for node in payload["nodes"]:
                updated_at = parse_iso_datetime(node.get("updatedAt"))
                if updated_at and updated_at > threshold:
                    results.append(node)
            if not payload["pageInfo"]["hasNextPage"]:
                break
            cursor = payload["pageInfo"]["endCursor"]
        return results

    def get_issues(self, owner: str, repo: str, since: str) -> list[dict[str, Any]]:
        if not self.token:
            self._warn_no_token("issues", owner, repo)
            return self._get_issues_rest(owner, repo, since)
        query = """
        query($owner: String!, $repo: String!, $cursor: String) {
          repository(owner: $owner, name: $repo) {
            issues(first: 50, after: $cursor, orderBy: {field: UPDATED_AT, direction: DESC}) {
              pageInfo { hasNextPage endCursor }
              nodes {
                id
                number
                title
                state
                createdAt
                updatedAt
                closedAt
                author { login ... on User { id name } }
                labels(first: 50) {
                  nodes {
                    id
                    name
                    color
                    description
                  }
                }
              }
            }
          }
        }
        """
        results: list[dict[str, Any]] = []
        threshold = self._since_datetime(since)
        cursor: str | None = None
        while True:
            data = self._graphql(query, {"owner": owner, "repo": repo, "cursor": cursor})
            payload = data["repository"]["issues"]
            for node in payload["nodes"]:
                updated_at = parse_iso_datetime(node.get("updatedAt"))
                if updated_at and updated_at > threshold:
                    results.append(node)
            if not payload["pageInfo"]["hasNextPage"]:
                break
            cursor = payload["pageInfo"]["endCursor"]
        return results

    def get_contributors(self, owner: str, repo: str, since: str) -> list[dict[str, Any]]:
        if self.token:
            return self._get_contributors_graphql(owner, repo, since)
        self._warn_no_token("contributors", owner, repo)
        return self._get_contributors_rest(owner, repo)

    def _get_contributors_rest(self, owner: str, repo: str) -> list[dict[str, Any]]:
        endpoint = f"/repos/{owner}/{repo}/contributors"
        contributors = list(self._rest_paginated(endpoint, params={"anon": "1"}))
        normalized: list[dict[str, Any]] = []
        for c in contributors:
            user = c or {}
            login = user.get("login") or "unknown"
            normalized.append(
                {
                    "author": {"id": f"user:{login.lower()}", "login": login, "name": login},
                    "contributions": int(user.get("contributions") or 0),
                    "first_seen_at": None,
                    "last_seen_at": None,
                }
            )
        return normalized

    def _get_contributors_graphql(self, owner: str, repo: str, since: str) -> list[dict[str, Any]]:
        query = """
        query($owner: String!, $repo: String!, $cursor: String, $since: GitTimestamp!) {
          repository(owner: $owner, name: $repo) {
            defaultBranchRef {
              target {
                ... on Commit {
                  history(first: 100, after: $cursor, since: $since) {
                    pageInfo { hasNextPage endCursor }
                    nodes {
                      committedDate
                      author { user { id login name } }
                    }
                  }
                }
              }
            }
          }
        }
        """
        since_dt = self._since_datetime(since)
        cursor: str | None = None
        aggregates: dict[str, dict[str, Any]] = {}
        while True:
            data = self._graphql(
                query, {"owner": owner, "repo": repo, "cursor": cursor, "since": to_iso(since_dt)}
            )
            history = (
                (data.get("repository") or {})
                .get("defaultBranchRef", {})
                .get("target", {})
                .get("history", {})
            )
            nodes = history.get("nodes") or []
            for node in nodes:
                committed = parse_iso_datetime(node.get("committedDate"))
                if not committed or committed <= since_dt:
                    continue
                user = ((node.get("author") or {}).get("user")) or {}
                if not user.get("login"):
                    continue
                login = user.get("login")
                author = {
                    "id": user.get("id") or f"user:{login.lower()}",
                    "login": login,
                    "name": user.get("name"),
                }
                key = author["id"]
                if key not in aggregates:
                    aggregates[key] = {
                        "author": author,
                        "contributions": 0,
                        "first_seen_at": to_iso(committed),
                        "last_seen_at": to_iso(committed),
                    }
                aggregates[key]["contributions"] += 1
                first_seen = parse_iso_datetime(aggregates[key]["first_seen_at"])
                last_seen = parse_iso_datetime(aggregates[key]["last_seen_at"])
                if first_seen and committed < first_seen:
                    aggregates[key]["first_seen_at"] = to_iso(committed)
                if last_seen and committed > last_seen:
                    aggregates[key]["last_seen_at"] = to_iso(committed)
            if not (history.get("pageInfo") or {}).get("hasNextPage"):
                break
            cursor = (history.get("pageInfo") or {}).get("endCursor")
        return list(aggregates.values())

    def _get_pull_requests_rest(self, owner: str, repo: str, since: str) -> list[dict[str, Any]]:
        threshold = self._since_datetime(since)
        endpoint = f"/repos/{owner}/{repo}/pulls"
        items = list(
            self._rest_paginated(
                endpoint,
                params={"state": "all", "sort": "updated", "direction": "desc"},
            )
        )
        results: list[dict[str, Any]] = []
        for item in items:
            updated_at = parse_iso_datetime(item.get("updated_at"))
            if not updated_at or updated_at <= threshold:
                continue
            number = int(item["number"])
            detail = self._request(
                "GET", f"{self.rest_base_url}/repos/{owner}/{repo}/pulls/{number}"
            ).json()
            labels = list(self._rest_paginated(f"/repos/{owner}/{repo}/issues/{number}/labels"))
            reviews = list(self._rest_paginated(f"/repos/{owner}/{repo}/pulls/{number}/reviews"))
            results.append(self._normalize_pr_rest(detail, labels, reviews))
        return results

    def _normalize_pr_rest(
        self, pr: dict[str, Any], labels: list[dict[str, Any]], reviews: list[dict[str, Any]]
    ) -> dict[str, Any]:
        merged_at = pr.get("merged_at")
        state = "OPEN"
        if merged_at:
            state = "MERGED"
        elif pr.get("state") == "closed":
            state = "CLOSED"
        author_login = ((pr.get("user") or {}).get("login")) or "unknown"
        author = {"id": f"user:{author_login.lower()}", "login": author_login, "name": author_login}
        normalized_reviews: list[dict[str, Any]] = []
        for r in reviews:
            reviewer_login = ((r.get("user") or {}).get("login")) or "unknown"
            normalized_reviews.append(
                {
                    "state": (r.get("state") or "").upper(),
                    "submittedAt": r.get("submitted_at"),
                    "author": {
                        "id": f"user:{reviewer_login.lower()}",
                        "login": reviewer_login,
                        "name": reviewer_login,
                    },
                }
            )
        normalized_labels = [
            {
                "id": f"label:{(label.get('name') or 'unknown')}",
                "name": label.get("name") or "unknown",
                "color": label.get("color"),
                "description": label.get("description"),
            }
            for label in labels
        ]
        return {
            "id": pr.get("node_id") or f"PR:{pr.get('id')}",
            "number": pr.get("number"),
            "title": pr.get("title"),
            "state": state,
            "createdAt": pr.get("created_at"),
            "updatedAt": pr.get("updated_at"),
            "mergedAt": merged_at,
            "closedAt": pr.get("closed_at"),
            "changedFiles": pr.get("changed_files") or 0,
            "additions": pr.get("additions") or 0,
            "deletions": pr.get("deletions") or 0,
            "commits": {"totalCount": pr.get("commits") or 0},
            "author": author,
            "reviews": {"nodes": normalized_reviews},
            "labels": {"nodes": normalized_labels},
        }

    def _get_issues_rest(self, owner: str, repo: str, since: str) -> list[dict[str, Any]]:
        threshold = self._since_datetime(since)
        endpoint = f"/repos/{owner}/{repo}/issues"
        items = list(
            self._rest_paginated(
                endpoint,
                params={
                    "state": "all",
                    "sort": "updated",
                    "direction": "desc",
                    "since": to_iso(threshold),
                },
            )
        )
        results: list[dict[str, Any]] = []
        for item in items:
            if item.get("pull_request"):
                continue
            updated_at = parse_iso_datetime(item.get("updated_at"))
            if not updated_at or updated_at <= threshold:
                continue
            author_login = ((item.get("user") or {}).get("login")) or "unknown"
            labels = item.get("labels") or []
            normalized_labels = [
                {
                    "id": f"label:{(label.get('name') or 'unknown')}",
                    "name": label.get("name") or "unknown",
                    "color": label.get("color"),
                    "description": label.get("description"),
                }
                for label in labels
                if isinstance(label, dict)
            ]
            results.append(
                {
                    "id": item.get("node_id") or f"I:{item.get('id')}",
                    "number": item.get("number"),
                    "title": item.get("title"),
                    "state": (item.get("state") or "").upper(),
                    "createdAt": item.get("created_at"),
                    "updatedAt": item.get("updated_at"),
                    "closedAt": item.get("closed_at"),
                    "author": {
                        "id": f"user:{author_login.lower()}",
                        "login": author_login,
                        "name": author_login,
                    },
                    "labels": {"nodes": normalized_labels},
                }
            )
        return results
