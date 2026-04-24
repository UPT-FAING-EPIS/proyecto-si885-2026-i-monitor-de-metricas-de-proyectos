<?php
declare(strict_types=1);

namespace App\Core;

final class Cookie
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_COOKIE[$key] ?? $default;
    }

    public static function set(string $key, string $value, int $ttlSeconds = 0): void
    {
        $expires = $ttlSeconds > 0 ? (time() + $ttlSeconds) : 0;
        setcookie($key, $value, [
            'expires' => $expires,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[$key] = $value;
    }

    public static function forget(string $key): void
    {
        setcookie($key, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[$key]);
    }
}

