<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function redirect(string $to): void
    {
        header('Location: ' . $to, true, 302);
        exit;
    }

    public function status(int $code): void
    {
        http_response_code($code);
    }

    public function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
