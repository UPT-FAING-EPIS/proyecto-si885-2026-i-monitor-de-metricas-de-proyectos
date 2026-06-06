import {
  Activity,
  AlertTriangle,
  BarChart3,
  CalendarClock,
  CheckCircle2,
  ChevronDown,
  Clock,
  Gauge,
  Kanban,
  MessageSquareText,
  ShieldAlert,
  Users,
} from 'lucide-react'
import { useMemo } from 'react'

import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { cn } from '../lib/utils'

type Trend = 'up' | 'down' | 'flat'
type ProjectStatus = 'Activo' | 'En riesgo' | 'En pausa' | 'Completado'

type Kpi = {
  label: string
  value: string
  helper: string
  delta: number
  trend: Trend
  icon: React.ComponentType<{ className?: string }>
}

type Member = {
  name: string
  role: string
  assigned: number
  done: number
}

type ActivityItem = {
  type: 'comentario' | 'cambio' | 'movimiento'
  title: string
  detail: string
  when: string
}

function initials(name: string) {
  const parts = name.trim().split(/\s+/).slice(0, 2)
  const letters = parts.map((p) => p[0]?.toUpperCase()).filter(Boolean)
  return letters.join('')
}

function StatusBadge({ status }: { status: ProjectStatus }) {
  const styles =
    status === 'Activo'
      ? 'border-sky-500/30 bg-sky-500/10 text-sky-700 dark:text-sky-300'
      : status === 'En riesgo'
        ? 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300'
        : status === 'En pausa'
          ? 'border-amber-500/30 bg-amber-500/10 text-amber-800 dark:text-amber-300'
          : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'

  return (
    <span className={cn('inline-flex rounded-full border px-2 py-0.5 text-[11px] font-medium', styles)}>
      {status}
    </span>
  )
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

function Sparkline({ trend }: { trend: Trend }) {
  const points = useMemo(() => {
    if (trend === 'up') return '2,14 8,11 14,12 20,8 26,7 32,4'
    if (trend === 'down') return '2,5 8,7 14,6 20,9 26,12 32,14'
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
          <p className="text-xs text-muted-foreground">{kpi.helper}</p>
        </div>
        <div className="flex items-center gap-2">
          <Sparkline trend={kpi.trend} />
          <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
            <Icon className="h-4 w-4 text-muted-foreground" />
          </div>
        </div>
      </CardHeader>
      <CardContent className="flex items-center justify-between pt-2">
        <TrendPill delta={kpi.delta} trend={kpi.trend} />
        <span className="text-[11px] text-muted-foreground">vs. 14 días</span>
      </CardContent>
    </Card>
  )
}

function LineChart({
  title,
  description,
  data,
}: {
  title: string
  description: string
  data: Array<{ x: string; y: number }>
}) {
  const min = Math.min(...data.map((d) => d.y))
  const max = Math.max(...data.map((d) => d.y))
  const range = Math.max(1, max - min)

  const points = data
    .map((d, idx) => {
      const x = (idx / Math.max(1, data.length - 1)) * 100
      const y = 100 - ((d.y - min) / range) * 100
      return `${x},${y}`
    })
    .join(' ')

  const area = `0,100 ${points} 100,100`

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
      <CardContent className="space-y-4">
        <div className="rounded-xl border bg-muted/10 p-3">
          <svg viewBox="0 0 100 100" className="h-44 w-full" role="img" aria-label={title}>
            <defs>
              <linearGradient id="area" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stopColor="hsl(var(--primary))" stopOpacity="0.25" />
                <stop offset="100%" stopColor="hsl(var(--primary))" stopOpacity="0.02" />
              </linearGradient>
            </defs>
            <path d={`M${area}`} fill="url(#area)" />
            <polyline
              fill="none"
              stroke="hsl(var(--primary))"
              strokeWidth="2.2"
              strokeLinejoin="round"
              strokeLinecap="round"
              points={points}
            />
            {[20, 40, 60, 80].map((x) => (
              <line key={x} x1={x} y1={0} x2={x} y2={100} stroke="hsl(var(--border))" strokeWidth="0.6" />
            ))}
            {[25, 50, 75].map((y) => (
              <line key={y} x1={0} y1={y} x2={100} y2={y} stroke="hsl(var(--border))" strokeWidth="0.6" />
            ))}
          </svg>
        </div>
        <div className="flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground">
          <span>{data[0]?.x}</span>
          <span>{data.at(-1)?.x}</span>
        </div>
      </CardContent>
    </Card>
  )
}

function BarList({
  title,
  description,
  items,
}: {
  title: string
  description: string
  items: Array<{ label: string; value: number; meta?: string }>
}) {
  const max = Math.max(...items.map((i) => i.value), 1)
  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0">
        <div className="space-y-1">
          <CardTitle className="text-base">{title}</CardTitle>
          <CardDescription>{description}</CardDescription>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <Users className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent className="space-y-3">
        {items.map((i) => (
          <div key={i.label} className="grid grid-cols-[1fr,auto] items-center gap-3">
            <div className="space-y-1">
              <div className="flex items-center justify-between text-sm">
                <span className="text-muted-foreground">{i.label}</span>
                <span className="text-xs text-muted-foreground">{i.meta}</span>
              </div>
              <div className="h-2 w-full rounded-full bg-muted">
                <div className="h-2 rounded-full bg-sky-500" style={{ width: `${(i.value / max) * 100}%` }} />
              </div>
            </div>
            <div className="text-sm font-semibold tabular-nums">{i.value}</div>
          </div>
        ))}
      </CardContent>
    </Card>
  )
}

