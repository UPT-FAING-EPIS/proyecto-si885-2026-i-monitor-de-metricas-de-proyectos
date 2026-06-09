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
        'sync' => '<path d="M21 12a9 9 0 0 0-15.4-6.4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 4v6h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 12a9 9 0 0 0 15.4 6.4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M21 20v-6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'alert' => '<path d="M12 9v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 17h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M10.3 4.4 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'check' => '<path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'open' => '<path d="M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 22C6.5 22 2 17.5 2 12S6.5 2 12 2s10 4.5 10 10-4.5 10-10 10Z" stroke="currentColor" stroke-width="2"/>',
        'calendar' => '<path d="M8 3v2M16 3v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 9h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M5 5h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'message' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M7 8h10M7 12h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'move' => '<path d="M12 3v18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 3l3 3M12 3 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 21l3-3M12 21 9 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'edit' => '<path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
    ];
    return $icons[$name] ?? '';
}

$projects = $projects ?? [
    [
        'id' => 'b1',
        'name' => 'Customer Portal',
        'status' => 'En curso',
        'lastSync' => '2026-06-06T15:42:00Z',
        'progress' => 86,
        'tasksTotal' => 210,
        'tasksDone' => 178,
        'tasksOverdue' => 6,
        'members' => [
            ['name' => 'María Gómez', 'initials' => 'MG', 'assigned' => 22],
            ['name' => 'Carlos Ruiz', 'initials' => 'CR', 'assigned' => 18],
            ['name' => 'Ana Torres', 'initials' => 'AT', 'assigned' => 15],
            ['name' => 'Diego Pérez', 'initials' => 'DP', 'assigned' => 12],
            ['name' => 'Elena K.', 'initials' => 'EK', 'assigned' => 9],
        ],
    ],
    [
        'id' => 'b2',
        'name' => 'Release 2.4',
        'status' => 'Riesgo',
        'lastSync' => '2026-06-06T14:20:00Z',
        'progress' => 63,
        'tasksTotal' => 145,
        'tasksDone' => 92,
        'tasksOverdue' => 21,
        'members' => [
            ['name' => 'Jorge L.', 'initials' => 'JL', 'assigned' => 20],
            ['name' => 'Sofía M.', 'initials' => 'SM', 'assigned' => 16],
            ['name' => 'Pablo N.', 'initials' => 'PN', 'assigned' => 13],
            ['name' => 'Laura Z.', 'initials' => 'LZ', 'assigned' => 11],
        ],
    ],
    [
        'id' => 'b3',
        'name' => 'Q3 Roadmap',
        'status' => 'En curso',
        'lastSync' => '2026-06-06T15:56:00Z',
        'progress' => 81,
        'tasksTotal' => 98,
        'tasksDone' => 71,
        'tasksOverdue' => 3,
        'members' => [
            ['name' => 'Lucía R.', 'initials' => 'LR', 'assigned' => 12],
            ['name' => 'María Gómez', 'initials' => 'MG', 'assigned' => 9],
            ['name' => 'Tomás V.', 'initials' => 'TV', 'assigned' => 8],
            ['name' => 'Iván S.', 'initials' => 'IS', 'assigned' => 7],
        ],
    ],
];

$id = isset($projectId) && (string)$projectId !== '' ? (string)$projectId : (isset($_GET['id']) ? (string)$_GET['id'] : 'b1');
$project = null;
foreach ($projects as $p) {
    if ($p['id'] === $id) {
        $project = $p;
        break;
    }
}
if ($project === null) {
    $project = $projects[0];
}

$tasksOpen = max(0, (int)$project['tasksTotal'] - (int)$project['tasksDone']);
$risks = 0;
if ((int)$project['tasksOverdue'] >= 10) $risks++;
if ((int)$project['progress'] <= 55) $risks++;
if ($project['status'] === 'Riesgo') $risks++;

$progressSeries = array_values(array_map(static fn ($v) => max(10, min(100, $v)), [
    (int)$project['progress'] - 22,
    (int)$project['progress'] - 18,
    (int)$project['progress'] - 14,
    (int)$project['progress'] - 12,
    (int)$project['progress'] - 9,
    (int)$project['progress'] - 6,
    (int)$project['progress'] - 4,
    (int)$project['progress'] - 3,
    (int)$project['progress'] - 2,
    (int)$project['progress'] - 1,
    (int)$project['progress'],
]));

$statusDistribution = [
    ['label' => 'To Do', 'value' => 18, 'tone' => 'slate'],
    ['label' => 'In Progress', 'value' => 42, 'tone' => 'pm'],
    ['label' => 'Blocked', 'value' => 7, 'tone' => 'rose'],
    ['label' => 'Done', 'value' => 33, 'tone' => 'emerald'],
];

