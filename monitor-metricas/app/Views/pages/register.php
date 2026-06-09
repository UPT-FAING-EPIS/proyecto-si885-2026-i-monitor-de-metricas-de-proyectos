<?php
declare(strict_types=1);

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$serverError = null;
if (isset($flash) && is_array($flash) && ($flash['type'] ?? '') === 'error') {
    $serverError = (string)($flash['message'] ?? '');
}
$serverSuccess = null;
if (isset($flash) && is_array($flash) && ($flash['type'] ?? '') === 'success') {
    $serverSuccess = (string)($flash['message'] ?? '');
}
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Registro · Project Metrics Monitor</title>
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
    <main class="min-h-full">
      <div class="mx-auto grid min-h-full w-full max-w-5xl grid-cols-1 gap-8 px-4 py-10 md:grid-cols-2 md:items-stretch md:gap-10 md:px-8 lg:px-10">
        <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft dark:border-slate-800 dark:bg-slate-900">
          <div class="absolute inset-0 bg-gradient-to-br from-pm-50 via-white to-transparent opacity-90 dark:from-slate-900 dark:via-slate-900"></div>
          <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-pm-200 blur-3xl opacity-60 dark:bg-pm-600/20"></div>
          <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-sky-200 blur-3xl opacity-40 dark:bg-sky-500/10"></div>
          <div class="relative flex h-full flex-col justify-between p-8 sm:p-10">
            <div>
              <div class="flex items-center gap-3">
                <div class="grid h-11 w-11 place-items-center rounded-xl bg-slate-900 text-white shadow-soft dark:bg-white dark:text-slate-950" aria-hidden="true">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M4 18V6a2 2 0 0 1 2-2h3v16H6a2 2 0 0 1-2-2Z" fill="currentColor" opacity="0.9"/>
                    <path d="M10 4h4v16h-4V4Z" fill="currentColor" opacity="0.8"/>
                    <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3V4Z" fill="currentColor"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Project Metrics Monitor</p>
                  <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Crea tu cuenta</h1>
                </div>
              </div>
              <p class="mt-6 text-pretty text-base leading-relaxed text-slate-600 dark:text-slate-300">
                Registra tu correo para acceder a dashboards ejecutivos y métricas sincronizadas desde Trello.
              </p>
            </div>
            <div class="mt-8 flex items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
              <p>© <?= h((string)date('Y')) ?> Project Metrics Monitor</p>
              <a href="/login" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-200 dark:ring-slate-800 dark:hover:bg-slate-800">Volver a login</a>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-soft dark:border-slate-800 dark:bg-slate-900">
          <div class="p-8 sm:p-10">
            <div>
              <h2 class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white">Registro</h2>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Correo y contraseña (Supabase).</p>
            </div>

            <?php if ($serverError !== null): ?>
              <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-200" role="alert" aria-live="polite">
                <?= h($serverError) ?>
              </div>
            <?php endif; ?>
            <?php if ($serverSuccess !== null): ?>
              <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-200" role="status" aria-live="polite">
                <?= h($serverSuccess) ?>
              </div>
            <?php endif; ?>

            <form class="mt-6 grid gap-4" method="post" action="/register" novalidate>
              <input type="hidden" name="csrf" value="<?= h((string)($csrf ?? '')) ?>" />

              <div class="grid gap-1.5">
                <label for="full_name" class="text-sm font-medium text-slate-800 dark:text-slate-200">Nombre completo</label>
                <input id="full_name" name="full_name" type="text" autocomplete="name" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
              </div>

              <div class="grid gap-1.5">
                <label for="email" class="text-sm font-medium text-slate-800 dark:text-slate-200">Correo electrónico</label>
                <input id="email" name="email" type="email" autocomplete="email" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
                <p class="text-xs text-slate-500 dark:text-slate-400">Se usará para autenticación y recuperación.</p>
              </div>

              <div class="grid gap-1.5">
                <label for="password" class="text-sm font-medium text-slate-800 dark:text-slate-200">Contraseña</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required minlength="6" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
                <p class="text-xs text-slate-500 dark:text-slate-400">Mínimo 6 caracteres.</p>
              </div>

              <div class="grid gap-1.5">
                <label for="password_confirm" class="text-sm font-medium text-slate-800 dark:text-slate-200">Confirmar contraseña</label>
                <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required minlength="6" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15" />
              </div>

              <button type="submit" class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-pm-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-pm-500 dark:hover:bg-pm-400">
                Crear cuenta
              </button>

              <p class="pt-2 text-xs text-slate-500 dark:text-slate-400">
                Si tu Supabase requiere confirmación por correo, recibirás un email para activar la cuenta.
              </p>
            </form>
          </div>
        </section>
      </div>
    </main>
  </body>
</html>
