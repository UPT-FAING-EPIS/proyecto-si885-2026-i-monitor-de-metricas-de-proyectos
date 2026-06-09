<?php
declare(strict_types=1);

$requestPath = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');

if (str_starts_with($requestPath, '/assets/')) {
    $projectRoot = dirname(__DIR__);
    $assetsRoot = realpath($projectRoot . DIRECTORY_SEPARATOR . 'assets');
    $relativePath = ltrim(str_replace('/', DIRECTORY_SEPARATOR, $requestPath), DIRECTORY_SEPARATOR);
    $assetPath = realpath($projectRoot . DIRECTORY_SEPARATOR . $relativePath);

    $isInsideAssets = $assetsRoot !== false
        && $assetPath !== false
        && is_file($assetPath)
        && (
            $assetPath === $assetsRoot
            || str_starts_with($assetPath, $assetsRoot . DIRECTORY_SEPARATOR)
        );

    if (!$isInsideAssets) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not Found';
        exit;
    }

    $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
    header('Content-Length: ' . (string)filesize($assetPath));
    readfile($assetPath);
    exit;
}

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

if (!class_exists(\App\Core\Router::class)) {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (str_starts_with($class, $prefix) === false) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

$router = new \App\Core\Router();

$container = new \App\Core\Container();
$container->set('pdo', static fn (\App\Core\Container $c) => \App\Core\Database::fromEnv()->pdo());
$container->set('crypto', static fn (\App\Core\Container $c) => new \App\Core\Crypto((string)($_ENV['APP_KEY'] ?? $_SERVER['APP_KEY'] ?? getenv('APP_KEY') ?: '')));
$container->set('http', static fn (\App\Core\Container $c) => new \App\Core\HttpClient());
$container->set(\App\Interfaces\ITrelloService::class, static fn (\App\Core\Container $c) => new \App\Services\TrelloService($c->get('http')));
$container->set(\App\Repositories\TrelloConnectionRepository::class, static fn (\App\Core\Container $c) => new \App\Repositories\TrelloConnectionRepository($c->get('pdo')));
$container->set(\App\Repositories\TrelloWorkspaceRepository::class, static fn (\App\Core\Container $c) => new \App\Repositories\TrelloWorkspaceRepository($c->get('pdo')));
$container->set(\App\Repositories\TrelloBoardRepository::class, static fn (\App\Core\Container $c) => new \App\Repositories\TrelloBoardRepository($c->get('pdo')));
$container->set(\App\Repositories\TrelloListRepository::class, static fn (\App\Core\Container $c) => new \App\Repositories\TrelloListRepository($c->get('pdo')));
$container->set(\App\Repositories\TrelloCardRepository::class, static fn (\App\Core\Container $c) => new \App\Repositories\TrelloCardRepository($c->get('pdo')));
$container->set(\App\Repositories\SyncLogRepository::class, static fn (\App\Core\Container $c) => new \App\Repositories\SyncLogRepository($c->get('pdo')));
$container->set(\App\Repositories\ProjectMetricsRepository::class, static fn (\App\Core\Container $c) => new \App\Repositories\ProjectMetricsRepository($c->get('pdo')));
$container->set(\App\Interfaces\IProjectMetricsService::class, static fn (\App\Core\Container $c) => new \App\Services\ProjectMetricsService($c->get(\App\Repositories\ProjectMetricsRepository::class)));
 $container->set(\App\Interfaces\IMonitoringService::class, static fn (\App\Core\Container $c) => new \App\Services\MonitoringService($c->get(\App\Repositories\ProjectMetricsRepository::class)));
$container->set(\App\Interfaces\ITrelloSyncService::class, static fn (\App\Core\Container $c) => new \App\Services\TrelloSyncService(
    $c->get('pdo'),
    $c->get('crypto'),
    $c->get(\App\Interfaces\ITrelloService::class),
    $c->get(\App\Repositories\TrelloConnectionRepository::class),
    $c->get(\App\Repositories\TrelloWorkspaceRepository::class),
    $c->get(\App\Repositories\TrelloBoardRepository::class),
    $c->get(\App\Repositories\TrelloListRepository::class),
    $c->get(\App\Repositories\TrelloCardRepository::class),
    $c->get(\App\Repositories\SyncLogRepository::class),
));
$container->set(\App\Controllers\DashboardController::class, static fn (\App\Core\Container $c) => new \App\Controllers\DashboardController(
    $c->get(\App\Interfaces\IMonitoringService::class),
));
$container->set(\App\Controllers\ProjectsController::class, static fn (\App\Core\Container $c) => new \App\Controllers\ProjectsController(
    $c->get(\App\Interfaces\IMonitoringService::class),
));
$container->set(\App\Controllers\AnalyticsController::class, static fn (\App\Core\Container $c) => new \App\Controllers\AnalyticsController(
    $c->get(\App\Interfaces\IMonitoringService::class),
));
$container->set(\App\Controllers\AlertsController::class, static fn (\App\Core\Container $c) => new \App\Controllers\AlertsController(
    $c->get(\App\Interfaces\IMonitoringService::class),
));
$container->set(\App\Controllers\TrelloController::class, static fn (\App\Core\Container $c) => new \App\Controllers\TrelloController(
    $c->get(\App\Interfaces\ITrelloSyncService::class),
    $c->get(\App\Interfaces\IProjectMetricsService::class),
));

$trelloInitErrorMessage = static function (\Throwable $e): string {
    $message = trim($e->getMessage());
    $lower = strtolower($message);

    if (str_contains($lower, 'app_key')) {
        return 'Falta configurar APP_KEY en Render. Agrega una APP_KEY segura en las variables del servicio y vuelve a desplegar.';
    }

    if (str_contains($lower, 'supabase db no está configurado') || str_contains($lower, 'supabase db no esta configurado')) {
        return 'Faltan variables de PostgreSQL en Render. Configura SUPABASE_DB_HOST, SUPABASE_DB_PORT, SUPABASE_DB_NAME, SUPABASE_DB_USER y SUPABASE_DB_PASSWORD.';
    }

    if (str_contains($lower, 'trello_connections') || str_contains($lower, 'trello_workspaces') || str_contains($lower, 'trello_boards') || str_contains($lower, 'trello_lists') || str_contains($lower, 'trello_cards') || str_contains($lower, 'sync_logs') || str_contains($lower, 'relation') || str_contains($lower, 'does not exist') || str_contains($lower, 'no existe')) {
        return 'Faltan las tablas del modulo Trello en Supabase. Ejecuta la migracion SQL de Trello en el SQL Editor y vuelve a intentar.';
    }

    if (str_contains($lower, 'pgsql') || str_contains($lower, 'sqlstate') || str_contains($lower, 'connection') || str_contains($lower, 'timeout')) {
        return 'No se pudo conectar a Supabase PostgreSQL desde Render. Verifica host, usuario, password, sslmode y acceso de red.';
    }

    return 'No se pudo inicializar Trello. Verifica APP_KEY, variables de Supabase PostgreSQL y ejecuta las migraciones SQL.';
};

$collectTrelloDiagnostics = static function (): array {
    $env = static function (string $key, string $default = ''): string {
        return (string)($_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default);
    };

    $host = trim($env('SUPABASE_DB_HOST'));
    $port = trim($env('SUPABASE_DB_PORT', '5432'));
    $db = trim($env('SUPABASE_DB_NAME', 'postgres'));
    $user = trim($env('SUPABASE_DB_USER'));
    $pass = $env('SUPABASE_DB_PASSWORD');
    $sslmode = trim($env('SUPABASE_DB_SSLMODE', 'require'));
    $appKey = $env('APP_KEY');

    $diagnostics = [
        'php_version' => PHP_VERSION,
        'pdo_loaded' => extension_loaded('pdo'),
        'pdo_pgsql_loaded' => extension_loaded('pdo_pgsql'),
        'curl_loaded' => extension_loaded('curl'),
        'openssl_loaded' => extension_loaded('openssl'),
        'app_key_present' => $appKey !== '',
        'app_key_length' => strlen($appKey),
        'db_host' => $host,
        'db_port' => $port,
        'db_name' => $db,
        'db_user' => $user,
        'db_password_present' => $pass !== '',
        'db_password_length' => strlen($pass),
        'db_sslmode' => $sslmode,
        'dsn' => 'pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $db . ';sslmode=' . $sslmode,
        'connection_test' => 'not-run',
        'connection_error' => '',
    ];

    if ($host === '' || $user === '' || $pass === '') {
        $diagnostics['connection_test'] = 'skipped-missing-env';
        return $diagnostics;
    }

    if (!extension_loaded('pdo_pgsql')) {
        $diagnostics['connection_test'] = 'skipped-missing-pdo_pgsql';
        return $diagnostics;
    }

    try {
        $pdo = new \PDO($diagnostics['dsn'], $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_TIMEOUT => 10,
        ]);
        $pdo->query('select 1');
        $diagnostics['connection_test'] = 'ok';
    } catch (\Throwable $e) {
        $diagnostics['connection_test'] = 'failed';
        $diagnostics['connection_error'] = $e->getMessage();
    }

    return $diagnostics;
};

