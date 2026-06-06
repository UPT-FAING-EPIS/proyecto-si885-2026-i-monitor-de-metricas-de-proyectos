import {
  BarChart3,
  Calendar,
  ChevronDown,
  Download,
  Gauge,
  GitCommit,
  LineChart,
  Repeat2,
  Timer,
  Users,
} from 'lucide-react'
import { useMemo, useState } from 'react'

import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { Checkbox } from '../components/ui/checkbox'
import { Input } from '../components/ui/input'
import { Label } from '../components/ui/label'
import { cn } from '../lib/utils'

type SeriesPoint = { x: string; y: number }
type Series = { name: string; color: string; points: SeriesPoint[] }

type ProjectOption = { id: string; name: string }
type TeamOption = { id: string; name: string }

const PROJECTS: ProjectOption[] = [
  { id: 'all', name: 'Todos los proyectos' },
  { id: 'p1', name: 'Plataforma Comercial' },
  { id: 'p2', name: 'Data Warehouse' },
  { id: 'p3', name: 'Migración Jira' },
  { id: 'p4', name: 'App Mobile' },
]

const TEAMS: TeamOption[] = [
  { id: 'all', name: 'Todos los equipos' },
  { id: 't1', name: 'Backend' },
  { id: 't2', name: 'Frontend' },
  { id: 't3', name: 'Data' },
  { id: 't4', name: 'QA' },
]

function formatDate(value: string) {
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return new Intl.DateTimeFormat('es-PE', { year: 'numeric', month: 'short', day: '2-digit' }).format(d)
}

function sum(values: number[]) {
  return values.reduce((a, b) => a + b, 0)
}

