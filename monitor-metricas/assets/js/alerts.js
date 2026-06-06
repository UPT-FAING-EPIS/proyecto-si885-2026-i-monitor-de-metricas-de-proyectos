const qs = (selector, root = document) => root.querySelector(selector);
const setTheme = (theme) => {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    localStorage.setItem('pm:theme', theme);
};
const getTheme = () => {
    const stored = localStorage.getItem('pm:theme');
    if (stored === 'light' || stored === 'dark')
        return stored;
    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches ?? false;
    return prefersDark ? 'dark' : 'light';
};
const updateThemeUI = () => {
    const label = qs('#themeLabel');
    if (!label)
        return;
    label.textContent = document.documentElement.classList.contains('dark') ? 'Light mode' : 'Dark mode';
};
const setSidebarOpen = (open) => {
    const sidebar = qs('#sidebar');
    const overlay = qs('#sidebarOverlay');
    if (!sidebar || !overlay)
        return;
    sidebar.classList.toggle('-translate-x-full', !open);
    overlay.classList.toggle('hidden', !open);
    overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
};
const wireSidebar = () => {
    const openBtn = qs('#sidebarOpen');
    const overlay = qs('#sidebarOverlay');
    if (openBtn)
        openBtn.addEventListener('click', () => setSidebarOpen(true));
    if (overlay)
        overlay.addEventListener('click', () => setSidebarOpen(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape')
            setSidebarOpen(false);
    });
};
const toast = (title, body) => {
    const el = qs('#toast');
    const t = qs('#toastTitle');
    const b = qs('#toastBody');
    if (!el || !t || !b)
        return;
    t.textContent = title;
    b.textContent = body;
    el.classList.remove('hidden');
    window.setTimeout(() => el.classList.add('hidden'), 2400);
};
const readPayload = () => {
    const el = qs('#pmAlertsData');
    if (!el?.textContent)
        return { alerts: [], projects: [], types: [] };
    try {
        return JSON.parse(el.textContent);
    }
    catch {
        return { alerts: [], projects: [], types: [] };
    }
};
const escapeHtml = (v) => v.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');
const parseDateOnly = (value) => {
    if (!value)
        return null;
    const d = new Date(`${value}T00:00:00`);
    const t = d.getTime();
    return Number.isNaN(t) ? null : t;
};
const parseIso = (value) => {
    const t = new Date(value).getTime();
    return Number.isNaN(t) ? 0 : t;
};
const formatIso = (iso) => {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime()))
        return '—';
    return d.toLocaleString('es-ES', { month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};
const severityStyles = (s) => {
    if (s === 'Riesgo Alto')
        return 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20';
    if (s === 'Riesgo Medio')
        return 'bg-amber-50 text-amber-800 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20';
    return 'bg-slate-50 text-slate-700 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800';
};
const typeLabel = (t) => {
    if (t === 'overdue')
        return 'Muchas tareas vencidas';
    if (t === 'overload')
        return 'Sobrecarga de usuarios';
    if (t === 'productivity')
        return 'Baja productividad';
    return 'Falta de actividad';
};
const init = () => {
    setTheme(getTheme());
    updateThemeUI();
    const themeToggle = qs('#themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            setTheme(next);
            updateThemeUI();
        });
    }
    wireSidebar();
    const payload = readPayload();
    const localKey = 'pm:alerts:resolved';
    const resolvedRaw = localStorage.getItem(localKey);
    const resolvedSet = new Set(resolvedRaw ? JSON.parse(resolvedRaw) : []);
    const alerts = payload.alerts.map((a) => ({ ...a, resolved: resolvedSet.has(a.id) }));
    const search = qs('#search');
    const projectFilter = qs('#projectFilter');
    const typeFilter = qs('#typeFilter');
    const dateFrom = qs('#dateFrom');
    const dateTo = qs('#dateTo');
    const onlyOpen = qs('#onlyOpen');
    const clearFilters = qs('#clearFilters');
    const list = qs('#alertsList');
    const empty = qs('#emptyState');
    const resultPill = qs('#resultPill');
    const countHigh = qs('#countHigh');
    const countMed = qs('#countMed');
    const countLow = qs('#countLow');
    const tabs = [qs('#tabHigh'), qs('#tabMed'), qs('#tabLow')].filter(Boolean);
    const detailBadge = qs('#detailBadge');
    const detailTitle = qs('#detailTitle');
    const detailMeta = qs('#detailMeta');
    const detailBody = qs('#detailBody');
    const detailAction = qs('#detailAction');
    const markResolved = qs('#markResolved');
    const openProject = qs('#openProject');
    if (!search ||
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
        !openProject) {
        return;
    }
    projectFilter.innerHTML = `<option value="">Todos</option>` + payload.projects.map((p) => `<option value="${escapeHtml(p)}">${escapeHtml(p)}</option>`).join('');
    typeFilter.innerHTML = `<option value="">Todos</option>` + payload.types.map((t) => `<option value="${t.id}">${escapeHtml(t.label)}</option>`).join('');
    let activeSeverity = 'Riesgo Alto';
    let selectedId = null;
    const setActiveTab = (severity) => {
        activeSeverity = severity;
        tabs.forEach((t) => {
            const s = t.getAttribute('data-severity') ?? '';
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
            if (fromT !== null && t < fromT)
                return false;
            if (toEnd !== null && t > toEnd)
                return false;
            return true;
        })
            .filter((a) => {
            if (!q)
                return true;
            const blob = `${a.project} ${a.signal} ${a.detail} ${a.recommended}`.toLowerCase();
            return blob.includes(q);
        })
            .sort((a, b) => parseIso(b.date) - parseIso(a.date));
    };
    const selectAlert = (id) => {
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
    const renderRow = (a, compact) => {
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
        list.querySelectorAll('button[data-id]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                if (!id)
                    return;
                selectAlert(id);
                list.querySelectorAll('button[data-id]').forEach((b) => b.classList.remove('ring-2', 'ring-pm-500/40', 'dark:ring-pm-400/25'));
                btn.classList.add('ring-2', 'ring-pm-500/40', 'dark:ring-pm-400/25');
            });
        });
        if (selectedId && !items.some((a) => a.id === selectedId)) {
            selectAlert(items[0]?.id ?? null);
        }
        else if (!selectedId) {
            selectAlert(items[0]?.id ?? null);
            if (items[0]) {
                const first = list.querySelector('button[data-id]');
                first?.classList.add('ring-2', 'ring-pm-500/40', 'dark:ring-pm-400/25');
            }
        }
    };
    tabs.forEach((t) => {
        t.addEventListener('click', () => {
            const sev = t.getAttribute('data-severity') ?? 'Riesgo Alto';
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
        if (!selectedId)
            return;
        resolvedSet.add(selectedId);
        localStorage.setItem(localKey, JSON.stringify(Array.from(resolvedSet)));
        const idx = alerts.findIndex((a) => a.id === selectedId);
        if (idx >= 0)
            alerts[idx].resolved = true;
        render();
        toast('Estado', 'La alerta fue marcada como resuelta.');
    });
    openProject.addEventListener('click', () => {
        if (!selectedId)
            return;
        const a = alerts.find((x) => x.id === selectedId);
        toast('Proyecto', a ? `Abrir: ${a.project}` : 'Abrir proyecto');
    });
    window.addEventListener('resize', () => render());
    setActiveTab(activeSeverity);
};
try {
    window.addEventListener('DOMContentLoaded', init);
}
catch { }

