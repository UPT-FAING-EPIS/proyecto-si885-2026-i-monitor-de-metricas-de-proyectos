DROP VIEW IF EXISTS vw_throughput;
CREATE VIEW vw_throughput AS
WITH commit_week AS (
    SELECT repo_id, strftime('%Y-%W', commit_date) AS year_week, COUNT(*) AS commits_count
    FROM fact_commits
    GROUP BY repo_id, strftime('%Y-%W', commit_date)
),
pr_week AS (
    SELECT repo_id, strftime('%Y-%W', merged_at) AS year_week, COUNT(*) AS prs_merged_count
    FROM fact_prs
    WHERE merged_at IS NOT NULL
    GROUP BY repo_id, strftime('%Y-%W', merged_at)
),
issue_week AS (
    SELECT repo_id, strftime('%Y-%W', closed_at) AS year_week, COUNT(*) AS issues_closed_count
    FROM fact_issues
    WHERE closed_at IS NOT NULL
    GROUP BY repo_id, strftime('%Y-%W', closed_at)
),
weeks AS (
    SELECT repo_id, year_week FROM commit_week
    UNION
    SELECT repo_id, year_week FROM pr_week
    UNION
    SELECT repo_id, year_week FROM issue_week
)
SELECT
    weeks.repo_id,
    dim_repo.repo_name,
    weeks.year_week,
    COALESCE(commit_week.commits_count, 0) AS commits_count,
    COALESCE(pr_week.prs_merged_count, 0) AS prs_merged_count,
    COALESCE(issue_week.issues_closed_count, 0) AS issues_closed_count,
    COALESCE(pr_week.prs_merged_count, 0) + COALESCE(issue_week.issues_closed_count, 0) AS throughput_total
FROM weeks
JOIN dim_repo ON dim_repo.repo_id = weeks.repo_id
LEFT JOIN commit_week ON commit_week.repo_id = weeks.repo_id AND commit_week.year_week = weeks.year_week
LEFT JOIN pr_week ON pr_week.repo_id = weeks.repo_id AND pr_week.year_week = weeks.year_week
LEFT JOIN issue_week ON issue_week.repo_id = weeks.repo_id AND issue_week.year_week = weeks.year_week;

DROP VIEW IF EXISTS vw_commits_daily;
CREATE VIEW vw_commits_daily AS
SELECT
    fact_commits.repo_id,
    dim_repo.repo_name,
    date(fact_commits.commit_date) AS day,
    COUNT(*) AS commits_count
FROM fact_commits
JOIN dim_repo ON dim_repo.repo_id = fact_commits.repo_id
GROUP BY fact_commits.repo_id, dim_repo.repo_name, date(fact_commits.commit_date);

DROP VIEW IF EXISTS vw_pr_status_summary;
CREATE VIEW vw_pr_status_summary AS
SELECT
    dim_repo.repo_id,
    dim_repo.repo_name,
    SUM(CASE WHEN fact_prs.state = 'OPEN' THEN 1 ELSE 0 END) AS prs_open,
    SUM(CASE WHEN fact_prs.state = 'CLOSED' THEN 1 ELSE 0 END) AS prs_closed,
    SUM(CASE WHEN fact_prs.state = 'MERGED' THEN 1 ELSE 0 END) AS prs_merged
FROM dim_repo
LEFT JOIN fact_prs ON fact_prs.repo_id = dim_repo.repo_id
GROUP BY dim_repo.repo_id, dim_repo.repo_name;

DROP VIEW IF EXISTS vw_issue_status_summary;
CREATE VIEW vw_issue_status_summary AS
SELECT
    dim_repo.repo_id,
    dim_repo.repo_name,
    SUM(CASE WHEN fact_issues.state = 'OPEN' THEN 1 ELSE 0 END) AS issues_open,
    SUM(CASE WHEN fact_issues.state = 'CLOSED' THEN 1 ELSE 0 END) AS issues_closed
FROM dim_repo
LEFT JOIN fact_issues ON fact_issues.repo_id = dim_repo.repo_id
GROUP BY dim_repo.repo_id, dim_repo.repo_name;

DROP VIEW IF EXISTS vw_release_cadence;
CREATE VIEW vw_release_cadence AS
SELECT
    repo_id,
    repo_name,
    COUNT(*) AS releases_count,
    ROUND(AVG(days_since_previous_release), 2) AS avg_days_between_releases,
    MAX(release_date) AS last_release_date
