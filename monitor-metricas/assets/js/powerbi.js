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
    const el = qs('#pmPowerBIData');
    if (!el?.textContent)
        return { reports: [], filters: { projects: [], teams: [], periods: [] } };
    try {
        return JSON.parse(el.textContent);
    }
    catch {
        return { reports: [], filters: { projects: [], teams: [], periods: [] } };
    }
};
const escapeHtml = (v) => v.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');
const toneClasses = (tone) => {
    if (tone === 'emerald')
        return 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20';
    if (tone === 'rose')
        return 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20';
    if (tone === 'amber')
        return 'bg-amber-50 text-amber-800 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20';
    return 'bg-pm-50 text-pm-800 ring-pm-100 dark:bg-pm-500/10 dark:text-pm-200 dark:ring-pm-500/20';
};
const fmt = (n) => {
    const isInt = Number.isInteger(n);
    return n.toLocaleString('es-ES', { maximumFractionDigits: isInt ? 0 : 1, minimumFractionDigits: isInt ? 0 : 1 });
};
const download = (filename, content, type) => {
    const blob = new Blob([content], { type });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
};
const buildEmbedHTML = (report, filters, theme) => {
    const dark = theme === 'dark';
    const bg = dark ? '#0b1220' : '#ffffff';
    const fg = dark ? '#e2e8f0' : '#0f172a';
    const muted = dark ? '#94a3b8' : '#64748b';
    const border = dark ? '#1f2937' : '#e2e8f0';
    const accent = dark ? '#60a5fa' : '#155fe0';
    const kpis = report.kpis
        .map((k) => `<div style="border:1px solid ${border}; border-radius:14px; padding:12px; min-width:160px;">
      <div style="font-size:12px; color:${muted}; font-weight:600;">${escapeHtml(k.label)}</div>
      <div style="margin-top:6px; font-size:22px; font-weight:800; color:${fg};">${escapeHtml(fmt(k.value))}</div>
      <div style="margin-top:8px; font-size:12px; color:${muted};">Δ ${k.delta >= 0 ? '+' : ''}${escapeHtml(fmt(k.delta))}%</div>
    </div>`)
        .join('');
    const subtitle = `${filters.project} · ${filters.team} · ${filters.period}`;
    return `<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>${escapeHtml(report.name)}</title>
  </head>
  <body style="margin:0; background:${bg}; color:${fg}; font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;">
    <div style="padding:18px;">
      <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap;">
        <div style="min-width:240px;">
          <div style="font-size:14px; font-weight:800;">${escapeHtml(report.name)}</div>
          <div style="margin-top:4px; font-size:12px; color:${muted};">${escapeHtml(subtitle)}</div>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
          <span style="font-size:12px; color:${muted}; border:1px solid ${border}; border-radius:999px; padding:6px 10px;">Embedded preview</span>
          <span style="font-size:12px; color:${muted}; border:1px solid ${border}; border-radius:999px; padding:6px 10px;">Solo lectura</span>
        </div>
      </div>

      <div style="margin-top:14px; height:1px; background:${border};"></div>

      <div style="margin-top:14px; display:flex; gap:12px; flex-wrap:wrap;">
        ${kpis}
      </div>

      <div style="margin-top:14px; border:1px solid ${border}; border-radius:16px; overflow:hidden;">
        <div style="padding:12px; background:${dark ? '#0f172a' : '#f8fafc'}; border-bottom:1px solid ${border}; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
          <div style="font-size:12px; color:${muted};">Vista de reporte (placeholder)</div>
          <div style="font-size:12px; color:${muted};">Actualizado: ${new Date().toLocaleString('es-ES')}</div>
        </div>
        <div style="padding:14px;">
          <div style="border:1px dashed ${border}; border-radius:14px; padding:14px;">
            <div style="font-size:12px; color:${muted};">Aquí se embebe Power BI (iframe/report embed). Esta demo simula el contenido.</div>
            <div style="margin-top:12px; height:180px; border-radius:14px; background:linear-gradient(135deg, ${accent}1A, transparent); border:1px solid ${border};"></div>
            <div style="margin-top:12px; display:grid; gap:10px; grid-template-columns: repeat(3, minmax(0, 1fr));">
              <div style="height:56px; border-radius:14px; border:1px solid ${border}; background:${dark ? '#0b1220' : '#ffffff'};"></div>
              <div style="height:56px; border-radius:14px; border:1px solid ${border}; background:${dark ? '#0b1220' : '#ffffff'};"></div>
              <div style="height:56px; border-radius:14px; border:1px solid ${border}; background:${dark ? '#0b1220' : '#ffffff'};"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>`;
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
            renderEmbed();
        });
    }
    wireSidebar();
    const payload = readPayload();
    const reportSelect = qs('#reportSelect');
    const reportTitle = qs('#reportTitle');
    const reportDesc = qs('#reportDesc');
    const embedMeta = qs('#embedMeta');
    const kpiRow = qs('#kpiRow');
    const frame = qs('#reportFrame');
    const embedContainer = qs('#embedContainer');
    const filterProject = qs('#filterProject');
    const filterTeam = qs('#filterTeam');
    const filterPeriod = qs('#filterPeriod');
    const filtersSummary = qs('#filtersSummary');
    const applyFilters = qs('#applyFilters');
    const resetFilters = qs('#resetFilters');
    const fullscreenBtn = qs('#fullscreenBtn');
    const exportPdf = qs('#exportPdf');
    const exportXlsx = qs('#exportXlsx');
    if (!reportSelect ||
        !reportTitle ||
        !reportDesc ||
        !embedMeta ||
        !kpiRow ||
        !frame ||
        !embedContainer ||
        !filterProject ||
        !filterTeam ||
        !filterPeriod ||
        !filtersSummary ||
        !applyFilters ||
        !resetFilters ||
        !fullscreenBtn ||
        !exportPdf ||
        !exportXlsx) {
        return;
    }
    const storageKey = 'pm:powerbi:state';
    const saved = localStorage.getItem(storageKey);
    const state = saved
        ? (() => {
            try {
                return JSON.parse(saved);
            }
            catch {
                return { reportId: payload.reports[0]?.id ?? 'exec', project: 'Todos', team: 'Todos', period: 'Últimos 30 días' };
            }
        })()
        : { reportId: payload.reports[0]?.id ?? 'exec', project: 'Todos', team: 'Todos', period: 'Últimos 30 días' };
    reportSelect.innerHTML = payload.reports.map((r) => `<option value="${escapeHtml(r.id)}">${escapeHtml(r.name)}</option>`).join('');
    filterProject.innerHTML = payload.filters.projects.map((p) => `<option value="${escapeHtml(p)}">${escapeHtml(p)}</option>`).join('');
    filterTeam.innerHTML = payload.filters.teams.map((t) => `<option value="${escapeHtml(t)}">${escapeHtml(t)}</option>`).join('');
    filterPeriod.innerHTML = payload.filters.periods.map((p) => `<option value="${escapeHtml(p)}">${escapeHtml(p)}</option>`).join('');
    const getReport = () => payload.reports.find((r) => r.id === reportSelect.value) ?? payload.reports[0];
    const renderKPIs = (report) => {
        kpiRow.innerHTML = report.kpis
            .map((k) => {
            const cls = toneClasses(k.tone);
            const deltaTone = k.delta >= 0 ? 'text-emerald-700 dark:text-emerald-200' : 'text-rose-700 dark:text-rose-200';
            return `
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="truncate text-sm font-medium text-slate-600 dark:text-slate-400">${escapeHtml(k.label)}</p>
                <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">${escapeHtml(fmt(k.value))}</p>
              </div>
              <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${cls}">
                ${k.delta >= 0 ? '+' : ''}${escapeHtml(fmt(k.delta))}%
              </span>
            </div>
            <p class="mt-3 text-xs ${deltaTone} font-semibold">Variación vs. periodo anterior</p>
          </div>
        `;
        })
            .join('');
    };
    const updateFiltersSummary = () => {
        filtersSummary.textContent = `${filterProject.value} · ${filterTeam.value} · ${filterPeriod.value}`;
    };
    const persist = () => {
        localStorage.setItem(storageKey, JSON.stringify({ reportId: reportSelect.value, project: filterProject.value, team: filterTeam.value, period: filterPeriod.value }));
    };
    const renderEmbed = () => {
        const report = getReport();
        if (!report)
            return;
        reportTitle.textContent = report.name;
        reportDesc.textContent = report.desc;
        embedMeta.textContent = `Embedded preview · ${filterProject.value} · ${filterTeam.value} · ${filterPeriod.value}`;
        renderKPIs(report);
        const theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        const html = buildEmbedHTML(report, { project: filterProject.value, team: filterTeam.value, period: filterPeriod.value }, theme);
        frame.srcdoc = html;
    };
    const applyStateToUI = () => {
        reportSelect.value = state.reportId;
        filterProject.value = state.project;
        filterTeam.value = state.team;
        filterPeriod.value = state.period;
        updateFiltersSummary();
        renderEmbed();
    };
    applyStateToUI();
    reportSelect.addEventListener('change', () => {
        persist();
        renderEmbed();
        toast('Reporte', 'Se actualizó el reporte seleccionado.');
    });
    const onFiltersChanged = () => updateFiltersSummary();
    filterProject.addEventListener('change', onFiltersChanged);
    filterTeam.addEventListener('change', onFiltersChanged);
    filterPeriod.addEventListener('change', onFiltersChanged);
    applyFilters.addEventListener('click', () => {
        persist();
        renderEmbed();
        toast('Filtros', 'Se aplicaron filtros al reporte.');
    });
    resetFilters.addEventListener('click', () => {
        filterProject.value = payload.filters.projects[0] ?? 'Todos';
        filterTeam.value = payload.filters.teams[0] ?? 'Todos';
        filterPeriod.value = payload.filters.periods[2] ?? (payload.filters.periods[0] ?? 'Últimos 30 días');
        updateFiltersSummary();
        persist();
        renderEmbed();
        toast('Filtros', 'Se restablecieron filtros.');
    });
    const requestFullscreen = async () => {
        if (document.fullscreenElement) {
            await document.exitFullscreen();
            return;
        }
        await embedContainer.requestFullscreen();
    };
    fullscreenBtn.addEventListener('click', async () => {
        try {
            await requestFullscreen();
            toast('Pantalla completa', document.fullscreenElement ? 'Modo pantalla completa activado.' : 'Modo pantalla completa desactivado.');
        }
        catch {
            toast('Pantalla completa', 'No disponible en este navegador/entorno.');
        }
    });
    document.addEventListener('fullscreenchange', () => {
        fullscreenBtn.textContent = document.fullscreenElement ? 'Salir de pantalla completa' : 'Pantalla completa';
    });
    exportPdf.addEventListener('click', () => {
        const report = getReport();
        const content = `Export PDF (demo)\nReporte: ${report?.name ?? ''}\nProyecto: ${filterProject.value}\nEquipo: ${filterTeam.value}\nPeriodo: ${filterPeriod.value}\nFecha: ${new Date().toISOString()}\n`;
        download(`powerbi-${report?.id ?? 'report'}.pdf.txt`, content, 'text/plain;charset=utf-8');
        toast('Exportación', 'Se generó una exportación PDF (demo).');
    });
    exportXlsx.addEventListener('click', () => {
        const report = getReport();
        const header = ['kpi', 'value', 'delta_pct'];
        const lines = [header.join(',')].concat((report?.kpis ?? []).map((k) => [k.label, String(k.value), String(k.delta)].join(',')));
        download(`powerbi-${report?.id ?? 'report'}.xlsx.csv`, lines.join('\n'), 'text/csv;charset=utf-8');
        toast('Exportación', 'Se descargó un archivo Excel (CSV) de KPIs (demo).');
    });
};
try {
    window.addEventListener('DOMContentLoaded', init);
}
catch { }

