<?php
declare(strict_types=1);

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
$container->set(\App\Controllers\TrelloController::class, static fn (\App\Core\Container $c) => new \App\Controllers\TrelloController($c->get(\App\Interfaces\ITrelloSyncService::class)));

$router->get('/', static function (\App\Core\Request $req, \App\Core\Response $res): void {
    $res->redirect('/dashboard');
});

$router->get('/login', [new \App\Controllers\AuthController(), 'showLogin']);
$router->post('/login', [new \App\Controllers\AuthController(), 'login']);
$router->get('/register', [new \App\Controllers\AuthController(), 'showRegister']);
$router->post('/register', [new \App\Controllers\AuthController(), 'register']);
$router->post('/logout', [new \App\Controllers\AuthController(), 'logout']);

$router->get('/dashboard', [new \App\Controllers\DashboardController(), 'index']);
$router->get('/projects', [new \App\Controllers\ProjectsController(), 'index']);
$router->get('/projects/{id}', [new \App\Controllers\ProjectsController(), 'show']);
$router->get('/analytics', [new \App\Controllers\AnalyticsController(), 'index']);
$router->get('/alerts', [new \App\Controllers\AlertsController(), 'index']);
$router->get('/powerbi', [new \App\Controllers\PowerBIController(), 'index']);
$router->get('/settings', [new \App\Controllers\SettingsController(), 'index']);
$router->get('/trello', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->index($req, $res);
});

$router->get('/api/trello/status', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->status($req, $res);
});
$router->post('/api/trello/connect', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->connect($req, $res);
});
$router->post('/api/trello/disconnect', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->disconnect($req, $res);
});
$router->post('/api/trello/sync', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->sync($req, $res);
});
$router->get('/api/trello/member', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->member($req, $res);
});
$router->get('/api/trello/workspaces', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->workspaces($req, $res);
});
$router->get('/api/trello/boards', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->boards($req, $res);
});
$router->get('/api/trello/lists', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->lists($req, $res);
});
$router->get('/api/trello/cards', static function (\App\Core\Request $req, \App\Core\Response $res) use ($container): void {
    $container->get(\App\Controllers\TrelloController::class)->cards($req, $res);
});

$router->dispatch(new \App\Core\Request());
