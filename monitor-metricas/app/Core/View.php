<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    /** @param array<string,mixed> $data */
    public static function render(string $view, array $data = []): void
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $view . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            echo 'View not found';
            return;
        }

        extract($data, EXTR_SKIP);
        require $file;
    }
}

