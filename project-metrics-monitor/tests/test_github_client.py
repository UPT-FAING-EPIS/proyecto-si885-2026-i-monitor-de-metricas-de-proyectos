from __future__ import annotations

from typing import Any

import pytest
import requests

from src.extract.github_client import GitHubClient


class FakeResponse:
    def __init__(
        self, status_code: int, payload: Any, headers: dict[str, str] | None = None
    ) -> None:
        self.status_code = status_code
        self._payload = payload
        self.headers = headers or {}

    def json(self) -> Any:
        return self._payload

    def raise_for_status(self) -> None:
        if self.status_code >= 400:
            raise requests.HTTPError(response=self)


class RecordingSleeper:
    def __init__(self) -> None:
        self.calls: list[int] = []

    def __call__(self, seconds: int) -> None:
        self.calls.append(int(seconds))


def test_request_retries_on_transient_failure(monkeypatch: pytest.MonkeyPatch) -> None:
    client = GitHubClient(token=None, max_retries=2)
    responses = [
        FakeResponse(503, {"message": "busy"}),
        FakeResponse(200, {"ok": True}),
    ]

    def fake_request(**kwargs: Any) -> FakeResponse:
        return responses.pop(0)

    monkeypatch.setattr(client.session, "request", fake_request)
    monkeypatch.setattr("src.extract.github_client.time.sleep", lambda _: None)

    response = client._request("GET", "https://example.test")
    assert response.json() == {"ok": True}


def test_rest_paginated_collects_multiple_pages(monkeypatch: pytest.MonkeyPatch) -> None:
    client = GitHubClient(token=None)
    responses = [
        FakeResponse(200, [{"id": 1}] * 100),
        FakeResponse(200, [{"id": 2}]),
    ]

    def fake_request(
        method: str,
        url: str,
        params: dict[str, Any] | None = None,
        json: Any = None,
        timeout: int = 60,
    ) -> FakeResponse:
        return responses.pop(0)

    monkeypatch.setattr(client.session, "request", fake_request)
    results = list(client._rest_paginated("/repos/sample/issues"))
    assert len(results) == 101


def test_request_honors_rate_limit_reset(monkeypatch: pytest.MonkeyPatch) -> None:
    client = GitHubClient(token=None, max_retries=2)
    sleeper = RecordingSleeper()
    import datetime as dt

    now_epoch = int(dt.datetime.now(dt.UTC).timestamp())
    reset_epoch = now_epoch + 1
    responses = [
        FakeResponse(
            403,
            {"message": "rate limit"},
            headers={"X-RateLimit-Remaining": "0", "X-RateLimit-Reset": str(reset_epoch)},
        ),
        FakeResponse(200, {"ok": True}),
    ]

    def fake_request(**kwargs: Any) -> FakeResponse:
        return responses.pop(0)

    monkeypatch.setattr(client.session, "request", fake_request)
    monkeypatch.setattr("src.extract.github_client.time.sleep", sleeper)

    response = client._request("GET", "https://example.test")
    assert response.json() == {"ok": True}
    assert sleeper.calls and sleeper.calls[0] >= 1


def test_request_uses_retry_after_header(monkeypatch: pytest.MonkeyPatch) -> None:
    client = GitHubClient(token=None, max_retries=2)
    sleeper = RecordingSleeper()
    responses = [
        FakeResponse(429, {"message": "too many"}, headers={"Retry-After": "3"}),
        FakeResponse(200, {"ok": True}),
    ]

    def fake_request(**kwargs: Any) -> FakeResponse:
        return responses.pop(0)

    monkeypatch.setattr(client.session, "request", fake_request)
    monkeypatch.setattr("src.extract.github_client.time.sleep", sleeper)
    response = client._request("GET", "https://example.test")
    assert response.json() == {"ok": True}
    assert 3 in sleeper.calls


def test_no_token_does_not_execute_graphql(monkeypatch: pytest.MonkeyPatch) -> None:
    client = GitHubClient(token=None)
    called = {"graphql": False}

    def fake_graphql(query: str, variables: dict[str, Any]) -> dict[str, Any]:
        called["graphql"] = True
        return {}

    monkeypatch.setattr(client, "_graphql", fake_graphql)
    monkeypatch.setattr(client, "_get_pull_requests_rest", lambda owner, repo, since: [])
    monkeypatch.setattr(client, "_get_issues_rest", lambda owner, repo, since: [])

    prs = client.get_pull_requests("o", "r", "2026-01-01T00:00:00Z")
    issues = client.get_issues("o", "r", "2026-01-01T00:00:00Z")
    assert prs == []
    assert issues == []
    assert called["graphql"] is False


def test_graphql_internal_requires_token() -> None:
    client = GitHubClient(token=None)
    with pytest.raises(RuntimeError):
        client._graphql("query { viewer { login } }", {})


