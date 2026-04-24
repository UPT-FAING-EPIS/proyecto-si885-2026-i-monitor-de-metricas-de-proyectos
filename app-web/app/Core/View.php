<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php';
        if (!is_file($viewPath)) {
            http_response_code(500);
            echo 'Vista no encontrada';
            return;
        }

        extract($data, EXTR_SKIP);
        $__viewPath = $viewPath;

        require __DIR__ . '/../Views/layout.php';
    }
}
