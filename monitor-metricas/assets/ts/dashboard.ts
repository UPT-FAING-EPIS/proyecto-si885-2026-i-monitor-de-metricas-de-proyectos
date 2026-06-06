type Theme = 'light' | 'dark';

type PMData = {
  projectProgressSeries: number[];
  teams: { name: string; value: number }[];
  statusDistribution: { label: string; value: number }[];
};

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

const readData = (): PMData | null => {
  const el = qs<HTMLScriptElement>('#pmData');
  if (!el?.textContent) return null;
  try {
    return JSON.parse(el.textContent) as PMData;
  } catch {
    return null;
  }
};

const toggleMenu = (btn: HTMLElement, menu: HTMLElement, open: boolean) => {
  btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  menu.classList.toggle('hidden', !open);
};

const wireDropdown = (btnSel: string, menuSel: string) => {
  const btn = qs<HTMLElement>(btnSel);
  const menu = qs<HTMLElement>(menuSel);
  if (!btn || !menu) return;

  const close = () => toggleMenu(btn, menu, false);
  const open = () => toggleMenu(btn, menu, true);
  const isOpen = () => !menu.classList.contains('hidden');

  btn.addEventListener('click', () => (isOpen() ? close() : open()));

  document.addEventListener('click', (e) => {
    const target = e.target as Node | null;
    if (!target) return;
    if (btn.contains(target) || menu.contains(target)) return;
    close();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });
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

const color = (token: 'stroke' | 'fill' | 'muted'): string => {
  const dark = document.documentElement.classList.contains('dark');
  if (token === 'muted') return dark ? '#334155' : '#e2e8f0';
  if (token === 'stroke') return dark ? '#93c5fd' : '#155fe0';
  return dark ? 'rgba(31, 122, 255, 0.12)' : 'rgba(21, 95, 224, 0.10)';
};

const renderLineChart = (svg: SVGSVGElement, series: number[]) => {
  const w = 600;
  const h = 200;
  const padX = 18;
  const padY = 18;
  const min = Math.min(...series);
  const max = Math.max(...series);
  const norm = (v: number) => {
    const t = max === min ? 0.5 : (v - min) / (max - min);
    return padY + (1 - t) * (h - padY * 2);
  };
  const step = (w - padX * 2) / Math.max(1, series.length - 1);
  const points = series.map((v, i) => [padX + i * step, norm(v)] as const);

  const path = points.reduce((acc, [x, y], i) => acc + (i === 0 ? `M ${x} ${y}` : ` L ${x} ${y}`), '');
  const area = `${path} L ${padX + (series.length - 1) * step} ${h - padY} L ${padX} ${h - padY} Z`;

  const gridLines = 4;
  const grid = Array.from({ length: gridLines + 1 }).map((_, i) => {
    const y = padY + (i * (h - padY * 2)) / gridLines;
    return `<line x1="${padX}" y1="${y}" x2="${w - padX}" y2="${y}" stroke="${color('muted')}" stroke-width="1" />`;
  }).join('');

  svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
  svg.innerHTML = `
    <g>${grid}</g>
    <path d="${area}" fill="${color('fill')}"></path>
    <path d="${path}" fill="none" stroke="${color('stroke')}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
    ${points.map(([x, y]) => `<circle cx="${x}" cy="${y}" r="3.5" fill="${color('stroke')}"></circle>`).join('')}
  `;
};

const renderBarChart = (svg: SVGSVGElement, series: { name: string; value: number }[]) => {
  const w = 600;
  const h = 200;
  const pad = 18;
  const max = Math.max(...series.map((s) => s.value), 100);
  const barW = (w - pad * 2) / series.length;

  svg.setAttribute('viewBox', `0 0 ${w} ${h}`);

  const gridLines = 4;
  const grid = Array.from({ length: gridLines + 1 }).map((_, i) => {
    const y = pad + (i * (h - pad * 2)) / gridLines;
    return `<line x1="${pad}" y1="${y}" x2="${w - pad}" y2="${y}" stroke="${color('muted')}" stroke-width="1" />`;
  }).join('');

  const bars = series.map((s, i) => {
    const x = pad + i * barW + barW * 0.2;
    const bw = barW * 0.6;
    const bh = ((h - pad * 2) * s.value) / max;
    const y = h - pad - bh;
    return `
      <rect x="${x}" y="${y}" width="${bw}" height="${bh}" rx="10" fill="${color('fill')}" stroke="${color('stroke')}" stroke-width="2"></rect>
      <text x="${x + bw / 2}" y="${h - 4}" text-anchor="middle" font-size="11" fill="${document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b'}">${s.name}</text>
    `;
  }).join('');

  svg.innerHTML = `<g>${grid}</g>${bars}`;
};

const polarToCartesian = (cx: number, cy: number, r: number, angle: number) => {
  const a = (angle - 90) * (Math.PI / 180);
  return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
};

const arcPath = (cx: number, cy: number, r: number, startAngle: number, endAngle: number) => {
  const start = polarToCartesian(cx, cy, r, endAngle);
  const end = polarToCartesian(cx, cy, r, startAngle);
  const largeArc = endAngle - startAngle <= 180 ? '0' : '1';
  return `M ${start.x} ${start.y} A ${r} ${r} 0 ${largeArc} 0 ${end.x} ${end.y}`;
};

const renderDonut = (svg: SVGSVGElement, series: { label: string; value: number }[]) => {
  const size = 120;
  const cx = 60;
  const cy = 60;
  const r = 44;
  const stroke = 14;
  const total = series.reduce((acc, s) => acc + s.value, 0) || 1;

  const palette = document.documentElement.classList.contains('dark')
    ? ['#94a3b8', '#60a5fa', '#fb7185', '#34d399']
    : ['#94a3b8', '#155fe0', '#f43f5e', '#10b981'];

  let angle = 0;
  const arcs = series.map((s, idx) => {
    const a0 = angle;
    const a1 = angle + (s.value / total) * 360;
    angle = a1;
    return `<path d="${arcPath(cx, cy, r, a0, a1)}" stroke="${palette[idx % palette.length]}" stroke-width="${stroke}" stroke-linecap="round" fill="none"></path>`;
  }).join('');

  svg.setAttribute('viewBox', `0 0 ${size} ${size}`);
  svg.innerHTML = `
    <circle cx="${cx}" cy="${cy}" r="${r}" stroke="${color('muted')}" stroke-width="${stroke}" fill="none"></circle>
    ${arcs}
    <circle cx="${cx}" cy="${cy}" r="${r - stroke}" fill="${document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff'}"></circle>
    <text x="${cx}" y="${cy - 2}" text-anchor="middle" font-size="16" font-weight="700" fill="${document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#0f172a'}">100%</text>
    <text x="${cx}" y="${cy + 16}" text-anchor="middle" font-size="10" fill="${document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b'}">mix</text>
  `;
};

const renderCharts = () => {
  const data = readData();
  if (!data) return;

  const progress = qs<SVGSVGElement>('#chartProgress');
  if (progress) renderLineChart(progress, data.projectProgressSeries);

  const teams = qs<SVGSVGElement>('#chartTeams');
  if (teams) renderBarChart(teams, data.teams);

  const status = qs<SVGSVGElement>('#chartStatus');
  if (status) renderDonut(status, data.statusDistribution);
};

const wireTheme = () => {
  setTheme(getTheme());
  updateThemeUI();

  const toggle = qs<HTMLButtonElement>('#themeToggle');
  if (toggle) {
    toggle.addEventListener('click', () => {
      const next: Theme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
      setTheme(next);
      updateThemeUI();
      renderCharts();
    });
  }
};

const init = () => {
  wireTheme();
  wireSidebar();
  wireDropdown('#notificationsBtn', '#notificationsMenu');
  wireDropdown('#profileBtn', '#profileMenu');
  renderCharts();

  const focusables = qsa<HTMLElement>('a,button,input,select,textarea,[tabindex]:not([tabindex="-1"])');
  focusables.forEach((el) => el.addEventListener('keyup', (e) => {
    if ((e as KeyboardEvent).key === 'Escape') (el as HTMLElement).blur();
  }));
};

try {
  window.addEventListener('DOMContentLoaded', init);
} catch {}