function average(values: number[]) {
  return values.length ? sum(values) / values.length : 0
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

function toCsv(rows: Array<Record<string, string | number>>) {
  const headers = Object.keys(rows[0] ?? {})
  const escape = (v: string | number) => {
    const s = String(v)
    if (/[",\n]/.test(s)) return `"${s.replaceAll('"', '""')}"`
    return s
  }
  const lines = [headers.join(','), ...rows.map((r) => headers.map((h) => escape(r[h] ?? '')).join(','))]
  return lines.join('\n')
}

function StatPill({
  label,
  value,
  helper,
  icon: Icon,
}: {
  label: string
  value: string
  helper: string
  icon: React.ComponentType<{ className?: string }>
}) {
  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
        <div className="space-y-1">
          <CardDescription>{label}</CardDescription>
          <CardTitle className="text-3xl">{value}</CardTitle>
          <p className="text-xs text-muted-foreground">{helper}</p>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <Icon className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent className="pt-2">
        <span className="text-[11px] text-muted-foreground">Comparativa disponible</span>
      </CardContent>
    </Card>
  )
}

function LineAreaChart({
  title,
  description,
  series,
}: {
  title: string
  description: string
  series: Series[]
}) {
  const all = series.flatMap((s) => s.points.map((p) => p.y))
  const min = Math.min(...all)
  const max = Math.max(...all)
  const range = Math.max(1, max - min)

  const paths = series.map((s) => {
    const points = s.points
      .map((p, idx) => {
        const x = (idx / Math.max(1, s.points.length - 1)) * 100
        const y = 100 - ((p.y - min) / range) * 100
        return { x, y }
      })
      .map((p) => `${p.x},${p.y}`)
      .join(' ')

    const area = `0,100 ${points} 100,100`
    return { ...s, pointsStr: points, areaStr: area }
  })

  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0">
        <div className="space-y-1">
          <CardTitle className="text-base">{title}</CardTitle>
          <CardDescription>{description}</CardDescription>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <LineChart className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="rounded-xl border bg-muted/10 p-3">
          <svg viewBox="0 0 100 100" className="h-44 w-full" role="img" aria-label={title}>
            {[20, 40, 60, 80].map((x) => (
              <line key={x} x1={x} y1={0} x2={x} y2={100} stroke="hsl(var(--border))" strokeWidth="0.6" />
            ))}
            {[25, 50, 75].map((y) => (
              <line key={y} x1={0} y1={y} x2={100} y2={y} stroke="hsl(var(--border))" strokeWidth="0.6" />
            ))}
            {paths.map((p) => (
              <path key={`${p.name}-area`} d={`M${p.areaStr}`} fill={p.color} opacity={0.12} />
            ))}
            {paths.map((p) => (
              <polyline
                key={`${p.name}-line`}
                fill="none"
                stroke={p.color}
                strokeWidth="2.2"
                strokeLinejoin="round"
                strokeLinecap="round"
                points={p.pointsStr}
              />
            ))}
          </svg>
        </div>

        <div className="flex flex-wrap items-center justify-between gap-3">
          <div className="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
            {series.map((s) => (
              <span key={s.name} className="inline-flex items-center gap-2">
                <span className="h-2 w-2 rounded-full" style={{ backgroundColor: s.color }} />
                {s.name}
              </span>
            ))}
          </div>
          <div className="flex items-center gap-2 text-xs text-muted-foreground">
            <span>{series[0]?.points[0]?.x}</span>
            <span className="opacity-40">—</span>
            <span>{series[0]?.points.at(-1)?.x}</span>
          </div>
        </div>
      </CardContent>
    </Card>
  )
}

function BarListChart({
  title,
  description,
  items,
  compareItems,
}: {
  title: string
  description: string
  items: Array<{ label: string; value: number }>
  compareItems?: Array<{ label: string; value: number }>
}) {
  const max = Math.max(
    ...items.map((i) => i.value),
    ...(compareItems?.map((i) => i.value) ?? []),
    1,
  )

  const compareMap = new Map(compareItems?.map((i) => [i.label, i.value]) ?? [])

  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0">
        <div className="space-y-1">
          <CardTitle className="text-base">{title}</CardTitle>
          <CardDescription>{description}</CardDescription>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <BarChart3 className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent className="space-y-3">
        {items.map((i) => {
          const compare = compareMap.get(i.label)
          return (
            <div key={i.label} className="space-y-1">
              <div className="flex items-center justify-between text-sm">
                <span className="text-muted-foreground">{i.label}</span>
                <div className="flex items-center gap-3">
                  {typeof compare === 'number' ? (
                    <span className="text-xs text-muted-foreground tabular-nums">comp: {compare}</span>
                  ) : null}
                  <span className="font-semibold tabular-nums">{i.value}</span>
                </div>
              </div>
              <div className="relative h-2 w-full rounded-full bg-muted">
                {typeof compare === 'number' ? (
                  <div
                    className="absolute left-0 top-0 h-2 rounded-full bg-muted-foreground/30"
                    style={{ width: `${(compare / max) * 100}%` }}
                    aria-hidden="true"
                  />
                ) : null}
                <div
                  className="absolute left-0 top-0 h-2 rounded-full bg-sky-500"
                  style={{ width: `${(i.value / max) * 100}%` }}
                  aria-hidden="true"
                />
              </div>
            </div>
          )
        })}
        {compareItems ? (
          <div className="flex items-center gap-3 pt-1 text-xs text-muted-foreground">
            <span className="inline-flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-sky-500" /> Actual
            </span>
            <span className="inline-flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-muted-foreground/30" /> Comparativa
            </span>
          </div>
        ) : null}
      </CardContent>
    </Card>
  )
}

function MetricCard({
  title,
  description,
  value,
  unit,
  compareValue,
  icon: Icon,
}: {
  title: string
  description: string
  value: number
  unit: string
  compareValue?: number
  icon: React.ComponentType<{ className?: string }>
}) {
  const delta = typeof compareValue === 'number' ? value - compareValue : null
  const isBetterLower = title.includes('Lead') || title.includes('Cycle')
  const good = delta === null ? null : isBetterLower ? delta <= 0 : delta >= 0

  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
        <div className="space-y-1">
          <CardTitle className="text-base">{title}</CardTitle>
          <CardDescription>{description}</CardDescription>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <Icon className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent className="flex items-end justify-between gap-4 pt-2">
        <div className="space-y-1">
          <div className="text-3xl font-semibold tabular-nums">
            {value.toFixed(1)}
            <span className="ml-1 text-sm font-medium text-muted-foreground">{unit}</span>
          </div>
          {typeof compareValue === 'number' ? (
            <div className="text-xs text-muted-foreground tabular-nums">
              comp: {compareValue.toFixed(1)}
              <span
                className={cn(
                  'ml-2 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-medium',
                  good === null && 'border-border bg-muted text-muted-foreground',
                  good === true &&
                    'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                  good === false &&
                    'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300',
                )}
              >
                {delta && delta > 0 ? '+' : ''}
                {delta?.toFixed(1)}
              </span>
            </div>
          ) : (
            <div className="text-xs text-muted-foreground">comp: —</div>
          )}
        </div>
        <div className="text-xs text-muted-foreground">
          {isBetterLower ? 'Menor es mejor' : 'Mayor es mejor'}
        </div>
      </CardContent>
    </Card>
  )
}

