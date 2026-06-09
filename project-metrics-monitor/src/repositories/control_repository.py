from __future__ import annotations

import sqlite3
from collections.abc import Callable
from contextlib import closing


class EtlControlRepository:
    def __init__(self, connection_factory: Callable[[], sqlite3.Connection]) -> None:
        self.connection_factory = connection_factory

    def get_last_loaded_at(self, source_name: str) -> str | None:
        with closing(self.connection_factory()) as connection:
            row = connection.execute(
                "SELECT last_loaded_at FROM etl_control WHERE source_name = ?",
                (source_name,),
            ).fetchone()
        return None if row is None else str(row[0])

    def update_last_loaded_at(self, source_name: str, last_loaded_at: str) -> None:
        with closing(self.connection_factory()) as connection:
            connection.execute(
                """
                INSERT INTO etl_control (source_name, last_loaded_at)
                VALUES (?, ?)
                ON CONFLICT(source_name)
                DO UPDATE SET last_loaded_at = excluded.last_loaded_at
                """,
                (source_name, last_loaded_at),
            )
            connection.commit()
