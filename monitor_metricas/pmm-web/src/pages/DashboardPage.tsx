import {
  Activity,
  AlertTriangle,
  BarChart3,
  CheckCircle2,
  ChevronDown,
  Gauge,
  LineChart,
  ShieldAlert,
  Users,
} from 'lucide-react'
import { useMemo } from 'react'

import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { cn } from '../lib/utils'

type Trend = 'up' | 'down' | 'flat'

type Kpi = {
  label: string
  value: string
  delta: number
  trend: Trend
  icon: React.ComponentType<{ className?: string }>
}

type Project = {
  name: string
  owner: string
  health: 'Excelente' | 'Bueno' | 'Atención' | 'Crítico'
  score: number
  completion: number
  overdueRate: number
}

function TrendPill({ delta, trend }: { delta: number; trend: Trend }) {
  const isUp = trend === 'up'
  const isDown = trend === 'down'
  const label = `${delta > 0 ? '+' : ''}${delta.toFixed(1)}%`
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-medium',
        isUp && 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        isDown && 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300',
        trend === 'flat' && 'border-border bg-muted text-muted-foreground',
      )}
      aria-label={`Variación ${label}`}
    >
      {label}
    </span>
  )
}

function MiniSparkline({ trend }: { trend: Trend }) {
  const points = useMemo(() => {
    if (trend === 'up') return '2,14 8,10 14,11 20,6 26,7 32,3'
    if (trend === 'down') return '2,4 8,7 14,6 20,10 26,12 32,14'
    return '2,10 8,10 14,10 20,10 26,10 32,10'
  }, [trend])
  return (
    <svg viewBox="0 0 34 18" className="h-5 w-10" aria-hidden="true" focusable="false">
      <polyline
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        points={points}
        className={cn(
          trend === 'up' && 'text-emerald-500',
          trend === 'down' && 'text-rose-500',
          trend === 'flat' && 'text-muted-foreground',
        )}
      />
    </svg>
  )
}

function KpiCard({ kpi }: { kpi: Kpi }) {
  const Icon = kpi.icon
  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
        <div className="space-y-1">
          <CardDescription>{kpi.label}</CardDescription>
          <CardTitle className="text-3xl">{kpi.value}</CardTitle>
        </div>
        <div className="flex items-center gap-2">
          <MiniSparkline trend={kpi.trend} />
          <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
            <Icon className="h-4 w-4 text-muted-foreground" />
          </div>
        </div>
      </CardHeader>
      <CardContent className="flex items-center justify-between pt-2">
        <TrendPill delta={kpi.delta} trend={kpi.trend} />
        <span className="text-[11px] text-muted-foreground">vs. 7 días</span>
      </CardContent>
    </Card>
  )
}

function ProgressRow({
  label,
  value,
  right,
}: {
  label: string
  value: number
  right?: string
}) {
  return (
    <div className="space-y-1">
      <div className="flex items-center justify-between text-sm">
        <span className="text-muted-foreground">{label}</span>
        <span className="font-medium">{right ?? `${Math.round(value)}%`}</span>
      </div>
      <div className="h-2 w-full rounded-full bg-muted">
        <div
          className="h-2 rounded-full bg-primary"
          style={{ width: `${Math.min(100, Math.max(0, value))}%` }}
          aria-hidden="true"
        />
      </div>
    </div>
  )
}

function BarList({
  items,
}: {
  items: Array<{ label: string; value: number; meta?: string }>
}) {
  const max = Math.max(...items.map((i) => i.value), 1)
  return (
    <div className="space-y-3">
      {items.map((i) => (
        <div key={i.label} className="grid grid-cols-[1fr,auto] items-center gap-3">
          <div className="space-y-1">
            <div className="flex items-center justify-between text-sm">
              <span className="text-muted-foreground">{i.label}</span>
              <span className="text-xs text-muted-foreground">{i.meta}</span>
            </div>
            <div className="h-2 w-full rounded-full bg-muted">
              <div
                className="h-2 rounded-full bg-sky-500"
                style={{ width: `${(i.value / max) * 100}%` }}
              />
            </div>
          </div>
          <div className="text-sm font-semibold tabular-nums">{i.value}</div>
        </div>
      ))}
    </div>
  )
}

