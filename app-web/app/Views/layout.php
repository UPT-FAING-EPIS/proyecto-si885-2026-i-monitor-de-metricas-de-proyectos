<?php
declare(strict_types=1);

use App\Core\Cookie;

$title = $_app['name'] ?? 'App';
$user = $_auth['user'] ?? null;
$userEmail = is_array($user) ? (string)($user['email'] ?? '') : '';
$isAuthed = Cookie::get('auth_access_token') ? true : false;

function e(mixed $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-900">
    <header class="bg-white border-b">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="font-semibold"><?= e($title) ?></a>
            <nav class="flex items-center gap-4 text-sm">
                <?php if ($isAuthed): ?>
                    <a class="hover:underline" href="/dashboard">Dashboard</a>
                    <a class="hover:underline" href="/projects">Proyectos</a>
                    <a class="hover:underline" href="/tasks">Mis tareas</a>
                    <span class="text-slate-500 hidden sm:inline"><?= e($userEmail) ?></span>
                    <form action="/logout" method="post">
                        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
                        <button class="px-3 py-1 rounded bg-slate-900 text-white">Salir</button>
                    </form>
                <?php else: ?>
                    <a class="hover:underline" href="/login">Ingresar</a>
                    <a class="hover:underline" href="/register">Crear cuenta</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">
        <?php if (is_string($_flash_error) && $_flash_error !== ''): ?>
            <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-800"><?= e($_flash_error) ?></div>
        <?php endif; ?>
        <?php if (is_string($_flash_success) && $_flash_success !== ''): ?>
            <div class="mb-4 p-3 rounded bg-emerald-50 border border-emerald-200 text-emerald-800"><?= e($_flash_success) ?></div>
        <?php endif; ?>

        <?php require $__viewPath; ?>
    </main>
</body>
</html>
