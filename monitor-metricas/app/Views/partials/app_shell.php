<?php
declare(strict_types=1);

if (!function_exists('pm_shell_h')) {
    function pm_shell_h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('pm_shell_icon')) {
    function pm_shell_icon(string $name): string
    {
        $icons = [
            'menu' => '<path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'dashboard' => '<path d="M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6V11h-6v9Zm0-18v7h6V2h-6Z" fill="currentColor"/>',
            'projects' => '<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M3 9h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.6"/>',
            'analytics' => '<path d="M4 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M4 19h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8 16V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 16V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M16 16v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'alerts' => '<path d="M12 9v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 17h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M10.3 4.4 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
            'powerbi' => '<path d="M5 20V9a2 2 0 0 1 2-2h1v13H5Z" fill="currentColor" opacity="0.85"/><path d="M10 20V4h4v16h-4Z" fill="currentColor" opacity="0.7"/><path d="M15 20V6h2a2 2 0 0 1 2 2v12h-4Z" fill="currentColor" opacity="0.9"/>',
            'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a8.2 8.2 0 0 0 .1-2l2-1.5-2-3.5-2.4 1a8 8 0 0 0-1.7-1l-.3-2.6h-4l-.3 2.6a8 8 0 0 0-1.7 1l-2.4-1-2 3.5 2 1.5a8.2 8.2 0 0 0 .1 2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.3 2.6h4l.3-2.6a8 8 0 0 0 1.7-1l2.4 1 2-3.5-2-1.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
            'search' => '<path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2"/><path d="M16.5 16.5 21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 17l5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        ];

        return $icons[$name] ?? '';
    }
}

if (!function_exists('pm_shell_initials')) {
    function pm_shell_initials(string $name, string $email): string
    {
        $source = trim($name) !== '' ? trim($name) : trim($email);
        if ($source === '') {
            return 'PM';
        }
        $parts = preg_split('/\s+/', $source) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        return $initials !== '' ? $initials : 'PM';
    }
}

if (!function_exists('pm_render_app_shell_start')) {
    /**
     * @param array{title:string,active:string,search_placeholder?:string} $config
     */
    function pm_render_app_shell_start(array $config): void
    {
        $title = $config['title'];
        $active = $config['active'];
        $searchPlaceholder = $config['search_placeholder'] ?? 'Buscar...';
        $csrf = (string)($_SESSION['csrf'] ?? '');
        $user = isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : [];
        $userName = trim((string)($user['name'] ?? ''));
        $userEmail = trim((string)($user['email'] ?? ''));
        $displayName = $userName !== '' ? $userName : ($userEmail !== '' ? $userEmail : 'Usuario');
        $subtitle = $userEmail !== '' ? $userEmail : 'Cuenta activa';
        $initials = pm_shell_initials($userName, $userEmail);
        $items = [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '/dashboard', 'icon' => 'dashboard'],
            ['key' => 'projects', 'label' => 'Proyectos', 'href' => '/projects', 'icon' => 'projects'],
            ['key' => 'analytics', 'label' => 'Analítica', 'href' => '/analytics', 'icon' => 'analytics'],
            ['key' => 'alerts', 'label' => 'Alertas', 'href' => '/alerts', 'icon' => 'alerts'],
            ['key' => 'powerbi', 'label' => 'Power BI', 'href' => '/powerbi', 'icon' => 'powerbi'],
            ['key' => 'settings', 'label' => 'Configuración', 'href' => '/settings', 'icon' => 'settings'],
        ];
        ?>
        <body class="h-full bg-slate-50 text-slate-900 antialiased">
          <script>
            try {
              localStorage.setItem('pm:theme', 'light');
              document.documentElement.classList.remove('dark');
            } catch (e) {}
          </script>
          <div class="min-h-screen">
            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm md:hidden" aria-hidden="true"></div>
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-200 bg-white shadow-soft transition-transform md:translate-x-0 md:shadow-none" aria-label="Sidebar">
              <div class="flex h-full flex-col">
                <div class="flex items-center gap-3 px-5 py-5">
                  <div class="grid h-10 w-10 place-items-center rounded-xl bg-slate-900 text-white" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                      <path d="M4 18V6a2 2 0 0 1 2-2h3v16H6a2 2 0 0 1-2-2Z" fill="currentColor" opacity="0.9"/>
                      <path d="M10 4h4v16h-4V4Z" fill="currentColor" opacity="0.8"/>
                      <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3V4Z" fill="currentColor"/>
                    </svg>
                  </div>
                  <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900">Project Metrics Monitor</p>
                    <p class="truncate text-xs text-slate-500"><?= pm_shell_h($title) ?></p>
                  </div>
                </div>

                <nav class="flex-1 px-3" aria-label="Navegación">
                  <?php foreach ($items as $item): ?>
                    <?php $isActive = $item['key'] === $active; ?>
                    <a href="<?= pm_shell_h($item['href']) ?>" class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm <?= $isActive ? 'bg-pm-50 font-semibold text-pm-800 ring-1 ring-pm-100' : 'font-medium text-slate-700 transition hover:bg-slate-50' ?>">
                      <span class="grid h-9 w-9 place-items-center rounded-lg <?= $isActive ? 'bg-white text-pm-700 ring-1 ring-slate-200' : 'bg-slate-50 text-slate-700 ring-1 ring-slate-200' ?>" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= pm_shell_icon($item['icon']) ?></svg>
                      </span>
                      <?= pm_shell_h($item['label']) ?>
                    </a>
                  <?php endforeach; ?>
                </nav>

                <div class="px-5 py-5">
                  <form method="post" action="/logout" class="grid gap-3">
                    <input type="hidden" name="csrf" value="<?= pm_shell_h($csrf) ?>" />
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><?= pm_shell_icon('logout') ?></svg>
                      Cerrar sesión
                    </button>
                  </form>
                </div>
              </div>
            </aside>

            <div class="md:pl-72">
              <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95">
                <div class="mx-auto flex max-w-[1400px] items-center gap-3 px-4 py-3 sm:px-6">
                  <button id="sidebarOpen" type="button" class="inline-flex items-center justify-center rounded-xl p-2 text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 md:hidden" aria-label="Abrir menú">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><?= pm_shell_icon('menu') ?></svg>
                  </button>

                  <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><?= pm_shell_icon('search') ?></svg>
                    </span>
                    <label class="sr-only" for="shellSearch">Buscar</label>
                    <input id="shellSearch" type="search" placeholder="<?= pm_shell_h($searchPlaceholder) ?>" class="w-full rounded-xl border border-slate-200 bg-white px-10 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15" />
                  </div>

                  <div class="inline-flex items-center gap-3 rounded-xl px-2 py-2 ring-1 ring-slate-200">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-pm-500 to-sky-500 text-sm font-semibold text-white" aria-hidden="true"><?= pm_shell_h($initials) ?></span>
                    <span class="hidden min-w-0 sm:block">
                      <span class="block truncate text-sm font-semibold text-slate-900"><?= pm_shell_h($displayName) ?></span>
                      <span class="block truncate text-xs text-slate-500"><?= pm_shell_h($subtitle) ?></span>
                    </span>
                  </div>
                </div>
              </header>
        <?php
    }
}

if (!function_exists('pm_render_app_shell_end')) {
    function pm_render_app_shell_end(): void
    {
        ?>
            </div>
          </div>
        <?php
    }
}
