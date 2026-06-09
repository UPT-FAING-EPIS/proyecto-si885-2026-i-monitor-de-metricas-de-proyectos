INSERT OR IGNORE INTO dim_author (author_id, login, display_name, author_type, anonymized_login)
VALUES ('unknown', 'unknown', 'Unknown', 'System', 'unknown');

INSERT OR IGNORE INTO dim_label (label_id, label_name, color, description)
VALUES ('label:unknown', 'unknown', '999999', 'Fallback label');
