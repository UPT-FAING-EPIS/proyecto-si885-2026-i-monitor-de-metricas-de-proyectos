type Theme = 'light' | 'dark';

type Member = { name: string; initials: string };
type Project = {
  id: string;
  name: string;
  status: string;
  owner: string;
  lastSync: string;
  progress: number;
  tasksTotal: number;
  tasksDone: number;
  tasksOverdue: number;
  members: Member[];
};

type Payload = { projects: Project[] };

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

const readData = (): Payload => {
  const el = qs<HTMLScriptElement>('#pmProjectsData');
  if (!el?.textContent) return { projects: [] };
  try {
    return JSON.parse(el.textContent) as Payload;
  } catch {
    return { projects: [] };
  }
};

const uniq = (arr: string[]) => Array.from(new Set(arr)).sort((a, b) => a.localeCompare(b, 'es'));

const parseDateOnly = (value: string): number | null => {
  if (!value) return null;
  const d = new Date(value + 'T00:00:00');
  const t = d.getTime();
  return Number.isNaN(t) ? null : t;
};

const parseIso = (value: string): number => {
  const t = new Date(value).getTime();
  return Number.isNaN(t) ? 0 : t;
};

const formatIsoLocal = (iso: string): string => {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString('es-ES', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};

const badgeForStatus = (status: string) => {
  if (status === 'Riesgo') return 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20';
  if (status === 'En espera') return 'bg-amber-50 text-amber-800 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20';
  if (status === 'Completado') return 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20';
  return 'bg-pm-50 text-pm-800 ring-pm-100 dark:bg-pm-500/10 dark:text-pm-200 dark:ring-pm-500/20';
};

const avatarColor = (initials: string) => {
  const palette = [
    'from-pm-500 to-sky-500',
    'from-emerald-500 to-teal-500',
    'from-amber-500 to-orange-500',
    'from-fuchsia-500 to-violet-500',
    'from-rose-500 to-pink-500',
  ];
  let hash = 0;
  for (const c of initials) hash = (hash * 31 + c.charCodeAt(0)) >>> 0;
  return palette[hash % palette.length];
};

const escapeHtml = (v: string) =>
  v.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');

const renderCard = (p: Project): string => {
  const statusBadge = badgeForStatus(p.status);
  const progress = Math.max(0, Math.min(100, Math.round(p.progress)));
  const riskHint = p.tasksOverdue >= 10 ? 'ring-rose-200 dark:ring-rose-900/50' : 'ring-slate-200 dark:ring-slate-800';
  const members = p.members.slice(0, 5);
  const extra = Math.max(0, p.members.length - members.length);

  return `
    <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-soft transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="truncate text-sm font-semibold text-slate-900 dark:text-white">${escapeHtml(p.name)}</h3>
            <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ${statusBadge}">${escapeHtml(p.status)}</span>
          </div>
          <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
            Responsable: <span class="font-semibold text-slate-700 dark:text-slate-200">${escapeHtml(p.owner)}</span>
          </p>
        </div>
        <div class="grid place-items-center rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ${riskHint} dark:bg-slate-950 dark:text-slate-200">
          ${progress}%
        </div>
      </div>

      <div class="mt-4">
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
          <span>Avance</span>
          <span class="font-semibold text-slate-700 dark:text-slate-200">${progress}%</span>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700" aria-hidden="true">
          <div class="h-full rounded-full bg-gradient-to-r from-pm-500 to-sky-500" style="width: ${progress}%"></div>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-3 gap-2">
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-800 dark:bg-slate-900">
          <p class="text-slate-500 dark:text-slate-400">Totales</p>
          <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">${p.tasksTotal}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs dark:border-emerald-900/50 dark:bg-emerald-500/10">
          <p class="text-emerald-700 dark:text-emerald-200">Completadas</p>
          <p class="mt-0.5 text-sm font-semibold text-emerald-800 dark:text-emerald-100">${p.tasksDone}</p>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs dark:border-rose-900/50 dark:bg-rose-500/10">
          <p class="text-rose-700 dark:text-rose-200">Vencidas</p>
          <p class="mt-0.5 text-sm font-semibold text-rose-800 dark:text-rose-100">${p.tasksOverdue}</p>
        </div>
      </div>

      <div class="mt-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Miembros</span>
          <div class="flex -space-x-2">
            ${members
              .map((m) => {
                const grad = avatarColor(m.initials);
                return `
                  <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br ${grad} text-[11px] font-semibold text-white ring-2 ring-white dark:ring-slate-900" title="${escapeHtml(m.name)}">
                    ${escapeHtml(m.initials)}
                  </span>
                `;
              })
              .join('')}
            ${extra > 0 ? `<span class="inline-flex h-8 min-w-8 items-center justify-center rounded-xl bg-slate-100 px-2 text-[11px] font-semibold text-slate-700 ring-2 ring-white dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-900">+${extra}</span>` : ''}
          </div>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Sync: ${escapeHtml(formatIsoLocal(p.lastSync))}</p>
      </div>

      <div class="mt-4 grid grid-cols-3 gap-2">
        <button data-action="details" data-id="${escapeHtml(p.id)}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
          Ver detalles
        </button>
        <button data-action="analytics" data-id="${escapeHtml(p.id)}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
          Analítica
        </button>
        <button data-action="powerbi" data-id="${escapeHtml(p.id)}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-pm-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-pm-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-pm-500 dark:hover:bg-pm-400">
          Power BI
        </button>
      </div>
    </article>
  `;
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

  const data = readData();
  const search = qs<HTMLInputElement>('#search');
  const navSearch = qs<HTMLInputElement>('#navSearch');
  const status = qs<HTMLSelectElement>('#status');
  const owner = qs<HTMLSelectElement>('#owner');
  const dateFrom = qs<HTMLInputElement>('#dateFrom');
  const dateTo = qs<HTMLInputElement>('#dateTo');
  const clearFilters = qs<HTMLButtonElement>('#clearFilters');
  const cards = qs<HTMLDivElement>('#cards');
  const resultCount = qs<HTMLElement>('#resultCount');
  const paginationLabel = qs<HTMLElement>('#paginationLabel');
  const prevPage = qs<HTMLButtonElement>('#prevPage');
  const nextPage = qs<HTMLButtonElement>('#nextPage');
  const pageButtons = qs<HTMLDivElement>('#pageButtons');
  if (!search || !status || !owner || !dateFrom || !dateTo || !clearFilters || !cards || !resultCount || !paginationLabel || !prevPage || !nextPage || !pageButtons) return;

  const owners = uniq(data.projects.map((p) => p.owner));
  owner.innerHTML = `<option value="">Todos</option>` + owners.map((o) => `<option value="${escapeHtml(o)}">${escapeHtml(o)}</option>`).join('');

  const pageSize = 6;
  let page = 1;

  const currentQuery = () => (search.value || navSearch?.value || '').trim().toLowerCase();

  const filtered = () => {
    const q = currentQuery();
    const s = status.value;
    const o = owner.value;
    const fromT = parseDateOnly(dateFrom.value);
    const toT = parseDateOnly(dateTo.value);
    const toEnd = toT === null ? null : toT + 24 * 60 * 60 * 1000 - 1;

    return data.projects.filter((p) => {
      if (q && !p.name.toLowerCase().includes(q)) return false;
      if (s && p.status !== s) return false;
      if (o && p.owner !== o) return false;
      const t = parseIso(p.lastSync);
      if (fromT !== null && t < fromT) return false;
      if (toEnd !== null && t > toEnd) return false;
      return true;
    });
  };

  const clampPage = (total: number) => {
    const pages = Math.max(1, Math.ceil(total / pageSize));
    page = Math.max(1, Math.min(page, pages));
    return pages;
  };

  const renderPagination = (total: number, pages: number) => {
    const start = total === 0 ? 0 : (page - 1) * pageSize + 1;
    const end = Math.min(total, page * pageSize);
    paginationLabel.textContent = `Mostrando ${start}–${end} de ${total}`;

    prevPage.disabled = page <= 1;
    nextPage.disabled = page >= pages;

    const maxButtons = 5;
    const windowStart = Math.max(1, Math.min(page - 2, pages - maxButtons + 1));
    const windowEnd = Math.min(pages, windowStart + maxButtons - 1);

    pageButtons.innerHTML = '';
    for (let p = windowStart; p <= windowEnd; p++) {
      const active = p === page;
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = String(p);
      btn.className = active
        ? 'inline-flex h-10 w-10 items-center justify-center rounded-xl bg-pm-600 text-sm font-semibold text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-pm-500'
        : 'inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800';
      btn.addEventListener('click', () => {
        page = p;
        render();
      });
      pageButtons.appendChild(btn);
    }
  };

  const wireCardActions = () => {
    cards.querySelectorAll<HTMLButtonElement>('button[data-action][data-id]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const action = btn.getAttribute('data-action') ?? '';
        const id = btn.getAttribute('data-id') ?? '';
        const project = data.projects.find((p) => p.id === id);
        const name = project?.name ?? 'Proyecto';
        if (action === 'details') toast('Ver detalles', `Acción para “${name}”.`);
        if (action === 'analytics') toast('Analítica', `Vista analítica para “${name}”.`);
        if (action === 'powerbi') toast('Power BI', `Abrir reporte de “${name}”.`);
      });
    });
  };

  const render = () => {
    const list = filtered();
    resultCount.textContent = `${list.length} resultados`;
    const pages = clampPage(list.length);

    const slice = list.slice((page - 1) * pageSize, page * pageSize);
    cards.innerHTML = slice.map(renderCard).join('');
    wireCardActions();
    renderPagination(list.length, pages);
  };

  const onFiltersChanged = () => {
    page = 1;
    render();
  };

  search.addEventListener('input', onFiltersChanged);
  status.addEventListener('change', onFiltersChanged);
  owner.addEventListener('change', onFiltersChanged);
  dateFrom.addEventListener('change', onFiltersChanged);
  dateTo.addEventListener('change', onFiltersChanged);

  if (navSearch) {
    navSearch.addEventListener('input', () => {
      search.value = navSearch.value;
      onFiltersChanged();
    });
  }

  clearFilters.addEventListener('click', () => {
    search.value = '';
    if (navSearch) navSearch.value = '';
    status.value = '';
    owner.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    onFiltersChanged();
    toast('Filtros limpiados', 'Se restablecieron los filtros.');
  });

  prevPage.addEventListener('click', () => {
    page = Math.max(1, page - 1);
    render();
  });
  nextPage.addEventListener('click', () => {
    page = page + 1;
    render();
  });

  render();
};

try {
  window.addEventListener('DOMContentLoaded', init);
} catch {}

