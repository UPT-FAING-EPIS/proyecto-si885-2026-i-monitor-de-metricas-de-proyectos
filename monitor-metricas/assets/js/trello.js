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
const setResultTone = (success) => {
    const icon = qs('#resultModalIcon');
    if (!icon)
        return;
    icon.className = `grid h-12 w-12 place-items-center rounded-2xl ${success
        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
        : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300'}`;
    icon.innerHTML = success
        ? '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        : '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v5m0 3h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.72 3h16.92a2 2 0 0 0 1.72-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
};
const showResultModal = (success, title, body) => {
    const modal = qs('#resultModal');
    const titleEl = qs('#resultModalTitle');
    const bodyEl = qs('#resultModalBody');
    if (!modal || !titleEl || !bodyEl) {
        toast(title, body);
        return;
    }
    titleEl.textContent = title;
    bodyEl.textContent = body;
    setResultTone(success);
    modal.classList.remove('hidden');
};
const closeResultModal = () => {
    qs('#resultModal')?.classList.add('hidden');
};
const getAuthorizeUrl = () => {
    const url = window.__PM?.trelloAuthorizeUrl;
    return typeof url === 'string' ? url.trim() : '';
};
const clearTokenHash = () => {
    if (!window.location.hash)
        return;
    history.replaceState(null, '', `${window.location.pathname}${window.location.search}`);
};
const popupFeatures = () => {
    const width = 760;
    const height = 820;
    const left = Math.max(0, Math.round((window.screen.width - width) / 2));
    const top = Math.max(0, Math.round((window.screen.height - height) / 2));
    return `popup=yes,width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`;
};
const openAuthorizePopup = () => {
    const authorizeUrl = getAuthorizeUrl();
    if (!authorizeUrl) {
        showResultModal(false, 'Autorizacion no disponible', 'Falta configurar la URL de autorizacion de Trello en el entorno.');
        return;
    }
    const popup = window.open(authorizeUrl, 'pm-trello-auth', popupFeatures());
    if (!popup) {
        window.location.href = authorizeUrl;
        return;
    }
    popup.focus();
};
const readTokenFromHash = () => {
    const hash = window.location.hash.startsWith('#') ? window.location.hash.slice(1) : window.location.hash;
    if (!hash)
        return '';
    const params = new URLSearchParams(hash);
    return String(params.get('token') ?? '').trim();
};
const connectWithToken = async (token, source = 'manual') => {
    const cleanToken = String(token ?? '').trim();
    if (!cleanToken) {
        if (source === 'manual') {
            toast('Token requerido', 'Ingresa el token para conectar Trello.');
        }
        return;
    }
    setBusy(true);
    try {
        await api('/api/trello/connect', { method: 'POST', body: { token: cleanToken } });
        const input = qs('#trelloToken');
        if (input)
            input.value = '';
        await refresh();
        showResultModal(true, 'Trello conectado', source === 'oauth'
            ? 'La cuenta fue autorizada en Trello y la conexion se completo automaticamente.'
            : 'La cuenta fue validada y ya puedes sincronizar datos para el monitoreo de metricas del proyecto.');
    }
    catch (err) {
        showResultModal(false, source === 'oauth' ? 'Autorizacion fallida' : 'Conexion fallida', err?.message ? String(err.message) : 'No se pudo conectar Trello.');
    }
    finally {
        setBusy(false);
    }
};
const wireAuthorizeFlow = () => {
    const authorizeBtn = qs('#authorizeTrelloBtn');
    authorizeBtn?.addEventListener('click', openAuthorizePopup);
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin)
            return;
        const data = event.data ?? {};
        if (data?.type !== 'pm:trello-authorized')
            return;
        const token = typeof data.token === 'string' ? data.token : '';
        if (!token)
            return;
        void connectWithToken(token, 'oauth');
    });
};
const handleAuthorizeCallback = () => {
    const token = readTokenFromHash();
    if (!token)
        return false;
    clearTokenHash();
    if (window.opener && window.opener !== window) {
        try {
            window.opener.postMessage({ type: 'pm:trello-authorized', token }, window.location.origin);
            document.body.innerHTML = '<div style="font-family:Inter,Arial,sans-serif;padding:32px;color:#0f172a"><h1 style="font-size:20px;margin:0 0 12px">Trello autorizado</h1><p style="margin:0 0 16px">La autorizacion fue enviada a Project Metrics Monitor. Puedes cerrar esta ventana.</p></div>';
            window.setTimeout(() => window.close(), 400);
            return true;
        }
        catch {
        }
    }
    void connectWithToken(token, 'oauth');
    return true;
};
const formatDate = (iso) => {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime()))
        return '—';
    return d.toLocaleString('es-ES', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};
