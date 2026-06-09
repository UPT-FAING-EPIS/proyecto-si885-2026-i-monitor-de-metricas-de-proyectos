const qs = (selector, root = document) => root.querySelector(selector);

const setTheme = (theme) => {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    localStorage.setItem('pm:theme', theme);
};

const getTheme = () => {
    const stored = localStorage.getItem('pm:theme');
    if (stored === 'light' || stored === 'dark') {
        return stored;
    }
    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches ?? false;
    return prefersDark ? 'dark' : 'light';
};

const updateThemeUI = () => {
    const label = qs('#themeLabel');
    if (!label) {
        return;
    }
    label.textContent = document.documentElement.classList.contains('dark') ? 'Light mode' : 'Dark mode';
};

const setSidebarOpen = (open) => {
    const sidebar = qs('#sidebar');
    const overlay = qs('#sidebarOverlay');
    if (!sidebar || !overlay) {
        return;
    }
    sidebar.classList.toggle('-translate-x-full', !open);
    overlay.classList.toggle('hidden', !open);
    overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
};

const wireSidebar = () => {
    const openBtn = qs('#sidebarOpen');
    const overlay = qs('#sidebarOverlay');
    openBtn?.addEventListener('click', () => setSidebarOpen(true));
    overlay?.addEventListener('click', () => setSidebarOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setSidebarOpen(false);
        }
    });
};

const toast = (title, body) => {
    const el = qs('#toast');
    const titleEl = qs('#toastTitle');
    const bodyEl = qs('#toastBody');
    if (!el || !titleEl || !bodyEl) {
        return;
    }
    titleEl.textContent = title;
    bodyEl.textContent = body;
    el.classList.remove('hidden');
    window.setTimeout(() => el.classList.add('hidden'), 2400);
};

const readPayload = () => {
    const el = qs('#pmProjectData');
    if (!el?.textContent) {
        return {
            project: { id: '', name: 'Proyecto', status: 'En curso', lastSync: '' },
            charts: { progressSeries: [], members: [], statusDistribution: [] },
        };
    }
    try {
        return JSON.parse(el.textContent);
    }
    catch {
        return {
            project: { id: '', name: 'Proyecto', status: 'En curso', lastSync: '' },
            charts: { progressSeries: [], members: [], statusDistribution: [] },
        };
    }
};

const formatDateTime = (iso) => {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) {
        return 'sin fecha';
    }
    return d.toLocaleString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const palette = (key) => {
    const dark = document.documentElement.classList.contains('dark');
    const map = {
        pm: { stroke: dark ? '#93c5fd' : '#155fe0', fill: dark ? 'rgba(31, 122, 255, 0.14)' : 'rgba(21, 95, 224, 0.10)' },
        sky: { stroke: dark ? '#7dd3fc' : '#0284c7', fill: dark ? 'rgba(125, 211, 252, 0.14)' : 'rgba(2, 132, 199, 0.10)' },
        emerald: { stroke: dark ? '#34d399' : '#10b981', fill: dark ? 'rgba(52, 211, 153, 0.14)' : 'rgba(16, 185, 129, 0.10)' },
        rose: { stroke: dark ? '#fb7185' : '#f43f5e', fill: dark ? 'rgba(251, 113, 133, 0.14)' : 'rgba(244, 63, 94, 0.10)' },
        slate: { stroke: dark ? '#94a3b8' : '#64748b', fill: dark ? 'rgba(148, 163, 184, 0.14)' : 'rgba(100, 116, 139, 0.10)' },
    };
    return map[key] ?? map.pm;
};

const renderLineChart = (svg, values) => {
    if (!svg) {
        return;
    }
    const safe = values.length > 0 ? values : [0, 0, 0, 0];
    const width = 600;
    const height = 220;
    const padX = 18;
    const padY = 20;
    const min = Math.min(...safe);
    const max = Math.max(...safe);
    const range = max === min ? 1 : max - min;
    const step = (width - padX * 2) / Math.max(1, safe.length - 1);
    const x = (index) => padX + index * step;
    const y = (value) => padY + (1 - (value - min) / range) * (height - padY * 2);
    const path = safe.reduce((acc, value, index) => acc + (index === 0 ? `M ${x(index)} ${y(value)}` : ` L ${x(index)} ${y(value)}`), '');
    const fillPath = `${path} L ${x(safe.length - 1)} ${height - padY} L ${x(0)} ${height - padY} Z`;
    const color = palette('pm');

    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    svg.innerHTML = `
      <path d="${fillPath}" fill="${color.fill}"></path>
      <path d="${path}" fill="none" stroke="${color.stroke}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
    `;
};

const renderBarsChart = (svg, rows) => {
    if (!svg) {
        return;
    }
    const safe = rows.length > 0 ? rows : [{ name: 'Sin datos', assigned: 0 }];
    const width = 600;
    const height = 220;
    const pad = 18;
    const max = Math.max(1, ...safe.map((row) => Number(row.assigned) || 0));
    const groupW = (width - pad * 2) / safe.length;
    const barW = groupW * 0.55;
    const color = palette('sky');
    const bars = safe.map((row, index) => {
        const assigned = Number(row.assigned) || 0;
        const barHeight = ((height - pad * 2) * assigned) / max;
        const x = pad + index * groupW + (groupW - barW) / 2;
        const y = height - pad - barHeight;
        return `<rect x="${x}" y="${y}" width="${barW}" height="${barHeight}" rx="10" fill="${color.fill}" stroke="${color.stroke}" stroke-width="2"></rect>`;
    }).join('');

    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    svg.innerHTML = bars;
};

