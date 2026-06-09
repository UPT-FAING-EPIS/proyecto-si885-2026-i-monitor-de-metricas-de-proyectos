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
        'expand' => '<path d="M15 3h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 21H3v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 3l-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'pdf' => '<path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M8 14h3M8 18h3M13 14h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'xlsx' => '<path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 17l2-3 2 3M9 14l4 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'filter' => '<path d="M4 5h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 12h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 19h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    ];
    return $icons[$name] ?? '';
}

$reports = [
    [
        'id' => 'exec',
        'name' => 'Dashboard Ejecutivo',
        'desc' => 'Portafolio, KPIs y señales de riesgo para gerencia.',
        'kpis' => [
            ['label' => 'Proyectos activos', 'value' => 18, 'delta' => +6.4, 'tone' => 'pm'],
            ['label' => 'Cumplimiento', 'value' => 92, 'delta' => +1.8, 'tone' => 'emerald'],
            ['label' => 'Vencidas', 'value' => 56, 'delta' => -3.2, 'tone' => 'rose'],
            ['label' => 'Riesgos', 'value' => 12, 'delta' => +9.7, 'tone' => 'amber'],
        ],
    ],
    [
        'id' => 'delivery',
        'name' => 'Delivery & Flow',
        'desc' => 'Velocidad, lead/cycle time y throughput.',
        'kpis' => [
            ['label' => 'Throughput', 'value' => 64, 'delta' => +4.1, 'tone' => 'pm'],
            ['label' => 'Velocidad', 'value' => 38, 'delta' => +2.3, 'tone' => 'emerald'],
            ['label' => 'Lead Time', 'value' => 9.4, 'delta' => -1.1, 'tone' => 'emerald'],
            ['label' => 'Cycle Time', 'value' => 5.2, 'delta' => -0.6, 'tone' => 'emerald'],
        ],
    ],
    [
        'id' => 'quality',
        'name' => 'Calidad & Riesgos',
        'desc' => 'Defectos, bloqueos, vencimientos y alertas.',
        'kpis' => [
            ['label' => 'Bloqueadas', 'value' => 8, 'delta' => +1.2, 'tone' => 'amber'],
            ['label' => 'Vencidas', 'value' => 56, 'delta' => +2.1, 'tone' => 'rose'],
            ['label' => 'Riesgo alto', 'value' => 2, 'delta' => +1.0, 'tone' => 'rose'],
            ['label' => 'Actividad', 'value' => 78, 'delta' => -0.8, 'tone' => 'pm'],
        ],
    ],
];

$payload = [
    'reports' => $reports,
    'filters' => [
        'projects' => ['Todos', 'Customer Portal', 'Release 2.4', 'Q3 Roadmap', 'Onboarding', 'QA Automation'],
        'teams' => ['Todos', 'Producto', 'Backend', 'Frontend', 'QA', 'Data', 'PMO'],
        'periods' => ['Últimas 24h', 'Últimos 7 días', 'Últimos 30 días', 'Trimestre'],
    ],
];
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Power BI Embedded · Project Metrics Monitor</title>
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
    <script id="pmPowerBIData" type="application/json"><?= (string)json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  </head>
    <?php require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'app_shell.php'; ?>
  <?php pm_render_app_shell_start([
      'title' => 'Power BI',
      'active' => 'powerbi',
      'search_placeholder' => 'Buscar paneles o métricas...',
  ]); ?>

        <main class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
              <h1 class="truncate text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Power BI Embedded</h1>
              <p id="reportDesc" class="mt-1 text-sm text-slate-600 dark:text-slate-400">—</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button id="fullscreenBtn" type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><?= icon('expand') ?></svg>
                </span>
                Pantalla completa
              </button>
              <button id="exportPdf" type="button" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 dark:bg-slate-950/10" aria-hidden="true">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><?= icon('pdf') ?></svg>
                </span>
                Exportar PDF
              </button>
              <button id="exportXlsx" type="button" class="inline-flex items-center gap-2 rounded-xl bg-pm-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-pm-500 dark:hover:bg-pm-400">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10" aria-hidden="true">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><?= icon('xlsx') ?></svg>
                </span>
                Exportar Excel
              </button>
            </div>
          </div>

          <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
            <div class="grid gap-3 lg:grid-cols-[360px_1fr] lg:items-end">
              <div class="grid gap-1.5">
                <label for="reportSelect" class="text-sm font-semibold text-slate-900 dark:text-white">Selector de reporte</label>
                <select id="reportSelect" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15"></select>
              </div>
              <div class="grid gap-1.5">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">KPIs</p>
                <div id="kpiRow" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"></div>
              </div>
            </div>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-[360px_1fr]">
            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Filtros</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Controla el contexto del reporte para gerencia.</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('filter') ?></svg>
                </span>
              </div>

              <form id="filtersForm" class="mt-5 grid gap-4" aria-label="Filtros del reporte">
                <div class="grid gap-1.5">
                  <label for="filterProject" class="text-sm font-semibold text-slate-900 dark:text-white">Proyecto</label>
                  <select id="filterProject" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15"></select>
                </div>
                <div class="grid gap-1.5">
                  <label for="filterTeam" class="text-sm font-semibold text-slate-900 dark:text-white">Equipo</label>
                  <select id="filterTeam" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15"></select>
                </div>
                <div class="grid gap-1.5">
                  <label for="filterPeriod" class="text-sm font-semibold text-slate-900 dark:text-white">Periodo</label>
                  <select id="filterPeriod" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15"></select>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
                  <p class="font-semibold text-slate-900 dark:text-white">Contexto</p>
                  <p id="filtersSummary" class="mt-1 text-xs text-slate-600 dark:text-slate-400">—</p>
                </div>
                <div class="grid gap-2 sm:grid-cols-2">
                  <button id="applyFilters" type="button" class="inline-flex items-center justify-center rounded-xl bg-pm-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-pm-500 dark:hover:bg-pm-400">
                    Aplicar
                  </button>
                  <button id="resetFilters" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    Restablecer
                  </button>
                </div>
              </form>
            </aside>

            <article class="rounded-2xl border border-slate-200 bg-white shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                <div class="min-w-0">
                  <h2 id="reportTitle" class="truncate text-sm font-semibold text-slate-900 dark:text-white">Dashboard principal</h2>
                  <p id="embedMeta" class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">Embedded preview · lectura</p>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Embedded</span>
              </div>
              <div class="border-t border-slate-200 dark:border-slate-800"></div>
              <div id="embedContainer" class="relative aspect-[16/10] w-full bg-slate-50 dark:bg-slate-950">
                <iframe
                  id="reportFrame"
                  class="absolute inset-0 h-full w-full"
                  title="Power BI Embedded"
                  sandbox="allow-scripts allow-forms allow-same-origin"
                  referrerpolicy="no-referrer"
                ></iframe>
              </div>
            </article>
          </section>

          <div id="toast" class="pointer-events-none fixed bottom-4 right-4 z-50 hidden w-[92vw] max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft dark:border-slate-800 dark:bg-slate-900" role="status" aria-live="polite">
            <p id="toastTitle" class="text-sm font-semibold text-slate-900 dark:text-white">Listo</p>
            <p id="toastBody" class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Acción completada.</p>
          </div>
        </main>
  <?php pm_render_app_shell_end(); ?>

    <script type="module" src="/assets/js/powerbi.js"></script>
  </body>
</html>