$renderTrelloBootstrapError = static function (string $message, array $diagnostics = []): void {
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $pretty = htmlspecialchars(json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}', ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Trello no disponible</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;margin:0;padding:32px}.card{max-width:920px;margin:40px auto;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(2,6,23,.08)}h1{margin:0 0 12px;font-size:24px}p{line-height:1.6}a{color:#155fe0;text-decoration:none}.muted{color:#475569;font-size:14px}pre{white-space:pre-wrap;word-break:break-word;background:#0f172a;color:#e2e8f0;padding:16px;border-radius:12px;overflow:auto;font-size:12px}</style>';
    echo '</head><body><div class="card"><h1>Trello no pudo inicializarse</h1><p>' . $safe . '</p><p class="muted">Revisa las variables del servicio en Render y la migración SQL del módulo Trello. Cuando lo corrijas, vuelve a abrir <code>/trello</code>.</p><h2>Diagnostico</h2><pre>' . $pretty . '</pre><p><a href="/settings?tab=integrations">Volver a Configuración</a></p></div></body></html>';
    exit;
};

$renderDashboardBootstrapError = static function (string $message, array $diagnostics = []): void {
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $pretty = htmlspecialchars(json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}', ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Dashboard no disponible</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;margin:0;padding:32px}.card{max-width:920px;margin:40px auto;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(2,6,23,.08)}h1{margin:0 0 12px;font-size:24px}p{line-height:1.6}a{color:#155fe0;text-decoration:none}.muted{color:#475569;font-size:14px}pre{white-space:pre-wrap;word-break:break-word;background:#0f172a;color:#e2e8f0;padding:16px;border-radius:12px;overflow:auto;font-size:12px}</style>';
    echo '</head><body><div class="card"><h1>Dashboard no pudo inicializarse</h1><p>' . $safe . '</p><p class="muted">Comparte este diagnóstico para identificar si falta una migración, una dependencia del contenedor o una consulta SQL.</p><h2>Diagnostico</h2><pre>' . $pretty . '</pre><p><a href="/login">Volver al inicio</a></p></div></body></html>';
    exit;
};

