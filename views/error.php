<?php

declare(strict_types=1);

use Scribe\View;
?>
<section class="error-state layout">
    <p class="eyebrow"><?= View::escape($code) ?></p>
    <h1><?= View::escape($heading) ?></h1>
    <p><?= View::escape($message) ?></p>
    <a href="/">Return to published posts</a>
</section>