FROM vw_release_summary
WHERE days_since_previous_release IS NOT NULL
GROUP BY repo_id, repo_name;

DROP VIEW IF EXISTS vw_flow_trends_weekly;
CREATE VIEW vw_flow_trends_weekly AS
WITH pr_opened AS (
    SELECT repo_id, strftime('%Y-%W', created_at) AS year_week, COUNT(*) AS prs_opened
    FROM fact_prs
    GROUP BY repo_id, strftime('%Y-%W', created_at)
),
pr_closed AS (
    SELECT repo_id, strftime('%Y-%W', merged_at) AS year_week, COUNT(*) AS prs_closed
    FROM fact_prs
    WHERE merged_at IS NOT NULL
    GROUP BY repo_id, strftime('%Y-%W', merged_at)
),
issue_opened AS (
    SELECT repo_id, strftime('%Y-%W', created_at) AS year_week, COUNT(*) AS issues_opened
    FROM fact_issues
    GROUP BY repo_id, strftime('%Y-%W', created_at)
),
issue_closed AS (
    SELECT repo_id, strftime('%Y-%W', closed_at) AS year_week, COUNT(*) AS issues_closed
    FROM fact_issues
    WHERE closed_at IS NOT NULL
    GROUP BY repo_id, strftime('%Y-%W', closed_at)
),
weeks AS (
    SELECT repo_id, year_week FROM pr_opened
    UNION
    SELECT repo_id, year_week FROM pr_closed
    UNION
    SELECT repo_id, year_week FROM issue_opened
    UNION
    SELECT repo_id, year_week FROM issue_closed
)
SELECT
    weeks.repo_id,
    dim_repo.repo_name,
    weeks.year_week,
    COALESCE(pr_opened.prs_opened, 0) + COALESCE(issue_opened.issues_opened, 0) AS opened_total,
    COALESCE(pr_closed.prs_closed, 0) + COALESCE(issue_closed.issues_closed, 0) AS closed_total
FROM weeks
JOIN dim_repo ON dim_repo.repo_id = weeks.repo_id
LEFT JOIN pr_opened ON pr_opened.repo_id = weeks.repo_id AND pr_opened.year_week = weeks.year_week
LEFT JOIN pr_closed ON pr_closed.repo_id = weeks.repo_id AND pr_closed.year_week = weeks.year_week
LEFT JOIN issue_opened ON issue_opened.repo_id = weeks.repo_id AND issue_opened.year_week = weeks.year_week
LEFT JOIN issue_closed ON issue_closed.repo_id = weeks.repo_id AND issue_closed.year_week = weeks.year_week;

DROP VIEW IF EXISTS vw_lead_time;
CREATE VIEW vw_lead_time AS
WITH ranked AS (
    SELECT
        repo_id,
        lead_time_hours,
        ROW_NUMBER() OVER (PARTITION BY repo_id ORDER BY lead_time_hours) AS rn,
        COUNT(*) OVER (PARTITION BY repo_id) AS cnt
    FROM fact_prs
    WHERE lead_time_hours IS NOT NULL
),
median_values AS (
    SELECT repo_id, AVG(lead_time_hours) AS median_lead_time_hours
    FROM ranked
    WHERE rn IN ((cnt + 1) / 2, (cnt + 2) / 2)
    GROUP BY repo_id
)
SELECT
    fact_prs.repo_id,
    dim_repo.repo_name,
    COUNT(*) AS prs_count,
    ROUND(AVG(fact_prs.lead_time_hours), 2) AS avg_lead_time_hours,
    ROUND(median_values.median_lead_time_hours, 2) AS median_lead_time_hours,
    ROUND(AVG(fact_prs.review_time_hours), 2) AS avg_review_time_hours,
    ROUND(AVG(fact_prs.additions + fact_prs.deletions), 2) AS avg_pr_size
FROM fact_prs
JOIN dim_repo ON dim_repo.repo_id = fact_prs.repo_id
LEFT JOIN median_values ON median_values.repo_id = fact_prs.repo_id
GROUP BY fact_prs.repo_id, dim_repo.repo_name, median_values.median_lead_time_hours;