const api = async (path, options = {}) => {
    const csrf = window.__PM?.csrf ?? '';
    const url = new URL(path, window.location.origin);
    if (options.query) {
        Object.entries(options.query).forEach(([k, v]) => {
            if (v === undefined || v === null)
                return;
            url.searchParams.set(k, String(v));
        });
    }
    const hasBody = options.body !== undefined && options.body !== null;
    const resp = await fetch(url.toString(), {
        method: options.method ?? (hasBody ? 'POST' : 'GET'),
        headers: {
            Accept: 'application/json',
            ...(hasBody ? { 'Content-Type': 'application/json' } : {}),
        },
        credentials: 'same-origin',
        body: hasBody ? JSON.stringify({ ...options.body, csrf }) : undefined,
    });
    const payload = await resp.json().catch(() => null);
    if (!resp.ok || !payload || payload.ok !== true) {
        const msg = payload?.error ? String(payload.error) : `Error HTTP ${resp.status}`;
        throw new Error(msg);
    }
    return payload.data;
};
const selectionKey = 'pm:trello:selectedWorkspaces';
const loadSelection = () => {
    try {
        const raw = localStorage.getItem(selectionKey);
        if (!raw)
            return new Set();
        const parsed = JSON.parse(raw);
        if (!Array.isArray(parsed))
            return new Set();
        return new Set(parsed.map((v) => String(v)).filter((v) => v.trim() !== ''));
    }
    catch {
        return new Set();
    }
};
const saveSelection = (set) => {
    localStorage.setItem(selectionKey, JSON.stringify(Array.from(set)));
};
const setBusy = (busy) => {
    const toggle = (el) => {
        if (!el)
            return;
        if (busy) {
            el.dataset.prevDisabled = el.disabled ? '1' : '0';
            el.disabled = true;
            return;
        }
        el.disabled = el.dataset.prevDisabled === '1';
        delete el.dataset.prevDisabled;
    };
    const syncNow = qs('#syncNowBtn');
    const disconnect = qs('#disconnectBtn');
    const connectTop = qs('#connectBtnTop');
    const connect = qs('#connectBtn');
    const submit = qs('#connectSubmit');
    const authorize = qs('#authorizeTrelloBtn');
    toggle(syncNow);
    toggle(disconnect);
    toggle(connectTop);
    toggle(connect);
    toggle(submit);
    toggle(authorize);
};
const renderConnection = (status) => {
    const connected = Boolean(status?.connected);
    const pill = qs('#connectionPill');
    const dot = qs('#connectionDot');
    const text = qs('#connectionText');
    const connectedPanel = qs('#connectedPanel');
    const disconnectedPanel = qs('#disconnectedPanel');
    const connectTop = qs('#connectBtnTop');
    const syncNow = qs('#syncNowBtn');
    const disconnect = qs('#disconnectBtn');
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
    if (connectTop)
        connectTop.classList.toggle('hidden', connected);
    if (syncNow)
        syncNow.disabled = !connected;
    if (disconnect)
        disconnect.disabled = !connected;
    const lastSync = qs('#lastSync');
    if (lastSync)
        lastSync.textContent = status?.last_sync_at ? formatDate(status.last_sync_at) : '—';
};
const renderMember = (member) => {
    const name = qs('#accountName');
    const email = qs('#accountEmail');
    const fullName = member?.fullName ? String(member.fullName) : '';
    const username = member?.username ? String(member.username) : '';
    const label = fullName || username || 'Trello';
    if (name)
        name.textContent = label;
    if (email)
        email.textContent = member?.email ? String(member.email) : '—';
};
const renderWorkspaces = (workspaces) => {
    const container = qs('#workspaces');
    const count = qs('#workspaceCount');
    if (!container || !count)
        return;
    const selection = loadSelection();
    if (selection.size === 0) {
        workspaces.forEach((w) => selection.add(String(w.trello_id)));
        saveSelection(selection);
    }
    count.textContent = String(workspaces.length);
    container.innerHTML = workspaces
        .map((w) => {
        const id = String(w.trello_id);
        const checked = selection.has(id) ? 'checked' : '';
        const inputId = `ws_${id}`;
        const name = String(w.name ?? id);
        return `
        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition hover:bg-white dark:border-slate-800 dark:bg-slate-950 dark:hover:bg-slate-900">
          <input id="${inputId}" data-wsid="${id}" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-pm-600 focus:ring-pm-500 dark:border-slate-700 dark:bg-slate-950 dark:text-pm-400 dark:focus:ring-pm-400" ${checked} />
          <span class="min-w-0">
            <span class="block truncate font-semibold text-slate-900 dark:text-white">${name}</span>
            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">${id}</span>
          </span>
        </label>
      `;
    })
        .join('');
    container.querySelectorAll('input[type="checkbox"][data-wsid]').forEach((input) => {
        input.addEventListener('change', () => {
            const id = input.getAttribute('data-wsid') ?? '';
            const next = loadSelection();
            if (input.checked)
                next.add(id);
            else
                next.delete(id);
            saveSelection(next);
            toast('Preferencias guardadas', 'Se actualizó la selección de workspaces.');
        });
    });
};
const renderMetrics = (metrics) => {
    const summary = metrics?.summary ?? {};
    const latestSync = metrics?.latest_sync ?? null;
    const boards = Array.isArray(metrics?.boards) ? metrics.boards : [];
    const recentLogs = Array.isArray(metrics?.recent_logs) ? metrics.recent_logs : [];
    const setText = (selector, value) => {
        const el = qs(selector);
        if (el)
            el.textContent = String(value);
    };
    setText('#metricTotalTasks', Number(summary.total_tasks) || 0);
    setText('#metricCompletedTasks', Number(summary.completed_tasks) || 0);
    setText('#metricPendingTasks', Number(summary.pending_tasks) || 0);
    setText('#metricOverdueTasks', Number(summary.overdue_tasks) || 0);
    setText('#metricBoards', Number(summary.boards) || 0);
    setText('#metricProgress', `${Number(summary.progress_percentage || 0).toFixed(1)}%`);
    const syncMeta = qs('#syncMeta');
    if (syncMeta) {
        if (latestSync?.finished_at || latestSync?.started_at) {
            const label = latestSync?.finished_at ? formatDate(latestSync.finished_at) : formatDate(latestSync.started_at);
            syncMeta.textContent = `Ultima sync: ${label}`;
        }
        else {
            syncMeta.textContent = 'Sin sincronizaciones';
        }
    }
    const latestSyncDetail = qs('#latestSyncDetail');
    if (latestSyncDetail) {
        latestSyncDetail.textContent = latestSync
            ? `Ultima ejecucion ${latestSync.sync_type} · Boards ${latestSync.boards_processed} · Lists ${latestSync.lists_processed} · Cards ${latestSync.cards_processed} · Errores ${latestSync.errors_count}.`
            : 'Aun no hay sincronizaciones registradas para este usuario.';
    }
    const boardMetrics = qs('#boardMetrics');
    if (boardMetrics) {
        if (boards.length === 0) {
            boardMetrics.innerHTML = '<div class="px-4 py-5 text-sm text-slate-500 dark:text-slate-400">Sin datos de boards. Conecta Trello y ejecuta una sincronizacion para calcular metricas.</div>';
        }
        else {
            boardMetrics.innerHTML = boards
                .map((board) => {
                const workspace = board.workspace_name ? `<p class="text-xs text-slate-500 dark:text-slate-400">${String(board.workspace_name)}</p>` : '';
                return `
              <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">${String(board.name ?? 'Board')}</p>
                  ${workspace}
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs sm:grid-cols-4">
                  <span class="rounded-full bg-slate-50 px-3 py-1.5 font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Total ${Number(board.total_tasks) || 0}</span>
                  <span class="rounded-full bg-emerald-50 px-3 py-1.5 font-semibold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20">Comp. ${Number(board.completed_tasks) || 0}</span>
                  <span class="rounded-full bg-amber-50 px-3 py-1.5 font-semibold text-amber-700 ring-1 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20">Pend. ${Number(board.pending_tasks) || 0}</span>
                  <span class="rounded-full bg-rose-50 px-3 py-1.5 font-semibold text-rose-700 ring-1 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/20">Venc. ${Number(board.overdue_tasks) || 0}</span>
                </div>
              </div>
            `;
            })
                .join('');
        }
    }
    const recentLogsEl = qs('#recentLogs');
    if (recentLogsEl) {
        if (recentLogs.length === 0) {
            recentLogsEl.innerHTML = '<div class="px-4 py-5 text-sm text-slate-500 dark:text-slate-400">Aun no existen logs de sincronizacion para este usuario.</div>';
        }
        else {
            recentLogsEl.innerHTML = recentLogs
                .map((log) => {
                const finishedAt = log.finished_at ? formatDate(log.finished_at) : 'En proceso';
                const tone = Number(log.errors_count) > 0
                    ? 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20'
                    : 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20';
                return `
              <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">Sync ${String(log.sync_type ?? 'all')}</p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Inicio ${formatDate(String(log.started_at ?? ''))} · Fin ${finishedAt}</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                  <span class="rounded-full bg-slate-50 px-3 py-1.5 font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Boards ${Number(log.boards_processed) || 0}</span>
                  <span class="rounded-full bg-slate-50 px-3 py-1.5 font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Lists ${Number(log.lists_processed) || 0}</span>
                  <span class="rounded-full bg-slate-50 px-3 py-1.5 font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800">Cards ${Number(log.cards_processed) || 0}</span>
                  <span class="rounded-full px-3 py-1.5 font-semibold ring-1 ${tone}">Errores ${Number(log.errors_count) || 0}</span>
                </div>
              </div>
            `;
            })
                .join('');
        }
    }
};
const focusConnectForm = () => {
    const card = qs('#connectCard');
    if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    const authorize = qs('#authorizeTrelloBtn');
    if (authorize) {
        window.setTimeout(() => authorize.focus(), 150);
    }
};
const refresh = async () => {
    const status = await api('/api/trello/status');
    renderConnection(status);
    const [metrics, member, workspaces] = await Promise.all([
        api('/api/trello/metrics').catch(() => null),
        status?.connected ? api('/api/trello/member').catch(() => null) : Promise.resolve(null),
        status?.connected ? api('/api/trello/workspaces').catch(() => []) : Promise.resolve([]),
    ]);
    if (metrics)
        renderMetrics(metrics);
    if (!status?.connected)
        return;
    if (member)
        renderMember(member);
    if (Array.isArray(workspaces))
        renderWorkspaces(workspaces);
};
const syncSelected = async () => {
    const selection = loadSelection();
    if (selection.size === 0) {
        toast('Selecciona un workspace', 'Debes seleccionar al menos un workspace para sincronizar.');
        return;
    }
    setBusy(true);
    try {
        let totalBoards = 0;
        let totalLists = 0;
        let totalCards = 0;
        let totalErrors = 0;
        let totalDuration = 0;
        for (const wid of selection) {
            const res = await api('/api/trello/sync', { method: 'POST', body: { type: 'workspace', workspace_id: wid } });
            totalBoards += Number(res?.boards) || 0;
            totalLists += Number(res?.lists) || 0;
            totalCards += Number(res?.cards) || 0;
            totalErrors += Number(res?.errors) || 0;
            totalDuration += Number(res?.duration_seconds) || 0;
        }
        await refresh();
        showResultModal(true, 'Sincronizacion completada', `Se actualizaron ${totalBoards} boards, ${totalLists} listas y ${totalCards} cards en ${totalDuration}s. Errores detectados: ${totalErrors}.`);
    }
    catch (e) {
        showResultModal(false, 'Sincronizacion fallida', e?.message ? String(e.message) : 'No se pudo sincronizar Trello.');
    }
    finally {
        setBusy(false);
    }
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
    wireAuthorizeFlow();
    const connectTop = qs('#connectBtnTop');
    const connect = qs('#connectBtn');
    const disconnect = qs('#disconnectBtn');
    const syncNow = qs('#syncNowBtn');
    const form = qs('#connectForm');
    const resultModalClose = qs('#resultModalClose');
    const resultModal = qs('#resultModal');
    resultModalClose?.addEventListener('click', closeResultModal);
    resultModal?.addEventListener('click', (e) => {
        if (e.target === resultModal)
            closeResultModal();
    });
    connectTop?.addEventListener('click', focusConnectForm);
    connect?.addEventListener('click', focusConnectForm);
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = qs('#trelloToken');
        const token = input ? String(input.value ?? '').trim() : '';
        void connectWithToken(token, 'manual');
    });
    disconnect?.addEventListener('click', async () => {
        setBusy(true);
        try {
            await api('/api/trello/disconnect', { method: 'POST', body: {} });
            toast('Trello desconectado', 'La cuenta fue desconectada.');
            localStorage.removeItem(selectionKey);
            await refresh();
        }
        catch (e) {
            toast('Error', e?.message ? String(e.message) : 'No se pudo desconectar.');
        }
        finally {
            setBusy(false);
        }
    });
    syncNow?.addEventListener('click', () => void syncSelected());
    const initial = window.__PM?.trelloStatus ?? null;
    const initialMetrics = window.__PM?.trelloMetrics ?? null;
    if (handleAuthorizeCallback())
        return;
    if (initial)
        renderConnection(initial);
    if (initialMetrics)
        renderMetrics(initialMetrics);
    void refresh();
};
window.addEventListener('DOMContentLoaded', init);
