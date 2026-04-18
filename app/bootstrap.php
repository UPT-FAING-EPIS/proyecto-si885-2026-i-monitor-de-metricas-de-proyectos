<?php
declare(strict_types=1);

use App\Core\Router;
use App\Core\Cookie;
use App\Services\SupabaseClient;

require_once __DIR__ . '/support/autoload.php';
require_once __DIR__ . '/support/env.php';

loadEnvFile(dirname(__DIR__) . '/.env');

$config = require __DIR__ . '/config/config.php';

$supabase = new SupabaseClient(
    supabaseUrl: $config['supabase']['url'],
    anonKey: $config['supabase']['anon_key'],
);

$router = new Router();

$router->get('/', function () {
    if (Cookie::get('auth_access_token')) {
        header('Location: /dashboard', true, 302);
        exit;
    }
    header('Location: /login', true, 302);
    exit;
});

$router->get('/register', [App\Controllers\AuthController::class, 'showRegister']);
$router->post('/register', [App\Controllers\AuthController::class, 'register']);
$router->get('/login', [App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [App\Controllers\AuthController::class, 'login']);
$router->post('/logout', [App\Controllers\AuthController::class, 'logout']);

$router->get('/dashboard', [App\Controllers\DashboardController::class, 'index']);

$router->get('/projects', [App\Controllers\ProjectsController::class, 'index']);
$router->get('/projects/new', [App\Controllers\ProjectsController::class, 'showCreate']);
$router->post('/projects/new', [App\Controllers\ProjectsController::class, 'create']);
$router->get('/projects/{id}', [App\Controllers\ProjectsController::class, 'show']);
$router->post('/projects/{id}/tasks', [App\Controllers\ProjectsController::class, 'createTask']);

$router->get('/tasks', [App\Controllers\TasksController::class, 'index']);
$router->get('/tasks/{id}', [App\Controllers\TasksController::class, 'show']);
$router->post('/tasks/{id}/status', [App\Controllers\TasksController::class, 'updateStatus']);
$router->post('/tasks/{id}/time', [App\Controllers\TasksController::class, 'addTime']);
$router->post('/tasks/{id}/evidence', [App\Controllers\TasksController::class, 'uploadEvidence']);

$router->dispatch(
    method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
    path: parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
    container: [
        'config' => $config,
        'supabase' => $supabase,
    ],
);

