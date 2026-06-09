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
        'alert' => '<path d="M12 9v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 17h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M10.3 4.4 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'shield' => '<path d="M12 3l9 4.5v6c0 5-3.8 8.8-9 10-5.2-1.2-9-5-9-10v-6L12 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'pulse' => '<path d="M3 12h4l2-5 4 10 2-5h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'clock' => '<path d="M12 22c5.5 0 10-4.5 10-10S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10Z" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a8.2 8.2 0 0 0 .1-2l2-1.5-2-3.5-2.4 1a8 8 0 0 0-1.7-1l-.3-2.6h-4l-.3 2.6a8 8 0 0 0-1.7 1l-2.4-1-2 3.5 2 1.5a8.2 8.2 0 0 0 .1 2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.3 2.6h4l.3-2.6a8 8 0 0 0 1.7-1l2.4 1 2-3.5-2-1.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'powerbi' => '<path d="M5 20V9a2 2 0 0 1 2-2h1v13H5Z" fill="currentColor" opacity="0.85"/><path d="M10 20V4h4v16h-4Z" fill="currentColor" opacity="0.7"/><path d="M15 20V6h2a2 2 0 0 1 2 2v12h-4Z" fill="currentColor" opacity="0.9"/>',
    ];
    return $icons[$name] ?? '';
}

$alerts = $alerts ?? [
    [
        'id' => 'a-1001',
        'severity' => 'Riesgo Alto',
        'date' => '2026-06-06T16:10:00Z',
        'project' => 'Release 2.4',
        'signal' => 'Muchas tareas vencidas',
        'detail' => '21 tareas vencidas en los últimos 3 días. Impacta el cumplimiento del release.',
        'recommended' => 'Repriorizar backlog y asignar capacidad adicional (Backend + QA).',
        'type' => 'overdue',
    ],
    [
        'id' => 'a-1002',
        'severity' => 'Riesgo Alto',
        'date' => '2026-06-06T15:34:00Z',
        'project' => 'Mobile App MVP',
        'signal' => 'Sobrecarga de usuarios',
        'detail' => '2 miembros concentran 62% de tareas asignadas. Riesgo de cuellos de botella.',
        'recommended' => 'Balancear asignación y limitar WIP por miembro.',
        'type' => 'overload',
    ],
    [
        'id' => 'a-1003',
        'severity' => 'Riesgo Medio',
        'date' => '2026-06-06T13:25:00Z',
        'project' => 'Onboarding',
        'signal' => 'Baja productividad',
        'detail' => 'Caída de 18% en completadas vs. periodo anterior.',
        'recommended' => 'Revisar dependencias/bloqueos y ajustar alcance del sprint.',
        'type' => 'productivity',
    ],
    [
        'id' => 'a-1004',
        'severity' => 'Riesgo Medio',
        'date' => '2026-06-06T12:05:00Z',
        'project' => 'Customer Portal',
        'signal' => 'Falta de actividad',
        'detail' => 'No se registran movimientos en listas críticas durante 8 horas.',
        'recommended' => 'Verificar sincronización y confirmar estado con el responsable.',
        'type' => 'inactivity',
    ],
    [
        'id' => 'a-1005',
        'severity' => 'Riesgo Bajo',
        'date' => '2026-06-05T19:42:00Z',
        'project' => 'QA Automation',
        'signal' => 'Falta de actividad',
        'detail' => 'Baja actividad en el tablero (actividad intermitente).',
        'recommended' => 'Revisar plan de automatización y actualizar hitos.',
        'type' => 'inactivity',
    ],
    [
        'id' => 'a-1006',
        'severity' => 'Riesgo Bajo',
        'date' => '2026-06-05T16:18:00Z',
        'project' => 'Q3 Roadmap',
        'signal' => 'Muchas tareas vencidas',
        'detail' => '3 tareas vencidas. Aún dentro del umbral operativo.',
        'recommended' => 'Reasignar responsables y ajustar fechas objetivo.',
        'type' => 'overdue',
    ],
];

