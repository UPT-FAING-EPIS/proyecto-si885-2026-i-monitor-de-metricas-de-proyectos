type Theme = 'light' | 'dark';
type Workspace = { id: string; name: string; selected: boolean; boards: number };

const qs = <T extends Element>(selector: string, root: ParentNode = document) => root.querySelector(selector) as T | null;

const setTheme = (theme: Theme) => {
  document.documentElement.classList.toggle('dark', theme === 'dark');
  localStorage.setItem('pm:theme', theme);
};

const getTheme = (): Theme => {
  const stored = localStorage.getItem('pm:theme');
  if (stored === 'light' || stored === 'dark') return stored;
  const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches ?? false;
  return prefersDark ? 'dark' : 'light';
};

const updateThemeUI = () => {
  const label = qs<HTMLSpanElement>('#themeLabel');
  if (!label) return;
  label.textContent = document.documentElement.classList.contains('dark') ? 'Light mode' : 'Dark mode';
};

const setSidebarOpen = (open: boolean) => {
  const sidebar = qs<HTMLElement>('#sidebar');
  const overlay = qs<HTMLElement>('#sidebarOverlay');
  if (!sidebar || !overlay) return;
  sidebar.classList.toggle('-translate-x-full', !open);
  overlay.classList.toggle('hidden', !open);
  overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
};

const wireSidebar = () => {
  const openBtn = qs<HTMLButtonElement>('#sidebarOpen');
  const overlay = qs<HTMLDivElement>('#sidebarOverlay');
  if (openBtn) openBtn.addEventListener('click', () => setSidebarOpen(true));
  if (overlay) overlay.addEventListener('click', () => setSidebarOpen(false));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') setSidebarOpen(false);
  });
};

const wireProfileMenu = () => {
  const btn = qs<HTMLElement>('#profileBtn');
  const menu = qs<HTMLElement>('#profileMenu');
  if (!btn || !menu) return;

  const close = () => {
    btn.setAttribute('aria-expanded', 'false');
    menu.classList.add('hidden');
  };
  const open = () => {
    btn.setAttribute('aria-expanded', 'true');
    menu.classList.remove('hidden');
  };
  const isOpen = () => !menu.classList.contains('hidden');

  btn.addEventListener('click', () => (isOpen() ? close() : open()));
  document.addEventListener('click', (e) => {
    const t = e.target as Node | null;
    if (!t) return;
    if (btn.contains(t) || menu.contains(t)) return;
    close();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });
};

const toast = (title: string, body: string) => {
  const el = qs<HTMLDivElement>('#toast');
  const t = qs<HTMLParagraphElement>('#toastTitle');
  const b = qs<HTMLParagraphElement>('#toastBody');
  if (!el || !t || !b) return;
  t.textContent = title;
  b.textContent = body;
  el.classList.remove('hidden');
  window.setTimeout(() => el.classList.add('hidden'), 2400);
};

const storageKey = 'pm:trello:connected';
const syncKey = 'pm:trello:lastSync';
const settingsKey = 'pm:trello:settings';
const workspacesKey = 'pm:trello:workspaces';

const defaultWorkspaces = (): Workspace[] => [
  { id: 'w1', name: 'PMO & Operaciones', selected: true, boards: 12 },
  { id: 'w2', name: 'Producto', selected: true, boards: 9 },
  { id: 'w3', name: 'Engineering', selected: false, boards: 15 },
  { id: 'w4', name: 'Data & BI', selected: true, boards: 6 },
];

const loadWorkspaces = (): Workspace[] => {
  const raw = localStorage.getItem(workspacesKey);
  if (!raw) return defaultWorkspaces();
  try {
    const parsed = JSON.parse(raw) as Workspace[];
    if (!Array.isArray(parsed)) return defaultWorkspaces();
    return parsed.map((w) => ({
      id: String(w.id),
      name: String(w.name),
      selected: Boolean(w.selected),
      boards: Number(w.boards) || 0,
    }));
  } catch {
    return defaultWorkspaces();
  }
};

const saveWorkspaces = (ws: Workspace[]) => {
  localStorage.setItem(workspacesKey, JSON.stringify(ws));
};

const setConnected = (connected: boolean) => {
  localStorage.setItem(storageKey, connected ? '1' : '0');
  renderConnection();
};

const isConnected = () => localStorage.getItem(storageKey) === '1';

const setLastSyncNow = () => {
  localStorage.setItem(syncKey, new Date().toISOString());
  renderLastSync();
};

