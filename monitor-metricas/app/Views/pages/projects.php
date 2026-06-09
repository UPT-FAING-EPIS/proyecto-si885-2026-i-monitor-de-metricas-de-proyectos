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
        'folder' => '<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M3 9h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.6"/>',
        'chart' => '<path d="M4 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M4 19h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8 16V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 16V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M16 16v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'powerbi' => '<path d="M5 20V9a2 2 0 0 1 2-2h1v13H5Z" fill="currentColor" opacity="0.85"/><path d="M10 20V4h4v16h-4Z" fill="currentColor" opacity="0.7"/><path d="M15 20V6h2a2 2 0 0 1 2 2v12h-4Z" fill="currentColor" opacity="0.9"/>',
        'external' => '<path d="M14 3h7v7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 14 21 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M21 14v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'calendar' => '<path d="M8 3v2M16 3v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 9h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M5 5h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
    ];
    return $icons[$name] ?? '';
}

$projects = $projects ?? [
    [
        'id' => 'b1',
        'name' => 'Customer Portal',
        'status' => 'En curso',
        'owner' => 'María G.',
        'lastSync' => '2026-06-06T15:42:00Z',
        'progress' => 86,
        'tasksTotal' => 210,
        'tasksDone' => 178,
        'tasksOverdue' => 6,
        'members' => [
            ['name' => 'María Gómez', 'initials' => 'MG'],
            ['name' => 'Carlos Ruiz', 'initials' => 'CR'],
            ['name' => 'Ana Torres', 'initials' => 'AT'],
            ['name' => 'Diego P.', 'initials' => 'DP'],
        ],
    ],
    [
        'id' => 'b2',
        'name' => 'Release 2.4',
        'status' => 'Riesgo',
        'owner' => 'Equipo Backend',
        'lastSync' => '2026-06-06T14:20:00Z',
        'progress' => 63,
        'tasksTotal' => 145,
        'tasksDone' => 92,
        'tasksOverdue' => 21,
        'members' => [
            ['name' => 'Jorge L.', 'initials' => 'JL'],
            ['name' => 'Sofía M.', 'initials' => 'SM'],
            ['name' => 'Pablo N.', 'initials' => 'PN'],
        ],
    ],
    [
        'id' => 'b3',
        'name' => 'Q3 Roadmap',
        'status' => 'En curso',
        'owner' => 'Equipo Producto',
        'lastSync' => '2026-06-06T15:56:00Z',
        'progress' => 81,
        'tasksTotal' => 98,
        'tasksDone' => 71,
        'tasksOverdue' => 3,
        'members' => [
            ['name' => 'Lucía R.', 'initials' => 'LR'],
            ['name' => 'María Gómez', 'initials' => 'MG'],
            ['name' => 'Tomás V.', 'initials' => 'TV'],
            ['name' => 'Iván S.', 'initials' => 'IS'],
            ['name' => 'Elena K.', 'initials' => 'EK'],
        ],
    ],
    [
        'id' => 'b4',
        'name' => 'QA Automation',
        'status' => 'En espera',
        'owner' => 'Equipo QA',
        'lastSync' => '2026-06-05T18:12:00Z',
        'progress' => 44,
        'tasksTotal' => 132,
        'tasksDone' => 58,
        'tasksOverdue' => 5,
        'members' => [
            ['name' => 'Nadia F.', 'initials' => 'NF'],
            ['name' => 'Bruno C.', 'initials' => 'BC'],
        ],
    ],
    [
        'id' => 'b5',
        'name' => 'FinOps Tracker',
        'status' => 'En curso',
        'owner' => 'Carlos R.',
        'lastSync' => '2026-06-06T13:05:00Z',
        'progress' => 76,
        'tasksTotal' => 64,
        'tasksDone' => 49,
        'tasksOverdue' => 2,
        'members' => [
            ['name' => 'Carlos Ruiz', 'initials' => 'CR'],
            ['name' => 'Valeria P.', 'initials' => 'VP'],
            ['name' => 'Matías H.', 'initials' => 'MH'],
        ],
    ],
    [
        'id' => 'b6',
        'name' => 'Onboarding',
        'status' => 'Riesgo',
        'owner' => 'PMO',
        'lastSync' => '2026-06-06T10:10:00Z',
        'progress' => 52,
        'tasksTotal' => 88,
        'tasksDone' => 46,
        'tasksOverdue' => 12,
        'members' => [
            ['name' => 'María Gómez', 'initials' => 'MG'],
            ['name' => 'Sofía M.', 'initials' => 'SM'],
            ['name' => 'Hugo T.', 'initials' => 'HT'],
        ],
    ],
    [
        'id' => 'b7',
        'name' => 'Infra Upgrade',
        'status' => 'En curso',
        'owner' => 'Plataforma',
        'lastSync' => '2026-06-04T20:45:00Z',
        'progress' => 69,
        'tasksTotal' => 120,
        'tasksDone' => 82,
        'tasksOverdue' => 9,
        'members' => [
            ['name' => 'Diego P.', 'initials' => 'DP'],
            ['name' => 'Pablo N.', 'initials' => 'PN'],
            ['name' => 'Laura Z.', 'initials' => 'LZ'],
        ],
    ],
    [
        'id' => 'b8',
        'name' => 'Data Quality',
        'status' => 'Completado',
        'owner' => 'Equipo Data',
        'lastSync' => '2026-06-03T12:30:00Z',
        'progress' => 100,
        'tasksTotal' => 74,
        'tasksDone' => 74,
        'tasksOverdue' => 0,
        'members' => [
            ['name' => 'Elena K.', 'initials' => 'EK'],
            ['name' => 'Matías H.', 'initials' => 'MH'],
            ['name' => 'Ana Torres', 'initials' => 'AT'],
        ],
    ],
    [
        'id' => 'b9',
        'name' => 'Security Hardening',
        'status' => 'En curso',
        'owner' => 'Seguridad',
        'lastSync' => '2026-06-06T09:10:00Z',
        'progress' => 58,
        'tasksTotal' => 96,
        'tasksDone' => 55,
        'tasksOverdue' => 4,
        'members' => [
            ['name' => 'Jorge L.', 'initials' => 'JL'],
            ['name' => 'Laura Z.', 'initials' => 'LZ'],
        ],
    ],
    [
        'id' => 'b10',
        'name' => 'Executive Reporting',
        'status' => 'En espera',
        'owner' => 'Gerencia',
        'lastSync' => '2026-06-02T16:00:00Z',
        'progress' => 31,
        'tasksTotal' => 54,
        'tasksDone' => 17,
        'tasksOverdue' => 1,
        'members' => [
            ['name' => 'María Gómez', 'initials' => 'MG'],
            ['name' => 'Valeria P.', 'initials' => 'VP'],
        ],
    ],
    [
        'id' => 'b11',
        'name' => 'Customer Success Ops',
        'status' => 'En curso',
        'owner' => 'CS',
        'lastSync' => '2026-06-01T11:05:00Z',
        'progress' => 73,
        'tasksTotal' => 66,
        'tasksDone' => 48,
        'tasksOverdue' => 3,
        'members' => [
            ['name' => 'Sofía M.', 'initials' => 'SM'],
            ['name' => 'Bruno C.', 'initials' => 'BC'],
            ['name' => 'Tomás V.', 'initials' => 'TV'],
        ],
    ],
    [
        'id' => 'b12',
        'name' => 'Mobile App MVP',
        'status' => 'Riesgo',
        'owner' => 'Frontend',
        'lastSync' => '2026-05-31T19:40:00Z',
        'progress' => 47,
        'tasksTotal' => 180,
        'tasksDone' => 92,
        'tasksOverdue' => 14,
        'members' => [
            ['name' => 'Ana Torres', 'initials' => 'AT'],
            ['name' => 'Diego P.', 'initials' => 'DP'],
            ['name' => 'Iván S.', 'initials' => 'IS'],
            ['name' => 'Nadia F.', 'initials' => 'NF'],
        ],
    ],
];

