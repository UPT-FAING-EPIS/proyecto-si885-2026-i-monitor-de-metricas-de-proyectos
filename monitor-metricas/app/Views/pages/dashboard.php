<?php
declare(strict_types=1);

$kpis = $kpis ?? [
    [
        'label' => 'Proyectos Activos',
        'value' => 18,
        'delta' => 6.4,
        'trend' => 'up',
        'icon' => 'layers',
    ],
    [
        'label' => 'Tareas Totales',
        'value' => 1240,
        'delta' => 3.1,
        'trend' => 'up',
        'icon' => 'list',
    ],
    [
        'label' => 'Tareas Completadas',
        'value' => 910,
        'delta' => 4.8,
        'trend' => 'up',
        'icon' => 'check',
    ],
    [
        'label' => 'Tareas Vencidas',
        'value' => 56,
        'delta' => 1.9,
        'trend' => 'down',
        'icon' => 'alert',
    ],
    [
        'label' => 'Riesgos Detectados',
        'value' => 12,
        'delta' => 9.7,
        'trend' => 'up',
        'icon' => 'shield',
    ],
];

$projectProgressSeries = $projectProgressSeries ?? [62, 64, 66, 65, 68, 71, 73, 75, 76, 78, 79, 81];
$teams = $teams ?? [
    ['name' => 'Producto', 'value' => 78],
    ['name' => 'Backend', 'value' => 66],
    ['name' => 'Frontend', 'value' => 72],
    ['name' => 'QA', 'value' => 59],
    ['name' => 'Data', 'value' => 64],
];

$statusDistribution = $statusDistribution ?? [
    ['label' => 'To Do', 'value' => 22, 'color' => 'bg-slate-400'],
    ['label' => 'In Progress', 'value' => 38, 'color' => 'bg-pm-500'],
    ['label' => 'Blocked', 'value' => 8, 'color' => 'bg-rose-500'],
    ['label' => 'Done', 'value' => 32, 'color' => 'bg-emerald-500'],
];

$recentActivity = $recentActivity ?? [
    ['title' => 'Se actualizó el board “Q3 Roadmap”', 'meta' => 'hace 6 min · Producto', 'type' => 'sync'],
    ['title' => 'Alerta: aumento de tareas vencidas en “Release 2.4”', 'meta' => 'hace 24 min · Backend', 'type' => 'alert'],
    ['title' => 'Nuevo riesgo detectado: baja actividad en “Onboarding”', 'meta' => 'hace 1 h · PMO', 'type' => 'risk'],
    ['title' => 'Se completaron 14 tareas en “Customer Portal”', 'meta' => 'hace 3 h · Frontend', 'type' => 'done'],
];

$topPerformance = $topPerformance ?? [
    ['name' => 'Customer Portal', 'owner' => 'María G.', 'progress' => 86, 'delta' => 8.2],
    ['name' => 'Q3 Roadmap', 'owner' => 'Equipo Producto', 'progress' => 81, 'delta' => 5.1],
    ['name' => 'Data Quality', 'owner' => 'Equipo Data', 'progress' => 79, 'delta' => 4.4],
    ['name' => 'FinOps Tracker', 'owner' => 'Carlos R.', 'progress' => 76, 'delta' => 3.7],
];

$topRisk = $topRisk ?? [
    ['name' => 'Release 2.4', 'owner' => 'Equipo Backend', 'risk' => 'Alto', 'overdue' => 21],
    ['name' => 'Onboarding', 'owner' => 'PMO', 'risk' => 'Medio', 'overdue' => 12],
    ['name' => 'Infra Upgrade', 'owner' => 'Plataforma', 'risk' => 'Medio', 'overdue' => 9],
    ['name' => 'QA Automation', 'owner' => 'Equipo QA', 'risk' => 'Bajo', 'overdue' => 5],
];

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function icon(string $name): string {
    $icons = [
        'layers' => '<path d="M12 2 3 7l9 5 9-5-9-5Zm0 10L3 7v10l9 5 9-5V7l-9 5Z" fill="currentColor" opacity="0.9"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3.5 6h.01M3.5 12h.01M3.5 18h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>',
        'check' => '<path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'alert' => '<path d="M12 9v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 17h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M10.3 4.4 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'shield' => '<path d="M12 3l9 4.5v6c0 5-3.8 8.8-9 10-5.2-1.2-9-5-9-10v-6L12 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 12l2 2 4-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'search' => '<path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2"/><path d="M16.5 16.5 21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'bell' => '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M13.7 21a2 2 0 0 1-3.4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'moon' => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
    ];
    return $icons[$name] ?? '';
}