DROP VIEW IF EXISTS vw_cycle_time;
CREATE VIEW vw_cycle_time AS
SELECT
    fact_issues.repo_id,
    dim_repo.repo_name,
    COUNT(*) AS issues_count,
    ROUND(AVG(fact_issues.cycle_time_hours), 2) AS avg_cycle_time_hours,
    ROUND(MAX(fact_issues.cycle_time_hours), 2) AS max_cycle_time_hours
FROM fact_issues
JOIN dim_repo ON dim_repo.repo_id = fact_issues.repo_id
WHERE fact_issues.cycle_time_hours IS NOT NULL
GROUP BY fact_issues.repo_id, dim_repo.repo_name;

DROP VIEW IF EXISTS vw_aging;
CREATE VIEW vw_aging AS
SELECT
    fact_issues.repo_id,
    dim_repo.repo_name,
    COUNT(*) AS open_issues,
    ROUND(AVG(fact_issues.current_age_hours), 2) AS avg_aging_hours,
    ROUND(MAX(fact_issues.current_age_hours), 2) AS max_aging_hours
FROM fact_issues
JOIN dim_repo ON dim_repo.repo_id = fact_issues.repo_id
WHERE fact_issues.state = 'OPEN'
GROUP BY fact_issues.repo_id, dim_repo.repo_name;

DROP VIEW IF EXISTS vw_repo_summary;
CREATE VIEW vw_repo_summary AS
SELECT
    dim_repo.repo_id,
    dim_repo.owner,
    dim_repo.repo_name,
    dim_repo.language,
    dim_repo.stars,
    dim_repo.forks,
    COUNT(DISTINCT fact_commits.commit_id) AS commits,
    COUNT(DISTINCT CASE WHEN fact_prs.merged_at IS NOT NULL THEN fact_prs.pr_id END) AS prs_merged,
    COUNT(DISTINCT CASE WHEN fact_issues.closed_at IS NOT NULL THEN fact_issues.issue_id END) AS issues_closed,
    COUNT(DISTINCT fact_releases.release_id) AS releases,
    MAX(fact_releases.release_date) AS latest_release_date
FROM dim_repo
LEFT JOIN fact_commits ON fact_commits.repo_id = dim_repo.repo_id
LEFT JOIN fact_prs ON fact_prs.repo_id = dim_repo.repo_id
LEFT JOIN fact_issues ON fact_issues.repo_id = dim_repo.repo_id
LEFT JOIN fact_releases ON fact_releases.repo_id = dim_repo.repo_id
GROUP BY dim_repo.repo_id, dim_repo.owner, dim_repo.repo_name, dim_repo.language, dim_repo.stars, dim_repo.forks;

DROP VIEW IF EXISTS vw_author_activity;
CREATE VIEW vw_author_activity AS
SELECT
    dim_author.author_id,
    dim_author.login,
    dim_author.anonymized_login,
    dim_repo.repo_id,
    dim_repo.repo_name,
    COUNT(DISTINCT fact_commits.commit_id) AS commits,
    COUNT(DISTINCT fact_prs.pr_id) AS prs_authored,
    COUNT(DISTINCT fact_issues.issue_id) AS issues_authored
FROM dim_author
CROSS JOIN dim_repo
LEFT JOIN fact_commits ON fact_commits.author_id = dim_author.author_id AND fact_commits.repo_id = dim_repo.repo_id
LEFT JOIN fact_prs ON fact_prs.author_id = dim_author.author_id AND fact_prs.repo_id = dim_repo.repo_id
LEFT JOIN fact_issues ON fact_issues.author_id = dim_author.author_id AND fact_issues.repo_id = dim_repo.repo_id
GROUP BY dim_author.author_id, dim_author.login, dim_author.anonymized_login, dim_repo.repo_id, dim_repo.repo_name
HAVING commits > 0 OR prs_authored > 0 OR issues_authored > 0;

DROP VIEW IF EXISTS vw_release_summary;
CREATE VIEW vw_release_summary AS
WITH ordered AS (
    SELECT
        repo_id,
        release_id,
        release_date,
        tag_name,
        LAG(release_date) OVER (PARTITION BY repo_id ORDER BY release_date) AS previous_release_date
    FROM fact_releases
)
SELECT
    ordered.repo_id,
    dim_repo.repo_name,
    ordered.release_id,
    ordered.release_date,
    ordered.tag_name,
    ROUND((julianday(ordered.release_date) - julianday(ordered.previous_release_date)), 2) AS days_since_previous_release