$activity = $activity ?? [
    ['type' => 'comment', 'title' => 'Comentario en “Login UX”', 'meta' => 'hace 12 min · Ana Torres', 'detail' => '“Validación de correo: agregar feedback inline y estado.”'],
    ['type' => 'move', 'title' => 'Movimiento de card', 'meta' => 'hace 38 min · Carlos Ruiz', 'detail' => '“API Rate Limit” pasó de In Progress → Blocked.'],
    ['type' => 'change', 'title' => 'Cambio en checklist', 'meta' => 'hace 2 h · María Gómez', 'detail' => '“Release Notes” completado (8/8).'],
    ['type' => 'comment', 'title' => 'Comentario en “Dashboard KPI”', 'meta' => 'hace 5 h · Diego Pérez', 'detail' => '“Alinear nomenclatura con Power BI: ‘Riesgos Detectados’.”'],
];

$statusBadge = match ($project['status']) {
    'Riesgo' => 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20',
    'Completado' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20',
    'En espera' => 'bg-amber-50 text-amber-800 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
    default => 'bg-pm-50 text-pm-800 ring-pm-100 dark:bg-pm-500/10 dark:text-pm-200 dark:ring-pm-500/20',
};

$payload = [
    'project' => [
        'id' => $project['id'],
        'name' => $project['name'],
        'status' => $project['status'],
        'lastSync' => $project['lastSync'],
    ],
    'charts' => [
        'progressSeries' => $progressSeries,
        'members' => $project['members'],
        'statusDistribution' => $statusDistribution,
    ],
];
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Detalle de Proyecto · <?= h((string)$project['name']) ?></title>
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
    <script id="pmProjectData" type="application/json"><?= h((string)json_encode($payload, JSON_UNESCAPED_UNICODE)) ?></script>
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
              <p class="truncate text-xs text-slate-500 dark:text-slate-400">Detalle de proyecto</p>
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

            <a href="/projects" class="mt-2 flex items-center gap-3 rounded-xl bg-pm-50 px-3 py-2.5 text-sm font-semibold text-pm-800 ring-1 ring-pm-100 dark:bg-pm-500/10 dark:text-pm-200 dark:ring-pm-500/20">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-white text-pm-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-pm-200 dark:ring-slate-800" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('folder') ?></svg>
              </span>
              Proyectos
            </a>

            <div class="mt-2 grid gap-1">
              <a href="/analytics" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" aria-hidden="true">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('chart') ?></svg>
                </span>
                Analítica
              </a>
              <a href="/powerbi" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" aria-hidden="true">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('powerbi') ?></svg>
                </span>
                Power BI
              </a>
            </div>
          </nav>

          <div class="px-5 py-5">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
              <p class="font-semibold text-slate-900 dark:text-white">Contexto</p>
              <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">La vista consolida métricas del board, productividad por miembro y eventos recientes.</p>
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
              <input id="search" type="search" placeholder="Buscar en este proyecto…" class="w-full rounded-xl border border-slate-200 bg-white px-10 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
            </div>

            <button id="themeToggle" type="button" class="hidden items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 sm:inline-flex dark:text-slate-200 dark:ring-slate-800 dark:hover:bg-slate-800">
              <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><?= icon('moon') ?></svg>
              </span>
              <span id="themeLabel">Dark mode</span>
            </button>

            <button type="button" class="relative inline-flex items-center justify-center rounded-xl p-2 text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-200 dark:ring-slate-800 dark:hover:bg-slate-800" aria-label="Notificaciones">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon('bell') ?></svg>
              <span class="absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">1</span>
            </button>

            <div class="inline-flex items-center gap-3 rounded-xl px-2 py-2 ring-1 ring-slate-200 dark:ring-slate-800">
              <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-pm-500 to-sky-500 text-sm font-semibold text-white" aria-hidden="true">MG</span>
              <span class="hidden min-w-0 sm:block">
                <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">María Gómez</span>
                <span class="block truncate text-xs text-slate-500 dark:text-slate-400">Gerencia</span>
              </span>
            </div>
          </div>
        </header>

        <main class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6">
          <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h1 class="truncate text-xl font-semibold tracking-tight text-slate-900 dark:text-white"><?= h((string)$project['name']) ?></h1>
                  <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 <?= h($statusBadge) ?>"><?= h((string)$project['status']) ?></span>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                  <span class="inline-flex items-center gap-2">
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><?= icon('calendar') ?></svg>
                    </span>
                    <span>Sincronización: <span id="lastSyncLabel" class="font-semibold text-slate-900 dark:text-white">—</span></span>
                  </span>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <button id="syncNow" type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><?= icon('sync') ?></svg>
                  Sincronizar
                </button>
                <button id="exportBtn" type="button" class="inline-flex items-center gap-2 rounded-xl bg-pm-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-pm-500 dark:hover:bg-pm-400">
                  Exportar
                </button>
              </div>
            </div>
          </section>

          <section class="mt-6 grid gap-4 lg:grid-cols-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Avance</p>
                  <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?= (int)$project['progress'] ?>%</p>
                </div>
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon('chart') ?></svg>
                </span>
              </div>
              <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800" aria-hidden="true">
                <div class="h-full rounded-full bg-gradient-to-r from-pm-500 to-sky-500" style="width: <?= (int)$project['progress'] ?>%"></div>
              </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Tareas abiertas</p>
                  <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?= $tasksOpen ?></p>
                </div>
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon('open') ?></svg>
                </span>
              </div>
              <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">De <?= (int)$project['tasksTotal'] ?> tareas totales</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Tareas cerradas</p>
                  <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?= (int)$project['tasksDone'] ?></p>
                </div>
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon('check') ?></svg>
                </span>
              </div>
              <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Cierre acumulado (periodo actual)</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Tareas vencidas</p>
                  <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?= (int)$project['tasksOverdue'] ?></p>
                </div>
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-rose-50 text-rose-800 ring-1 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon('alert') ?></svg>
                </span>
              </div>
              <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Impacta riesgo y cumplimiento</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Riesgos</p>
                  <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?= $risks ?></p>
                </div>
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-amber-50 text-amber-900 ring-1 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon('alert') ?></svg>
                </span>
              </div>
              <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Señales detectadas automáticamente</p>
            </article>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Avance temporal</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Evolución del progreso (últimos periodos)</p>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Tendencia</span>
              </div>
              <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-b from-pm-50 to-white dark:border-slate-800 dark:from-pm-500/10 dark:to-slate-900">
                <div class="p-4">
                  <svg id="chartProgress" class="h-52 w-full" viewBox="0 0 600 220" preserveAspectRatio="none" aria-label="Gráfico de avance temporal"></svg>
                </div>
              </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Productividad por miembro</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tareas asignadas (proxy de carga)</p>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Miembros</span>
              </div>
              <div class="mt-4">
                <svg id="chartMembers" class="h-52 w-full" viewBox="0 0 600 220" preserveAspectRatio="none" aria-label="Gráfico de productividad por miembro"></svg>
              </div>
            </article>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Distribución de estados</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mix actual del board</p>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">100%</span>
              </div>
              <div class="mt-5 grid gap-4 sm:grid-cols-[220px_1fr] sm:items-center">
                <div class="mx-auto">
                  <svg id="chartStatus" class="h-52 w-52" viewBox="0 0 120 120" aria-label="Distribución de estados"></svg>
                </div>
                <div id="statusLegend" class="grid gap-2"></div>
              </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Miembros</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Resumen por persona (asignadas)</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('users') ?></svg>
                </span>
              </div>
              <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                <div class="grid divide-y divide-slate-200 dark:divide-slate-800">
                  <?php foreach ($project['members'] as $m): ?>
                    <div class="flex items-center justify-between gap-3 bg-white p-4 dark:bg-slate-900">
                      <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-pm-500 to-sky-500 text-sm font-semibold text-white" aria-hidden="true"><?= h((string)$m['initials']) ?></span>
                        <div class="min-w-0">
                          <p class="truncate text-sm font-semibold text-slate-900 dark:text-white"><?= h((string)$m['name']) ?></p>
                          <p class="truncate text-xs text-slate-500 dark:text-slate-400">Rol: contributor</p>
                        </div>
                      </div>
                      <div class="text-right">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Asignadas</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white"><?= (int)$m['assigned'] ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          </section>

          <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Actividad reciente</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Comentarios, cambios y movimientos del board</p>
              </div>
              <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800"><?= count($activity) ?> eventos</span>
            </div>

            <div class="mt-4 grid gap-2">
              <?php foreach ($activity as $a):
                $typeBadge = match ($a['type']) {
                    'comment' => 'bg-pm-50 text-pm-800 ring-pm-100 dark:bg-pm-500/10 dark:text-pm-200 dark:ring-pm-500/20',
                    'move' => 'bg-amber-50 text-amber-800 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
                    'change' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20',
                    default => 'bg-slate-50 text-slate-700 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800',
                };
                $typeIcon = match ($a['type']) {
                    'comment' => icon('message'),
                    'move' => icon('move'),
                    'change' => icon('edit'),
                    default => icon('edit'),
                };
              ?>
                <div class="flex items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800">
                  <div class="flex min-w-0 gap-3">
                    <span class="mt-0.5 grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= $typeIcon ?></svg>
                    </span>
                    <div class="min-w-0">
                      <p class="truncate text-sm font-semibold text-slate-900 dark:text-white"><?= h((string)$a['title']) ?></p>
                      <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400"><?= h((string)$a['meta']) ?></p>
                      <p class="mt-2 text-sm text-slate-700 dark:text-slate-200"><?= h((string)$a['detail']) ?></p>
                    </div>
                  </div>
                  <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 <?= h($typeBadge) ?>"><?= h(strtoupper((string)$a['type'])) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </section>

          <div id="toast" class="pointer-events-none fixed bottom-4 right-4 z-50 hidden w-[92vw] max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft dark:border-slate-800 dark:bg-slate-900" role="status" aria-live="polite">
            <p id="toastTitle" class="text-sm font-semibold text-slate-900 dark:text-white">Listo</p>
            <p id="toastBody" class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Acción completada.</p>
          </div>
        </main>
      </div>
    </div>

    <script type="module" src="/assets/js/project.js"></script>
  </body>
</html>
