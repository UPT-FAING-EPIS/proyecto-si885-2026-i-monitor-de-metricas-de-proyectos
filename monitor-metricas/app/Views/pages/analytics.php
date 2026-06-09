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
        'chart' => '<path d="M4 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M4 19h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8 16V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 16V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M16 16v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'download' => '<path d="M12 3v10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'folder' => '<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M3 9h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.6"/>',
        'filter' => '<path d="M4 5h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 12h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 19h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    ];
    return $icons[$name] ?? '';
}

$payload = $payload ?? [
    'projects' => [
        ['id' => 'b1', 'name' => 'Customer Portal'],
        ['id' => 'b2', 'name' => 'Release 2.4'],
        ['id' => 'b3', 'name' => 'Q3 Roadmap'],
        ['id' => 'b4', 'name' => 'QA Automation'],
    ],
    'teams' => [
        ['id' => 'pmo', 'name' => 'PMO'],
        ['id' => 'product', 'name' => 'Producto'],
        ['id' => 'backend', 'name' => 'Backend'],
        ['id' => 'frontend', 'name' => 'Frontend'],
        ['id' => 'qa', 'name' => 'QA'],
        ['id' => 'data', 'name' => 'Data'],
    ],
    'summary' => [
        'projectCount' => 4,
        'teamCount' => 6,
        'totalTasks' => 0,
        'completedTasks' => 0,
        'pendingTasks' => 0,
        'overdueTasks' => 0,
        'progress' => 0,
        'topProject' => 'Customer Portal',
        'topProjectProgress' => 86,
        'topTeam' => 'Producto',
        'topTeamProgress' => 78,
    ],
];
$summary = isset($payload['summary']) && is_array($payload['summary']) ? $payload['summary'] : [
    'projectCount' => 0,
    'teamCount' => 0,
    'totalTasks' => 0,
    'completedTasks' => 0,
    'pendingTasks' => 0,
    'overdueTasks' => 0,
    'progress' => 0,
    'topProject' => '',
    'topProjectProgress' => 0,
    'topTeam' => '',
    'topTeamProgress' => 0,
];
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Analítica avanzada · Project Metrics Monitor</title>
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
    <script id="pmAnalyticsData" type="application/json"><?= (string)json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  </head>
    <?php require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'app_shell.php'; ?>
  <?php pm_render_app_shell_start([
      'title' => 'Analítica',
      'active' => 'analytics',
      'search_placeholder' => 'Buscar métricas o proyectos...',
  ]); ?>

        <main class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
              <h1 class="truncate text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Analítica avanzada</h1>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Tendencias históricas, comparativas y métricas de flujo.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <span id="summaryPill" class="rounded-full bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" role="status" aria-live="polite">—</span>
              <label class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">
                <input id="compareToggle" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" />
                Comparar
              </label>
            </div>
          </div>

          <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Proyectos analizados</p>
              <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white"><?= h((string)($summary['projectCount'] ?? 0)) ?></p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= h((string)($summary['teamCount'] ?? 0)) ?> equipos detectados</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Avance global</p>
              <p class="mt-2 text-2xl font-semibold text-pm-700 dark:text-pm-300"><?= h((string)($summary['progress'] ?? 0)) ?>%</p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= h((string)($summary['completedTasks'] ?? 0)) ?> completadas</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tareas activas</p>
              <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white"><?= h((string)($summary['pendingTasks'] ?? 0)) ?></p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">de <?= h((string)($summary['totalTasks'] ?? 0)) ?> tareas</p>
            </article>
            <article class="rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-soft dark:border-rose-900/50 dark:bg-rose-950/30">
              <p class="text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-200">Tareas vencidas</p>
              <p class="mt-2 text-2xl font-semibold text-rose-800 dark:text-rose-100"><?= h((string)($summary['overdueTasks'] ?? 0)) ?></p>
              <p class="mt-1 text-xs text-rose-700/80 dark:text-rose-200/80">Monitoreadas desde Trello</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Mejor rendimiento</p>
              <p class="mt-2 truncate text-sm font-semibold text-slate-900 dark:text-white"><?= h((string)($summary['topProject'] ?? '')) ?></p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Proyecto: <?= h((string)($summary['topProjectProgress'] ?? 0)) ?>%</p>
              <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">Equipo: <?= h((string)($summary['topTeam'] ?? '')) ?> (<?= h((string)($summary['topTeamProgress'] ?? 0)) ?>%)</p>
            </article>
          </section>

          <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('filter') ?></svg>
                </span>
                <div>
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">Filtros</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Proyecto, fecha y equipo</p>
                </div>
              </div>
              <button id="resetFilters" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                Restablecer
              </button>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-[260px_1fr_220px_240px] lg:items-end">
              <div class="grid gap-1.5">
                <label for="projectSelect" class="text-sm font-semibold text-slate-900 dark:text-white">Proyecto</label>
                <select id="projectSelect" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15"></select>
              </div>

              <div class="grid gap-2 sm:grid-cols-2">
                <div class="grid gap-1.5">
                  <label for="dateFrom" class="text-sm font-semibold text-slate-900 dark:text-white">Fecha (desde)</label>
                  <input id="dateFrom" type="date" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
                </div>
                <div class="grid gap-1.5">
                  <label for="dateTo" class="text-sm font-semibold text-slate-900 dark:text-white">Fecha (hasta)</label>
                  <input id="dateTo" type="date" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
                </div>
              </div>

              <div class="grid gap-1.5">
                <label for="teamSelect" class="text-sm font-semibold text-slate-900 dark:text-white">Equipo</label>
                <select id="teamSelect" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15"></select>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
                <p class="font-semibold text-slate-900 dark:text-white">Comparativa</p>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Superpone el periodo anterior con línea punteada.</p>
              </div>
            </div>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Burn Down</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Trabajo restante vs. tiempo</p>
                </div>
                <div class="flex items-center gap-2">
                  <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Restante</span>
                </div>
              </div>
              <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-b from-pm-50 to-white dark:border-slate-800 dark:from-pm-500/10 dark:to-slate-900">
                <div class="p-4">
                  <svg id="chartBurnDown" class="h-56 w-full" viewBox="0 0 600 240" preserveAspectRatio="none" aria-label="Gráfico Burn Down"></svg>
                </div>
              </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Burn Up</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Completado vs. alcance</p>
                </div>
                <div class="flex items-center gap-2">
                  <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Completado</span>
                </div>
              </div>
              <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-b from-sky-50 to-white dark:border-slate-800 dark:from-sky-500/10 dark:to-slate-900">
                <div class="p-4">
                  <svg id="chartBurnUp" class="h-56 w-full" viewBox="0 0 600 240" preserveAspectRatio="none" aria-label="Gráfico Burn Up"></svg>
                </div>
              </div>
            </article>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Productividad</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Entrega por periodo (tareas)</p>
                </div>
                <span id="productivityBadge" class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20">—</span>
              </div>
              <div class="mt-4">
                <svg id="chartProductivity" class="h-56 w-full" viewBox="0 0 600 240" preserveAspectRatio="none" aria-label="Gráfico de Productividad"></svg>
              </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Velocidad</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Puntos por sprint (comparativo)</p>
                </div>
                <span id="velocityBadge" class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">—</span>
              </div>
              <div class="mt-4">
                <svg id="chartVelocity" class="h-56 w-full" viewBox="0 0 600 240" preserveAspectRatio="none" aria-label="Gráfico de Velocidad"></svg>
              </div>
            </article>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Lead Time</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tiempo desde creación hasta completado (días)</p>
                </div>
                <span id="leadBadge" class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">—</span>
              </div>
              <div class="mt-4">
                <svg id="chartLeadTime" class="h-56 w-full" viewBox="0 0 600 240" preserveAspectRatio="none" aria-label="Gráfico Lead Time"></svg>
              </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Cycle Time</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tiempo en progreso hasta completado (días)</p>
                </div>
                <span id="cycleBadge" class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">—</span>
              </div>
              <div class="mt-4">
                <svg id="chartCycleTime" class="h-56 w-full" viewBox="0 0 600 240" preserveAspectRatio="none" aria-label="Gráfico Cycle Time"></svg>
              </div>
            </article>
          </section>

          <div id="toast" class="pointer-events-none fixed bottom-4 right-4 z-50 hidden w-[92vw] max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft dark:border-slate-800 dark:bg-slate-900" role="status" aria-live="polite">
            <p id="toastTitle" class="text-sm font-semibold text-slate-900 dark:text-white">Listo</p>
            <p id="toastBody" class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Acción completada.</p>
          </div>
        </main>
  <?php pm_render_app_shell_end(); ?>

    <script type="module" src="/assets/js/analytics.js"></script>
  </body>
</html>
