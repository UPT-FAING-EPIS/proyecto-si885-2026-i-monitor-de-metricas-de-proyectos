from __future__ import annotations

from datetime import UTC, datetime


def parse_iso_datetime(value: str | None) -> datetime | None:
    if not value:
        return None
    normalized = value.replace("Z", "+00:00")
    return datetime.fromisoformat(normalized).astimezone(UTC)


def parse_iso_date(value: str) -> datetime:
    return datetime.fromisoformat(value).replace(tzinfo=UTC)


def to_date_id(value: datetime | None) -> int | None:
    if value is None:
        return None
    return int(value.strftime("%Y%m%d"))


def to_iso(value: datetime | None) -> str | None:
    if value is None:
        return None
    return value.astimezone(UTC).replace(microsecond=0).isoformat()


def utc_now_iso() -> str:
    return to_iso(datetime.now(UTC))
