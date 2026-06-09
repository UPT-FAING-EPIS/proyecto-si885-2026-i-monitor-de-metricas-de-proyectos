from __future__ import annotations

import re

from src.utils.dates import parse_iso_date, parse_iso_datetime

OWNER_RE = re.compile(r"^[A-Za-z0-9_.-]+$")
REPO_RE = re.compile(r"^[A-Za-z0-9_.-]+$")


def validate_owner(owner: str) -> str:
    candidate = owner.strip()
    if not candidate or not OWNER_RE.fullmatch(candidate):
        raise ValueError(f"Owner invalido: {owner}")
    return candidate


def validate_repos(repos: str) -> list[str]:
    values = [repo.strip() for repo in repos.split(",") if repo.strip()]
    if not values:
        raise ValueError("Debe indicar al menos un repositorio.")
    invalid = [repo for repo in values if not REPO_RE.fullmatch(repo)]
    if invalid:
        raise ValueError(f"Repositorios invalidos: {', '.join(invalid)}")
    return values


def validate_since(since: str) -> str:
    value = since.strip()
    try:
        parse_iso_date(value)
        return value
    except ValueError:
        if parse_iso_datetime(value) is None:
            raise
        return value
