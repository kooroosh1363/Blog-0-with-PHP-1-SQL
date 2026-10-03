<?php

declare(strict_types=1);

use Scribe\Database;

require_once dirname(__DIR__) . '/src/Database.php';

$path = getenv('BLOG_DB_PATH');
$databasePath = is_string($path) && $path !== ''
    ? $path
    : dirname(__DIR__) . '/var/blog.sqlite';

$pdo = Database::connect($databasePath);
Database::migrate($pdo, dirname(__DIR__) . '/database/schema.sql');

fwrite(STDOUT, "Initialized database at {$databasePath}
");
