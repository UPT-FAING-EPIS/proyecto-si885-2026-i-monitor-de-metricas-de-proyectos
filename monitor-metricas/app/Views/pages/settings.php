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
        'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a8.2 8.2 0 0 0 .1-2l2-1.5-2-3.5-2.4 1a8 8 0 0 0-1.7-1l-.3-2.6h-4l-.3 2.6a8 8 0 0 0-1.7 1l-2.4-1-2 3.5 2 1.5a8.2 8.2 0 0 0 .1 2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.3 2.6h4l.3-2.6a8 8 0 0 0 1.7-1l2.4 1 2-3.5-2-1.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'lock' => '<path d="M19 11H5v10h14V11Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'plug' => '<path d="M9 2v6M15 2v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 8h10v4a5 5 0 0 1-10 0V8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 17v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'trello' => '<path d="M5 4h6a2 2 0 0 1 2 2v10a4 4 0 0 1-4 4H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" fill="currentColor" opacity="0.85"/><path d="M15 4h4a2 2 0 0 1 2 2v6a4 4 0 0 1-4 4h-2V6a2 2 0 0 1 2-2Z" fill="currentColor" opacity="0.6"/>',
        'powerbi' => '<path d="M5 20V9a2 2 0 0 1 2-2h1v13H5Z" fill="currentColor" opacity="0.85"/><path d="M10 20V4h4v16h-4Z" fill="currentColor" opacity="0.7"/><path d="M15 20V6h2a2 2 0 0 1 2 2v12h-4Z" fill="currentColor" opacity="0.9"/>',
        'notif' => '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M13.7 21a2 2 0 0 1-3.4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'check' => '<path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'alert' => '<path d="M12 9v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 17h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M10.3 4.4 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
    ];
    return $icons[$name] ?? '';
}
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Configuración · Project Metrics Monitor</title>
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
              <p class="truncate text-xs text-slate-500 dark:text-slate-400">Configuración</p>
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
            <a href="/alerts" class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" aria-hidden="true">
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

            <a href="/settings" class="mt-2 flex items-center gap-3 rounded-xl bg-pm-50 px-3 py-2.5 text-sm font-semibold text-pm-800 ring-1 ring-pm-100 dark:bg-pm-500/10 dark:text-pm-200 dark:ring-pm-500/20">
              <span class="grid h-9 w-9 place-items-center rounded-lg bg-white text-pm-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-pm-200 dark:ring-slate-800" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('settings') ?></svg>
              </span>
              Configuración
            </a>
          </nav>

          <div class="px-5 py-5">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
              <p class="font-semibold text-slate-900 dark:text-white">Buenas prácticas</p>
              <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Activa 2FA y revisa integraciones periódicamente.</p>
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
              <input id="search" type="search" placeholder="Buscar configuración…" class="w-full rounded-xl border border-slate-200 bg-white px-10 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
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
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
              <h1 class="truncate text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Configuración</h1>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Preferencias de cuenta, seguridad e integraciones.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <span id="saveState" class="rounded-full bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800" role="status" aria-live="polite">Sin cambios</span>
              <button id="saveBtn" type="button" class="inline-flex items-center justify-center rounded-xl bg-pm-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-pm-500 dark:hover:bg-pm-400" disabled>
                Guardar cambios
              </button>
            </div>
          </div>

          <section class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-soft dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
              <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Secciones</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Navegación por pestañas (responsive).</p>
              </div>
              <div class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">v1</div>
            </div>
            <div class="border-t border-slate-200 dark:border-slate-800"></div>

            <div class="flex flex-col gap-4 p-5 lg:flex-row">
              <nav class="lg:w-80" aria-label="Pestañas de configuración">
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1" role="tablist">
                  <button class="tab-btn flex items-center gap-3 rounded-2xl bg-pm-50 px-4 py-3 text-left text-sm font-semibold text-pm-800 ring-1 ring-pm-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-pm-500/10 dark:text-pm-200 dark:ring-pm-500/20" role="tab" aria-selected="true" data-tab="profile">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-pm-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-pm-200 dark:ring-slate-800" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('user') ?></svg>
                    </span>
                    Perfil
                  </button>
                  <button class="tab-btn flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" role="tab" aria-selected="false" data-tab="security">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('lock') ?></svg>
                    </span>
                    Seguridad
                  </button>
                  <button class="tab-btn flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" role="tab" aria-selected="false" data-tab="integrations">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('plug') ?></svg>
                    </span>
                    Integraciones
                  </button>
                  <button class="tab-btn flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" role="tab" aria-selected="false" data-tab="trello">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('trello') ?></svg>
                    </span>
                    Trello
                  </button>
                  <button class="tab-btn flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" role="tab" aria-selected="false" data-tab="powerbi">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('powerbi') ?></svg>
                    </span>
                    Power BI
                  </button>
                  <button class="tab-btn flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" role="tab" aria-selected="false" data-tab="notifications">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-800 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('notif') ?></svg>
                    </span>
                    Notificaciones
                  </button>
                </div>
              </nav>

              <div class="min-w-0 flex-1">
                <div id="tab-profile" class="tab-panel">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Perfil</h3>
                      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Información de cuenta y preferencias personales.</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20">Activo</span>
                  </div>

                  <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Usuario</p>
                      <div class="mt-3 flex items-center gap-3">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-pm-500 to-sky-500 text-base font-semibold text-white" aria-hidden="true">MG</span>
                        <div class="min-w-0">
                          <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">María Gómez</p>
                          <p class="truncate text-xs text-slate-500 dark:text-slate-400">maria.gomez@company.com</p>
                        </div>
                      </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Preferencias</p>
                      <div class="mt-3 grid gap-3">
                        <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                          <span class="font-semibold text-slate-900 dark:text-white">Formato de fecha</span>
                          <select id="prefDate" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="es" selected>ES</option>
                            <option value="en">EN</option>
                          </select>
                        </label>
                        <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                          <span class="font-semibold text-slate-900 dark:text-white">Zona horaria</span>
                          <select id="prefTZ" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="local" selected>Local</option>
                            <option value="utc">UTC</option>
                          </select>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="tab-security" class="tab-panel hidden">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Seguridad</h3>
                      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Políticas de acceso y protección de cuenta.</p>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 ring-1 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20">Revisión sugerida</span>
                  </div>

                  <div class="mt-4 grid gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                      <p class="text-sm font-semibold text-slate-900 dark:text-white">Autenticación</p>
                      <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                          <input id="sec2fa" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" />
                          <span>
                            <span class="block font-semibold text-slate-900 dark:text-white">2FA</span>
                            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Requiere segundo factor al ingresar.</span>
                          </span>
                        </label>
                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                          <input id="secSso" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" checked />
                          <span>
                            <span class="block font-semibold text-slate-900 dark:text-white">SSO corporativo</span>
                            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Control de acceso centralizado.</span>
                          </span>
                        </label>
                      </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                      <p class="text-sm font-semibold text-slate-900 dark:text-white">Sesiones</p>
                      <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Último acceso: hoy · 09:12</p>
                        <button id="secSignOutAll" type="button" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-200 dark:hover:bg-rose-950/50">
                          Cerrar sesiones
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="tab-integrations" class="tab-panel hidden">
                  <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Integraciones</h3>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Administración general de conectores.</p>
                  <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <a href="/trello" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800">
                      <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-50 text-slate-900 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('trello') ?></svg>
                        </span>
                        <div class="min-w-0">
                          <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">Trello</p>
                          <p class="truncate text-xs text-slate-500 dark:text-slate-400">Sincronización de boards y actividad.</p>
                        </div>
                      </div>
                    </a>
                    <a href="/powerbi" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800">
                      <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-50 text-slate-900 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-800" aria-hidden="true">
                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= icon('powerbi') ?></svg>
                        </span>
                        <div class="min-w-0">
                          <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">Power BI</p>
                          <p class="truncate text-xs text-slate-500 dark:text-slate-400">Embebidos y exportaciones.</p>
                        </div>
                      </div>
                    </a>
                  </div>
                </div>

                <div id="tab-trello" class="tab-panel hidden">
                  <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Trello</h3>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Preferencias del conector (resumen).</p>
                  <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Estado</p>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                      <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                        Conectado
                      </span>
                      <a href="/trello" class="text-sm font-semibold text-pm-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-pm-300">Abrir configuración</a>
                    </div>
                  </div>
                </div>

                <div id="tab-powerbi" class="tab-panel hidden">
                  <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Power BI</h3>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Embebidos, permisos y accesos.</p>
                  <div class="mt-4 grid gap-3">
                    <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm dark:border-slate-800 dark:bg-slate-900">
                      <input id="pbiEmbed" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" checked />
                      <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Power BI Embedded</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Habilita dashboards embebidos para usuarios autorizados.</span>
                      </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm dark:border-slate-800 dark:bg-slate-900">
                      <input id="pbiExport" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" checked />
                      <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Exportaciones</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Permite exportar PDF/Excel según roles.</span>
                      </span>
                    </label>
                  </div>
                </div>

                <div id="tab-notifications" class="tab-panel hidden">
                  <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Notificaciones</h3>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Alertas y recordatorios de operación.</p>
                  <div class="mt-4 grid gap-3">
                    <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm dark:border-slate-800 dark:bg-slate-900">
                      <input id="nRisk" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" checked />
                      <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Riesgo Alto</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Notifica cuando se supere el umbral crítico.</span>
                      </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm dark:border-slate-800 dark:bg-slate-900">
                      <input id="nDigest" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" checked />
                      <span>
                        <span class="block font-semibold text-slate-900 dark:text-white">Resumen semanal</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Informe ejecutivo por correo.</span>
                      </span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <div id="toast" class="pointer-events-none fixed bottom-4 right-4 z-50 hidden w-[92vw] max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft dark:border-slate-800 dark:bg-slate-900" role="status" aria-live="polite">
            <p id="toastTitle" class="text-sm font-semibold text-slate-900 dark:text-white">Listo</p>
            <p id="toastBody" class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Acción completada.</p>
          </div>
        </main>
      </div>
    </div>

    <script type="module" src="/assets/js/settings.js"></script>
  </body>
</html>