$summary = $summary ?? [
    'projects' => count($projects),
    'workspaces' => 0,
    'tasksTotal' => array_sum(array_map(static fn (array $project): int => (int)($project['tasksTotal'] ?? 0), $projects)),
    'tasksDone' => array_sum(array_map(static fn (array $project): int => (int)($project['tasksDone'] ?? 0), $projects)),
    'tasksPending' => max(0, array_sum(array_map(static fn (array $project): int => (int)($project['tasksTotal'] ?? 0), $projects)) - array_sum(array_map(static fn (array $project): int => (int)($project['tasksDone'] ?? 0), $projects))),
    'tasksOverdue' => array_sum(array_map(static fn (array $project): int => (int)($project['tasksOverdue'] ?? 0), $projects)),
    'progress' => count($projects) > 0 ? (int)round(array_sum(array_map(static fn (array $project): int => (int)($project['progress'] ?? 0), $projects)) / count($projects)) : 0,
    'lastSync' => $projects[0]['lastSync'] ?? '',
];
$statusSummary = $statusSummary ?? [
    'enCurso' => count(array_filter($projects, static fn (array $project): bool => (string)($project['status'] ?? '') === 'En curso')),
    'riesgo' => count(array_filter($projects, static fn (array $project): bool => (string)($project['status'] ?? '') === 'Riesgo')),
    'completado' => count(array_filter($projects, static fn (array $project): bool => (string)($project['status'] ?? '') === 'Completado')),
    'espera' => count(array_filter($projects, static fn (array $project): bool => (string)($project['status'] ?? '') === 'En espera')),
];

