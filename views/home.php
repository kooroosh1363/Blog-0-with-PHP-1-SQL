<?php

declare(strict_types=1);

use Scribe\View;
?>
<section class="hero layout">
    <p class="eyebrow">Publishing core</p>
    <h1>Small enough to audit. Real enough to trust.</h1>
    <p class="hero-copy">
        SCRIBE is a server-rendered PHP blog backed by SQLite and prepared SQL.
        The public web surface is read-only; authoring stays outside the browser.
    </p>
    <div class="architecture-note">
        <strong>Scope boundary</strong>
        <p>No fake login, no exposed admin panel, no browser-side write endpoint, and no claim of GitHub Pages compatibility.</p>
    </div>
</section>

<section class="feed layout" aria-labelledby="feed-title">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Published posts</p>
            <h2 id="feed-title">Database-backed content, not template placeholders.</h2>
        </div>
        <span><?= View::escape($total) ?> result<?= $total === 1 ? '' : 's' ?></span>
    </div>

    <form class="search-form" action="/" method="get" role="search">
        <label for="q">Search title or excerpt</label>
        <div>
            <input id="q" name="q" type="search" maxlength="100" value="<?= View::escape($query) ?>" placeholder="Try security, data, publishing…">
            <button type="submit">Search</button>
        </div>
    </form>

    <?php if ($posts === []): ?>
        <div class="empty-state">
            <strong>No published posts matched this query.</strong>
            <p>Search is literal and wildcard characters are escaped before the prepared SQL executes.</p>
        </div>
    <?php else: ?>
        <div class="post-list">
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <div>
                        <p class="post-date">
                            <?= View::escape(date('M j, Y', strtotime((string) ($post['published_at'] ?? $post['created_at'])))) ?>
                        </p>
                        <h3>
                            <a href="/post.php?slug=<?= rawurlencode((string) $post['slug']) ?>">
                                <?= View::escape($post['title']) ?>
                            </a>
                        </h3>
                        <p><?= View::escape($post['excerpt']) ?></p>
                    </div>
                    <a class="read-link" href="/post.php?slug=<?= rawurlencode((string) $post['slug']) ?>">Read post →</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Post pages">
            <?php if ($page > 1): ?>
                <a href="/?<?= View::escape(http_build_query(['q' => $query, 'page' => $page - 1])) ?>">← Newer</a>
            <?php endif; ?>
            <span>Page <?= View::escape($page) ?> of <?= View::escape($totalPages) ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="/?<?= View::escape(http_build_query(['q' => $query, 'page' => $page + 1])) ?>">Older →</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
