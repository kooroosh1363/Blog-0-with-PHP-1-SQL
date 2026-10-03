<?php

declare(strict_types=1);

use Scribe\Security;
use Scribe\View;

require_once dirname(__DIR__) . '/src/bootstrap.php';

Security::applyHeaders();

$queryValue = $_GET['q'] ?? '';
$query = is_string($queryValue) ? trim(substr($queryValue, 0, 100)) : '';

$pageValue = $_GET['page'] ?? '1';
$page = is_string($pageValue) && ctype_digit($pageValue)
    ? max(1, (int) $pageValue)
    : 1;

$perPage = 6;

try {
    $repository = scribe_repository();
    $total = $repository->countPublished($query);
    $totalPages = max(1, (int) ceil($total / $perPage));

    if ($page > $totalPages && $total > 0) {
        $page = $totalPages;
    }

    $posts = $repository->listPublished(
        $query,
        $perPage,
        ($page - 1) * $perPage
    );

    $content = View::render('home', [
        'posts' => $posts,
        'query' => $query,
        'page' => $page,
        'total' => $total,
        'totalPages' => $totalPages,
    ]);

    echo View::render('layout', [
        'pageTitle' => 'SCRIBE — PHP/SQLite Publishing Core',
        'description' => 'A secure, server-rendered PHP and SQLite publishing core.',
        'content' => $content,
    ]);
} catch (Throwable $error) {
    http_response_code(503);

    $content = View::render('error', [
        'code' => '503',
        'heading' => 'Publishing database unavailable.',
        'message' => 'Initialize the local database before serving SCRIBE.',
    ]);

    echo View::render('layout', [
        'pageTitle' => 'Service unavailable · SCRIBE',
        'content' => $content,
    ]);
}
