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

$emailValue = '';
$rememberValue = false;
?>
<!doctype html>
<html lang="es" class="h-full" data-theme="pm">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Login · Project Metrics Monitor</title>
    <script>
      (function () {
        try {
          var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
          document.documentElement.classList.toggle('dark', prefersDark);
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
      <div class="mx-auto flex min-h-full w-full max-w-md flex-col justify-center px-4 py-10 sm:px-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-soft dark:border-slate-800 dark:bg-slate-900 sm:p-10">
          <div class="flex items-center gap-3">
            <div class="grid h-11 w-11 place-items-center rounded-xl bg-slate-900 text-white shadow-soft dark:bg-white dark:text-slate-950" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M4 18V6a2 2 0 0 1 2-2h3v16H6a2 2 0 0 1-2-2Z" fill="currentColor" opacity="0.9"/>
                <path d="M10 4h4v16h-4V4Z" fill="currentColor" opacity="0.8"/>
                <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3V4Z" fill="currentColor"/>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="truncate text-sm font-medium text-slate-600 dark:text-slate-400">Project Metrics Monitor</p>
              <h1 class="truncate text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Iniciar sesión</h1>
            </div>
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

          <form id="loginForm" class="mt-6 grid gap-4" method="post" action="/login">
            <input type="hidden" name="csrf" value="<?= h((string)($csrf ?? '')) ?>" />

            <div class="grid gap-1.5">
              <label for="email" class="text-sm font-medium text-slate-800 dark:text-slate-200">Correo</label>
              <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                inputmode="email"
                required
                value="<?= h($emailValue) ?>"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15"
              />
            </div>

            <div class="grid gap-1.5">
              <label for="password" class="text-sm font-medium text-slate-800 dark:text-slate-200">Contraseña</label>
              <div class="relative">
                <input
                  id="password"
                  name="password"
                  type="password"
                  autocomplete="current-password"
                  required
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-20 text-sm text-slate-900 shadow-sm outline-none transition focus:border-pm-500 focus:ring-4 focus:ring-pm-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:ring-pm-400/15"
                />
                <button
                  id="togglePassword"
                  type="button"
                  class="absolute inset-y-0 right-2 inline-flex items-center rounded-lg px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-slate-300 dark:hover:bg-slate-800"
                  aria-pressed="false"
                >
                  Mostrar
                </button>
              </div>
            </div>

            <button id="submitBtn" type="submit" class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-pm-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-pm-500 dark:hover:bg-pm-400">
              <span id="submitLabel">Ingresar</span>
              <span id="submitSpinner" class="ml-2 hidden h-4 w-4 animate-spin rounded-full border-2 border-white/60 border-t-white" aria-hidden="true"></span>
            </button>

            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
              <span class="text-slate-600 dark:text-slate-400">¿No tienes cuenta?</span>
              <a href="/register" class="font-semibold text-pm-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:text-pm-300">Crear cuenta</a>
            </div>
          </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">© <?= h((string)date('Y')) ?> Project Metrics Monitor</p>
      </div>
    </main>

    <script>
      (function () {
        var form = document.getElementById('loginForm');
        var submitBtn = document.getElementById('submitBtn');
        var submitLabel = document.getElementById('submitLabel');
        var submitSpinner = document.getElementById('submitSpinner');
        var toggle = document.getElementById('togglePassword');
        var password = document.getElementById('password');

        if (toggle && password) {
          toggle.addEventListener('click', function () {
            var isHidden = password.getAttribute('type') === 'password';
            password.setAttribute('type', isHidden ? 'text' : 'password');
            toggle.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            toggle.textContent = isHidden ? 'Ocultar' : 'Mostrar';
          });
        }

        if (form && submitBtn && submitLabel && submitSpinner) {
          form.addEventListener('submit', function () {
            submitBtn.setAttribute('disabled', 'disabled');
            submitLabel.textContent = 'Ingresando…';
            submitSpinner.classList.remove('hidden');
          });
        }
      })();
    </script>
  </body>
</html>