$csrf = $_SESSION['csrf'] ?? '';

$payload = $payload ?? [
    'projectProgressSeries' => $projectProgressSeries,
    'teams' => $teams,
    'statusDistribution' => array_map(static fn ($s) => ['label' => $s['label'], 'value' => $s['value']], $statusDistribution),
];
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Dashboard Ejecutivo · Project Metrics Monitor</title>
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
    <script id="pmData" type="application/json"><?= (string)json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  </head>
  <?php require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'app_shell.php'; ?>
  <?php pm_render_app_shell_start([
      'title' => 'Dashboard',
      'active' => 'dashboard',
      'search_placeholder' => 'Buscar proyectos, equipos o riesgos...',
  ]); ?>

        <main class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
              <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Visión general</h1>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">KPIs, tendencias y señales de riesgo en un solo lugar.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                Últimas 24h
              </button>
              <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-pm-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-pm-500 dark:hover:bg-pm-400">
                Exportar
              </button>
            </div>
          </div>

          <section class="mt-6 grid gap-4 lg:grid-cols-5">
            <?php foreach ($kpis as $kpi):
              $isUp = $kpi['trend'] === 'up';
              $deltaColor = $isUp ? 'text-emerald-700 bg-emerald-50 ring-emerald-100 dark:text-emerald-200 dark:bg-emerald-500/10 dark:ring-emerald-500/20' : 'text-rose-700 bg-rose-50 ring-rose-100 dark:text-rose-200 dark:bg-rose-500/10 dark:ring-rose-500/20';
              $arrow = $isUp ? 'M12 6v12M12 6l-4 4M12 6l4 4' : 'M12 18V6M12 18l-4-4M12 18l4-4';
            ?>
              <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-600 dark:text-slate-400"><?= h((string)$kpi['label']) ?></p>
                    <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?= h((string)$kpi['value']) ?></p>
                  </div>
                  <span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= icon($kpi['icon']) ?></svg>
                  </span>
                </div>
                <div class="mt-3 flex items-center justify-between">
                  <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 <?= h($deltaColor) ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="<?= h($arrow) ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?= h(number_format((float)$kpi['delta'], 1)) ?>%
                  </span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">vs. periodo anterior</span>
                </div>
                <div class="mt-3 h-10">
                  <svg class="h-10 w-full" viewBox="0 0 120 40" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0 30 C 20 28, 30 15, 45 18 S 75 30, 90 16 S 110 6, 120 12" fill="none" stroke="currentColor" stroke-width="2" class="<?= $isUp ? 'text-emerald-500' : 'text-rose-500' ?>" opacity="0.9"></path>
                    <path d="M0 30 C 20 28, 30 15, 45 18 S 75 30, 90 16 S 110 6, 120 12 L120 40 L0 40 Z" class="<?= $isUp ? 'fill-emerald-500/10' : 'fill-rose-500/10' ?>"></path>
                  </svg>
                </div>
              </article>
            <?php endforeach; ?>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Avance general de proyectos</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Promedio ponderado de avance (últimos 12 periodos)</p>
                </div>
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">
                    <span class="h-2 w-2 rounded-full bg-pm-500" aria-hidden="true"></span>
                    Serie principal
                  </span>
                </div>
              </div>
              <div class="mt-4">
                <div class="flex items-end justify-between">
                  <p class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">81%</p>
                  <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-200">+5.6%</p>
                </div>
                <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-b from-pm-50 to-white dark:border-slate-800 dark:from-pm-500/10 dark:to-slate-900">
                  <div class="p-4">
                    <svg id="chartProgress" class="h-48 w-full" viewBox="0 0 600 200" preserveAspectRatio="none" aria-label="Gráfico de avance general"></svg>
                  </div>
                </div>
              </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Productividad por equipo</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Índice compuesto (entrega, calidad, cumplimiento)</p>
                </div>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                  Ver detalle
                </button>
              </div>
              <div class="mt-4 grid gap-3">
                <svg id="chartTeams" class="h-48 w-full" viewBox="0 0 600 200" preserveAspectRatio="none" aria-label="Gráfico de productividad por equipo"></svg>
                <div class="grid gap-2">
                  <?php foreach ($teams as $t): ?>
                    <div class="flex items-center justify-between gap-3 text-sm">
                      <span class="font-medium text-slate-700 dark:text-slate-200"><?= h((string)$t['name']) ?></span>
                      <span class="font-semibold text-slate-900 dark:text-white"><?= h((string)$t['value']) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Distribución de estados</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mix de trabajo actual en portafolio</p>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">
                  Total 100%
                </span>
              </div>
              <div class="mt-5 grid gap-4 sm:grid-cols-[220px_1fr] sm:items-center">
                <div class="mx-auto">
                  <svg id="chartStatus" class="h-52 w-52" viewBox="0 0 120 120" aria-label="Gráfico de dona de estados"></svg>
                </div>
                <div class="grid gap-2">
                  <?php foreach ($statusDistribution as $s): ?>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-950">
                      <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full <?= h($s['color']) ?>" aria-hidden="true"></span>
                        <span class="font-medium text-slate-700 dark:text-slate-200"><?= h((string)$s['label']) ?></span>
                      </div>
                      <span class="font-semibold text-slate-900 dark:text-white"><?= h((string)$s['value']) ?>%</span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Actividad reciente</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Eventos relevantes y señales de cambio</p>
                </div>
                <a href="/analytics" class="text-xs font-semibold text-pm-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-pm-300">Ver todo</a>
              </div>
              <div class="mt-4 grid gap-2">
                <?php foreach ($recentActivity as $a):
                  $badge = match ($a['type']) {
                      'alert' => 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20',
                      'risk' => 'bg-amber-50 text-amber-800 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
                      'done' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20',
                      default => 'bg-slate-50 text-slate-700 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800',
                  };
                ?>
                  <div class="flex items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800">
                    <div class="min-w-0">
                      <p class="truncate text-sm font-medium text-slate-900 dark:text-white"><?= h((string)$a['title']) ?></p>
                      <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400"><?= h((string)$a['meta']) ?></p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 <?= h($badge) ?>">
                      <?= h(strtoupper((string)$a['type'])) ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
            </article>
          </section>

          <section class="mt-6 grid gap-4 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-end justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Top proyectos con mejor rendimiento</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Progreso alto y mejora sostenida</p>
                </div>
                <a href="/projects" class="text-xs font-semibold text-pm-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-pm-300">Ver proyectos</a>
              </div>
              <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                <div class="grid divide-y divide-slate-200 dark:divide-slate-800">
                  <?php foreach ($topPerformance as $p): ?>
                    <div class="grid grid-cols-1 gap-3 bg-white p-4 sm:grid-cols-[1fr_120px] sm:items-center dark:bg-slate-900">
                      <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                          <p class="truncate text-sm font-semibold text-slate-900 dark:text-white"><?= h((string)$p['name']) ?></p>
                          <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20">
                            +<?= h(number_format((float)$p['delta'], 1)) ?>%
                          </span>
                        </div>
                        <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400"><?= h((string)$p['owner']) ?></p>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800" aria-hidden="true">
                          <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-sky-500" style="width: <?= (int)$p['progress'] ?>%"></div>
                        </div>
                      </div>
                      <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Avance</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white"><?= (int)$p['progress'] ?>%</p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-end justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Top proyectos con riesgo</h2>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Señales de alerta y vencimientos</p>
                </div>
                <a href="/alerts" class="text-xs font-semibold text-pm-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-pm-300">Ver alertas</a>
              </div>
              <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                <div class="grid divide-y divide-slate-200 dark:divide-slate-800">
                  <?php foreach ($topRisk as $r):
                    $riskBadge = match ($r['risk']) {
                        'Alto' => 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20',
                        'Medio' => 'bg-amber-50 text-amber-800 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
                        default => 'bg-slate-50 text-slate-700 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800',
                    };
                  ?>
                    <div class="grid grid-cols-1 gap-3 bg-white p-4 sm:grid-cols-[1fr_160px] sm:items-center dark:bg-slate-900">
                      <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                          <p class="truncate text-sm font-semibold text-slate-900 dark:text-white"><?= h((string)$r['name']) ?></p>
                          <span class="rounded-full px-2 py-0.5 text-xs font-semibold ring-1 <?= h($riskBadge) ?>"><?= h((string)$r['risk']) ?></span>
                        </div>
                        <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400"><?= h((string)$r['owner']) ?></p>
                      </div>
                      <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Vencidas</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white"><?= (int)$r['overdue'] ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          </section>
        </main>
  <?php pm_render_app_shell_end(); ?>

    <script type="module" src="/assets/js/dashboard.js"></script>
  </body>
</html>
