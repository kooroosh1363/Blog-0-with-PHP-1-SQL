<?php

declare(strict_types=1);

use Scribe\View;

$published = (string) ($post['published_at'] ?? $post['created_at']);
?>
<article class="article layout">
    <a class="back-link" href="/">← All posts</a>
    <p class="eyebrow">Published <?= View::escape(date('M j, Y', strtotime($published))) ?></p>
    <h1><?= View::escape($post['title']) ?></h1>
    <p class="article-excerpt"><?= View::escape($post['excerpt']) ?></p>
    <div class="article-body"><?= nl2br(View::escape($post['body'])) ?></div>
</article>
