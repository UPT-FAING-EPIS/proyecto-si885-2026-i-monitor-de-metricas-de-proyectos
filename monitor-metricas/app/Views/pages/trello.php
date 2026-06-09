<?php
declare(strict_types=1);

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function icon(string $name): string {
    $icons = [
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'search' => '<path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2"/><path d="M16.5 16.5 21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'bell' => '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M13.7 21a2 2 0 0 1-3.4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'moon' => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'link' => '<path d="M10 13a5 5 0 0 1 0-7l1-1a5 5 0 0 1 7 7l-1 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M14 11a5 5 0 0 1 0 7l-1 1a5 5 0 0 1-7-7l1-1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'sync' => '<path d="M21 12a9 9 0 0 0-15.4-6.4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 4v6h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 12a9 9 0 0 0 15.4 6.4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M21 20v-6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'power' => '<path d="M12 2v10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 4.5A9 9 0 1 0 17 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'trello' => '<path d="M5 4h6a2 2 0 0 1 2 2v10a4 4 0 0 1-4 4H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" fill="currentColor" opacity="0.85"/><path d="M15 4h4a2 2 0 0 1 2 2v6a4 4 0 0 1-4 4h-2V6a2 2 0 0 1 2-2Z" fill="currentColor" opacity="0.6"/>',
    ];
    return $icons[$name] ?? '';
}

$csrf = $_SESSION['csrf'] ?? '';
$trelloStatus = isset($trelloStatus) && is_array($trelloStatus) ? $trelloStatus : null;
$trelloMetrics = isset($trelloMetrics) && is_array($trelloMetrics) ? $trelloMetrics : ['summary' => [], 'boards' => [], 'latest_sync' => null];
$trelloError = isset($trelloError) && is_string($trelloError) ? $trelloError : '';
$trelloApiKey = trim((string)($_ENV['TRELLO_API_KEY'] ?? $_SERVER['TRELLO_API_KEY'] ?? getenv('TRELLO_API_KEY') ?: ''));
$configuredAppUrl = trim((string)($_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? getenv('APP_URL') ?: ''));
$forwardedProto = trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
$forwardedHost = trim((string)($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''));
$requestHost = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
$requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https'
    ? 'https'
    : 'http';
