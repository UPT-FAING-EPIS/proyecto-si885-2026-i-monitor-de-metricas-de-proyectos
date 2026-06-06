type Theme = 'light' | 'dark';
type TabId = 'profile' | 'security' | 'integrations' | 'trello' | 'powerbi' | 'notifications';

const qs = <T extends Element>(selector: string, root: ParentNode = document) => root.querySelector(selector) as T | null;
const qsa = <T extends Element>(selector: string, root: ParentNode = document) => Array.from(root.querySelectorAll(selector)) as T[];

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

const storageKey = 'pm:settings:state';

const getTabFromHash = (): TabId | null => {
  const raw = (location.hash || '').replace('#', '').trim();
  const v = raw as TabId;
  const allowed: TabId[] = ['profile', 'security', 'integrations', 'trello', 'powerbi', 'notifications'];
  return allowed.includes(v) ? v : null;
};

const setHash = (tab: TabId) => {
  const next = `#${tab}`;
  if (location.hash === next) return;
  history.replaceState(null, '', next);
};

const applyTab = (tab: TabId) => {
  qsa<HTMLButtonElement>('.tab-btn').forEach((btn) => {
    const id = (btn.getAttribute('data-tab') ?? 'profile') as TabId;
    const active = id === tab;
    btn.setAttribute('aria-selected', active ? 'true' : 'false');
    btn.classList.toggle('bg-pm-50', active);
    btn.classList.toggle('text-pm-800', active);
    btn.classList.toggle('ring-pm-100', active);
    btn.classList.toggle('dark:bg-pm-500/10', active);
    btn.classList.toggle('dark:text-pm-200', active);
    btn.classList.toggle('dark:ring-pm-500/20', active);

    btn.classList.toggle('border', !active);
    btn.classList.toggle('border-slate-200', !active);
    btn.classList.toggle('bg-white', !active);
    btn.classList.toggle('text-slate-700', !active);
    btn.classList.toggle('hover:bg-slate-50', !active);
    btn.classList.toggle('dark:border-slate-800', !active);
    btn.classList.toggle('dark:bg-slate-900', !active);
    btn.classList.toggle('dark:text-slate-200', !active);
    btn.classList.toggle('dark:hover:bg-slate-800', !active);
  });

  qsa<HTMLElement>('.tab-panel').forEach((panel) => panel.classList.add('hidden'));
  const panel = qs<HTMLElement>(`#tab-${tab}`);
  panel?.classList.remove('hidden');

  setHash(tab);
};

const readSnapshot = () => {
  const inputs = qsa<HTMLInputElement>('input[type="checkbox"], input[type="radio"]');
  const selects = qsa<HTMLSelectElement>('select');
  const pairs: Record<string, string> = {};
  inputs.forEach((i) => {
    const id = i.id;
    if (!id) return;
    pairs[`i:${id}`] = i.checked ? '1' : '0';
  });
  selects.forEach((s) => {
    const id = s.id;
    if (!id) return;
    pairs[`s:${id}`] = s.value;
  });
  return pairs;
};

const diffChanged = (a: Record<string, string>, b: Record<string, string>) => {
  const keys = new Set([...Object.keys(a), ...Object.keys(b)]);
  for (const k of keys) {
    if ((a[k] ?? '') !== (b[k] ?? '')) return true;
  }
  return false;
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

  const saveState = qs<HTMLElement>('#saveState');
  const saveBtn = qs<HTMLButtonElement>('#saveBtn');
  if (!saveState || !saveBtn) return;

  const base = (() => {
    const raw = localStorage.getItem(storageKey);
    if (!raw) return null;
    try {
      return JSON.parse(raw) as Record<string, string>;
    } catch {
      return null;
    }
  })();

  const initialSnapshot = base ?? readSnapshot();
  const persistBaseline = () => localStorage.setItem(storageKey, JSON.stringify(initialSnapshot));
  if (!base) persistBaseline();

  const updateSaveUI = () => {
    const current = readSnapshot();
    const changed = diffChanged(initialSnapshot, current);
    saveBtn.disabled = !changed;
    saveState.textContent = changed ? 'Cambios pendientes' : 'Sin cambios';
    saveState.className = changed
      ? 'rounded-full bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800 ring-1 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20'
      : 'rounded-full bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800';
  };

  const wireChangeTracking = () => {
    qsa<HTMLInputElement>('input[type="checkbox"], input[type="radio"]').forEach((i) => i.addEventListener('change', updateSaveUI));
    qsa<HTMLSelectElement>('select').forEach((s) => s.addEventListener('change', updateSaveUI));
  };

  wireChangeTracking();
  updateSaveUI();

  qsa<HTMLButtonElement>('.tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = (btn.getAttribute('data-tab') ?? 'profile') as TabId;
      applyTab(id);
    });
  });

  const tabFromHash = getTabFromHash();
  const defaultTab: TabId = tabFromHash ?? 'profile';
  applyTab(defaultTab);

  window.addEventListener('hashchange', () => {
    const t = getTabFromHash();
    if (t) applyTab(t);
  });

  const secSignOutAll = qs<HTMLButtonElement>('#secSignOutAll');
  secSignOutAll?.addEventListener('click', () => toast('Sesiones', 'Acción simulada: cerrar sesiones activas.'));

  saveBtn.addEventListener('click', () => {
    const current = readSnapshot();
    localStorage.setItem(storageKey, JSON.stringify(current));
    Object.keys(initialSnapshot).forEach((k) => delete initialSnapshot[k]);
    Object.assign(initialSnapshot, current);
    updateSaveUI();
    toast('Configuración', 'Cambios guardados (demo).');
  });
};

try {
  window.addEventListener('DOMContentLoaded', init);
} catch {}