export function AdvancedAnalyticsPage() {
  const [projectId, setProjectId] = useState<ProjectOption['id']>('all')
  const [teamId, setTeamId] = useState<TeamOption['id']>('all')
  const [from, setFrom] = useState('2026-05-01')
  const [to, setTo] = useState('2026-06-06')
  const [compare, setCompare] = useState(true)

  const selectionLabel = useMemo(() => {
    const project = PROJECTS.find((p) => p.id === projectId)?.name ?? '—'
    const team = TEAMS.find((t) => t.id === teamId)?.name ?? '—'
    return `${project} · ${team}`
  }, [projectId, teamId])

  const dateLabel = useMemo(() => {
    return `${formatDate(from)} — ${formatDate(to)}`
  }, [from, to])

  const burnDown = useMemo<Series[]>(() => {
    const base = [180, 160, 144, 132, 121, 108, 96, 84, 73, 62, 55, 48, 42, 36]
    const current = base.map((v, i) => Math.max(0, v - i * 2))
    const compareSeries = base.map((v, i) => Math.max(0, v - i * 1.5))
    const x = Array.from({ length: base.length }).map((_, i) => `D${i + 1}`)
    return [
      { name: 'Actual', color: 'hsl(var(--primary))', points: x.map((xi, i) => ({ x: xi, y: current[i] ?? 0 })) },
      ...(compare
        ? [
            {
              name: 'Comparativa',
              color: 'hsl(199 89% 48%)',
              points: x.map((xi, i) => ({ x: xi, y: compareSeries[i] ?? 0 })),
            },
          ]
        : []),
    ]
  }, [compare])

  const burnUp = useMemo<Series[]>(() => {
    const done = [10, 18, 28, 34, 42, 55, 63, 71, 78, 86, 92, 101, 112, 124]
    const scope = [140, 140, 142, 145, 150, 152, 154, 156, 160, 162, 166, 170, 172, 176]
    const x = Array.from({ length: done.length }).map((_, i) => `D${i + 1}`)
    const compareDone = done.map((v, i) => Math.max(0, v - Math.round(i * 0.4)))

    return [
      { name: 'Done', color: 'hsl(var(--primary))', points: x.map((xi, i) => ({ x: xi, y: done[i] ?? 0 })) },
      { name: 'Scope', color: 'hsl(215 20.2% 65.1%)', points: x.map((xi, i) => ({ x: xi, y: scope[i] ?? 0 })) },
      ...(compare
        ? [
            {
              name: 'Done (comp)',
              color: 'hsl(199 89% 48%)',
              points: x.map((xi, i) => ({ x: xi, y: compareDone[i] ?? 0 })),
            },
          ]
        : []),
    ]
  }, [compare])

  const productivity = useMemo(() => {
    const current = [
      { label: 'Ana', value: 28 },
      { label: 'Luis', value: 22 },
      { label: 'María', value: 18 },
      { label: 'Diego', value: 14 },
    ]
    const previous = [
      { label: 'Ana', value: 24 },
      { label: 'Luis', value: 19 },
      { label: 'María', value: 16 },
      { label: 'Diego', value: 11 },
    ]
    return { current, previous: compare ? previous : undefined }
  }, [compare])

  const velocity = useMemo(() => {
    const current = [18, 22, 20, 26, 24, 28].map((y, i) => ({ x: `S${i + 1}`, y }))
    const previous = [16, 19, 18, 22, 21, 24].map((y, i) => ({ x: `S${i + 1}`, y }))
    const series: Series[] = [
      { name: 'Actual', color: 'hsl(var(--primary))', points: current },
      ...(compare ? [{ name: 'Comparativa', color: 'hsl(199 89% 48%)', points: previous }] : []),
    ]
    return series
  }, [compare])

  const leadTime = useMemo(() => {
    const current = [6.2, 5.7, 5.9, 5.4, 5.1, 4.8, 4.9].map((y, i) => ({ x: `W${i + 1}`, y }))
    const previous = [6.8, 6.1, 6.0, 5.8, 5.6, 5.2, 5.1].map((y, i) => ({ x: `W${i + 1}`, y }))
    const series: Series[] = [
      { name: 'Actual', color: 'hsl(var(--primary))', points: current },
      ...(compare ? [{ name: 'Comparativa', color: 'hsl(199 89% 48%)', points: previous }] : []),
    ]
    return series
  }, [compare])

  const cycleTime = useMemo(() => {
    const current = [3.1, 3.0, 2.9, 2.8, 2.7, 2.6, 2.5].map((y, i) => ({ x: `W${i + 1}`, y }))
    const previous = [3.4, 3.3, 3.2, 3.1, 3.0, 2.9, 2.8].map((y, i) => ({ x: `W${i + 1}`, y }))
    const series: Series[] = [
      { name: 'Actual', color: 'hsl(var(--primary))', points: current },
      ...(compare ? [{ name: 'Comparativa', color: 'hsl(199 89% 48%)', points: previous }] : []),
    ]
    return series
  }, [compare])

  const summary = useMemo(() => {
    const velocityNow = average(velocity[0]?.points.map((p) => p.y) ?? [])
    const velocityPrev = compare ? average(velocity[1]?.points.map((p) => p.y) ?? []) : undefined
    const leadNow = average(leadTime[0]?.points.map((p) => p.y) ?? [])
    const leadPrev = compare ? average(leadTime[1]?.points.map((p) => p.y) ?? []) : undefined
    const cycleNow = average(cycleTime[0]?.points.map((p) => p.y) ?? [])
    const cyclePrev = compare ? average(cycleTime[1]?.points.map((p) => p.y) ?? []) : undefined

    return {
      velocityNow,
      velocityPrev,
      leadNow,
      leadPrev,
      cycleNow,
      cyclePrev,
    }
  }, [velocity, leadTime, cycleTime, compare])

  function exportCsv() {
    const rows: Array<Record<string, string | number>> = [
      {
        seleccion: selectionLabel,
        desde: from,
        hasta: to,
        comparativa: compare ? 'si' : 'no',
        velocidad_prom: Number(summary.velocityNow.toFixed(2)),
        lead_time_prom_dias: Number(summary.leadNow.toFixed(2)),
        cycle_time_prom_dias: Number(summary.cycleNow.toFixed(2)),
      },
      ...velocity[0].points.map((p, idx) => ({
        tipo: 'velocidad',
        periodo: p.x,
        actual: p.y,
        comparativa: compare ? velocity[1]?.points[idx]?.y ?? '' : '',
      })),
      ...leadTime[0].points.map((p, idx) => ({
        tipo: 'lead_time',
        periodo: p.x,
        actual: p.y,
        comparativa: compare ? leadTime[1]?.points[idx]?.y ?? '' : '',
      })),
      ...cycleTime[0].points.map((p, idx) => ({
        tipo: 'cycle_time',
        periodo: p.x,
        actual: p.y,
        comparativa: compare ? cycleTime[1]?.points[idx]?.y ?? '' : '',
      })),
    ]

    downloadText('analytics-export.csv', toCsv(rows), 'text/csv;charset=utf-8')
  }

  return (
    <div className="min-h-svh bg-background text-foreground">
      <header className="border-b bg-background/70 backdrop-blur">
        <div className="mx-auto max-w-6xl px-4 py-5 sm:px-6">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div className="flex items-start gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                <BarChart3 className="h-5 w-5 text-primary" />
              </div>
              <div className="min-w-0">
                <h1 className="text-lg font-semibold tracking-tight">Analítica avanzada</h1>
                <p className="mt-1 text-sm text-muted-foreground">
                  Tendencias históricas y comparativas de proyectos Trello.
                </p>
              </div>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <Button type="button" variant="outline" className="gap-2">
                <Calendar className="h-4 w-4" />
                {dateLabel}
                <ChevronDown className="h-4 w-4 text-muted-foreground" />
              </Button>
              <Button type="button" className="gap-2" onClick={exportCsv}>
                <Download className="h-4 w-4" />
                Exportar
              </Button>
            </div>
          </div>

          <div className="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-[1fr,1fr,1fr,auto] lg:items-end">
            <div>
              <Label className="text-xs text-muted-foreground">Proyecto</Label>
              <select
                className={cn(
                  'mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm',
                  'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                )}
                value={projectId}
                onChange={(e) => setProjectId(e.target.value)}
                aria-label="Filtro por proyecto"
              >
                {PROJECTS.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <Label className="text-xs text-muted-foreground">Equipo</Label>
              <select
                className={cn(
                  'mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm',
                  'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                )}
                value={teamId}
                onChange={(e) => setTeamId(e.target.value)}
                aria-label="Filtro por equipo"
              >
                {TEAMS.map((t) => (
                  <option key={t.id} value={t.id}>
                    {t.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <Label className="text-xs text-muted-foreground">Fecha</Label>
              <div className="mt-1 grid grid-cols-2 gap-2">
                <Input type="date" value={from} onChange={(e) => setFrom(e.target.value)} aria-label="Desde" />
                <Input type="date" value={to} onChange={(e) => setTo(e.target.value)} aria-label="Hasta" />
              </div>
            </div>

            <div className="flex items-center justify-between gap-3 rounded-xl border bg-muted/10 p-3">
              <div className="flex items-center gap-2">
                <Checkbox checked={compare} onCheckedChange={(v) => setCompare(Boolean(v))} />
                <div className="leading-tight">
                  <p className="text-sm font-semibold">Comparativas</p>
                  <p className="text-xs text-muted-foreground">Periodo anterior</p>
                </div>
              </div>
              <Repeat2 className="h-4 w-4 text-muted-foreground" />
            </div>
          </div>

          <div className="mt-4 text-xs text-muted-foreground">
            Selección: <span className="font-semibold text-foreground">{selectionLabel}</span>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">
        <section aria-label="Resumen ejecutivo">
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <StatPill
              label="Velocidad promedio"
              value={summary.velocityNow.toFixed(1)}
              helper="Story points / sprint"
              icon={Gauge}
            />
            <StatPill
              label="Lead Time"
              value={summary.leadNow.toFixed(1)}
              helper="Días promedio (end-to-end)"
              icon={Timer}
            />
            <StatPill
              label="Cycle Time"
              value={summary.cycleNow.toFixed(1)}
              helper="Días promedio (en progreso)"
              icon={GitCommit}
            />
          </div>
        </section>

        <section aria-label="Burn charts" className="grid grid-cols-1 gap-4 xl:grid-cols-2">
          <LineAreaChart
            title="Burn Down"
            description="Trabajo restante en el tiempo"
            series={burnDown}
          />
          <LineAreaChart
            title="Burn Up"
            description="Trabajo completado vs alcance"
            series={burnUp}
          />
        </section>

        <section aria-label="Productividad y velocidad" className="grid grid-cols-1 gap-4 xl:grid-cols-3">
          <div className="xl:col-span-1">
            <BarListChart
              title="Productividad"
              description="Cierre de tareas por miembro"
              items={productivity.current}
              compareItems={productivity.previous}
            />
          </div>
          <div className="xl:col-span-2">
            <LineAreaChart
              title="Velocidad"
              description="Story points completados por sprint"
              series={velocity}
            />
          </div>
        </section>

        <section aria-label="Flow metrics" className="grid grid-cols-1 gap-4 xl:grid-cols-2">
          <LineAreaChart
            title="Lead Time"
            description="Tiempo desde creación a cierre (días)"
            series={leadTime}
          />
          <LineAreaChart
            title="Cycle Time"
            description="Tiempo en estado activo (días)"
            series={cycleTime}
          />
        </section>

        <section aria-label="Comparativas" className="grid grid-cols-1 gap-4 xl:grid-cols-3">
          <MetricCard
            title="Velocidad (Δ)"
            description="Promedio actual vs comparativa"
            value={summary.velocityNow}
            compareValue={summary.velocityPrev}
            unit="pts"
            icon={Gauge}
          />
          <MetricCard
            title="Lead Time (Δ)"
            description="Promedio actual vs comparativa"
            value={summary.leadNow}
            compareValue={summary.leadPrev}
            unit="d"
            icon={Timer}
          />
          <MetricCard
            title="Cycle Time (Δ)"
            description="Promedio actual vs comparativa"
            value={summary.cycleNow}
            compareValue={summary.cyclePrev}
            unit="d"
            icon={Users}
          />
        </section>
      </main>
    </div>
  )
}
