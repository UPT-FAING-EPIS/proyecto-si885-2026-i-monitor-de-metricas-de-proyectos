<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $savePath = (string)ini_get('session.save_path');
            $firstPath = $savePath;
            if (str_contains($savePath, ';')) {
                $parts = explode(';', $savePath);
                $firstPath = (string)end($parts);
            }
            $firstPath = trim($firstPath);
            if ($firstPath !== '' && !is_dir($firstPath)) {
                @mkdir($firstPath, 0777, true);
            }
            if ($firstPath !== '' && !is_dir($firstPath)) {
                $preferred = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sessions';
                if (!is_dir($preferred)) {
                    @mkdir($preferred, 0777, true);
                }
                if (is_dir($preferred)) {
                    @session_save_path($preferred);
                    @ini_set('session.save_path', $preferred);
                } else {
                    $tmp = sys_get_temp_dir();
                    if ($tmp !== '' && is_dir($tmp)) {
                        @session_save_path($tmp);
                        @ini_set('session.save_path', $tmp);
                    }
                }
            }
            session_start();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function pullFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
