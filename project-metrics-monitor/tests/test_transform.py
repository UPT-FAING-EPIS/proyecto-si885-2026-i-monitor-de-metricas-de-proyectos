from __future__ import annotations

from src.models.entities import ExtractedBundle, RepositoryPayload
from src.transform.dataset_builder import DatasetBuilder


def build_bundle() -> ExtractedBundle:
    return ExtractedBundle(
        owner="sample-org",
        repositories={
            "sample-repo": RepositoryPayload(
                repo={
                    "id": 1001,
                    "full_name": "sample-org/sample-repo",
                    "description": "repo",
                    "language": "Python",
                    "stargazers_count": 1,
                    "forks_count": 1,
                    "open_issues_count": 2,
                    "default_branch": "main",
                    "archived": False,
                    "visibility": "public",
                    "created_at": "2026-01-01T00:00:00Z",
                    "updated_at": "2026-01-02T00:00:00Z",
                },
                commits=[
                    {
                        "sha": "sha-1",
                        "author": {"id": "user:alice", "login": "alice", "type": "User"},
                        "commit": {
                            "author": {"date": "2026-01-10T10:00:00Z", "name": "Alice"},
                            "message": "feat: first commit",
                        },
                        "stats": {"additions": 10, "deletions": 2},
                    }
                ],
                pull_requests=[
                    {
                        "id": "pr-1",
                        "number": 1,
                        "state": "MERGED",
                        "createdAt": "2026-01-11T10:00:00Z",
                        "mergedAt": "2026-01-12T10:00:00Z",
                        "closedAt": "2026-01-12T10:00:00Z",
                        "changedFiles": 2,
                        "additions": 20,
                        "deletions": 5,
                        "commits": {"totalCount": 1},
                        "author": {"id": "user:bob", "login": "bob", "name": "Bob"},
                        "reviews": {
                            "nodes": [
                                {
                                    "state": "CHANGES_REQUESTED",
                                    "submittedAt": "2026-01-11T11:00:00Z",
                                    "author": {"id": "user:carol", "login": "carol", "name": "Carol"},
                                }
                            ]
                        },
                        "labels": {"nodes": [{"id": "label:enhancement", "name": "enhancement", "color": "fff", "description": "Enhancement"}]},
                    }
                ],
                issues=[
                    {
                        "id": "issue-1",
                        "number": 10,
                        "state": "OPEN",
                        "createdAt": "2026-01-13T08:00:00Z",
                        "closedAt": None,
                        "author": {"id": "user:alice", "login": "alice", "name": "Alice"},
                        "labels": {"nodes": [{"id": "label:bug", "name": "bug", "color": "red", "description": "Bug"}]},
                    }
                ],
                releases=[
                    {
                        "id": 501,
                        "published_at": "2026-01-15T10:00:00Z",
                        "tag_name": "v1.0.0",
                        "name": "Initial release",
                        "draft": False,
                        "prerelease": False,
                    }
                ],
                workflow_runs=[
                    {
                        "id": 7001,
                        "created_at": "2026-01-14T10:00:00Z",
                        "updated_at": "2026-01-14T10:05:00Z",
                        "run_started_at": "2026-01-14T10:01:00Z",
                        "status": "completed",
                        "conclusion": "failure",
                        "name": "CI",
                        "head_branch": "main",
                        "event": "push",
                    }
                ],
                contributors=[
                    {
                        "author": {"id": "user:alice", "login": "alice", "name": "Alice"},
                        "contributions": 2,
                        "first_seen_at": "2026-01-10T10:00:00Z",
                        "last_seen_at": "2026-01-12T10:00:00Z",
                    }
                ],
            )
        },
    )


def test_dataset_builder_generates_dimensions_and_facts() -> None:
    dataset = DatasetBuilder().build(build_bundle())

    assert dataset["dim_repo"].shape[0] == 1
    assert dataset["fact_commits"].shape[0] == 1
    assert dataset["fact_prs"].shape[0] == 1
    assert dataset["fact_issues"].shape[0] == 1
    assert dataset["fact_releases"].shape[0] == 1
    assert dataset["fact_workflows"].shape[0] == 1
    assert dataset["fact_contributors"].shape[0] == 1
    assert dataset["bridge_issue_labels"].shape[0] == 1
    assert dataset["bridge_pr_labels"].shape[0] == 1
    assert "anonymized_login" in dataset["dim_author"].columns
    assert float(dataset["fact_prs"].iloc[0]["lead_time_hours"]) == 24.0
    assert int(dataset["fact_issues"].iloc[0]["is_bug"]) == 1
