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
    toggle(syncNow);
    toggle(disconnect);
    toggle(connectTop);
    toggle(connect);
    toggle(submit);
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
const focusConnectForm = () => {
    const card = qs('#connectCard');
    if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    const input = qs('#trelloToken');
    if (input) {
        window.setTimeout(() => input.focus(), 150);
    }
};
const refresh = async () => {
    const status = await api('/api/trello/status');
    renderConnection(status);
    if (!status?.connected)
        return;
    const [member, workspaces] = await Promise.all([
        api('/api/trello/member').catch(() => null),
        api('/api/trello/workspaces').catch(() => []),
    ]);
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
        toast('Sincronización completa', `Boards: ${totalBoards} · Lists: ${totalLists} · Cards: ${totalCards} · Errores: ${totalErrors} · ${totalDuration}s`);
    }
    catch (e) {
        toast('Error', e?.message ? String(e.message) : 'No se pudo sincronizar.');
    }
    finally {
        setBusy(false);
    }
};
const wireSyncSettings = () => {
    const settingsKey = 'pm:trello:settings';
    const form = qs('#syncSettings');
    const modeAuto = qs('#modeAuto');
    const modeManual = qs('#modeManual');
    const frequency = qs('#frequency');
    if (!form || !modeAuto || !modeManual || !frequency)
        return;
    const load = () => {
        const raw = localStorage.getItem(settingsKey);
        if (!raw)
            return;
        try {
            const parsed = JSON.parse(raw);
            if (parsed.mode === 'manual')
                modeManual.checked = true;
            if (parsed.mode === 'auto')
                modeAuto.checked = true;
            if (typeof parsed.frequency === 'string')
                frequency.value = parsed.frequency;
        }
        catch { }
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
    const themeToggle = qs('#themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            setTheme(next);
            updateThemeUI();
        });
    }
    wireSidebar();
    wireSyncSettings();
    const connectTop = qs('#connectBtnTop');
    const connect = qs('#connectBtn');
    const disconnect = qs('#disconnectBtn');
    const syncNow = qs('#syncNowBtn');
    const form = qs('#connectForm');
    connectTop?.addEventListener('click', focusConnectForm);
    connect?.addEventListener('click', focusConnectForm);
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = qs('#trelloToken');
        const token = input ? String(input.value ?? '').trim() : '';
        if (!token) {
            toast('Token requerido', 'Ingresa el token para conectar Trello.');
            return;
        }
        setBusy(true);
        try {
            await api('/api/trello/connect', { method: 'POST', body: { token } });
            if (input)
                input.value = '';
            toast('Trello conectado', 'Conexión establecida correctamente.');
            await refresh();
        }
        catch (err) {
            toast('Error', err?.message ? String(err.message) : 'No se pudo conectar Trello.');
        }
        finally {
            setBusy(false);
        }
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
    if (initial)
        renderConnection(initial);
    void refresh();
};
window.addEventListener('DOMContentLoaded', init);