$payload = [
    'projects' => $projects,
];
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Proyectos · Project Metrics Monitor</title>
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
    <script id="pmProjectsData" type="application/json"><?= (string)json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  </head>
    <?php require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'app_shell.php'; ?>
  <?php pm_render_app_shell_start([
      'title' => 'Proyectos',
      'active' => 'projects',
      'search_placeholder' => 'Buscar proyectos sincronizados...',
  ]); ?>

        <main class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
              <h1 class="truncate text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Proyectos</h1>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Boards sincronizados desde Trello con métricas clave y accesos directos.</p>
            </div>
            <div class="flex items-center gap-2">
              <span id="resultCount" class="rounded-full bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" role="status" aria-live="polite">0 resultados</span>
            </div>
          </div>

          <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Proyectos sincronizados</p>
              <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white"><?= h((string)($summary['projects'] ?? 0)) ?></p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= h((string)($summary['workspaces'] ?? 0)) ?> workspaces detectados</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Avance global</p>
              <p class="mt-2 text-2xl font-semibold text-pm-700 dark:text-pm-300"><?= h((string)($summary['progress'] ?? 0)) ?>%</p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= h((string)($summary['tasksDone'] ?? 0)) ?> tareas completadas</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pendientes</p>
              <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white"><?= h((string)($summary['tasksPending'] ?? 0)) ?></p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">de <?= h((string)($summary['tasksTotal'] ?? 0)) ?> tareas totales</p>
            </article>
            <article class="rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-soft dark:border-rose-900/50 dark:bg-rose-950/30">
              <p class="text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-200">Vencidas</p>
              <p class="mt-2 text-2xl font-semibold text-rose-800 dark:text-rose-100"><?= h((string)($summary['tasksOverdue'] ?? 0)) ?></p>
              <p class="mt-1 text-xs text-rose-700/80 dark:text-rose-200/80">En riesgo: <?= h((string)($statusSummary['riesgo'] ?? 0)) ?> proyectos</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Estado actual</p>
              <div class="mt-2 grid gap-1 text-sm text-slate-700 dark:text-slate-200">
                <p>En curso: <?= h((string)($statusSummary['enCurso'] ?? 0)) ?></p>
                <p>Completados: <?= h((string)($statusSummary['completado'] ?? 0)) ?></p>
                <p>En espera: <?= h((string)($statusSummary['espera'] ?? 0)) ?></p>
              </div>
            </article>
          </section>

          <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
            <div class="grid gap-3 lg:grid-cols-[1fr_190px_220px_1fr_auto] lg:items-end">
              <div class="grid gap-1.5">
                <label for="search" class="text-sm font-semibold text-slate-900 dark:text-white">Buscador</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('search') ?></svg>
                  </span>
                  <input id="search" type="search" placeholder="Nombre del board…" class="w-full rounded-xl border border-slate-200 bg-white px-10 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
                </div>
              </div>

              <div class="grid gap-1.5">
                <label for="status" class="text-sm font-semibold text-slate-900 dark:text-white">Estado</label>
                <select id="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15">
                  <option value="">Todos</option>
                  <option value="En curso">En curso</option>
                  <option value="En espera">En espera</option>
                  <option value="Riesgo">Riesgo</option>
                  <option value="Completado">Completado</option>
                </select>
              </div>

              <div class="grid gap-1.5">
                <label for="owner" class="text-sm font-semibold text-slate-900 dark:text-white">Responsable</label>
                <select id="owner" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15">
                  <option value="">Todos</option>
                </select>
              </div>

              <div class="grid gap-2 sm:grid-cols-2">
                <div class="grid gap-1.5">
                  <label for="dateFrom" class="text-sm font-semibold text-slate-900 dark:text-white">Fecha (desde)</label>
                  <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('calendar') ?></svg>
                    </span>
                    <input id="dateFrom" type="date" class="w-full rounded-xl border border-slate-200 bg-white px-10 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
                  </div>
                </div>
                <div class="grid gap-1.5">
                  <label for="dateTo" class="text-sm font-semibold text-slate-900 dark:text-white">Fecha (hasta)</label>
                  <input id="dateTo" type="date" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
                </div>
              </div>

              <div class="flex gap-2 lg:justify-end">
                <button id="clearFilters" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                  Limpiar
                </button>
              </div>
            </div>
          </section>

          <section class="mt-6">
            <div id="cards" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>
          </section>

          <nav class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft dark:border-slate-800 dark:bg-slate-900" aria-label="Paginación">
            <p id="paginationLabel" class="text-sm text-slate-600 dark:text-slate-400">Mostrando 0–0 de 0</p>
            <div class="flex flex-wrap items-center gap-2">
              <button id="prevPage" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Anterior</button>
              <div id="pageButtons" class="flex items-center gap-1"></div>
              <button id="nextPage" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Siguiente</button>
            </div>
          </nav>

          <div id="toast" class="pointer-events-none fixed bottom-4 right-4 z-50 hidden w-[92vw] max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft dark:border-slate-800 dark:bg-slate-900" role="status" aria-live="polite">
            <p id="toastTitle" class="text-sm font-semibold text-slate-900 dark:text-white">Listo</p>
            <p id="toastBody" class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Acción completada.</p>
          </div>
        </main>
  <?php pm_render_app_shell_end(); ?>

    <script type="module" src="/assets/js/projects.js"></script>
  </body>
</html>
