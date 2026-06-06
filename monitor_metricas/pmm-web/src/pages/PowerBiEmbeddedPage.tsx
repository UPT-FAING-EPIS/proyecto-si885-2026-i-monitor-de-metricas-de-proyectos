import {
  ChevronDown,
  Download,
  Expand,
  Filter,
  Gauge,
  LineChart,
  Maximize2,
  Minimize2,
  RefreshCw,
  SlidersHorizontal,
} from 'lucide-react'
import { useEffect, useMemo, useRef, useState } from 'react'

import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { Input } from '../components/ui/input'
import { Label } from '../components/ui/label'
import { cn } from '../lib/utils'

type Report = {
  id: string
  name: string
  description: string
  updatedAt: string
}

type Kpi = {
  label: string
  value: string
  helper: string
  icon: React.ComponentType<{ className?: string }>
}

function formatDate(value: string) {
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return new Intl.DateTimeFormat('es-PE', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(d)
}

function downloadText(filename: string, contents: string, mime: string) {
  const blob = new Blob([contents], { type: mime })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}

function exportExcelXls(rows: Array<Record<string, string | number>>) {
  const headers = Object.keys(rows[0] ?? {})
  const escape = (v: string | number) =>
    String(v)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')

  const table = `
<table>
  <thead>
    <tr>${headers.map((h) => `<th>${escape(h)}</th>`).join('')}</tr>
  </thead>
  <tbody>
    ${rows
      .map(
        (r) =>
          `<tr>${headers.map((h) => `<td>${escape(r[h] ?? '')}</td>`).join('')}</tr>`,
      )
      .join('')}
  </tbody>
</table>`.trim()

  const html = `
<html>
  <head>
    <meta charset="utf-8" />
  </head>
  <body>
    ${table}
  </body>
</html>`.trim()

  downloadText('powerbi-export.xls', html, 'application/vnd.ms-excel;charset=utf-8')
}

function KpiCard({ kpi }: { kpi: Kpi }) {
  const Icon = kpi.icon
  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
        <div className="space-y-1">
          <CardDescription>{kpi.label}</CardDescription>
          <CardTitle className="text-3xl">{kpi.value}</CardTitle>
          <p className="text-xs text-muted-foreground">{kpi.helper}</p>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <Icon className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent className="pt-2">
        <span className="text-[11px] text-muted-foreground">Fuente: Power BI</span>
      </CardContent>
    </Card>
  )
}

