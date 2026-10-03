<?php

declare(strict_types=1);

use Scribe\Database;
use Scribe\PostRepository;
use Scribe\View;

require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/PostRepository.php';
require_once dirname(__DIR__) . '/src/View.php';

$tests = [];

function test(string $name, callable $callback): void
{
    global $tests;
    $tests[] = [$name, $callback];
}

function expect(bool $condition, string $message = 'Expectation failed.'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function freshRepository(): array
{
    $pdo = Database::connect(':memory:');
    Database::migrate($pdo, dirname(__DIR__) . '/database/schema.sql');

    return [$pdo, new PostRepository($pdo)];
}

test('schema creates posts table', function (): void {
    [$pdo] = freshRepository();
    $name = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='posts'")->fetchColumn();
    expect($name === 'posts');
});

test('valid slug is accepted', function (): void {
    expect(PostRepository::isValidSlug('secure-publishing-101'));
});

test('uppercase slug is rejected', function (): void {
    expect(!PostRepository::isValidSlug('Secure-Publishing'));
});

test('slug with repeated hyphen is rejected', function (): void {
    expect(!PostRepository::isValidSlug('secure--publishing'));
});

test('draft creation does not set published_at', function (): void {
    [$pdo, $repo] = freshRepository();
    $repo->create('Draft', 'draft-post', 'Excerpt', 'Body', 'draft');
    $row = $pdo->query("SELECT status, published_at FROM posts WHERE slug='draft-post'")->fetch();
    expect($row['status'] === 'draft');
    expect($row['published_at'] === null);
});

test('published creation sets published_at', function (): void {
    [$pdo, $repo] = freshRepository();
    $repo->create('Published', 'published-post', 'Excerpt', 'Body', 'published');
    $row = $pdo->query("SELECT status, published_at FROM posts WHERE slug='published-post'")->fetch();
    expect($row['status'] === 'published');
    expect(is_string($row['published_at']) && $row['published_at'] !== '');
});

test('public list excludes drafts', function (): void {
    [, $repo] = freshRepository();
    $repo->create('Visible', 'visible-post', 'Visible excerpt', 'Body', 'published');
    $repo->create('Hidden', 'hidden-post', 'Hidden excerpt', 'Body', 'draft');
    $rows = $repo->listPublished('', 10, 0);
    expect(count($rows) === 1);
    expect($rows[0]['slug'] === 'visible-post');
});

test('public count excludes drafts', function (): void {
    [, $repo] = freshRepository();
    $repo->create('Visible', 'visible-post', 'Visible excerpt', 'Body', 'published');
    $repo->create('Hidden', 'hidden-post', 'Hidden excerpt', 'Body', 'draft');
    expect($repo->countPublished('') === 1);
});

test('slug lookup excludes drafts', function (): void {
    [, $repo] = freshRepository();
    $repo->create('Hidden', 'hidden-post', 'Excerpt', 'Body', 'draft');
    expect($repo->findPublishedBySlug('hidden-post') === null);
});

test('slug lookup rejects invalid input before query result', function (): void {
    [, $repo] = freshRepository();
    expect($repo->findPublishedBySlug("' OR 1=1 --") === null);
});

test('search matches published title', function (): void {
    [, $repo] = freshRepository();
    $repo->create('Prepared Statements', 'prepared-statements', 'SQL safety', 'Body', 'published');
    expect(count($repo->listPublished('Prepared', 10, 0)) === 1);
});

test('search matches published excerpt', function (): void {
    [, $repo] = freshRepository();
    $repo->create('SQL Notes', 'sql-notes', 'Bound parameters prevent injection', 'Body', 'published');
    expect(count($repo->listPublished('parameters', 10, 0)) === 1);
});

test('LIKE percent is treated literally', function (): void {
    [, $repo] = freshRepository();
    $repo->create('Hundred percent', 'hundred-percent', '100% literal', 'Body', 'published');
    $repo->create('Other', 'other-post', '1000 literal', 'Body', 'published');
    $rows = $repo->listPublished('100%', 10, 0);
    expect(count($rows) === 1);
    expect($rows[0]['slug'] === 'hundred-percent');
});

test('LIKE underscore is treated literally', function (): void {
    [, $repo] = freshRepository();
    $repo->create('Underscore', 'underscore-post', 'a_b literal', 'Body', 'published');
    $repo->create('Wildcard lookalike', 'lookalike-post', 'acb literal', 'Body', 'published');
    $rows = $repo->listPublished('a_b', 10, 0);
    expect(count($rows) === 1);
});

test('list limit is bounded to 50', function (): void {
    [, $repo] = freshRepository();

    for ($i = 1; $i <= 55; $i++) {
        $repo->create("Post {$i}", "post-{$i}", 'Excerpt', 'Body', 'published');
    }

    expect(count($repo->listPublished('', 500, 0)) === 50);
});

test('negative offset is normalized', function (): void {
    [, $repo] = freshRepository();
    $repo->create('One', 'one-post', 'Excerpt', 'Body', 'published');
    expect(count($repo->listPublished('', 10, -10)) === 1);
});

test('duplicate slug is rejected by database constraint', function (): void {
    [, $repo] = freshRepository();
    $repo->create('One', 'unique-slug', 'Excerpt', 'Body', 'draft');

    $thrown = false;

    try {
        $repo->create('Two', 'unique-slug', 'Excerpt', 'Body', 'draft');
    } catch (Throwable) {
        $thrown = true;
    }

    expect($thrown);
});

test('invalid status is rejected', function (): void {
    [, $repo] = freshRepository();

    $thrown = false;

    try {
        $repo->create('Post', 'valid-slug', 'Excerpt', 'Body', 'archived');
    } catch (InvalidArgumentException) {
        $thrown = true;
    }

    expect($thrown);
});

test('empty title is rejected', function (): void {
    [, $repo] = freshRepository();

    $thrown = false;

    try {
        $repo->create('', 'valid-slug', 'Excerpt', 'Body', 'draft');
    } catch (InvalidArgumentException) {
        $thrown = true;
    }

    expect($thrown);
});

test('empty body is rejected', function (): void {
    [, $repo] = freshRepository();

    $thrown = false;

    try {
        $repo->create('Post', 'valid-slug', 'Excerpt', '', 'draft');
    } catch (InvalidArgumentException) {
        $thrown = true;
    }

    expect($thrown);
});

test('HTML output escaping blocks script markup', function (): void {
    $escaped = View::escape('<script>alert("x")</script>');
    expect(!str_contains($escaped, '<script>'));
    expect(str_contains($escaped, '&lt;script&gt;'));
});

$passed = 0;
$failed = 0;

foreach ($tests as [$name, $callback]) {
    try {
        $callback();
        $passed++;
        fwrite(STDOUT, "PASS {$name}
");
    } catch (Throwable $error) {
        $failed++;
        fwrite(STDERR, "FAIL {$name}: {$error->getMessage()}
");
    }
}

fwrite(STDOUT, "
{$passed} passed, {$failed} failed
");
exit($failed === 0 ? 0 : 1);