const renderDonut = (svg, rows) => {
    if (!svg) {
        return;
    }
    const safe = rows.length > 0 ? rows : [{ label: 'Sin datos', value: 1, tone: 'slate' }];
    const total = Math.max(1, safe.reduce((acc, row) => acc + (Number(row.value) || 0), 0));
    const center = 60;
    const radius = 42;
    const circumference = 2 * Math.PI * radius;
    let offset = 0;

    const toneMap = {
        pm: palette('pm').stroke,
        rose: palette('rose').stroke,
        emerald: palette('emerald').stroke,
        slate: palette('slate').stroke,
    };

    const segments = safe.map((row) => {
        const fraction = (Number(row.value) || 0) / total;
        const length = fraction * circumference;
        const dashArray = `${length} ${circumference - length}`;
        const segment = `
          <circle
            cx="${center}"
            cy="${center}"
            r="${radius}"
            fill="none"
            stroke="${toneMap[row.tone] ?? toneMap.pm}"
            stroke-width="16"
            stroke-linecap="round"
            stroke-dasharray="${dashArray}"
            stroke-dashoffset="${-offset}"
            transform="rotate(-90 ${center} ${center})"
          ></circle>
        `;
        offset += length;
        return segment;
    }).join('');

    svg.setAttribute('viewBox', '0 0 120 120');
    svg.innerHTML = `
      <circle cx="${center}" cy="${center}" r="${radius}" fill="none" stroke="${palette('slate').fill}" stroke-width="16"></circle>
      ${segments}
      <text x="${center}" y="${center - 2}" text-anchor="middle" class="fill-slate-900 dark:fill-white" font-size="18" font-weight="700">${total}</text>
      <text x="${center}" y="${center + 16}" text-anchor="middle" fill="#64748b" font-size="10">items</text>
    `;
};

const renderLegend = (root, rows) => {
    if (!root) {
        return;
    }
    const safe = rows.length > 0 ? rows : [{ label: 'Sin datos', value: 0, tone: 'slate' }];
    const toneClasses = {
        pm: 'bg-pm-500',
        rose: 'bg-rose-500',
        emerald: 'bg-emerald-500',
        slate: 'bg-slate-400',
    };
    root.innerHTML = safe.map((row) => `
      <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center gap-2">
          <span class="inline-flex h-2.5 w-2.5 rounded-full ${toneClasses[row.tone] ?? toneClasses.pm}"></span>
          <span class="text-sm font-medium text-slate-700 dark:text-slate-200">${row.label}</span>
        </div>
        <span class="text-sm font-semibold text-slate-900 dark:text-white">${row.value}</span>
      </div>
    `).join('');
};

const init = () => {
    setTheme(getTheme());
    updateThemeUI();
    qs('#themeToggle')?.addEventListener('click', () => {
        const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        setTheme(next);
        updateThemeUI();
        const payload = readPayload();
        renderLineChart(qs('#chartProgress'), (payload.charts?.progressSeries ?? []).map((value) => Number(value) || 0));
        renderBarsChart(qs('#chartMembers'), payload.charts?.members ?? []);
        renderDonut(qs('#chartStatus'), payload.charts?.statusDistribution ?? []);
        renderLegend(qs('#statusLegend'), payload.charts?.statusDistribution ?? []);
    });

    wireSidebar();

    const payload = readPayload();
    const progressSeries = (payload.charts?.progressSeries ?? []).map((value) => Number(value) || 0);
    const members = Array.isArray(payload.charts?.members) ? payload.charts.members : [];
    const statusDistribution = Array.isArray(payload.charts?.statusDistribution) ? payload.charts.statusDistribution : [];

    const lastSyncLabel = qs('#lastSyncLabel');
    if (lastSyncLabel) {
        lastSyncLabel.textContent = formatDateTime(payload.project?.lastSync ?? '');
    }

    renderLineChart(qs('#chartProgress'), progressSeries);
    renderBarsChart(qs('#chartMembers'), members);
    renderDonut(qs('#chartStatus'), statusDistribution);
    renderLegend(qs('#statusLegend'), statusDistribution);

    qs('#syncNow')?.addEventListener('click', () => {
        toast('Sincronización', 'Ejecuta la sincronización desde Trello para actualizar este proyecto.');
        window.location.href = '/trello';
    });

    qs('#exportBtn')?.addEventListener('click', () => {
        const exportPayload = {
            project: payload.project ?? {},
            charts: {
                progressSeries,
                members,
                statusDistribution,
            },
        };
        const blob = new Blob([JSON.stringify(exportPayload, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        const projectName = String(payload.project?.name ?? 'proyecto').toLowerCase().replace(/[^a-z0-9]+/gi, '-');
        link.href = url;
        link.download = `${projectName || 'proyecto'}-metricas.json`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
        toast('Exportación', 'Se descargó el resumen del proyecto.');
    });
};

try {
    window.addEventListener('DOMContentLoaded', init);
}
catch {
    // no-op
}
