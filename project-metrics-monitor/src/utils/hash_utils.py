from __future__ import annotations

import hashlib


def hash_identity(value: str | None, salt: str = "project-metrics-monitor") -> str:
    normalized = (value or "unknown").strip().lower()
    return hashlib.sha256(f"{salt}:{normalized}".encode("utf-8")).hexdigest()