$router->get('/', static function (\App\Core\Request $req, \App\Core\Response $res): void {
    $res->redirect('/dashboard');
});

$router->get('/login', [new \App\Controllers\AuthController(), 'showLogin']);
$router->post('/login', [new \App\Controllers\AuthController(), 'login']);
$router->get('/register', [new \App\Controllers\AuthController(), 'showRegister']);
$router->post('/register', [new \App\Controllers\AuthController(), 'register']);
$router->post('/logout', [new \App\Controllers\AuthController(), 'logout']);

$router->get('/dashboard', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $collectTrelloDiagnostics, $renderDashboardBootstrapError): void {
    try {
        $container->get(\App\Controllers\DashboardController::class)->index($req, $res);
    } catch (\Throwable $e) {
        $diagnostics = $collectTrelloDiagnostics();
        $diagnostics['session_user_id'] = (string)($_SESSION['user']['id'] ?? '');
        $diagnostics['route'] = '/dashboard';
        $diagnostics['error_message'] = $e->getMessage();
        error_log('Dashboard route init error: ' . json_encode($diagnostics, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $renderDashboardBootstrapError('Ocurrio un error al construir el dashboard con datos reales.', $diagnostics);
    }
});
$router->get('/projects', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\ProjectsController::class)->index($req, $res);
});
$router->get('/projects/{id}', static function (\App\Core\Request $req, \App\Core\Response $res, array $params) use ($container): void {
    $container->get(\App\Controllers\ProjectsController::class)->show($req, $res, $params);
});
$router->get('/analytics', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\AnalyticsController::class)->index($req, $res);
});
$router->get('/alerts', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\AlertsController::class)->index($req, $res);
});
$router->get('/powerbi', [new \App\Controllers\PowerBIController(), 'index']);
$router->get('/settings', [new \App\Controllers\SettingsController(), 'index']);
$router->get('/trello', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage, $collectTrelloDiagnostics, $renderTrelloBootstrapError): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->index($req, $res);
    } catch (\Throwable $e) {
        $diagnostics = $collectTrelloDiagnostics();
        error_log('Trello route init error: ' . json_encode([
            'message' => $e->getMessage(),
            'diagnostics' => $diagnostics,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $renderTrelloBootstrapError($trelloInitErrorMessage($e), $diagnostics);
    }
});

$router->get('/api/trello/status', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->status($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});
$router->post('/api/trello/connect', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->connect($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});
$router->post('/api/trello/disconnect', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->disconnect($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});
$router->post('/api/trello/sync', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->sync($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});
$router->get('/api/trello/member', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->member($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});
$router->get('/api/trello/workspaces', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->workspaces($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});
$router->get('/api/trello/boards', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->boards($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});
$router->get('/api/trello/lists', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->lists($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});
$router->get('/api/trello/cards', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->cards($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});
$router->get('/api/trello/metrics', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container, $trelloInitErrorMessage): void {
    try {
        $container->get(\App\Controllers\TrelloController::class)->metrics($req, $res);
    } catch (\Throwable $e) {
        error_log('Trello API init error: ' . $e->getMessage());
        $res->json(['ok' => false, 'error' => $trelloInitErrorMessage($e)], 503);
    }
});

$router->dispatch(new \App\Core\Request());