$runtimeOrigin = '';
if ($forwardedHost !== '' || $requestHost !== '') {
    $runtimeOrigin = $requestScheme . '://' . ($forwardedHost !== '' ? $forwardedHost : $requestHost);
}
$appUrl = $runtimeOrigin !== '' ? $runtimeOrigin : $configuredAppUrl;
$trelloAuthorizeUrl = '';
if ($trelloApiKey !== '' && $appUrl !== '') {
    $trelloAuthorizeUrl = 'https://trello.com/1/authorize?' . http_build_query([
        'expiration' => 'never',
        'name' => 'Project Metrics Monitor',
        'scope' => 'read',
        'response_type' => 'token',
        'key' => $trelloApiKey,
        'return_url' => rtrim($appUrl, '/') . '/trello',
    ]);
}
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Integración Trello · Project Metrics Monitor</title>
    <script>
      (function () {
        try {
          var stored = localStorage.getItem('pm:theme');
          var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
          var theme = stored === 'light' || stored === 'dark' ? stored : (prefersDark ? 'dark' : 'light');
          document.documentElement.classList.toggle('dark', theme === 'dark');
        } catch (e) {}
      })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              pm: {
                50: '#eef6ff',
                100: '#d9ebff',
                200: '#b8dbff',
                300: '#86c2ff',
                400: '#4ca2ff',
                500: '#1f7aff',
                600: '#155fe0',
                700: '#134bb4',
                800: '#133f91',
                900: '#123574'
              }
            },
            boxShadow: {
              'soft': '0 10px 30px rgba(2, 6, 23, 0.10)'
            }
          }
        }
      };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/assets/css/app.css" />
    <script>
      window.__PM = window.__PM || {};
      window.__PM.csrf = <?= json_encode((string)$csrf) ?>;
      window.__PM.trelloStatus = <?= json_encode($trelloStatus) ?>;
      window.__PM.trelloMetrics = <?= json_encode($trelloMetrics) ?>;
      window.__PM.trelloAuthorizeUrl = <?= json_encode($trelloAuthorizeUrl) ?>;
    </script>
  </head>
  <body class="h-full bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="min-h-screen">
      <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm md:hidden" aria-hidden="true"></div>

      <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-200 bg-white shadow-soft transition-transform md:translate-x-0 md:shadow-none dark:border-slate-800 dark:bg-slate-900" aria-label="Sidebar">
        <div class="flex h-full flex-col">
          <div class="flex items-center gap-3 px-5 py-5">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-950" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M4 18V6a2 2 0 0 1 2-2h3v16H6a2 2 0 0 1-2-2Z" fill="currentColor" opacity="0.9"/>
                <path d="M10 4h4v16h-4V4Z" fill="currentColor" opacity="0.8"/>
                <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3V4Z" fill="currentColor"/>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">Project Metrics Monitor</p>
              <p class="truncate text-xs text-slate-500 dark:text-slate-400">Integraciones</p>
            </div>
          </div>

          <nav class="flex-1 px-3" aria-label="Navegación">
            <a href="/dashboard" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6V11h-6v9Zm0-18v7h6V2h-6Z" fill="currentColor"/>
                </svg>
              </span>
              Dashboard
            </a>

            <div class="mt-2 grid gap-1">
              <?php
              $items = [
                  ['label' => 'Proyectos', 'href' => '/projects', 'glyph' => '<path d="M4 4h7l2 2h7v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M4 6h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.6"/>'],
                  ['label' => 'Analítica', 'href' => '/analytics', 'glyph' => '<path d="M4 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M4 19h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8 16V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 16V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M16 16v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'],
                  ['label' => 'Alertas', 'href' => '/alerts', 'glyph' => '<path d="M12 9v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 17h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M10.3 4.4 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>'],
                  ['label' => 'Power BI', 'href' => '/powerbi', 'glyph' => '<path d="M5 20V9a2 2 0 0 1 2-2h1v13H5Z" fill="currentColor" opacity="0.85"/><path d="M10 20V4h4v16h-4Z" fill="currentColor" opacity="0.7"/><path d="M15 20V6h2a2 2 0 0 1 2 2v12h-4Z" fill="currentColor" opacity="0.9"/>' ],
                  ['label' => 'Configuración', 'href' => '/settings', 'glyph' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a8.2 8.2 0 0 0 .1-2l2-1.5-2-3.5-2.4 1a8 8 0 0 0-1.7-1l-.3-2.6h-4l-.3 2.6a8 8 0 0 0-1.7 1l-2.4-1-2 3.5 2 1.5a8.2 8.2 0 0 0 .1 2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.3 2.6h4l.3-2.6a8 8 0 0 0 1.7-1l2.4 1 2-3.5-2-1.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>' ],
              ];
              foreach ($items as $it): ?>
                <a href="<?= h($it['href']) ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800">
                  <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= $it['glyph'] ?></svg>
                  </span>
                  <?= h($it['label']) ?>
                </a>
              <?php endforeach; ?>
            </div>

            <div class="mt-5 px-3">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Integraciones</p>
            </div>
            <a href="/trello" class="mt-2 flex items-center gap-3 rounded-xl bg-pm-50 px-3 py-2.5 text-sm font-semibold text-pm-800 ring-1 ring-pm-100 dark:bg-pm-500/10 dark:text-pm-200 dark:ring-pm-500/20">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-white text-pm-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-pm-200 dark:ring-slate-800" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('trello') ?></svg>
              </span>
              Trello
            </a>
          </nav>

          <div class="px-5 py-5">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
              <p class="font-semibold text-slate-900 dark:text-white">Recomendación</p>
              <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Usa sincronización automática para KPIs más consistentes.</p>
              <div class="mt-3 flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                Buenas prácticas habilitadas
              </div>
            </div>
          </div>
        </div>
      </aside>

      <div class="md:pl-72">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900/70">
          <div class="mx-auto flex max-w-[1400px] items-center gap-3 px-4 py-3 sm:px-6">
            <button id="sidebarOpen" type="button" class="inline-flex items-center justify-center rounded-xl p-2 text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 md:hidden dark:text-slate-200 dark:ring-slate-800 dark:hover:bg-slate-800" aria-label="Abrir menú">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon('menu') ?></svg>
            </button>

            <div class="relative flex-1">
              <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('search') ?></svg>
              </span>
              <label class="sr-only" for="search">Buscar</label>
              <input id="search" type="search" placeholder="Buscar proyectos, equipos, tableros…" class="w-full rounded-xl border border-slate-200 bg-white px-10 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
            </div>

            <button id="themeToggle" type="button" class="hidden items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 sm:inline-flex dark:text-slate-200 dark:ring-slate-800 dark:hover:bg-slate-800">
              <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><?= icon('moon') ?></svg>
              </span>
              <span id="themeLabel">Dark mode</span>
            </button>

            <button id="notificationsBtn" type="button" class="relative inline-flex items-center justify-center rounded-xl p-2 text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-200 dark:ring-slate-800 dark:hover:bg-slate-800" aria-haspopup="menu" aria-expanded="false" aria-label="Notificaciones">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon('bell') ?></svg>
              <span class="absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">1</span>
            </button>

            <details class="relative group">
              <summary class="inline-flex cursor-pointer list-none items-center gap-3 rounded-xl px-2 py-2 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:ring-slate-800 dark:hover:bg-slate-800" aria-label="Perfil de usuario">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-pm-500 to-sky-500 text-sm font-semibold text-white" aria-hidden="true">MG</span>
                <span class="hidden min-w-0 sm:block">
                  <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">María Gómez</span>
                  <span class="block truncate text-xs text-slate-500 dark:text-slate-400">Gerencia</span>
                </span>
              </summary>
              <div class="absolute right-0 mt-2 hidden w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft group-open:block dark:border-slate-800 dark:bg-slate-900" role="menu" aria-label="Menú de perfil">
                <a href="/settings" class="block px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800" role="menuitem">Configuración</a>
                <div class="border-t border-slate-200 dark:border-slate-800"></div>
                <form method="post" action="/logout">
                  <input type="hidden" name="csrf" value="<?= h((string)$csrf) ?>" />
                  <button type="submit" class="block w-full px-4 py-3 text-left text-sm font-semibold text-rose-600 hover:bg-rose-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:text-rose-300 dark:hover:bg-rose-950/30" role="menuitem">Cerrar sesión</button>
                </form>
              </div>
            </details>
          </div>
        </header>

        <main class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
              <h1 class="truncate text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Integración con Trello</h1>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Autoriza tu cuenta en Trello para sincronizar boards y calcular métricas automáticamente.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <span id="connectionPill" class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" role="status" aria-live="polite">
                <span id="connectionDot" class="h-2.5 w-2.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                <span id="connectionText">No conectado</span>
              </span>
              <button id="connectBtnTop" type="button" <?= $trelloError !== '' ? 'disabled' : '' ?> class="inline-flex items-center gap-2 rounded-xl bg-pm-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-pm-500 dark:hover:bg-pm-400">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10" aria-hidden="true">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('link') ?></svg>
                </span>
                Autorizar con Trello
              </button>
            </div>
          </div>

          <?php if ($trelloError !== ''): ?>
            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">
              <p class="font-semibold">Trello no pudo inicializarse</p>
              <p class="mt-1 text-xs text-amber-800 dark:text-amber-200"><?= h($trelloError) ?></p>
            </div>
          <?php endif; ?>

          <section class="mt-6 grid gap-4 xl:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Estado de conexión</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Información de la cuenta y espacios detectados.</p>
                </div>
                <div class="flex items-center gap-2">
                  <button id="syncNowBtn" type="button" <?= $trelloError !== '' ? 'disabled' : '' ?> class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><?= icon('sync') ?></svg>
                    Sincronizar ahora
                  </button>
                  <button id="disconnectBtn" type="button" <?= $trelloError !== '' ? 'disabled' : '' ?> class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-200 dark:hover:bg-rose-950/50">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><?= icon('power') ?></svg>
                    Desconectar
                  </button>
                </div>
              </div>

              <div id="connectedPanel" class="mt-5 hidden">
                <div class="grid gap-4 sm:grid-cols-2">
                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cuenta</p>
                    <div class="mt-3 flex items-center gap-3">
                      <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-pm-500 to-sky-500 text-sm font-semibold text-white" aria-hidden="true">TG</span>
                      <div class="min-w-0">
                        <p id="accountName" class="truncate text-sm font-semibold text-slate-900 dark:text-white">Trello User</p>
                        <p id="accountEmail" class="truncate text-xs text-slate-500 dark:text-slate-400">user@company.com</p>
                      </div>
                    </div>
                  </div>

                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Última sincronización</p>
                    <p id="lastSync" class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">—</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Incluye boards, listas, cards y actividad.</p>
                  </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                  <div class="flex items-center justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-slate-900 dark:text-white">Espacios de trabajo detectados</p>
                      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Selecciona qué workspaces sincronizar.</p>
                    </div>
                    <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">
                      <span id="workspaceCount">0</span> workspaces
                    </span>
                  </div>

                  <div id="workspaces" class="mt-4 grid gap-2 sm:grid-cols-2"></div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                  <div class="flex items-center justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-slate-900 dark:text-white">Métricas del proyecto</p>
                      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Indicadores calculados desde boards, lists y cards sincronizadas.</p>
                    </div>
                    <span id="syncMeta" class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Sin datos</span>
                  </div>

                  <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total tareas</p>
                      <p id="metricTotalTasks" class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">0</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Completadas</p>
                      <p id="metricCompletedTasks" class="mt-2 text-2xl font-semibold text-emerald-600 dark:text-emerald-300">0</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pendientes</p>
                      <p id="metricPendingTasks" class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">0</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Vencidas</p>
                      <p id="metricOverdueTasks" class="mt-2 text-2xl font-semibold text-rose-600 dark:text-rose-300">0</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Boards activos</p>
                      <p id="metricBoards" class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">0</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Avance</p>
                      <p id="metricProgress" class="mt-2 text-2xl font-semibold text-pm-700 dark:text-pm-300">0%</p>
                    </div>
                  </div>

                  <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                    <div class="bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 dark:bg-slate-950 dark:text-white">Boards con mayor carga</div>
                    <div id="boardMetrics" class="divide-y divide-slate-200 dark:divide-slate-800"></div>
                  </div>

                  <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                    <div class="bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 dark:bg-slate-950 dark:text-white">Logs recientes de sincronización</div>
                    <div id="recentLogs" class="divide-y divide-slate-200 dark:divide-slate-800"></div>
                  </div>
                </div>
              </div>

              <div id="disconnectedPanel" class="mt-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
                  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                      <div class="flex items-center gap-2">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-slate-900 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('trello') ?></svg>
                        </span>
                        <div>
                          <p class="text-sm font-semibold text-slate-900 dark:text-white">No hay una cuenta conectada</p>
                          <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Inicia la autorización oficial en Trello para iniciar la sincronización.</p>
                        </div>
                      </div>
                      <ul class="mt-4 grid gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2">
                          <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-pm-500" aria-hidden="true"></span>
                          Detecta workspaces y boards disponibles
                        </li>
                        <li class="flex items-start gap-2">
                          <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-pm-500" aria-hidden="true"></span>
                          Sincroniza actividad para métricas y alertas
                        </li>
                        <li class="flex items-start gap-2">
                          <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-pm-500" aria-hidden="true"></span>
                          Controla frecuencia y modo de actualización
                        </li>
                      </ul>
                    </div>
                    <button id="connectBtn" type="button" <?= $trelloError !== '' ? 'disabled' : '' ?> class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-pm-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto dark:bg-pm-500 dark:hover:bg-pm-400">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><?= icon('link') ?></svg>
                      Autorizar con Trello
                    </button>
                  </div>

                  <div id="connectCard" class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                      <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Conectar cuenta Trello</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Se abrirá Trello en otra ventana para autorizar acceso de lectura. Al finalizar, la conexión quedará registrada automáticamente.</p>
                      </div>
                      <button id="authorizeTrelloBtn" type="button" <?= ($trelloAuthorizeUrl === '' || $trelloError !== '') ? 'disabled' : '' ?> class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Abrir Trello</button>
                    </div>

                    <form id="connectForm" class="mt-4 grid gap-3" method="post" action="/trello" novalidate>
                      <input type="hidden" name="csrf" value="<?= h((string)$csrf) ?>" />
                      <div class="grid gap-1.5">
                        <label for="trelloToken" class="text-sm font-semibold text-slate-900 dark:text-white">Token de acceso</label>
                        <input id="trelloToken" name="token" type="password" autocomplete="off" spellcheck="false" placeholder="Solo como respaldo manual si la autorización emergente falla" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
                        <p class="text-xs text-slate-500 dark:text-slate-400">Flujo recomendado: usar el botón “Abrir Trello”. Este campo queda solo como alternativa manual.</p>
                      </div>

                      <div class="flex flex-col gap-2 sm:flex-row sm:justify-between">
                        <button id="focusManualConnectBtn" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Usar token manual</button>
                        <button id="connectSubmit" type="button" class="inline-flex items-center justify-center rounded-xl bg-pm-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-pm-500 dark:hover:bg-pm-400">Conectar manualmente</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </article>

            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Flujo funcional</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Solo se muestran acciones operativas disponibles para el monitoreo de métricas.</p>
              </div>

              <div class="mt-5 grid gap-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">1. Conecta Trello</p>
                  <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Genera tu token, valida la cuenta y registra la conexión segura.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">2. Sincroniza</p>
                  <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Trae workspaces, boards, lists y cards para alimentar métricas y alertas.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">3. Monitorea KPIs</p>
                  <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Visualiza tareas totales, completadas, pendientes, vencidas y avance porcentual.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">Uso en dashboards</p>
                  <p id="latestSyncDetail" class="mt-1 text-xs text-slate-600 dark:text-slate-400">Aun no hay sincronizaciones registradas para este usuario.</p>
                </div>
              </div>
            </aside>
          </section>

          <div id="toast" class="pointer-events-none fixed bottom-4 right-4 z-50 hidden w-[92vw] max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft dark:border-slate-800 dark:bg-slate-900" role="status" aria-live="polite">
            <p id="toastTitle" class="text-sm font-semibold text-slate-900 dark:text-white">Listo</p>
            <p id="toastBody" class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Acción completada.</p>
          </div>

          <div id="resultModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="resultModalTitle">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"></div>
            <div class="relative mx-auto flex min-h-full max-w-md items-center px-4 py-10">
              <div class="w-full rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start gap-3">
                  <div id="resultModalIcon" class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <h3 id="resultModalTitle" class="text-base font-semibold text-slate-900 dark:text-white">Operación completada</h3>
                    <p id="resultModalBody" class="mt-1 text-sm text-slate-600 dark:text-slate-300">La acción se ejecutó correctamente.</p>
                  </div>
                </div>
                <div class="mt-5 flex justify-end">
                  <button id="resultModalClose" type="button" class="inline-flex items-center justify-center rounded-xl bg-pm-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-pm-500 dark:hover:bg-pm-400">Entendido</button>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>

    <script type="module" src="/assets/js/trello.js"></script>
  </body>
</html>
