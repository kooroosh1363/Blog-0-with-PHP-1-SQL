<?php

declare(strict_types=1);

use Scribe\Database;
use Scribe\PostRepository;
use Scribe\Security;
use Scribe\View;

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/PostRepository.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/View.php';

function scribe_database_path(): string
{
    $configured = getenv('BLOG_DB_PATH');

    return is_string($configured) && $configured !== ''
        ? $configured
        : dirname(__DIR__) . '/var/blog.sqlite';
}

function scribe_repository(): PostRepository
{
    static $repository = null;

    if ($repository instanceof PostRepository) {
        return $repository;
    }

    $path = scribe_database_path();

    if ($path !== ':memory:' && !is_file($path)) {
        throw new RuntimeException(
            'Database is not initialized. Run: php bin/init-db.php && php bin/seed.php'
        );
    }

    $repository = new PostRepository(Database::connect($path));

    return $repository;
}
