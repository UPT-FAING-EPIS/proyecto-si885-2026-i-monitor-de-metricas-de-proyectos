from __future__ import annotations

import sqlite3
from contextlib import closing
from pathlib import Path

import pandas as pd


class SQLiteLoader:
    def __init__(self, db_path: Path, schema_path: Path, views_path: Path, seed_path: Path) -> None:
        self.db_path = db_path
        self.schema_path = schema_path
        self.views_path = views_path
        self.seed_path = seed_path

    def connect(self) -> sqlite3.Connection:
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        connection = sqlite3.connect(self.db_path)
        connection.execute("PRAGMA foreign_keys = ON;")
        return connection

    def initialize(self) -> None:
        with closing(self.connect()) as connection:
            connection.executescript(self.schema_path.read_text(encoding="utf-8"))
            connection.executescript(self.seed_path.read_text(encoding="utf-8"))
            connection.executescript(self.views_path.read_text(encoding="utf-8"))
            connection.commit()

    def upsert_dataframe(
        self, connection: sqlite3.Connection, table_name: str, df: pd.DataFrame
    ) -> None:
        if df.empty:
            return
        columns = list(df.columns)
        placeholders = ", ".join("?" for _ in columns)
        sql = f"""
        INSERT OR REPLACE INTO {table_name} ({", ".join(columns)})
        VALUES ({placeholders})
        """
        rows = [
            tuple(None if pd.isna(value) else value for value in row)
            for row in df.itertuples(index=False)
        ]
        connection.executemany(sql, rows)

    def load_dataset(self, dataset: dict[str, pd.DataFrame]) -> None:
        load_order = [
            "dim_date",
            "dim_repo",
            "dim_author",
            "dim_label",
            "fact_commits",
            "fact_prs",
            "fact_issues",
            "fact_releases",
            "fact_workflows",
            "fact_contributors",
            "bridge_issue_labels",
            "bridge_pr_labels",
        ]
        with closing(self.connect()) as connection:
            for table_name in load_order:
                self.upsert_dataframe(
                    connection, table_name, dataset.get(table_name, pd.DataFrame())
                )
            connection.commit()

    def fetch_dataframe(self, sql: str) -> pd.DataFrame:
        with closing(self.connect()) as connection:
            return pd.read_sql_query(sql, connection)