$payload = $payload ?? [
    'alerts' => $alerts,
    'projects' => array_values(array_unique(array_map(static fn ($a) => (string)$a['project'], $alerts))),
    'types' => [
        ['id' => 'overdue', 'label' => 'Muchas tareas vencidas'],
        ['id' => 'overload', 'label' => 'Sobrecarga de usuarios'],
        ['id' => 'productivity', 'label' => 'Baja productividad'],
        ['id' => 'inactivity', 'label' => 'Falta de actividad'],
    ],
];
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Alertas · Project Metrics Monitor</title>
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
    <script id="pmAlertsData" type="application/json"><?= h((string)json_encode($payload, JSON_UNESCAPED_UNICODE)) ?></script>
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
              <p class="truncate text-xs text-slate-500 dark:text-slate-400">Centro de monitoreo</p>
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
            <a href="/projects" class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('folder') ?></svg>
              </span>
              Proyectos
            </a>
            <a href="/analytics" class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('chart') ?></svg>
              </span>
              Analítica
            </a>

            <a href="/alerts" class="mt-2 flex items-center gap-3 rounded-xl bg-pm-50 px-3 py-2.5 text-sm font-semibold text-pm-800 ring-1 ring-pm-100 dark:bg-pm-500/10 dark:text-pm-200 dark:ring-pm-500/20">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-white text-pm-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-pm-200 dark:ring-slate-800" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('alert') ?></svg>
              </span>
              Alertas
            </a>

            <a href="/powerbi" class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('powerbi') ?></svg>
              </span>
              Power BI
            </a>
            <a href="/settings" class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('settings') ?></svg>
              </span>
              Configuración
            </a>

            <div class="mt-5 px-3">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Señales</p>
            </div>
            <div class="mt-2 grid gap-2 px-3">
              <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs dark:border-slate-800 dark:bg-slate-950">
                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                  <span class="grid h-7 w-7 place-items-center rounded-lg bg-white text-slate-900 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><?= icon('pulse') ?></svg>
                  </span>
                  Monitoreo activo
                </span>
                <span class="font-semibold text-slate-900 dark:text-white">ON</span>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs dark:border-slate-800 dark:bg-slate-950">
                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                  <span class="grid h-7 w-7 place-items-center rounded-lg bg-white text-slate-900 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><?= icon('shield') ?></svg>
                  </span>
                  Reglas
                </span>
                <span class="font-semibold text-slate-900 dark:text-white">12</span>
              </div>
            </div>
          </nav>

          <div class="px-5 py-5">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
              <p class="font-semibold text-slate-900 dark:text-white">Objetivo</p>
              <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Prioriza Riesgo Alto y coordina acciones de mitigación.</p>
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
              <input id="search" type="search" placeholder="Buscar alertas por proyecto, señal o acción…" class="w-full rounded-xl border border-slate-200 bg-white px-10 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
            </div>

            <button id="themeToggle" type="button" class="hidden items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 sm:inline-flex dark:text-slate-200 dark:ring-slate-800 dark:hover:bg-slate-800">
              <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><?= icon('moon') ?></svg>
              </span>
              <span id="themeLabel">Dark mode</span>
            </button>

            <button type="button" class="relative inline-flex items-center justify-center rounded-xl p-2 text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-200 dark:ring-slate-800 dark:hover:bg-slate-800" aria-label="Notificaciones">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon('bell') ?></svg>
              <span class="absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">3</span>
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
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
              <h1 class="truncate text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Alertas</h1>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Centro de monitoreo de situaciones críticas detectadas automáticamente.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <span id="resultPill" class="rounded-full bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" role="status" aria-live="polite">0 alertas</span>
              <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-white text-slate-900 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><?= icon('clock') ?></svg>
                </span>
                <span>Actualizado</span>
              </span>
            </div>
          </div>

          <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Severidad">
                <button id="tabHigh" type="button" class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 ring-1 ring-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20" role="tab" aria-selected="true" data-severity="Riesgo Alto">
                  Riesgo Alto
                  <span id="countHigh" class="inline-flex h-6 min-w-6 items-center justify-center rounded-lg bg-rose-100 px-2 text-xs font-bold text-rose-800 dark:bg-rose-500/20 dark:text-rose-100">0</span>
                </button>
                <button id="tabMed" type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" role="tab" aria-selected="false" data-severity="Riesgo Medio">
                  Riesgo Medio
                  <span id="countMed" class="inline-flex h-6 min-w-6 items-center justify-center rounded-lg bg-slate-100 px-2 text-xs font-bold text-slate-700 dark:bg-slate-950 dark:text-slate-200">0</span>
                </button>
                <button id="tabLow" type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" role="tab" aria-selected="false" data-severity="Riesgo Bajo">
                  Riesgo Bajo
                  <span id="countLow" class="inline-flex h-6 min-w-6 items-center justify-center rounded-lg bg-slate-100 px-2 text-xs font-bold text-slate-700 dark:bg-slate-950 dark:text-slate-200">0</span>
                </button>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
                  <input id="onlyOpen" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" checked />
                  Solo activas
                </label>
                <button id="clearFilters" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                  Limpiar
                </button>
              </div>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-[260px_220px_1fr] lg:items-end">
              <div class="grid gap-1.5">
                <label for="projectFilter" class="text-sm font-semibold text-slate-900 dark:text-white">Proyecto</label>
                <select id="projectFilter" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15">
                  <option value="">Todos</option>
                </select>
              </div>
              <div class="grid gap-1.5">
                <label for="typeFilter" class="text-sm font-semibold text-slate-900 dark:text-white">Tipo</label>
                <select id="typeFilter" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15">
                  <option value="">Todos</option>
                </select>
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
            </div>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-[1.4fr_1fr]">
            <article class="rounded-2xl border border-slate-200 bg-white shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-center justify-between gap-3 px-5 py-4">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Listado de alertas</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Severidad, fecha, proyecto y acción recomendada.</p>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Centro</span>
              </div>
              <div class="border-t border-slate-200 dark:border-slate-800"></div>

              <div class="hidden lg:block">
                <div class="grid grid-cols-[140px_170px_1fr_1.2fr] gap-3 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                  <span>Severidad</span>
                  <span>Fecha</span>
                  <span>Proyecto</span>
                  <span>Acción recomendada</span>
                </div>
                <div class="border-t border-slate-200 dark:border-slate-800"></div>
              </div>

              <div id="alertsList" class="grid"></div>

              <div id="emptyState" class="hidden px-5 py-10 text-center">
                <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><?= icon('alert') ?></svg>
                </div>
                <p class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">Sin alertas para los filtros actuales</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Ajusta severidad o limpia filtros para ver resultados.</p>
              </div>
            </article>

            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Detalle</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Contexto y recomendación para responder rápido.</p>
                </div>
                <span id="detailBadge" class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">—</span>
              </div>

              <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                <p id="detailTitle" class="text-sm font-semibold text-slate-900 dark:text-white">Selecciona una alerta</p>
                <p id="detailMeta" class="mt-1 text-xs text-slate-500 dark:text-slate-400">—</p>
                <p id="detailBody" class="mt-3 text-sm text-slate-700 dark:text-slate-200">Haz clic en una fila para ver el detalle.</p>
              </div>

              <div class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Acción recomendada</p>
                <p id="detailAction" class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">—</p>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                  <button id="markResolved" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" disabled>
                    Marcar resuelta
                  </button>
                  <button id="openProject" type="button" class="inline-flex items-center justify-center rounded-xl bg-pm-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-pm-500 dark:hover:bg-pm-400" disabled>
                    Abrir proyecto
                  </button>
                </div>
              </div>
            </aside>
          </section>

          <div id="toast" class="pointer-events-none fixed bottom-4 right-4 z-50 hidden w-[92vw] max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft dark:border-slate-800 dark:bg-slate-900" role="status" aria-live="polite">
            <p id="toastTitle" class="text-sm font-semibold text-slate-900 dark:text-white">Listo</p>
            <p id="toastBody" class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Acción completada.</p>
          </div>
        </main>
      </div>
    </div>

    <script type="module" src="/assets/js/alerts.js"></script>
  </body>
</html>