export function PowerBiEmbeddedPage() {
  const reports: Report[] = useMemo(
    () => [
      {
        id: 'r1',
        name: 'Dashboard Ejecutivo (Global)',
        description: 'Vista de gerencia: KPIs, riesgos y estado general.',
        updatedAt: '2026-06-06T11:20:00',
      },
      {
        id: 'r2',
        name: 'Delivery & Throughput',
        description: 'Velocidad, productividad, lead/cycle time por equipo.',
        updatedAt: '2026-06-05T18:40:00',
      },
      {
        id: 'r3',
        name: 'Riesgos & Alertas',
        description: 'Detección temprana: vencimientos, sobrecargas y bloqueos.',
        updatedAt: '2026-06-04T09:15:00',
      },
    ],
    [],
  )

  const kpis: Kpi[] = useMemo(
    () => [
      { label: 'Proyectos activos', value: '12', helper: 'En ejecución', icon: Gauge },
      { label: 'Cumplimiento SLA', value: '86%', helper: 'Últimos 30 días', icon: LineChart },
      { label: 'Riesgos críticos', value: '3', helper: 'Requieren acción', icon: Filter },
      { label: 'Tareas vencidas', value: '37', helper: 'Todas las áreas', icon: SlidersHorizontal },
    ],
    [],
  )

  const [reportId, setReportId] = useState(reports[0]?.id ?? 'r1')
  const report = reports.find((r) => r.id === reportId) ?? reports[0]

  const [project, setProject] = useState<string>('Todos')
  const [team, setTeam] = useState<string>('Todos')
  const [from, setFrom] = useState('2026-05-01')
  const [to, setTo] = useState('2026-06-06')

  const [filtersOpen, setFiltersOpen] = useState(true)
  const reportHostRef = useRef<HTMLDivElement | null>(null)
  const [isFullscreen, setIsFullscreen] = useState(false)

  useEffect(() => {
    const onChange = () => setIsFullscreen(Boolean(document.fullscreenElement))
    document.addEventListener('fullscreenchange', onChange)
    return () => document.removeEventListener('fullscreenchange', onChange)
  }, [])

  const selectionSummary = useMemo(() => {
    return `${project} · ${team} · ${from} — ${to}`
  }, [project, team, from, to])

  function toggleFullscreen() {
    const el = reportHostRef.current
    if (!el) return
    if (document.fullscreenElement) document.exitFullscreen?.()
    else el.requestFullscreen?.()
  }

  function exportPdf() {
    window.print()
  }

  function exportExcel() {
    exportExcelXls([
      {
        Reporte: report?.name ?? '—',
        Proyecto: project,
        Equipo: team,
        Desde: from,
        Hasta: to,
        Generado: new Date().toISOString(),
      },
      ...kpis.map((k) => ({ KPI: k.label, Valor: k.value, Detalle: k.helper })),
    ])
  }

  return (
    <div className="min-h-svh bg-background text-foreground">
      <header className="border-b bg-background/70 backdrop-blur">
        <div className="mx-auto max-w-7xl px-4 py-5 sm:px-6">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div className="flex items-start gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                <LineChart className="h-5 w-5 text-primary" />
              </div>
              <div className="min-w-0">
                <h1 className="text-lg font-semibold tracking-tight">Power BI Embedded</h1>
                <p className="mt-1 text-sm text-muted-foreground">
                  Dashboards ejecutivos embebidos para toma de decisiones.
                </p>
              </div>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <Button type="button" variant="outline" className="gap-2" onClick={() => setFiltersOpen((p) => !p)}>
                <Filter className="h-4 w-4" />
                Filtros
                <ChevronDown className={cn('h-4 w-4 text-muted-foreground transition-transform', filtersOpen && 'rotate-180')} />
              </Button>
              <Button type="button" variant="outline" className="gap-2" onClick={toggleFullscreen}>
                {isFullscreen ? <Minimize2 className="h-4 w-4" /> : <Maximize2 className="h-4 w-4" />}
                Pantalla completa
              </Button>
              <Button type="button" variant="outline" className="gap-2" onClick={exportPdf}>
                <Download className="h-4 w-4" />
                Exportar PDF
              </Button>
              <Button type="button" className="gap-2" onClick={exportExcel}>
                <Download className="h-4 w-4" />
                Exportar Excel
              </Button>
            </div>
          </div>

          <div className="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-[1.2fr,0.8fr] lg:items-end">
            <div>
              <Label className="text-xs text-muted-foreground">Selector de reporte</Label>
              <div className="mt-1 grid grid-cols-1 gap-2 sm:grid-cols-[1fr,auto] sm:items-center">
                <select
                  className={cn(
                    'flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                  )}
                  value={reportId}
                  onChange={(e) => setReportId(e.target.value)}
                  aria-label="Selector de reporte"
                >
                  {reports.map((r) => (
                    <option key={r.id} value={r.id}>
                      {r.name}
                    </option>
                  ))}
                </select>
                <div className="flex items-center justify-between gap-3 rounded-xl border bg-muted/10 p-3 sm:h-10 sm:rounded-md sm:px-3 sm:py-0">
                  <span className="text-xs text-muted-foreground">Actualizado</span>
                  <span className="text-xs font-semibold tabular-nums">
                    {report?.updatedAt ? formatDate(report.updatedAt) : '—'}
                  </span>
                </div>
              </div>
              <p className="mt-2 text-sm text-muted-foreground">{report?.description}</p>
            </div>

            <div className="rounded-xl border bg-muted/10 p-4">
              <p className="text-xs text-muted-foreground">Contexto activo</p>
              <p className="mt-1 text-sm font-semibold">{selectionSummary}</p>
            </div>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6">
        <section aria-label="KPIs">
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {kpis.map((k) => (
              <KpiCard key={k.label} kpi={k} />
            ))}
          </div>
        </section>

        <section aria-label="Dashboard y filtros" className="grid grid-cols-1 gap-4 lg:grid-cols-[320px,1fr]">
          <Card className={cn('shadow-sm', !filtersOpen && 'hidden lg:block')}>
            <CardHeader className="flex flex-row items-start justify-between space-y-0">
              <div className="space-y-1">
                <CardTitle className="text-base">Filtros</CardTitle>
                <CardDescription>Aplicados al reporte embebido</CardDescription>
              </div>
              <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
                <SlidersHorizontal className="h-4 w-4 text-muted-foreground" />
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label className="text-xs text-muted-foreground">Proyecto</Label>
                <select
                  className={cn(
                    'flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                  )}
                  value={project}
                  onChange={(e) => setProject(e.target.value)}
                  aria-label="Filtro proyecto"
                >
                  <option value="Todos">Todos</option>
                  <option value="Plataforma Comercial">Plataforma Comercial</option>
                  <option value="Data Warehouse">Data Warehouse</option>
                  <option value="Migración Jira">Migración Jira</option>
                  <option value="App Mobile">App Mobile</option>
                </select>
              </div>

              <div className="space-y-2">
                <Label className="text-xs text-muted-foreground">Equipo</Label>
                <select
                  className={cn(
                    'flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                  )}
                  value={team}
                  onChange={(e) => setTeam(e.target.value)}
                  aria-label="Filtro equipo"
                >
                  <option value="Todos">Todos</option>
                  <option value="Backend">Backend</option>
                  <option value="Frontend">Frontend</option>
                  <option value="Data">Data</option>
                  <option value="QA">QA</option>
                </select>
              </div>

              <div className="space-y-2">
                <Label className="text-xs text-muted-foreground">Fecha</Label>
                <div className="grid grid-cols-2 gap-2">
                  <Input type="date" value={from} onChange={(e) => setFrom(e.target.value)} aria-label="Desde" />
                  <Input type="date" value={to} onChange={(e) => setTo(e.target.value)} aria-label="Hasta" />
                </div>
              </div>

              <div className="rounded-xl border bg-muted/10 p-3">
                <p className="text-xs text-muted-foreground">Sugerencia</p>
                <p className="mt-1 text-sm font-medium">
                  Filtra por equipo para comparar rendimiento entre áreas con un mismo reporte.
                </p>
              </div>

              <div className="flex flex-col gap-2 sm:flex-row lg:flex-col">
                <Button type="button" variant="outline" className="w-full gap-2" onClick={() => {}}>
                  <RefreshCw className="h-4 w-4" />
                  Actualizar
                </Button>
                <Button type="button" className="w-full gap-2" onClick={() => {}}>
                  <Expand className="h-4 w-4" />
                  Aplicar
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card className="shadow-sm">
            <CardHeader className="flex flex-row items-start justify-between space-y-0">
              <div className="space-y-1">
                <CardTitle className="text-base">Dashboard principal</CardTitle>
                <CardDescription>Reporte embebido (placeholder de integración)</CardDescription>
              </div>
              <div className="flex items-center gap-2">
                <span className="hidden sm:inline-flex rounded-full border bg-muted/30 px-3 py-1 text-xs text-muted-foreground">
                  Power BI Embedded
                </span>
              </div>
            </CardHeader>
            <CardContent>
              <div
                ref={reportHostRef}
                className={cn(
                  'relative overflow-hidden rounded-xl border bg-muted/10',
                  'min-h-[520px] lg:min-h-[680px]',
                )}
              >
                <div className="absolute inset-0 bg-gradient-to-br from-primary/10 via-transparent to-transparent" />
                <div className="absolute inset-0 grid place-items-center p-6">
                  <div className="w-full max-w-xl rounded-2xl border bg-background/80 p-6 backdrop-blur">
                    <div className="flex items-start justify-between gap-4">
                      <div className="space-y-1">
                        <p className="text-sm font-semibold">Área de reporte embebido</p>
                        <p className="text-sm text-muted-foreground">
                          Aquí se renderiza el iframe/SDK de Power BI con los filtros aplicados.
                        </p>
                      </div>
                      <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                        <LineChart className="h-5 w-5 text-muted-foreground" />
                      </div>
                    </div>
                    <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                      <div className="rounded-xl border bg-muted/10 p-3">
                        <p className="text-xs text-muted-foreground">Reporte</p>
                        <p className="mt-1 text-sm font-semibold">{report?.name ?? '—'}</p>
                      </div>
                      <div className="rounded-xl border bg-muted/10 p-3">
                        <p className="text-xs text-muted-foreground">Filtros</p>
                        <p className="mt-1 text-sm font-semibold">{project}</p>
                      </div>
                      <div className="rounded-xl border bg-muted/10 p-3">
                        <p className="text-xs text-muted-foreground">Periodo</p>
                        <p className="mt-1 text-sm font-semibold">
                          {from} — {to}
                        </p>
                      </div>
                    </div>
                    <div className="mt-4 text-xs text-muted-foreground">
                      Para producción: autenticar, obtener embed token y cargar el reporte con el SDK.
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </section>
      </main>
    </div>
  )
}

