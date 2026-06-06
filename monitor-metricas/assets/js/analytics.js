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
    const el = qs('#pmAnalyticsData');
    if (!el?.textContent)
        return { projects: [], teams: [] };
    try {
        return JSON.parse(el.textContent);
    }
    catch {
        return { projects: [], teams: [] };
    }
};
const toDateInputValue = (d) => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${dd}`;
};
const parseDateOnly = (value) => {
    if (!value)
        return null;
    const d = new Date(`${value}T00:00:00`);
    const t = d.getTime();
    return Number.isNaN(t) ? null : t;
};
const daysBetween = (from, to) => Math.max(1, Math.round((to - from) / (24 * 60 * 60 * 1000)) + 1);
const hashSeed = (s) => {
    let h = 2166136261;
    for (let i = 0; i < s.length; i++) {
        h ^= s.charCodeAt(i);
        h = Math.imul(h, 16777619);
    }
    return h >>> 0;
};
const rng = (seed) => {
    let x = seed >>> 0;
    return () => {
        x = (x * 1664525 + 1013904223) >>> 0;
        return x / 0xffffffff;
    };
};
const seriesSmooth = (values, amount) => {
    const out = [...values];
    for (let i = 1; i < out.length - 1; i++) {
        out[i] = out[i] * (1 - amount) + ((out[i - 1] + out[i] + out[i + 1]) / 3) * amount;
    }
    return out;
};
const palette = (key) => {
    const dark = document.documentElement.classList.contains('dark');
    const map = {
        pm: { stroke: dark ? '#93c5fd' : '#155fe0', fill: dark ? 'rgba(31, 122, 255, 0.14)' : 'rgba(21, 95, 224, 0.10)', muted: dark ? '#334155' : '#e2e8f0' },
        sky: { stroke: dark ? '#7dd3fc' : '#0284c7', fill: dark ? 'rgba(125, 211, 252, 0.14)' : 'rgba(2, 132, 199, 0.10)', muted: dark ? '#334155' : '#e2e8f0' },
        emerald: { stroke: dark ? '#34d399' : '#10b981', fill: dark ? 'rgba(52, 211, 153, 0.12)' : 'rgba(16, 185, 129, 0.10)', muted: dark ? '#334155' : '#e2e8f0' },
        rose: { stroke: dark ? '#fb7185' : '#f43f5e', fill: dark ? 'rgba(251, 113, 133, 0.12)' : 'rgba(244, 63, 94, 0.10)', muted: dark ? '#334155' : '#e2e8f0' },
        slate: { stroke: dark ? '#94a3b8' : '#64748b', fill: dark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(100, 116, 139, 0.10)', muted: dark ? '#334155' : '#e2e8f0' },
    };
    return map[key];
};
const renderLine = (svg, series, height = 240) => {
    const w = 600;
    const h = height;
    const padX = 18;
    const padY = 20;
    const all = series.flatMap((s) => s.values);
    const min = Math.min(...all);
    const max = Math.max(...all);
    const range = max === min ? 1 : max - min;
    const n = Math.max(2, ...series.map((s) => s.values.length));
    const step = (w - padX * 2) / (n - 1);
    const y = (v) => padY + (1 - (v - min) / range) * (h - padY * 2);
    const x = (i) => padX + i * step;
    const gridLines = 4;
    const grid = Array.from({ length: gridLines + 1 })
        .map((_, i) => {
        const yy = padY + (i * (h - padY * 2)) / gridLines;
        return `<line x1="${padX}" y1="${yy}" x2="${w - padX}" y2="${yy}" stroke="${palette('slate').muted}" stroke-width="1" />`;
    })
        .join('');
    const paths = series
        .map((s) => {
        const p = s.values.reduce((acc, v, i) => acc + (i === 0 ? `M ${x(i)} ${y(v)}` : ` L ${x(i)} ${y(v)}`), '');
        const pal = palette(s.color);
        const dashed = s.dashed ? 'stroke-dasharray="6 6"' : '';
        const fill = s.fill ? `<path d="${p} L ${x(s.values.length - 1)} ${h - padY} L ${x(0)} ${h - padY} Z" fill="${pal.fill}"></path>` : '';
        return `${fill}<path d="${p}" fill="none" stroke="${pal.stroke}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" ${dashed}></path>`;
    })
        .join('');
    svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
    svg.innerHTML = `<g>${grid}</g>${paths}`;
};
const renderBars = (svg, valuesA, valuesB, colorA, colorB) => {
    const w = 600;
    const h = 240;
    const pad = 18;
    const all = valuesB ? [...valuesA, ...valuesB] : valuesA;
    const max = Math.max(...all, 1);
    const n = Math.max(1, valuesA.length);
    const groupW = (w - pad * 2) / n;
    const barW = valuesB ? groupW * 0.35 : groupW * 0.55;
    const gap = valuesB ? groupW * 0.1 : groupW * 0.15;
    const palA = palette(colorA);
    const palB = palette(colorB);
    const gridLines = 4;
    const grid = Array.from({ length: gridLines + 1 })
        .map((_, i) => {
        const yy = pad + (i * (h - pad * 2)) / gridLines;
        return `<line x1="${pad}" y1="${yy}" x2="${w - pad}" y2="${yy}" stroke="${palette('slate').muted}" stroke-width="1" />`;
    })
        .join('');
    const bars = valuesA
        .map((v, i) => {
        const x0 = pad + i * groupW;
        const hA = ((h - pad * 2) * v) / max;
        const yA = h - pad - hA;
        const xA = x0 + (valuesB ? gap : (groupW - barW) / 2);
        const rA = `<rect x="${xA}" y="${yA}" width="${barW}" height="${hA}" rx="10" fill="${palA.fill}" stroke="${palA.stroke}" stroke-width="2"></rect>`;
        if (!valuesB)
            return rA;
        const vb = valuesB[i] ?? 0;
        const hB = ((h - pad * 2) * vb) / max;
        const yB = h - pad - hB;
        const xB = x0 + gap + barW + gap;
        const rB = `<rect x="${xB}" y="${yB}" width="${barW}" height="${hB}" rx="10" fill="${palB.fill}" stroke="${palB.stroke}" stroke-width="2" stroke-dasharray="6 6"></rect>`;
        return rA + rB;
    })
        .join('');
    svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
    svg.innerHTML = `<g>${grid}</g>${bars}`;
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
const fmt = (n, digits = 1) => {
    const v = Number.isFinite(n) ? n : 0;
    return v.toLocaleString('es-ES', { maximumFractionDigits: digits, minimumFractionDigits: digits });
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
            render();
        });
    }
    wireSidebar();
    const payload = readPayload();
    const projectSelect = qs('#projectSelect');
    const teamSelect = qs('#teamSelect');
    const dateFrom = qs('#dateFrom');
    const dateTo = qs('#dateTo');
    const compareToggle = qs('#compareToggle');
    const summaryPill = qs('#summaryPill');
    const exportBtn = qs('#exportBtn');
    const exportMenu = qs('#exportMenu');
    const exportCsv = qs('#exportCsv');
    const exportJson = qs('#exportJson');
    const resetFilters = qs('#resetFilters');
    const chartBurnDown = qs('#chartBurnDown');
    const chartBurnUp = qs('#chartBurnUp');
    const chartProductivity = qs('#chartProductivity');
    const chartVelocity = qs('#chartVelocity');
    const chartLeadTime = qs('#chartLeadTime');
    const chartCycleTime = qs('#chartCycleTime');
    const productivityBadge = qs('#productivityBadge');
    const velocityBadge = qs('#velocityBadge');
    const leadBadge = qs('#leadBadge');
    const cycleBadge = qs('#cycleBadge');
    if (!projectSelect ||
        !teamSelect ||
        !dateFrom ||
        !dateTo ||
        !compareToggle ||
        !summaryPill ||
        !exportBtn ||
        !exportMenu ||
        !exportCsv ||
        !exportJson ||
        !resetFilters ||
        !chartBurnDown ||
        !chartBurnUp ||
        !chartProductivity ||
        !chartVelocity ||
        !chartLeadTime ||
        !chartCycleTime ||
        !productivityBadge ||
        !velocityBadge ||
        !leadBadge ||
        !cycleBadge) {
        return;
    }
    projectSelect.innerHTML = payload.projects.map((p, idx) => `<option value="${p.id}" ${idx === 0 ? 'selected' : ''}>${p.name}</option>`).join('');
    teamSelect.innerHTML = `<option value="all">Todos</option>` + payload.teams.map((t) => `<option value="${t.id}">${t.name}</option>`).join('');
    const now = new Date();
    const start = new Date(now);
    start.setDate(start.getDate() - 29);
    dateFrom.value = toDateInputValue(start);
    dateTo.value = toDateInputValue(now);
    const toggleExportMenu = (open) => {
        exportBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        exportMenu.classList.toggle('hidden', !open);
    };
    exportBtn.addEventListener('click', () => toggleExportMenu(exportMenu.classList.contains('hidden')));
    document.addEventListener('click', (e) => {
        const t = e.target;
        if (!t)
            return;
        if (exportBtn.contains(t) || exportMenu.contains(t))
            return;
        toggleExportMenu(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape')
            toggleExportMenu(false);
    });
    const generate = (seedKey, count, profile) => {
        const r = rng(hashSeed(seedKey));
        const values = [];
        if (profile === 'burndown') {
            const startV = 220 + Math.round(r() * 180);
            let cur = startV;
            for (let i = 0; i < count; i++) {
                const dec = (startV / count) * (0.6 + r() * 0.8);
                cur = i === 0 ? startV : Math.max(0, cur - dec);
                values.push(cur);
            }
            return seriesSmooth(values, 0.35);
        }
        if (profile === 'burnup') {
            const scope = 240 + Math.round(r() * 180);
            let done = Math.round(scope * (0.15 + r() * 0.15));
            for (let i = 0; i < count; i++) {
                const inc = (scope / count) * (0.6 + r() * 0.9);
                done = i === 0 ? done : Math.min(scope, done + inc);
                values.push(done);
            }
            return seriesSmooth(values, 0.25);
        }
        if (profile === 'productivity') {
            for (let i = 0; i < count; i++)
                values.push(8 + r() * 12 + Math.sin(i / 4) * 2);
            return seriesSmooth(values, 0.45).map((v) => Math.max(0, v));
        }
        if (profile === 'velocity') {
            for (let i = 0; i < count; i++)
                values.push(24 + r() * 18 + Math.sin(i / 2) * 3);
            return seriesSmooth(values, 0.25).map((v) => Math.max(0, v));
        }
        if (profile === 'lead') {
            for (let i = 0; i < count; i++)
                values.push(9 + r() * 10 + Math.sin(i / 3) * 1.8);
            return seriesSmooth(values, 0.35).map((v) => Math.max(1, v));
        }
        for (let i = 0; i < count; i++)
            values.push(4 + r() * 8 + Math.sin(i / 3) * 1.2);
        return seriesSmooth(values, 0.35).map((v) => Math.max(0.5, v));
    };
    const state = () => {
        const fromT = parseDateOnly(dateFrom.value);
        const toT = parseDateOnly(dateTo.value);
        const from = fromT ?? Date.now() - 29 * 24 * 60 * 60 * 1000;
        const to = toT ?? Date.now();
        const days = daysBetween(from, to);
        return { projectId: projectSelect.value, teamId: teamSelect.value, from, to, days, compare: compareToggle.checked };
    };
    const buildExport = () => {
        const s = state();
        const base = `${s.projectId}:${s.teamId}:${s.from}:${s.to}`;
        const burndown = generate(`${base}:burndown`, s.days, 'burndown');
        const burnup = generate(`${base}:burnup`, s.days, 'burnup');
        const productivity = generate(`${base}:productivity`, s.days, 'productivity');
        const lead = generate(`${base}:lead`, s.days, 'lead');
        const cycle = generate(`${base}:cycle`, s.days, 'cycle');
        return { s, burndown, burnup, productivity, lead, cycle };
    };
    const exportCSV = () => {
        const { s, burndown, burnup, productivity, lead, cycle } = buildExport();
        const header = ['idx', 'burndown_remaining', 'burnup_done', 'productivity', 'lead_time_days', 'cycle_time_days'];
        const lines = [header.join(',')];
        for (let i = 0; i < s.days; i++) {
            lines.push([i + 1, burndown[i].toFixed(2), burnup[i].toFixed(2), productivity[i].toFixed(2), lead[i].toFixed(2), cycle[i].toFixed(2)].join(','));
        }
        download(`analytics-${s.projectId}-${toDateInputValue(new Date(s.to))}.csv`, lines.join('\n'), 'text/csv;charset=utf-8');
        toast('Exportación', 'Se descargó el CSV con series y métricas.');
    };
    const exportJSON = () => {
        const { s, burndown, burnup, productivity, lead, cycle } = buildExport();
        const obj = { filters: s, series: { burndown, burnup, productivity, lead, cycle } };
        download(`analytics-${s.projectId}-${toDateInputValue(new Date(s.to))}.json`, JSON.stringify(obj, null, 2), 'application/json');
        toast('Exportación', 'Se descargó el JSON con filtros y series.');
    };
    exportCsv.addEventListener('click', () => {
        exportCSV();
        exportMenu.classList.add('hidden');
        exportBtn.setAttribute('aria-expanded', 'false');
    });
    exportJson.addEventListener('click', () => {
        exportJSON();
        exportMenu.classList.add('hidden');
        exportBtn.setAttribute('aria-expanded', 'false');
    });
    resetFilters.addEventListener('click', () => {
        projectSelect.selectedIndex = 0;
        teamSelect.value = 'all';
        compareToggle.checked = false;
        dateFrom.value = toDateInputValue(start);
        dateTo.value = toDateInputValue(now);
        render();
        toast('Filtros', 'Se restablecieron los filtros.');
    });
    const render = () => {
        const s = state();
        const rangeDays = s.days;
        summaryPill.textContent = `${rangeDays} días · ${projectSelect.options[projectSelect.selectedIndex]?.text ?? s.projectId} · ${teamSelect.value === 'all' ? 'Todos' : teamSelect.options[teamSelect.selectedIndex]?.text ?? s.teamId}`;
        const base = `${s.projectId}:${s.teamId}:${s.from}:${s.to}`;
        const burnDown = generate(`${base}:burndown`, rangeDays, 'burndown');
        const burnUp = generate(`${base}:burnup`, rangeDays, 'burnup');
        const productivity = generate(`${base}:productivity`, rangeDays, 'productivity');
        const sprintCount = Math.max(4, Math.round(rangeDays / 7));
        const velocity = generate(`${base}:velocity`, sprintCount, 'velocity');
        const lead = generate(`${base}:lead`, Math.max(8, Math.round(rangeDays / 4)), 'lead');
        const cycle = generate(`${base}:cycle`, Math.max(8, Math.round(rangeDays / 4)), 'cycle');
        const prevBase = `${s.projectId}:${s.teamId}:${s.from - (s.to - s.from)}:${s.to - (s.to - s.from)}`;
        const prevBurnDown = s.compare ? generate(`${prevBase}:burndown`, rangeDays, 'burndown') : null;
        const prevBurnUp = s.compare ? generate(`${prevBase}:burnup`, rangeDays, 'burnup') : null;
        const prevProductivity = s.compare ? generate(`${prevBase}:productivity`, rangeDays, 'productivity') : null;
        const prevVelocity = s.compare ? generate(`${prevBase}:velocity`, sprintCount, 'velocity') : null;
        const prevLead = s.compare ? generate(`${prevBase}:lead`, Math.max(8, Math.round(rangeDays / 4)), 'lead') : null;
        const prevCycle = s.compare ? generate(`${prevBase}:cycle`, Math.max(8, Math.round(rangeDays / 4)), 'cycle') : null;
        renderLine(chartBurnDown, [
            { label: 'Actual', values: burnDown, color: 'pm', fill: true },
            ...(prevBurnDown ? [{ label: 'Anterior', values: prevBurnDown, color: 'slate', dashed: true }] : []),
        ]);
        renderLine(chartBurnUp, [
            { label: 'Actual', values: burnUp, color: 'sky', fill: true },
            ...(prevBurnUp ? [{ label: 'Anterior', values: prevBurnUp, color: 'slate', dashed: true }] : []),
        ]);
        renderLine(chartProductivity, [
            { label: 'Actual', values: productivity, color: 'emerald', fill: true },
            ...(prevProductivity ? [{ label: 'Anterior', values: prevProductivity, color: 'slate', dashed: true }] : []),
        ]);
        renderBars(chartVelocity, velocity, prevVelocity, 'pm', 'slate');
        renderLine(chartLeadTime, [
            { label: 'Actual', values: lead, color: 'pm', fill: true },
            ...(prevLead ? [{ label: 'Anterior', values: prevLead, color: 'slate', dashed: true }] : []),
        ]);
        renderLine(chartCycleTime, [
            { label: 'Actual', values: cycle, color: 'rose', fill: true },
            ...(prevCycle ? [{ label: 'Anterior', values: prevCycle, color: 'slate', dashed: true }] : []),
        ]);
        const avg = (arr) => arr.reduce((a, b) => a + b, 0) / Math.max(1, arr.length);
        const deltaPct = (cur, prev) => (prev === null || prev === 0 ? null : ((cur - prev) / prev) * 100);
        const prodAvg = avg(productivity);
        const prodPrev = prevProductivity ? avg(prevProductivity) : null;
        const prodDelta = deltaPct(prodAvg, prodPrev);
        productivityBadge.textContent = prodDelta === null ? `Promedio: ${fmt(prodAvg, 1)}` : `Promedio: ${fmt(prodAvg, 1)} · ${prodDelta >= 0 ? '+' : ''}${fmt(prodDelta, 1)}%`;
        const velAvg = avg(velocity);
        const velPrev = prevVelocity ? avg(prevVelocity) : null;
        const velDelta = deltaPct(velAvg, velPrev);
        velocityBadge.textContent = velDelta === null ? `Avg: ${fmt(velAvg, 0)}` : `Avg: ${fmt(velAvg, 0)} · ${velDelta >= 0 ? '+' : ''}${fmt(velDelta, 1)}%`;
        const leadAvg = avg(lead);
        const leadPrev = prevLead ? avg(prevLead) : null;
        const leadDelta = deltaPct(leadAvg, leadPrev);
        leadBadge.textContent = leadDelta === null ? `Avg: ${fmt(leadAvg, 1)} días` : `Avg: ${fmt(leadAvg, 1)} · ${leadDelta >= 0 ? '+' : ''}${fmt(leadDelta, 1)}%`;
        const cycleAvg = avg(cycle);
        const cyclePrev = prevCycle ? avg(prevCycle) : null;
        const cycleDelta = deltaPct(cycleAvg, cyclePrev);
        cycleBadge.textContent = cycleDelta === null ? `Avg: ${fmt(cycleAvg, 1)} días` : `Avg: ${fmt(cycleAvg, 1)} · ${cycleDelta >= 0 ? '+' : ''}${fmt(cycleDelta, 1)}%`;
    };
    const onChange = () => render();
    projectSelect.addEventListener('change', onChange);
    teamSelect.addEventListener('change', onChange);
    dateFrom.addEventListener('change', onChange);
    dateTo.addEventListener('change', onChange);
    compareToggle.addEventListener('change', onChange);
    render();
};
try {
    window.addEventListener('DOMContentLoaded', init);
}
catch { }

