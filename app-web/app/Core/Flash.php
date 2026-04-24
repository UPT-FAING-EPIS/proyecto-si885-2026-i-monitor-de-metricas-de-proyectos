<?php
declare(strict_types=1);

namespace App\Core;

final class Flash
{
    public static function set(string $key, string $value): void
    {
        Cookie::set('_flash_' . $key, base64_encode($value), 60);
    }

    public static function pull(string $key): ?string
    {
        $raw = Cookie::get('_flash_' . $key);
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        Cookie::forget('_flash_' . $key);
        $decoded = base64_decode($raw, true);
        return is_string($decoded) ? $decoded : null;
    }
}