FROM ordered
JOIN dim_repo ON dim_repo.repo_id = ordered.repo_id;

DROP VIEW IF EXISTS vw_quality_metrics;
CREATE VIEW vw_quality_metrics AS
WITH issues_agg AS (
    SELECT
        repo_id,
        COUNT(DISTINCT issue_id) AS issues_total,
        SUM(CASE WHEN is_bug = 1 THEN 1 ELSE 0 END) AS bugs_total
    FROM fact_issues
    GROUP BY repo_id
),
prs_agg AS (
    SELECT
        repo_id,
        SUM(COALESCE(requested_changes_count, 0)) AS prs_with_requested_changes
    FROM fact_prs
    GROUP BY repo_id
),
workflows_agg AS (
    SELECT
        repo_id,
        COUNT(DISTINCT workflow_id) AS workflows_total,
        SUM(CASE WHEN conclusion NOT IN ('success', 'skipped') AND conclusion IS NOT NULL THEN 1 ELSE 0 END) AS workflows_failed
    FROM fact_workflows
    GROUP BY repo_id
)
SELECT
    dim_repo.repo_id,
    dim_repo.repo_name,
    COALESCE(issues_agg.issues_total, 0) AS issues_total,
    COALESCE(issues_agg.bugs_total, 0) AS bugs_total,
    ROUND(CAST(COALESCE(issues_agg.bugs_total, 0) AS REAL) / NULLIF(COALESCE(issues_agg.issues_total, 0), 0), 4) AS bug_ratio,
    COALESCE(prs_agg.prs_with_requested_changes, 0) AS prs_with_requested_changes,
    ROUND(CAST(COALESCE(workflows_agg.workflows_failed, 0) AS REAL) / NULLIF(COALESCE(workflows_agg.workflows_total, 0), 0), 4) AS workflow_failure_rate
FROM dim_repo
LEFT JOIN issues_agg ON issues_agg.repo_id = dim_repo.repo_id
LEFT JOIN prs_agg ON prs_agg.repo_id = dim_repo.repo_id
LEFT JOIN workflows_agg ON workflows_agg.repo_id = dim_repo.repo_id;

DROP VIEW IF EXISTS vw_public_author;
CREATE VIEW vw_public_author AS
SELECT author_id, anonymized_login
FROM dim_author;

DROP VIEW IF EXISTS vw_public_commits;
CREATE VIEW vw_public_commits AS
SELECT
    commit_id,
    repo_id,
    author_id,
    date_id,
    commit_date,
    additions,
    deletions
FROM fact_commits;

DROP VIEW IF EXISTS vw_public_prs;
CREATE VIEW vw_public_prs AS
SELECT
    pr_id,
    pr_number,
    repo_id,
    author_id,
    created_date_id,
    merged_date_id,
    created_at,
    merged_at,
    closed_at,
    state,
    lead_time_hours,
    review_time_hours,
    commits_count,
    changed_files,
    additions,
    deletions,
    requested_changes_count
FROM fact_prs;

DROP VIEW IF EXISTS vw_public_issues;
CREATE VIEW vw_public_issues AS
SELECT
    issue_id,
    issue_number,
    repo_id,
    author_id,
    created_date_id,
    closed_date_id,
    created_at,
    closed_at,
    cycle_time_hours,
    current_age_hours,
    state,
    is_bug
FROM fact_issues;

DROP VIEW IF EXISTS vw_public_repo_summary;
CREATE VIEW vw_public_repo_summary AS
SELECT
    repo_id,
    owner,
    repo_name,
    language,
    stars,
    forks,
    commits,
    prs_merged,
    issues_closed,
    releases,
    latest_release_date
FROM vw_repo_summary;

DROP VIEW IF EXISTS vw_public_quality_metrics;
CREATE VIEW vw_public_quality_metrics AS
SELECT
    repo_id,
    repo_name,
    issues_total,
    bugs_total,
    bug_ratio,
    prs_with_requested_changes,
    workflow_failure_rate
FROM vw_quality_metrics;

DROP VIEW IF EXISTS vw_public_throughput;
CREATE VIEW vw_public_throughput AS
SELECT repo_id, repo_name, year_week, prs_merged_count, issues_closed_count, throughput_total
FROM vw_throughput;