const formatDate = (iso: string): string => {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString('es-ES', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};

const renderLastSync = () => {
  const el = qs<HTMLElement>('#lastSync');
  if (!el) return;
  const iso = localStorage.getItem(syncKey);
  el.textContent = iso ? formatDate(iso) : '—';
};

const renderWorkspaces = () => {
  const container = qs<HTMLDivElement>('#workspaces');
  const count = qs<HTMLElement>('#workspaceCount');
  if (!container || !count) return;

  const ws = loadWorkspaces();
  count.textContent = String(ws.length);

  container.innerHTML = ws
    .map((w) => {
      const checked = w.selected ? 'checked' : '';
      const id = `ws_${w.id}`;
      return `
        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition hover:bg-white dark:border-slate-800 dark:bg-slate-950 dark:hover:bg-slate-900">
          <input id="${id}" data-wsid="${w.id}" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" ${checked} />
          <span class="min-w-0">
            <span class="block truncate font-semibold text-slate-900 dark:text-white">${w.name}</span>
            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">${w.boards} boards detectados</span>
          </span>
        </label>
      `;
    })
    .join('');

  container.querySelectorAll<HTMLInputElement>('input[type="checkbox"][data-wsid]').forEach((input) => {
    input.addEventListener('change', () => {
      const id = input.getAttribute('data-wsid') ?? '';
      const next = loadWorkspaces().map((w) => (w.id === id ? { ...w, selected: input.checked } : w));
      saveWorkspaces(next);
      toast('Preferencias guardadas', 'Se actualizó la selección de workspaces.');
    });
  });
};

const renderConnection = () => {
  const connected = isConnected();

  const pill = qs<HTMLElement>('#connectionPill');
  const dot = qs<HTMLElement>('#connectionDot');
  const text = qs<HTMLElement>('#connectionText');

  const connectedPanel = qs<HTMLElement>('#connectedPanel');
  const disconnectedPanel = qs<HTMLElement>('#disconnectedPanel');

  const connectTop = qs<HTMLButtonElement>('#connectBtnTop');
  const connect = qs<HTMLButtonElement>('#connectBtn');
  const syncNow = qs<HTMLButtonElement>('#syncNowBtn');
  const disconnect = qs<HTMLButtonElement>('#disconnectBtn');

  if (pill && dot && text) {
    dot.classList.toggle('bg-emerald-500', connected);
    dot.classList.toggle('bg-slate-400', !connected);
    text.textContent = connected ? 'Conectado' : 'No conectado';
    pill.classList.toggle('bg-emerald-50', connected);
    pill.classList.toggle('text-emerald-700', connected);
    pill.classList.toggle('ring-emerald-100', connected);
    pill.classList.toggle('dark:bg-emerald-500/10', connected);
    pill.classList.toggle('dark:text-emerald-200', connected);
    pill.classList.toggle('dark:ring-emerald-500/20', connected);
  }

  connectedPanel?.classList.toggle('hidden', !connected);
  disconnectedPanel?.classList.toggle('hidden', connected);

  if (connectTop) connectTop.classList.toggle('hidden', connected);
  if (syncNow) syncNow.disabled = !connected;
  if (disconnect) disconnect.disabled = !connected;

  if (connected) {
    const name = qs<HTMLElement>('#accountName');
    const email = qs<HTMLElement>('#accountEmail');
    if (name) name.textContent = 'Trello: Trello User';
    if (email) email.textContent = 'user@company.com';
    renderWorkspaces();
    renderLastSync();
  }

  if (!connected) {
    toast('Trello desconectado', 'Conecta una cuenta para sincronizar proyectos.');
  }
};

const wireConnect = () => {
  const connectTop = qs<HTMLButtonElement>('#connectBtnTop');
  const connect = qs<HTMLButtonElement>('#connectBtn');
  const disconnect = qs<HTMLButtonElement>('#disconnectBtn');
  const syncNow = qs<HTMLButtonElement>('#syncNowBtn');

  const connectAction = () => {
    setConnected(true);
    if (!localStorage.getItem(syncKey)) setLastSyncNow();
    toast('Trello conectado', 'Se detectaron workspaces y permisos correctamente.');
  };
  const disconnectAction = () => {
    setConnected(false);
  };

  connectTop?.addEventListener('click', connectAction);
  connect?.addEventListener('click', connectAction);
  disconnect?.addEventListener('click', disconnectAction);

  syncNow?.addEventListener('click', () => {
    if (!isConnected()) return;
    setLastSyncNow();
    toast('Sincronización completa', 'La última sincronización fue actualizada.');
  });
};

const wireSyncSettings = () => {
  const form = qs<HTMLFormElement>('#syncSettings');
  const modeAuto = qs<HTMLInputElement>('#modeAuto');
  const modeManual = qs<HTMLInputElement>('#modeManual');
  const frequency = qs<HTMLSelectElement>('#frequency');
  if (!form || !modeAuto || !modeManual || !frequency) return;

  const load = () => {
    const raw = localStorage.getItem(settingsKey);
    if (!raw) return;
    try {
      const parsed = JSON.parse(raw) as { mode?: string; frequency?: string };
      if (parsed.mode === 'manual') modeManual.checked = true;
      if (parsed.mode === 'auto') modeAuto.checked = true;
      if (typeof parsed.frequency === 'string') frequency.value = parsed.frequency;
    } catch {}
  };

  const update = () => {
    const mode = modeManual.checked ? 'manual' : 'auto';
    frequency.disabled = mode === 'manual';
    localStorage.setItem(settingsKey, JSON.stringify({ mode, frequency: frequency.value }));
  };

  load();
  update();

  form.addEventListener('change', () => {
    update();
    toast('Configuración guardada', 'Se actualizó el modo de sincronización.');
  });
};

const init = () => {
  setTheme(getTheme());
  updateThemeUI();

  const themeToggle = qs<HTMLButtonElement>('#themeToggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const next: Theme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
      setTheme(next);
      updateThemeUI();
    });
  }

  wireSidebar();
  wireProfileMenu();
  wireConnect();
  wireSyncSettings();
  renderConnection();
};

try {
  window.addEventListener('DOMContentLoaded', init);
} catch {}