function Donut({
  segments,
}: {
  segments: Array<{ label: string; value: number; color: string }>
}) {
  const total = segments.reduce((a, b) => a + b.value, 0) || 1
  const stops = segments.reduce<{ acc: number; parts: string[] }>(
    (state, seg) => {
      const start = (state.acc / total) * 100
      const end = ((state.acc + seg.value) / total) * 100
      state.parts.push(`${seg.color} ${start}% ${end}%`)
      state.acc += seg.value
      return state
    },
    { acc: 0, parts: [] },
  )

  return (
    <div className="flex items-center gap-5">
      <div
        className="relative h-40 w-40 shrink-0 rounded-full"
        style={{ background: `conic-gradient(${stops.parts.join(', ')})` }}
        role="img"
        aria-label="Distribución de estados"
      >
        <div className="absolute inset-5 rounded-full bg-card" />
        <div className="absolute inset-0 grid place-items-center">
          <div className="text-center">
            <div className="text-2xl font-semibold tabular-nums">{total}</div>
            <div className="text-xs text-muted-foreground">items</div>
          </div>
        </div>
      </div>

      <div className="space-y-2">
        {segments.map((s) => (
          <div key={s.label} className="flex items-center justify-between gap-6 text-sm">
            <div className="flex items-center gap-2">
              <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: s.color }} />
              <span className="text-muted-foreground">{s.label}</span>
            </div>
            <span className="font-medium tabular-nums">{s.value}</span>
          </div>
        ))}
      </div>
    </div>
  )
}

function HealthBadge({ health }: { health: Project['health'] }) {
  const styles =
    health === 'Excelente'
      ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
      : health === 'Bueno'
        ? 'border-sky-500/30 bg-sky-500/10 text-sky-700 dark:text-sky-300'
        : health === 'Atención'
          ? 'border-amber-500/30 bg-amber-500/10 text-amber-800 dark:text-amber-300'
          : 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300'

  return (
    <span className={cn('inline-flex rounded-full border px-2 py-0.5 text-[11px] font-medium', styles)}>
      {health}
    </span>
  )
}

