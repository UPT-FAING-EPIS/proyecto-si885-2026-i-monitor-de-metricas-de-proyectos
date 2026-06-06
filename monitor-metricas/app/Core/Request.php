<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    private ?array $json = null;

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        return $path ? rtrim($path, '/') ?: '/' : '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }
        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }
        $json = $this->json();
        if (is_array($json) && array_key_exists($key, $json)) {
            return $json[$key];
        }
        return $default;
    }

    public function csrf(): string
    {
        if (isset($_POST['csrf'])) {
            return (string)$_POST['csrf'];
        }

        $json = $this->json();
        if (is_array($json) && isset($json['csrf'])) {
            return (string)$json['csrf'];
        }

        $header = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return $header !== '' ? $header : '';
    }

    public function json(): ?array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $ct = (string)($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
        if ($ct === '' || stripos($ct, 'application/json') === false) {
            $this->json = null;
            return null;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            $this->json = null;
            return null;
        }

        $decoded = json_decode($raw, true);
        $this->json = is_array($decoded) ? $decoded : null;
        return $this->json;
    }
}