function Donut({
  title,
  description,
  segments,
}: {
  title: string
  description: string
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
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0">
        <div className="space-y-1">
          <CardTitle className="text-base">{title}</CardTitle>
          <CardDescription>{description}</CardDescription>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <Kanban className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div className="flex flex-col gap-5 sm:flex-row sm:items-center">
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
          <div className="w-full space-y-2">
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
      </CardContent>
    </Card>
  )
}

function MembersTable({ members }: { members: Member[] }) {
  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0">
        <div className="space-y-1">
          <CardTitle className="text-base">Miembros</CardTitle>
          <CardDescription>Asignación y cierre de tareas por persona</CardDescription>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <Users className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="text-xs text-muted-foreground">
            <tr className="border-b">
              <th className="py-2 text-left font-medium">Miembro</th>
              <th className="py-2 text-left font-medium">Rol</th>
              <th className="py-2 text-right font-medium">Asignadas</th>
              <th className="py-2 text-right font-medium">Cerradas</th>
            </tr>
          </thead>
          <tbody>
            {members.map((m) => (
              <tr key={m.name} className="border-b last:border-b-0">
                <td className="py-3">
                  <div className="flex items-center gap-3">
                    <span className="grid h-8 w-8 place-items-center rounded-full border bg-muted/30 text-xs font-semibold text-muted-foreground">
                      {initials(m.name)}
                    </span>
                    <div className="min-w-0">
                      <div className="font-medium">{m.name}</div>
                      <div className="text-xs text-muted-foreground">
                        Eficiencia{' '}
                        <span className="font-semibold tabular-nums">
                          {m.assigned > 0 ? Math.round((m.done / m.assigned) * 100) : 0}%
                        </span>
                      </div>
                    </div>
                  </div>
                </td>
                <td className="py-3 text-muted-foreground">{m.role}</td>
                <td className="py-3 text-right font-semibold tabular-nums">{m.assigned}</td>
                <td className="py-3 text-right font-semibold tabular-nums">{m.done}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </CardContent>
    </Card>
  )
}

function ActivityList({ items }: { items: ActivityItem[] }) {
  const iconByType: Record<ActivityItem['type'], React.ComponentType<{ className?: string }>> = {
    comentario: MessageSquareText,
    cambio: Clock,
    movimiento: Activity,
  }
  return (
    <Card className="shadow-sm">
      <CardHeader className="flex flex-row items-start justify-between space-y-0">
        <div className="space-y-1">
          <CardTitle className="text-base">Actividad reciente</CardTitle>
          <CardDescription>Comentarios, cambios y movimientos</CardDescription>
        </div>
        <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
          <Activity className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent className="space-y-3">
        {items.map((a, idx) => {
          const Icon = iconByType[a.type]
          return (
            <div key={idx} className="flex items-start gap-3 rounded-xl border bg-muted/10 p-3">
              <div className="mt-0.5 flex h-8 w-8 items-center justify-center rounded-lg border bg-background">
                <Icon className="h-4 w-4 text-muted-foreground" />
              </div>
              <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <p className="text-sm font-semibold">{a.title}</p>
                  <span className="text-xs text-muted-foreground">{a.when}</span>
                </div>
                <p className="mt-1 text-sm text-muted-foreground">{a.detail}</p>
              </div>
            </div>
          )
        })}
      </CardContent>
    </Card>
  )
}

export function ProjectDetailPage() {
  const projectName = 'Plataforma Comercial'
  const status: ProjectStatus = 'Activo'
  const lastSync = '06 jun 2026 · 12:40'

  const kpis: Kpi[] = [
    { label: 'Avance', value: '78%', helper: 'Progreso del board', delta: 4.3, trend: 'up', icon: Gauge },
    { label: 'Tareas abiertas', value: '42', helper: 'Pendientes / en progreso', delta: -2.1, trend: 'down', icon: Kanban },
    { label: 'Tareas cerradas', value: '142', helper: 'Últimos 30 días', delta: 6.8, trend: 'up', icon: CheckCircle2 },
    { label: 'Tareas vencidas', value: '6', helper: 'Requieren atención', delta: -1.4, trend: 'down', icon: AlertTriangle },
    { label: 'Riesgos', value: '3', helper: 'Detectados por reglas', delta: 0.0, trend: 'flat', icon: ShieldAlert },
  ]

  const temporal = [
    { x: 'Sem 1', y: 38 },
    { x: 'Sem 2', y: 46 },
    { x: 'Sem 3', y: 52 },
    { x: 'Sem 4', y: 61 },
    { x: 'Sem 5', y: 69 },
    { x: 'Sem 6', y: 78 },
  ]

  const productivity = [
    { label: 'Ana', value: 28, meta: 'tareas cerradas' },
    { label: 'Luis', value: 22, meta: 'tareas cerradas' },
    { label: 'María', value: 18, meta: 'tareas cerradas' },
    { label: 'Diego', value: 14, meta: 'tareas cerradas' },
  ]

  const distribution = [
    { label: 'To Do', value: 21, color: 'hsl(217.2 91.2% 59.8%)' },
    { label: 'In Progress', value: 14, color: 'hsl(199 89% 48%)' },
    { label: 'Blocked', value: 4, color: 'hsl(0 84.2% 60.2%)' },
    { label: 'Done', value: 142, color: 'hsl(142.1 76.2% 36.3%)' },
  ]

  const members: Member[] = [
    { name: 'Ana Torres', role: 'Frontend', assigned: 36, done: 28 },
    { name: 'Luis Ramos', role: 'Backend', assigned: 30, done: 22 },
    { name: 'María Paredes', role: 'PM', assigned: 22, done: 18 },
    { name: 'Diego Soto', role: 'QA', assigned: 18, done: 14 },
  ]

  const activity: ActivityItem[] = [
    {
      type: 'comentario',
      title: 'Comentario en “Habilitar SSO”',
      detail: 'Se solicitó validar impacto en usuarios externos antes del despliegue.',
      when: 'Hace 12 min',
    },
    {
      type: 'movimiento',
      title: 'Movimiento de tarjeta',
      detail: '“Métricas de SLA” pasó de To Do a In Progress.',
      when: 'Hace 40 min',
    },
    {
      type: 'cambio',
      title: 'Cambio de etiqueta',
      detail: 'Se marcó “Riesgo: Dependencia” en “Integración Trello API”.',
      when: 'Hoy 09:05',
    },
  ]

  return (
    <div className="min-h-svh bg-background text-foreground">
      <header className="border-b bg-background/70 backdrop-blur">
        <div className="mx-auto max-w-6xl px-4 py-5 sm:px-6">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div className="flex items-start gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                <Kanban className="h-5 w-5 text-primary" />
              </div>
              <div className="min-w-0">
                <div className="flex flex-wrap items-center gap-2">
                  <h1 className="truncate text-lg font-semibold tracking-tight">{projectName}</h1>
                  <StatusBadge status={status} />
                </div>
                <div className="mt-1 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                  <span className="inline-flex items-center gap-2">
                    <CalendarClock className="h-4 w-4" />
                    Sincronización: {lastSync}
                  </span>
                </div>
              </div>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <Button type="button" variant="outline" className="gap-2">
                <Clock className="h-4 w-4" />
                Últimos 30 días
                <ChevronDown className="h-4 w-4 text-muted-foreground" />
              </Button>
              <Button type="button" className="gap-2">
                <BarChart3 className="h-4 w-4" />
                Exportar
              </Button>
            </div>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">
        <section aria-label="KPIs">
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            {kpis.map((k) => (
              <KpiCard key={k.label} kpi={k} />
            ))}
          </div>
        </section>

        <section aria-label="Gráficos" className="grid grid-cols-1 gap-4 xl:grid-cols-3">
          <div className="xl:col-span-2">
            <LineChart
              title="Avance temporal"
              description="Evolución del progreso (semanal)"
              data={temporal}
            />
          </div>
          <BarList
            title="Productividad por miembro"
            description="Cierre de tareas (últimos 30 días)"
            items={productivity}
          />
        </section>

        <section aria-label="Distribución y miembros" className="grid grid-cols-1 gap-4 xl:grid-cols-3">
          <div className="xl:col-span-1">
            <Donut
              title="Distribución de estados"
              description="Work items por estado"
              segments={distribution}
            />
          </div>
          <div className="xl:col-span-2">
            <MembersTable members={members} />
          </div>
        </section>

        <section aria-label="Actividad reciente">
          <ActivityList items={activity} />
        </section>
      </main>
    </div>
  )
}
