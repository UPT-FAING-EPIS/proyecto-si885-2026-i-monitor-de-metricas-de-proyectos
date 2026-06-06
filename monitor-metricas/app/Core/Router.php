<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<int, array{pattern:string, handler:callable}>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $pattern, callable $handler): void
    {
        $this->routes['GET'][] = ['pattern' => $this->normalize($pattern), 'handler' => $handler];
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->routes['POST'][] = ['pattern' => $this->normalize($pattern), 'handler' => $handler];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            $params = $this->match($route['pattern'], $path);
            if ($params === null) {
                continue;
            }
            $handler = $route['handler'];
            $argc = 2;
            if (is_array($handler) && isset($handler[0], $handler[1]) && is_object($handler[0]) && is_string($handler[1])) {
                $argc = (new \ReflectionMethod($handler[0], $handler[1]))->getNumberOfParameters();
            } elseif (is_object($handler) && !$handler instanceof \Closure && is_callable($handler)) {
                $argc = (new \ReflectionMethod($handler, '__invoke'))->getNumberOfParameters();
            } elseif ($handler instanceof \Closure) {
                $argc = (new \ReflectionFunction($handler))->getNumberOfParameters();
            } elseif (is_string($handler) && is_callable($handler)) {
                $argc = (new \ReflectionFunction($handler))->getNumberOfParameters();
            }

            if ($argc >= 3) {
                ($handler)($request, new Response(), $params);
            } else {
                ($handler)($request, new Response());
            }
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    private function normalize(string $pattern): string
    {
        $pattern = '/' . ltrim($pattern, '/');
        $pattern = rtrim($pattern, '/') ?: '/';
        return $pattern;
    }

    /** @return array<string,string>|null */
    private function match(string $pattern, string $path): ?array
    {
        if ($pattern === $path) {
            return [];
        }

        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        if ($regex === null) {
            return null;
        }
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $k => $v) {
            if (!is_string($k)) {
                continue;
            }
            $params[$k] = (string)$v;
        }
        return $params;
    }
}
