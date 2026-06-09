PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS dim_date (
    date_id INTEGER PRIMARY KEY,
    full_date TEXT NOT NULL UNIQUE,
    year INTEGER NOT NULL,
    quarter INTEGER NOT NULL,
    month INTEGER NOT NULL,
    month_name TEXT NOT NULL,
    week_of_year INTEGER NOT NULL,
    day_of_month INTEGER NOT NULL,
    day_of_week INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS dim_repo (
    repo_id INTEGER PRIMARY KEY,
    owner TEXT NOT NULL,
    repo_name TEXT NOT NULL,
    full_name TEXT NOT NULL UNIQUE,
    description TEXT,
    language TEXT,
    stars INTEGER NOT NULL DEFAULT 0,
    forks INTEGER NOT NULL DEFAULT 0,
    open_issues INTEGER NOT NULL DEFAULT 0,
    default_branch TEXT,
    archived INTEGER NOT NULL DEFAULT 0,
    visibility TEXT NOT NULL DEFAULT 'public',
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS dim_author (
    author_id TEXT PRIMARY KEY,
    login TEXT NOT NULL,
    display_name TEXT NOT NULL,
    author_type TEXT NOT NULL,
    anonymized_login TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS dim_label (
    label_id TEXT PRIMARY KEY,
    label_name TEXT NOT NULL,
    color TEXT,
    description TEXT
);

CREATE TABLE IF NOT EXISTS fact_commits (
    commit_id TEXT PRIMARY KEY,
    repo_id INTEGER NOT NULL,
    author_id TEXT NOT NULL,
    date_id INTEGER,
    commit_date TEXT NOT NULL,
    message TEXT NOT NULL,
    additions INTEGER NOT NULL DEFAULT 0,
    deletions INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (repo_id) REFERENCES dim_repo(repo_id),
    FOREIGN KEY (author_id) REFERENCES dim_author(author_id),
    FOREIGN KEY (date_id) REFERENCES dim_date(date_id)
);

CREATE TABLE IF NOT EXISTS fact_prs (
    pr_id TEXT PRIMARY KEY,
    pr_number INTEGER NOT NULL,
    repo_id INTEGER NOT NULL,
    author_id TEXT NOT NULL,
    created_date_id INTEGER,
    merged_date_id INTEGER,
    created_at TEXT NOT NULL,
    merged_at TEXT,
    closed_at TEXT,
    state TEXT NOT NULL,
    lead_time_hours REAL,
    review_time_hours REAL,
    commits_count INTEGER NOT NULL DEFAULT 0,
    changed_files INTEGER NOT NULL DEFAULT 0,
    additions INTEGER NOT NULL DEFAULT 0,
    deletions INTEGER NOT NULL DEFAULT 0,
    requested_changes_count INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (repo_id) REFERENCES dim_repo(repo_id),
    FOREIGN KEY (author_id) REFERENCES dim_author(author_id),
    FOREIGN KEY (created_date_id) REFERENCES dim_date(date_id),
    FOREIGN KEY (merged_date_id) REFERENCES dim_date(date_id)
);

CREATE TABLE IF NOT EXISTS fact_issues (
    issue_id TEXT PRIMARY KEY,
    issue_number INTEGER NOT NULL,
    repo_id INTEGER NOT NULL,
    author_id TEXT NOT NULL,
    created_date_id INTEGER,
    closed_date_id INTEGER,
    created_at TEXT NOT NULL,
    closed_at TEXT,
    cycle_time_hours REAL,
    current_age_hours REAL,
    state TEXT NOT NULL,
    is_bug INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (repo_id) REFERENCES dim_repo(repo_id),
    FOREIGN KEY (author_id) REFERENCES dim_author(author_id),
    FOREIGN KEY (created_date_id) REFERENCES dim_date(date_id),
    FOREIGN KEY (closed_date_id) REFERENCES dim_date(date_id)
);

CREATE TABLE IF NOT EXISTS fact_releases (
    release_id INTEGER PRIMARY KEY,
    repo_id INTEGER NOT NULL,
    date_id INTEGER,
    release_date TEXT NOT NULL,
    tag_name TEXT NOT NULL,
    name TEXT,
    draft INTEGER NOT NULL DEFAULT 0,
    prerelease INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (repo_id) REFERENCES dim_repo(repo_id),
    FOREIGN KEY (date_id) REFERENCES dim_date(date_id)
);

CREATE TABLE IF NOT EXISTS fact_workflows (
    workflow_id INTEGER PRIMARY KEY,
    repo_id INTEGER NOT NULL,
    date_id INTEGER,
    run_date TEXT NOT NULL,
    status TEXT NOT NULL,
    conclusion TEXT,
    workflow_name TEXT,
    branch TEXT,
    event TEXT,
    duration_seconds INTEGER,
    FOREIGN KEY (repo_id) REFERENCES dim_repo(repo_id),
    FOREIGN KEY (date_id) REFERENCES dim_date(date_id)
);

CREATE TABLE IF NOT EXISTS fact_contributors (
    contributor_id TEXT PRIMARY KEY,
    repo_id INTEGER NOT NULL,
    author_id TEXT NOT NULL,
    contributions INTEGER NOT NULL DEFAULT 0,
    first_seen_date_id INTEGER,
    last_seen_date_id INTEGER,
    first_seen_at TEXT,
    last_seen_at TEXT,
    FOREIGN KEY (repo_id) REFERENCES dim_repo(repo_id),
    FOREIGN KEY (author_id) REFERENCES dim_author(author_id),
    FOREIGN KEY (first_seen_date_id) REFERENCES dim_date(date_id),
    FOREIGN KEY (last_seen_date_id) REFERENCES dim_date(date_id)
);

CREATE TABLE IF NOT EXISTS bridge_issue_labels (
    issue_id TEXT NOT NULL,
    label_id TEXT NOT NULL,
    PRIMARY KEY (issue_id, label_id),
    FOREIGN KEY (issue_id) REFERENCES fact_issues(issue_id),
    FOREIGN KEY (label_id) REFERENCES dim_label(label_id)
);

CREATE TABLE IF NOT EXISTS bridge_pr_labels (
    pr_id TEXT NOT NULL,
    label_id TEXT NOT NULL,
    PRIMARY KEY (pr_id, label_id),
    FOREIGN KEY (pr_id) REFERENCES fact_prs(pr_id),
    FOREIGN KEY (label_id) REFERENCES dim_label(label_id)
);

CREATE TABLE IF NOT EXISTS etl_control (
    source_name TEXT PRIMARY KEY,
    last_loaded_at TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_fact_commits_repo_date ON fact_commits (repo_id, date_id);
CREATE INDEX IF NOT EXISTS idx_fact_prs_repo_created ON fact_prs (repo_id, created_date_id);
CREATE INDEX IF NOT EXISTS idx_fact_issues_repo_created ON fact_issues (repo_id, created_date_id);
CREATE INDEX IF NOT EXISTS idx_fact_releases_repo_date ON fact_releases (repo_id, date_id);
CREATE INDEX IF NOT EXISTS idx_fact_workflows_repo_date ON fact_workflows (repo_id, date_id);
CREATE INDEX IF NOT EXISTS idx_fact_contributors_repo_author ON fact_contributors (repo_id, author_id);
CREATE INDEX IF NOT EXISTS idx_bridge_issue_labels_label ON bridge_issue_labels (label_id);
CREATE INDEX IF NOT EXISTS idx_bridge_pr_labels_label ON bridge_pr_labels (label_id);
