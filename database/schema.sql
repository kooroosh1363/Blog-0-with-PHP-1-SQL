PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE
        CHECK (length(slug) BETWEEN 3 AND 120),
    title TEXT NOT NULL
        CHECK (length(title) BETWEEN 1 AND 160),
    excerpt TEXT NOT NULL DEFAULT ''
        CHECK (length(excerpt) <= 320),
    body TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'draft'
        CHECK (status IN ('draft', 'published')),
    published_at TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_posts_publication
    ON posts(status, published_at DESC, id DESC);
