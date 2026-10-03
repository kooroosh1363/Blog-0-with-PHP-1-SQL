<?php

declare(strict_types=1);

use Scribe\Database;
use Scribe\PostRepository;

require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/PostRepository.php';

$path = getenv('BLOG_DB_PATH');
$databasePath = is_string($path) && $path !== ''
    ? $path
    : dirname(__DIR__) . '/var/blog.sqlite';

$pdo = Database::connect($databasePath);
Database::migrate($pdo, dirname(__DIR__) . '/database/schema.sql');

$repository = new PostRepository($pdo);

$count = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();

if ($count > 0) {
    fwrite(STDOUT, "Seed skipped: posts already exist.
");
    exit(0);
}

$repository->create(
    'Why SCRIBE keeps the public surface read-only',
    'why-scribe-keeps-the-public-surface-read-only',
    'A small publishing system can remove an entire class of web risk by moving authoring out of the browser.',
    "The original repository included a static admin dashboard template, but no real authentication or write path.

SCRIBE chooses a smaller security boundary: the browser can only read published posts. Authoring happens through local command-line scripts, so there is no exposed login form, password reset flow, CSRF-sensitive write endpoint, or session state to pretend is secure.",
    'published'
);

$repository->create(
    'Prepared SQL is the default, not the cleanup step',
    'prepared-sql-is-the-default',
    'All user-controlled lookup values flow through prepared PDO statements.',
    "Search and slug lookup are implemented with bound parameters from the start.

The repository also escapes LIKE wildcard characters before searching so a literal percent or underscore remains a literal search character instead of silently changing query semantics.",
    'published'
);

$repository->create(
    'Drafts stay out of public queries',
    'drafts-stay-out-of-public-queries',
    'Publication status is enforced in the repository layer, not only in the template.',
    "A draft can exist in SQLite without becoming publicly visible.

Public list, count, and slug lookup queries all require status = published. This makes the visibility rule testable and keeps it out of presentation code.",
    'draft'
);

fwrite(STDOUT, "Seeded SCRIBE demo content.
");
