<?php

declare(strict_types=1);

use Scribe\View;

$pageTitle = $pageTitle ?? 'SCRIBE';
$description = $description ?? 'A small PHP and SQLite publishing core.';
$content = $content ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= View::escape($description) ?>">
    <title><?= View::escape($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<a class="skip-link" href="#content">Skip to content</a>
<header class="site-header">
    <div class="layout header-row">
        <a class="brand" href="/">SCRIBE</a>
        <span class="header-note">PHP · SQLite · server-rendered publishing</span>
    </div>
</header>
<main id="content">
    <?= $content ?>
</main>
<footer class="site-footer layout">
    <strong>SCRIBE</strong>
    <span>Read-only web surface · CLI publishing workflow</span>
</footer>
</body>
</html>