function Table({
  title,
  icon: Icon,
  projects,
}: {
  title: string
  icon: React.ComponentType<{ className?: string }>
  projects: Project[]
}) {
  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-center justify-between space-y-0">
        <div className="space-y-1">
          <CardTitle className="text-base">{title}</CardTitle>
          <CardDescription>Últimos 30 días</CardDescription>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <Icon className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="text-xs text-muted-foreground">
            <tr className="border-b">
              <th className="py-2 text-left font-medium">Proyecto</th>
              <th className="py-2 text-left font-medium">Owner</th>
              <th className="py-2 text-left font-medium">Salud</th>
              <th className="py-2 text-right font-medium">Score</th>
            </tr>
          </thead>
          <tbody>
            {projects.map((p) => (
              <tr key={p.name} className="border-b last:border-b-0">
                <td className="py-3">
                  <div className="font-medium">{p.name}</div>
                  <div className="text-xs text-muted-foreground">Avance {p.completion}%</div>
                </td>
                <td className="py-3 text-muted-foreground">{p.owner}</td>
                <td className="py-3">
                  <HealthBadge health={p.health} />
                </td>
                <td className="py-3 text-right font-semibold tabular-nums">{p.score}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </CardContent>
    </Card>
  )
}

export function DashboardPage() {
  const kpis: Kpi[] = [
    { label: 'Proyectos Activos', value: '12', delta: 8.2, trend: 'up', icon: Gauge },
    { label: 'Tareas Totales', value: '1,284', delta: 3.1, trend: 'up', icon: BarChart3 },
    { label: 'Tareas Completadas', value: '946', delta: 6.7, trend: 'up', icon: CheckCircle2 },
    { label: 'Tareas Vencidas', value: '37', delta: -4.5, trend: 'down', icon: AlertTriangle },
    { label: 'Riesgos Detectados', value: '9', delta: 0.0, trend: 'flat', icon: ShieldAlert },
  ]

  const projectProgress = [
    { label: 'Plataforma Comercial', value: 78, meta: '78%' },
    { label: 'Data Warehouse', value: 64, meta: '64%' },
    { label: 'Migración Jira', value: 52, meta: '52%' },
    { label: 'App Mobile', value: 46, meta: '46%' },
    { label: 'Seguridad & Compliance', value: 38, meta: '38%' },
  ]

  const teamProductivity = [
    { label: 'Backend', value: 42, meta: 'tareas/semana' },
    { label: 'Frontend', value: 35, meta: 'tareas/semana' },
    { label: 'Data', value: 27, meta: 'tareas/semana' },
    { label: 'QA', value: 19, meta: 'tareas/semana' },
  ]

  const distribution = [
    { label: 'To Do', value: 214, color: 'hsl(217.2 91.2% 59.8%)' },
    { label: 'In Progress', value: 126, color: 'hsl(199 89% 48%)' },
    { label: 'Blocked', value: 31, color: 'hsl(0 84.2% 60.2%)' },
    { label: 'Done', value: 402, color: 'hsl(142.1 76.2% 36.3%)' },
  ]

  const activity = [
    { who: 'Ana', action: 'movió “Implementar SSO” a In Progress', when: 'Hace 8 min' },
    { who: 'Luis', action: 'cerró 5 tareas en “Data Warehouse”', when: 'Hace 32 min' },
    { who: 'María', action: 'creó alerta por tareas vencidas', when: 'Hoy 09:12' },
    { who: 'Diego', action: 'actualizó indicador de riesgo', when: 'Ayer 18:40' },
  ]

  const topBest: Project[] = [
    { name: 'Plataforma Comercial', owner: 'Comercial', health: 'Excelente', score: 92, completion: 78, overdueRate: 2 },
    { name: 'FinOps', owner: 'Operaciones', health: 'Bueno', score: 86, completion: 71, overdueRate: 3 },
    { name: 'Observabilidad', owner: 'Plataforma', health: 'Bueno', score: 84, completion: 66, overdueRate: 4 },
    { name: 'Onboarding', owner: 'Producto', health: 'Bueno', score: 81, completion: 59, overdueRate: 5 },
  ]

  const topRisk: Project[] = [
    { name: 'Seguridad & Compliance', owner: 'Seguridad', health: 'Crítico', score: 41, completion: 38, overdueRate: 18 },
    { name: 'Migración Jira', owner: 'PMO', health: 'Atención', score: 56, completion: 52, overdueRate: 12 },
    { name: 'App Mobile', owner: 'Digital', health: 'Atención', score: 59, completion: 46, overdueRate: 10 },
    { name: 'Data Warehouse', owner: 'Data', health: 'Atención', score: 62, completion: 64, overdueRate: 9 },
  ]

  return (
    <section className="space-y-6" aria-label="Contenido principal del dashboard">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold tracking-tight">Resumen ejecutivo</h1>
          <p className="text-sm text-muted-foreground">
            Indicadores clave y analítica operativa para líderes.
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <Button type="button" variant="outline" className="gap-2">
            <Activity className="h-4 w-4" />
            Últimos 7 días
            <ChevronDown className="h-4 w-4 text-muted-foreground" />
          </Button>
          <Button type="button" className="gap-2">
            <LineChart className="h-4 w-4" />
            Exportar
          </Button>
        </div>
      </div>

      <section aria-label="KPIs">
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
          {kpis.map((kpi) => (
            <KpiCard key={kpi.label} kpi={kpi} />
          ))}
        </div>
      </section>

      <section aria-label="Avances y productividad" className="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <Card className="shadow-sm">
          <CardHeader className="flex flex-row items-center justify-between space-y-0">
            <div className="space-y-1">
              <CardTitle className="text-base">Avance general de proyectos</CardTitle>
              <CardDescription>Progreso por iniciativa (selección)</CardDescription>
            </div>
            <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
              <Gauge className="h-4 w-4 text-muted-foreground" />
            </div>
          </CardHeader>
          <CardContent className="space-y-4">
            {projectProgress.map((p) => (
              <ProgressRow key={p.label} label={p.label} value={p.value} right={p.meta} />
            ))}
          </CardContent>
        </Card>

        <Card className="shadow-sm">
          <CardHeader className="flex flex-row items-center justify-between space-y-0">
            <div className="space-y-1">
              <CardTitle className="text-base">Productividad por equipo</CardTitle>
              <CardDescription>Throughput (tareas completadas)</CardDescription>
            </div>
            <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
              <Users className="h-4 w-4 text-muted-foreground" />
            </div>
          </CardHeader>
          <CardContent>
            <BarList items={teamProductivity} />
          </CardContent>
        </Card>
      </section>

      <section aria-label="Distribución y actividad" className="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <Card className="shadow-sm">
          <CardHeader className="flex flex-row items-center justify-between space-y-0">
            <div className="space-y-1">
              <CardTitle className="text-base">Distribución de estados</CardTitle>
              <CardDescription>Work items por estado (tableros)</CardDescription>
            </div>
            <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
              <BarChart3 className="h-4 w-4 text-muted-foreground" />
            </div>
          </CardHeader>
          <CardContent>
            <Donut segments={distribution} />
          </CardContent>
        </Card>

        <Card className="shadow-sm">
          <CardHeader className="flex flex-row items-center justify-between space-y-0">
            <div className="space-y-1">
              <CardTitle className="text-base">Actividad reciente</CardTitle>
              <CardDescription>Eventos relevantes para gerencia</CardDescription>
            </div>
            <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
              <Activity className="h-4 w-4 text-muted-foreground" />
            </div>
          </CardHeader>
          <CardContent className="space-y-3">
            {activity.map((a, idx) => (
              <div key={idx} className="flex items-start justify-between gap-4">
                <div className="min-w-0">
                  <p className="text-sm">
                    <span className="font-semibold">{a.who}</span>{' '}
                    <span className="text-muted-foreground">{a.action}</span>
                  </p>
                  <p className="mt-1 text-xs text-muted-foreground">{a.when}</p>
                </div>
                <div className="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary/60" aria-hidden="true" />
              </div>
            ))}
          </CardContent>
        </Card>
      </section>

      <section aria-label="Top proyectos" className="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <Table title="Top proyectos con mejor rendimiento" icon={CheckCircle2} projects={topBest} />
        <Table title="Top proyectos con riesgo" icon={ShieldAlert} projects={topRisk} />
      </section>
    </section>
  )
}
