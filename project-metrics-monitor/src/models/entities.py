from __future__ import annotations

from dataclasses import dataclass, field
from typing import Any


@dataclass(slots=True)
class RepositoryPayload:
    repo: dict[str, Any]
    commits: list[dict[str, Any]] = field(default_factory=list)
    pull_requests: list[dict[str, Any]] = field(default_factory=list)
    issues: list[dict[str, Any]] = field(default_factory=list)
    releases: list[dict[str, Any]] = field(default_factory=list)
    workflow_runs: list[dict[str, Any]] = field(default_factory=list)
    contributors: list[dict[str, Any]] = field(default_factory=list)


@dataclass(slots=True)
class ExtractedBundle:
    owner: str
    repositories: dict[str, RepositoryPayload]


@dataclass(slots=True)
class EtlResult:
    loaded_at: str
    table_counts: dict[str, int]
    dry_run: bool
