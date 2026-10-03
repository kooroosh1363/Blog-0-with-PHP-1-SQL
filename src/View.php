<?php

declare(strict_types=1);

namespace Scribe;

use RuntimeException;

final class View
{
    public static function escape(string|int|float|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function render(string $template, array $data = []): string
    {
        $templatePath = dirname(__DIR__) . '/views/' . $template . '.php';

        if (!is_file($templatePath)) {
            throw new RuntimeException('View template not found.');
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        if ($content === false) {
            throw new RuntimeException('Unable to render view.');
        }

        return $content;
    }
}
