<?php

declare(strict_types=1);

use Scribe\Database;
use Scribe\PostRepository;

require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/PostRepository.php';

$options = getopt('', ['title:', 'slug:', 'excerpt:', 'body-file:', 'status::']);

$required = ['title', 'slug', 'excerpt', 'body-file'];

foreach ($required as $key) {
    if (!isset($options[$key]) || !is_string($options[$key]) || trim($options[$key]) === '') {
        fwrite(STDERR, "Missing required option --{$key}
");
        exit(2);
    }
}

$body = file_get_contents($options['body-file']);

if ($body === false) {
    fwrite(STDERR, "Unable to read body file.
");
    exit(2);
}

$status = isset($options['status']) && is_string($options['status'])
    ? $options['status']
    : 'draft';

$path = getenv('BLOG_DB_PATH');
$databasePath = is_string($path) && $path !== ''
    ? $path
    : dirname(__DIR__) . '/var/blog.sqlite';

$pdo = Database::connect($databasePath);
Database::migrate($pdo, dirname(__DIR__) . '/database/schema.sql');

$repository = new PostRepository($pdo);

try {
    $id = $repository->create(
        $options['title'],
        $options['slug'],
        $options['excerpt'],
        $body,
        $status
    );
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "
");
    exit(1);
}

fwrite(STDOUT, "Created post #{$id} as {$status}.
");
