type Theme = 'light' | 'dark';

type AlertSeverity = 'Riesgo Alto' | 'Riesgo Medio' | 'Riesgo Bajo';
type AlertType = 'overdue' | 'overload' | 'productivity' | 'inactivity';

type AlertItem = {
  id: string;
  severity: AlertSeverity;
  date: string;
  project: string;
  signal: string;
  detail: string;
  recommended: string;
  type: AlertType;
  resolved?: boolean;
};

type Payload = {
  alerts: AlertItem[];
  projects: string[];
  types: { id: AlertType; label: string }[];
};

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

const readPayload = (): Payload => {
  const el = qs<HTMLScriptElement>('#pmAlertsData');
  if (!el?.textContent) return { alerts: [], projects: [], types: [] };
  try {
    return JSON.parse(el.textContent) as Payload;
  } catch {
    return { alerts: [], projects: [], types: [] };
  }
};

const escapeHtml = (v: string) =>
  v.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');

const parseDateOnly = (value: string): number | null => {
  if (!value) return null;
  const d = new Date(`${value}T00:00:00`);
  const t = d.getTime();
  return Number.isNaN(t) ? null : t;
};

const parseIso = (value: string): number => {
  const t = new Date(value).getTime();
  return Number.isNaN(t) ? 0 : t;
};

