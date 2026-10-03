<?php

declare(strict_types=1);

use Scribe\Security;
use Scribe\View;

require_once dirname(__DIR__) . '/src/bootstrap.php';

Security::applyHeaders();

$slugValue = $_GET['slug'] ?? '';
$slug = is_string($slugValue) ? trim($slugValue) : '';

try {
    $post = scribe_repository()->findPublishedBySlug($slug);

    if ($post === null) {
        http_response_code(404);

        $content = View::render('error', [
            'code' => '404',
            'heading' => 'Published post not found.',
            'message' => 'The requested slug is invalid, missing, or not publicly published.',
        ]);

        echo View::render('layout', [
            'pageTitle' => 'Post not found · SCRIBE',
            'content' => $content,
        ]);

        exit;
    }

    $content = View::render('post', ['post' => $post]);

    echo View::render('layout', [
        'pageTitle' => $post['title'] . ' · SCRIBE',
        'description' => $post['excerpt'],
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
