<?php

declare(strict_types=1);

namespace Scribe;

final class Security
{
    public static function applyHeaders(): void
    {
        header("Content-Security-Policy: default-src 'self'; style-src 'self'; img-src 'self' data:; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");
        header('Referrer-Policy: no-referrer');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }
}
