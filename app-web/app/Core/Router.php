<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<int, array{pattern:string, handler:mixed}>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $pattern, mixed $handler): void
    {
        $this->routes['GET'][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function post(string $pattern, mixed $handler): void
    {
        $this->routes['POST'][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function dispatch(string $method, string $path, array $container = []): void
    {
        $method = strtoupper($method);
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            $params = $this->match($route['pattern'], $path);
            if ($params === null) {
                continue;
            }

            $handler = $route['handler'];
            if (is_callable($handler)) {
                $handler(...array_values($params));
                return;
            }

            if (is_array($handler) && count($handler) === 2) {
                [$class, $action] = $handler;
                $controller = new $class($container);
                $controller->$action(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        echo '404';
    }

    private function match(string $pattern, string $path): ?array
    {
        $patternParts = explode('/', trim($pattern, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (count($patternParts) !== count($pathParts)) {
            return null;
        }

        $params = [];
        foreach ($patternParts as $i => $part) {
            if (preg_match('/^\{(\w+)\}$/', $part, $m) === 1) {
                $params[$m[1]] = $pathParts[$i];
                continue;
            }
            if ($part !== $pathParts[$i]) {
                return null;
            }
        }

        return $params;
    }
}