def test_pull_requests_rest_fallback_normalizes(monkeypatch: pytest.MonkeyPatch) -> None:
    client = GitHubClient(token=None)

    def fake_rest_paginated(endpoint: str, params: dict[str, Any] | None = None):
        if endpoint.endswith("/pulls"):
            yield {
                "number": 7,
                "updated_at": "2026-01-02T00:00:01Z",
            }
            return
        if endpoint.endswith("/issues/7/labels"):
            yield {"name": "bug", "color": "d73a4a", "description": "Bug"}
            return
        if endpoint.endswith("/pulls/7/reviews"):
            yield {
                "state": "CHANGES_REQUESTED",
                "submitted_at": "2026-01-02T01:00:00Z",
                "user": {"login": "rev"},
            }
            return
        return

    def fake_request(
        method: str,
        url: str,
        params: dict[str, Any] | None = None,
        json: Any = None,
        timeout: int = 60,
    ):
        if url.endswith("/pulls/7"):
            return FakeResponse(
                200,
                {
                    "id": 77,
                    "node_id": "PR_NODE_7",
                    "number": 7,
                    "title": "t",
                    "state": "closed",
                    "created_at": "2026-01-01T00:00:00Z",
                    "updated_at": "2026-01-02T00:00:01Z",
                    "closed_at": "2026-01-02T00:00:01Z",
                    "merged_at": "2026-01-02T00:00:01Z",
                    "changed_files": 1,
                    "additions": 2,
                    "deletions": 1,
                    "commits": 1,
                    "user": {"login": "auth"},
                },
            )
        return FakeResponse(200, {})

    monkeypatch.setattr(client, "_rest_paginated", fake_rest_paginated)
    monkeypatch.setattr(client.session, "request", fake_request)

    prs = client.get_pull_requests("o", "r", "2026-01-02T00:00:00Z")
    assert len(prs) == 1
    assert prs[0]["id"] == "PR_NODE_7"
    assert prs[0]["state"] == "MERGED"
    assert prs[0]["reviews"]["nodes"][0]["state"] == "CHANGES_REQUESTED"
    assert prs[0]["labels"]["nodes"][0]["name"] == "bug"


def test_issues_rest_fallback_filters_and_skips_prs(monkeypatch: pytest.MonkeyPatch) -> None:
    client = GitHubClient(token=None)

    def fake_rest_paginated(endpoint: str, params: dict[str, Any] | None = None):
        assert endpoint.endswith("/issues")
        yield {
            "id": 1,
            "node_id": "NODE_PR",
            "number": 1,
            "title": "pr-as-issue",
            "state": "open",
            "created_at": "2026-01-02T00:00:00Z",
            "updated_at": "2026-01-02T00:00:01Z",
            "pull_request": {"url": "x"},
            "user": {"login": "x"},
            "labels": [],
        }
        yield {
            "id": 2,
            "node_id": "NODE_ISSUE",
            "number": 2,
            "title": "issue",
            "state": "closed",
            "created_at": "2026-01-02T00:00:00Z",
            "updated_at": "2026-01-02T00:00:01Z",
            "closed_at": "2026-01-02T00:00:01Z",
            "user": {"login": "alice"},
            "labels": [{"name": "bug", "color": "red", "description": "Bug"}],
        }

    monkeypatch.setattr(client, "_rest_paginated", fake_rest_paginated)
    issues = client.get_issues("o", "r", "2026-01-02T00:00:00Z")
    assert len(issues) == 1
    assert issues[0]["id"] == "NODE_ISSUE"
    assert issues[0]["labels"]["nodes"][0]["name"] == "bug"


def test_contributors_rest_normalizes(monkeypatch: pytest.MonkeyPatch) -> None:
    client = GitHubClient(token=None)

    def fake_rest_paginated(endpoint: str, params: dict[str, Any] | None = None):
        assert endpoint.endswith("/contributors")
        yield {"login": "alice", "contributions": 7}

    monkeypatch.setattr(client, "_rest_paginated", fake_rest_paginated)
    contributors = client.get_contributors("o", "r", "2026-01-01T00:00:00Z")
    assert contributors[0]["author"]["login"] == "alice"
    assert contributors[0]["contributions"] == 7


def test_contributors_graphql_aggregates(monkeypatch: pytest.MonkeyPatch) -> None:
    client = GitHubClient(token="token")

    def fake_request(
        method: str,
        url: str,
        params: dict[str, Any] | None = None,
        json: Any = None,
        timeout: int = 60,
    ):
        return FakeResponse(
            200,
            {
                "data": {
                    "repository": {
                        "defaultBranchRef": {
                            "target": {
                                "history": {
                                    "pageInfo": {"hasNextPage": False, "endCursor": None},
                                    "nodes": [
                                        {
                                            "committedDate": "2026-01-02T00:00:01Z",
                                            "author": {
                                                "user": {
                                                    "id": "U1",
                                                    "login": "alice",
                                                    "name": "Alice",
                                                }
                                            },
                                        },
                                        {
                                            "committedDate": "2026-01-02T00:00:02Z",
                                            "author": {
                                                "user": {
                                                    "id": "U1",
                                                    "login": "alice",
                                                    "name": "Alice",
                                                }
                                            },
                                        },
                                    ],
                                }
                            }
                        }
                    }
                }
            },
        )

    monkeypatch.setattr(client.session, "request", fake_request)
    contributors = client.get_contributors("o", "r", "2026-01-02T00:00:00Z")
    assert len(contributors) == 1
    assert contributors[0]["author"]["login"] == "alice"
    assert contributors[0]["contributions"] == 2