const formatIso = (iso: string): string => {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString('es-ES', { month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};

const severityStyles = (s: AlertSeverity) => {
  if (s === 'Riesgo Alto') return 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20';
  if (s === 'Riesgo Medio') return 'bg-amber-50 text-amber-800 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20';
  return 'bg-slate-50 text-slate-700 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800';
};

const typeLabel = (t: AlertType): string => {
  if (t === 'overdue') return 'Muchas tareas vencidas';
  if (t === 'overload') return 'Sobrecarga de usuarios';
  if (t === 'productivity') return 'Baja productividad';
  return 'Falta de actividad';
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

  const payload = readPayload();
  const localKey = 'pm:alerts:resolved';
  const resolvedRaw = localStorage.getItem(localKey);
  const resolvedSet = new Set<string>(resolvedRaw ? JSON.parse(resolvedRaw) : []);
  const alerts: AlertItem[] = payload.alerts.map((a) => ({ ...a, resolved: resolvedSet.has(a.id) }));

  const search = qs<HTMLInputElement>('#search');
  const projectFilter = qs<HTMLSelectElement>('#projectFilter');
  const typeFilter = qs<HTMLSelectElement>('#typeFilter');
  const dateFrom = qs<HTMLInputElement>('#dateFrom');
  const dateTo = qs<HTMLInputElement>('#dateTo');
  const onlyOpen = qs<HTMLInputElement>('#onlyOpen');
  const clearFilters = qs<HTMLButtonElement>('#clearFilters');

  const list = qs<HTMLDivElement>('#alertsList');
  const empty = qs<HTMLDivElement>('#emptyState');
  const resultPill = qs<HTMLElement>('#resultPill');

  const countHigh = qs<HTMLElement>('#countHigh');
  const countMed = qs<HTMLElement>('#countMed');
  const countLow = qs<HTMLElement>('#countLow');

  const tabs = [
    qs<HTMLButtonElement>('#tabHigh'),
    qs<HTMLButtonElement>('#tabMed'),
    qs<HTMLButtonElement>('#tabLow'),
  ].filter(Boolean) as HTMLButtonElement[];

  const detailBadge = qs<HTMLElement>('#detailBadge');
  const detailTitle = qs<HTMLElement>('#detailTitle');
  const detailMeta = qs<HTMLElement>('#detailMeta');
  const detailBody = qs<HTMLElement>('#detailBody');
  const detailAction = qs<HTMLElement>('#detailAction');
  const markResolved = qs<HTMLButtonElement>('#markResolved');
  const openProject = qs<HTMLButtonElement>('#openProject');

  if (
    !search ||
    !projectFilter ||
    !typeFilter ||
    !dateFrom ||
    !dateTo ||
    !onlyOpen ||
    !clearFilters ||
    !list ||
    !empty ||
    !resultPill ||
    !countHigh ||
    !countMed ||
    !countLow ||
    !detailBadge ||
    !detailTitle ||
    !detailMeta ||
    !detailBody ||
    !detailAction ||
    !markResolved ||
    !openProject
  ) {
    return;
  }

  projectFilter.innerHTML = `<option value="">Todos</option>` + payload.projects.map((p) => `<option value="${escapeHtml(p)}">${escapeHtml(p)}</option>`).join('');
  typeFilter.innerHTML = `<option value="">Todos</option>` + payload.types.map((t) => `<option value="${t.id}">${escapeHtml(t.label)}</option>`).join('');

  let activeSeverity: AlertSeverity = 'Riesgo Alto';
  let selectedId: string | null = null;

  const setActiveTab = (severity: AlertSeverity) => {
    activeSeverity = severity;
    tabs.forEach((t) => {
      const s = (t.getAttribute('data-severity') ?? '') as AlertSeverity;
      const active = s === severity;
      t.setAttribute('aria-selected', active ? 'true' : 'false');
      t.classList.toggle('border', !active);
      t.classList.toggle('border-slate-200', !active);
      t.classList.toggle('bg-white', !active);
      t.classList.toggle('text-slate-700', !active);
      t.classList.toggle('hover:bg-slate-50', !active);
      t.classList.toggle('dark:border-slate-800', !active);
      t.classList.toggle('dark:bg-slate-900', !active);
      t.classList.toggle('dark:text-slate-200', !active);
      t.classList.toggle('dark:hover:bg-slate-800', !active);
    });
    render();
  };

  const updateCounts = () => {
    const high = alerts.filter((a) => a.severity === 'Riesgo Alto' && (!a.resolved || !onlyOpen.checked)).length;
    const med = alerts.filter((a) => a.severity === 'Riesgo Medio' && (!a.resolved || !onlyOpen.checked)).length;
    const low = alerts.filter((a) => a.severity === 'Riesgo Bajo' && (!a.resolved || !onlyOpen.checked)).length;
    countHigh.textContent = String(high);
    countMed.textContent = String(med);
    countLow.textContent = String(low);
  };

  const currentQuery = () => search.value.trim().toLowerCase();

  const filtered = () => {
    const q = currentQuery();
    const proj = projectFilter.value;
    const typ = typeFilter.value;
    const fromT = parseDateOnly(dateFrom.value);
    const toT = parseDateOnly(dateTo.value);
    const toEnd = toT === null ? null : toT + 24 * 60 * 60 * 1000 - 1;
    return alerts
      .filter((a) => a.severity === activeSeverity)
      .filter((a) => (onlyOpen.checked ? !a.resolved : true))
      .filter((a) => (proj ? a.project === proj : true))
      .filter((a) => (typ ? a.type === typ : true))
      .filter((a) => {
        const t = parseIso(a.date);
        if (fromT !== null && t < fromT) return false;
        if (toEnd !== null && t > toEnd) return false;
        return true;
      })
      .filter((a) => {
        if (!q) return true;
        const blob = `${a.project} ${a.signal} ${a.detail} ${a.recommended}`.toLowerCase();
        return blob.includes(q);
      })
      .sort((a, b) => parseIso(b.date) - parseIso(a.date));
  };

  const selectAlert = (id: string | null) => {
    selectedId = id;
    const a = id ? alerts.find((x) => x.id === id) ?? null : null;
    if (!a) {
      detailBadge.textContent = '—';
      detailBadge.className =
        'rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800';
      detailTitle.textContent = 'Selecciona una alerta';
      detailMeta.textContent = '—';
      detailBody.textContent = 'Haz clic en una fila para ver el detalle.';
      detailAction.textContent = '—';
      markResolved.disabled = true;
      openProject.disabled = true;
      return;
    }

    detailBadge.textContent = a.severity;
    detailBadge.className = `rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ${severityStyles(a.severity)}`;
    detailTitle.textContent = a.signal;
    detailMeta.textContent = `${a.project} · ${formatIso(a.date)} · ${typeLabel(a.type)}`;
    detailBody.textContent = a.detail;
    detailAction.textContent = a.recommended;
    markResolved.disabled = Boolean(a.resolved);
    openProject.disabled = false;
  };

  const renderRow = (a: AlertItem, compact: boolean) => {
    const sev = severityStyles(a.severity);
    const resolved = Boolean(a.resolved);
    const rowTone = resolved ? 'opacity-70' : 'opacity-100';
    const borderTone = resolved ? 'border-slate-200/60 dark:border-slate-800/60' : 'border-slate-200 dark:border-slate-800';
    const active = selectedId === a.id;
    const activeRing = active ? 'ring-2 ring-pm-500/40 dark:ring-pm-400/25' : '';

    if (compact) {
      return `
        <button type="button" data-id="${escapeHtml(a.id)}" class="text-left ${rowTone} ${activeRing} w-full rounded-2xl border ${borderTone} bg-white px-4 py-3 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-slate-900 dark:hover:bg-slate-800">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ${sev}">${escapeHtml(a.severity)}</span>
                ${resolved ? '<span class="rounded-full bg-slate-50 px-2 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Resuelta</span>' : ''}
              </div>
              <p class="mt-2 truncate text-sm font-semibold text-slate-900 dark:text-white">${escapeHtml(a.signal)}</p>
              <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">${escapeHtml(a.project)} · ${escapeHtml(formatIso(a.date))}</p>
            </div>
          </div>
          <p class="mt-3 text-sm text-slate-700 dark:text-slate-200">${escapeHtml(a.recommended)}</p>
        </button>
      `;
    }

    return `
      <button type="button" data-id="${escapeHtml(a.id)}" class="${rowTone} ${activeRing} w-full border-t ${borderTone} bg-white px-5 py-3 text-left transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pm-500 dark:bg-slate-900 dark:hover:bg-slate-800">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-[140px_170px_1fr_1.2fr] lg:items-start">
          <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${sev}">${escapeHtml(a.severity)}</span>
            ${resolved ? '<span class="rounded-full bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Resuelta</span>' : ''}
          </div>
          <span class="text-sm font-medium text-slate-700 dark:text-slate-200">${escapeHtml(formatIso(a.date))}</span>
          <span class="text-sm font-semibold text-slate-900 dark:text-white">${escapeHtml(a.project)}</span>
          <span class="text-sm text-slate-700 dark:text-slate-200">${escapeHtml(a.recommended)}</span>
        </div>
        <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">${escapeHtml(a.signal)} · ${escapeHtml(typeLabel(a.type))}</div>
      </button>
    `;
  };

  const render = () => {
    updateCounts();
    const items = filtered();
    resultPill.textContent = `${items.length} alertas`;
    empty.classList.toggle('hidden', items.length !== 0);
    list.classList.toggle('hidden', items.length === 0);

    const compact = window.matchMedia?.('(max-width: 1023px)')?.matches ?? false;
    list.innerHTML = items.map((a) => renderRow(a, compact)).join('');

    list.querySelectorAll<HTMLButtonElement>('button[data-id]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        if (!id) return;
        selectAlert(id);
        list.querySelectorAll('button[data-id]').forEach((b) => b.classList.remove('ring-2', 'ring-pm-500/40', 'dark:ring-pm-400/25'));
        btn.classList.add('ring-2', 'ring-pm-500/40', 'dark:ring-pm-400/25');
      });
    });

    if (selectedId && !items.some((a) => a.id === selectedId)) {
      selectAlert(items[0]?.id ?? null);
    } else if (!selectedId) {
      selectAlert(items[0]?.id ?? null);
      if (items[0]) {
        const first = list.querySelector<HTMLButtonElement>('button[data-id]');
        first?.classList.add('ring-2', 'ring-pm-500/40', 'dark:ring-pm-400/25');
      }
    }
  };

  tabs.forEach((t) => {
    t.addEventListener('click', () => {
      const sev = (t.getAttribute('data-severity') ?? 'Riesgo Alto') as AlertSeverity;
      setActiveTab(sev);
      toast('Filtro', `Severidad: ${sev}.`);
    });
  });

  const onFiltersChanged = () => {
    selectedId = null;
    render();
  };

  search.addEventListener('input', onFiltersChanged);
  projectFilter.addEventListener('change', onFiltersChanged);
  typeFilter.addEventListener('change', onFiltersChanged);
  dateFrom.addEventListener('change', onFiltersChanged);
  dateTo.addEventListener('change', onFiltersChanged);
  onlyOpen.addEventListener('change', onFiltersChanged);

  clearFilters.addEventListener('click', () => {
    search.value = '';
    projectFilter.value = '';
    typeFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    onlyOpen.checked = true;
    selectedId = null;
    render();
    toast('Filtros', 'Se restablecieron los filtros.');
  });

  markResolved.addEventListener('click', () => {
    if (!selectedId) return;
    resolvedSet.add(selectedId);
    localStorage.setItem(localKey, JSON.stringify(Array.from(resolvedSet)));
    const idx = alerts.findIndex((a) => a.id === selectedId);
    if (idx >= 0) alerts[idx].resolved = true;
    render();
    toast('Estado', 'La alerta fue marcada como resuelta.');
  });

  openProject.addEventListener('click', () => {
    if (!selectedId) return;
    const a = alerts.find((x) => x.id === selectedId);
    toast('Proyecto', a ? `Abrir: ${a.project}` : 'Abrir proyecto');
  });

  window.addEventListener('resize', () => render());

  setActiveTab(activeSeverity);
};

try {
  window.addEventListener('DOMContentLoaded', init);
} catch {}

