import {
  Activity,
  AlertTriangle,
  Bell,
  ChevronDown,
  Clock,
  Search,
  ShieldAlert,
  SignalLow,
  SignalMedium,
  SignalHigh,
} from 'lucide-react'
import { useMemo, useState } from 'react'

import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { Input } from '../components/ui/input'
import { Label } from '../components/ui/label'
import { cn } from '../lib/utils'

type Severity = 'Riesgo Alto' | 'Riesgo Medio' | 'Riesgo Bajo'

type AlertItem = {
  id: string
  severity: Severity
  date: string
  project: string
  title: string
  reason: string
  recommendedAction: string
}

function formatDateTime(value: string) {
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

function SeverityPill({ severity }: { severity: Severity }) {
  const styles =
    severity === 'Riesgo Alto'
      ? 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300'
      : severity === 'Riesgo Medio'
        ? 'border-amber-500/30 bg-amber-500/10 text-amber-800 dark:text-amber-300'
        : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'

  const Icon =
    severity === 'Riesgo Alto' ? SignalHigh : severity === 'Riesgo Medio' ? SignalMedium : SignalLow

  return (
    <span className={cn('inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium', styles)}>
      <Icon className="h-4 w-4" />
      {severity}
    </span>
  )
}

function CategoryButton({
  active,
  label,
  count,
  onClick,
}: {
  active: boolean
  label: Severity | 'Todas'
  count: number
  onClick: () => void
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        'inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium transition-colors',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
        active ? 'border-primary/40 bg-primary/5 text-foreground' : 'bg-background hover:bg-muted',
      )}
      aria-pressed={active}
    >
      <span className="text-muted-foreground">{label}</span>
      <span className="rounded-full border bg-muted/30 px-2 py-0.5 text-xs tabular-nums text-muted-foreground">
        {count}
      </span>
    </button>
  )
}

export function AlertsPage() {
  const [query, setQuery] = useState('')
  const [category, setCategory] = useState<Severity | 'Todas'>('Todas')
  const [project, setProject] = useState<string | 'Todos'>('Todos')
  const [from, setFrom] = useState('2026-05-20')
  const [to, setTo] = useState('2026-06-06')

  const alerts: AlertItem[] = useMemo(
    () => [
      {
        id: 'a1',
        severity: 'Riesgo Alto',
        date: '2026-06-06T10:12:00',
        project: 'Seguridad & Compliance',
        title: 'Muchas tareas vencidas',
        reason: '25 tareas vencidas en los últimos 7 días (umbral: 10).',
        recommendedAction: 'Priorizar backlog crítico y reasignar capacidad por 48h.',
      },
      {
        id: 'a2',
        severity: 'Riesgo Alto',
        date: '2026-06-05T17:40:00',
        project: 'App Mobile',
        title: 'Sobrecarga de usuarios',
        reason: '2 miembros concentran el 61% de tareas “In Progress”.',
        recommendedAction: 'Redistribuir asignaciones y limitar WIP por persona.',
      },
      {
        id: 'a3',
        severity: 'Riesgo Medio',
        date: '2026-06-05T09:05:00',
        project: 'Migración Jira',
        title: 'Baja productividad',
        reason: 'Throughput -18% vs periodo anterior.',
        recommendedAction: 'Revisar bloqueos, dependencias y redefinir objetivos de sprint.',
      },
      {
        id: 'a4',
        severity: 'Riesgo Medio',
        date: '2026-06-04T12:22:00',
        project: 'Data Warehouse',
        title: 'Falta de actividad',
        reason: 'Sin movimientos en listas clave por 48h.',
        recommendedAction: 'Confirmar disponibilidad del equipo y activar plan de recuperación.',
      },
      {
        id: 'a5',
        severity: 'Riesgo Bajo',
        date: '2026-06-03T16:10:00',
        project: 'Observabilidad',
        title: 'Aumento de Cycle Time',
        reason: 'Cycle Time +0.4 días (promedio) en la última semana.',
        recommendedAction: 'Auditar handoffs y reducir tareas grandes (splitting).',
      },
      {
        id: 'a6',
        severity: 'Riesgo Bajo',
        date: '2026-06-02T08:35:00',
        project: 'Plataforma Comercial',
        title: 'Riesgo de dependencia',
        reason: '2 tareas bloqueadas por proveedor externo.',
        recommendedAction: 'Escalar dependencias y definir alternativas (fallback).',
      },
    ],
    [],
  )

  const projects = useMemo(() => {
    const set = new Set<string>()
    alerts.forEach((a) => set.add(a.project))
    return Array.from(set).sort((a, b) => a.localeCompare(b))
  }, [alerts])

  const counts = useMemo(() => {
    const by: Record<Severity, number> = {
      'Riesgo Alto': 0,
      'Riesgo Medio': 0,
      'Riesgo Bajo': 0,
    }
    alerts.forEach((a) => {
      by[a.severity] += 1
    })
    return by
  }, [alerts])

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase()
    const fromDate = from ? new Date(from) : null
    const toDate = to ? new Date(to) : null
    if (toDate) toDate.setHours(23, 59, 59, 999)

    return alerts
      .filter((a) => {
        if (category !== 'Todas' && a.severity !== category) return false
        if (project !== 'Todos' && a.project !== project) return false
        if (q) {
          const hay = `${a.title} ${a.project} ${a.reason} ${a.recommendedAction}`.toLowerCase()
          if (!hay.includes(q)) return false
        }
        const d = new Date(a.date)
        if (fromDate && d < fromDate) return false
        if (toDate && d > toDate) return false
        return true
      })
      .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
  }, [alerts, category, project, query, from, to])

  return (
    <div className="min-h-svh bg-background text-foreground">
      <header className="border-b bg-background/70 backdrop-blur">
        <div className="mx-auto max-w-6xl px-4 py-5 sm:px-6">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div className="flex items-start gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                <Bell className="h-5 w-5 text-primary" />
              </div>
              <div className="min-w-0">
                <h1 className="text-lg font-semibold tracking-tight">Alertas</h1>
                <p className="mt-1 text-sm text-muted-foreground">
                  Centro de monitoreo para situaciones críticas detectadas automáticamente.
                </p>
              </div>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <Button type="button" variant="outline" className="gap-2">
                <Clock className="h-4 w-4" />
                Últimos 30 días
                <ChevronDown className="h-4 w-4 text-muted-foreground" />
              </Button>
              <Button type="button" className="gap-2">
                <ShieldAlert className="h-4 w-4" />
                Revisar reglas
              </Button>
            </div>
          </div>

          <div className="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-[1fr,1fr,1fr,auto] lg:items-end">
            <div className="lg:col-span-2">
              <Label className="text-xs text-muted-foreground">Buscador</Label>
              <div className="relative mt-1">
                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  placeholder="Buscar por proyecto, severidad o causa…"
                  className="pl-9"
                  aria-label="Buscar alertas"
                />
              </div>
            </div>

            <div>
              <Label className="text-xs text-muted-foreground">Proyecto</Label>
              <select
                className={cn(
                  'mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm',
                  'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                )}
                value={project}
                onChange={(e) => setProject(e.target.value)}
                aria-label="Filtrar por proyecto"
              >
                <option value="Todos">Todos</option>
                {projects.map((p) => (
                  <option key={p} value={p}>
                    {p}
                  </option>
                ))}
              </select>
            </div>

            <div className="grid grid-cols-2 gap-2 rounded-xl border bg-muted/10 p-3">
              <div>
                <Label className="text-[11px] text-muted-foreground">Desde</Label>
                <Input type="date" value={from} onChange={(e) => setFrom(e.target.value)} aria-label="Desde" />
              </div>
              <div>
                <Label className="text-[11px] text-muted-foreground">Hasta</Label>
                <Input type="date" value={to} onChange={(e) => setTo(e.target.value)} aria-label="Hasta" />
              </div>
            </div>
          </div>

          <div className="mt-4 flex flex-wrap items-center gap-2">
            <CategoryButton
              active={category === 'Todas'}
              label="Todas"
              count={counts['Riesgo Alto'] + counts['Riesgo Medio'] + counts['Riesgo Bajo']}
              onClick={() => setCategory('Todas')}
            />
            <CategoryButton
              active={category === 'Riesgo Alto'}
              label="Riesgo Alto"
              count={counts['Riesgo Alto']}
              onClick={() => setCategory('Riesgo Alto')}
            />
            <CategoryButton
              active={category === 'Riesgo Medio'}
              label="Riesgo Medio"
              count={counts['Riesgo Medio']}
              onClick={() => setCategory('Riesgo Medio')}
            />
            <CategoryButton
              active={category === 'Riesgo Bajo'}
              label="Riesgo Bajo"
              count={counts['Riesgo Bajo']}
              onClick={() => setCategory('Riesgo Bajo')}
            />
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">
        <section aria-label="Resumen">
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Card className="shadow-sm">
              <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
                <div className="space-y-1">
                  <CardDescription>Riesgo Alto</CardDescription>
                  <CardTitle className="text-3xl tabular-nums">{counts['Riesgo Alto']}</CardTitle>
                </div>
                <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
                  <AlertTriangle className="h-4 w-4 text-rose-500" />
                </div>
              </CardHeader>
              <CardContent className="pt-2 text-xs text-muted-foreground">
                Atención inmediata y mitigación.
              </CardContent>
            </Card>

            <Card className="shadow-sm">
              <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
                <div className="space-y-1">
                  <CardDescription>Riesgo Medio</CardDescription>
                  <CardTitle className="text-3xl tabular-nums">{counts['Riesgo Medio']}</CardTitle>
                </div>
                <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
                  <SignalMedium className="h-4 w-4 text-amber-500" />
                </div>
              </CardHeader>
              <CardContent className="pt-2 text-xs text-muted-foreground">
                Revisar tendencias y corregir desvíos.
              </CardContent>
            </Card>

            <Card className="shadow-sm">
              <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
                <div className="space-y-1">
                  <CardDescription>Riesgo Bajo</CardDescription>
                  <CardTitle className="text-3xl tabular-nums">{counts['Riesgo Bajo']}</CardTitle>
                </div>
                <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
                  <SignalLow className="h-4 w-4 text-emerald-500" />
                </div>
              </CardHeader>
              <CardContent className="pt-2 text-xs text-muted-foreground">
                Monitoreo continuo y prevención.
              </CardContent>
            </Card>
          </div>
        </section>

        <section aria-label="Lista de alertas">
          <Card className="shadow-sm">
            <CardHeader className="flex flex-row items-start justify-between space-y-0">
              <div className="space-y-1">
                <CardTitle className="text-base">Situaciones detectadas</CardTitle>
                <CardDescription>
                  Severidad, fecha, proyecto y acción recomendada.
                </CardDescription>
              </div>
              <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
                <Activity className="h-4 w-4 text-muted-foreground" />
              </div>
            </CardHeader>
            <CardContent className="space-y-3">
              {filtered.length === 0 ? (
                <div className="rounded-xl border bg-muted/20 p-8 text-center">
                  <p className="text-sm font-semibold">No hay alertas para mostrar</p>
                  <p className="mt-1 text-sm text-muted-foreground">
                    Ajusta filtros o cambia el rango de fechas.
                  </p>
                </div>
              ) : (
                <div className="space-y-3">
                  {filtered.map((a) => (
                    <div
                      key={a.id}
                      className="rounded-xl border bg-muted/10 p-4 transition-colors hover:bg-muted/20"
                    >
                      <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div className="min-w-0 space-y-2">
                          <div className="flex flex-wrap items-center gap-2">
                            <SeverityPill severity={a.severity} />
                            <span className="text-xs text-muted-foreground">{formatDateTime(a.date)}</span>
                            <span className="text-xs text-muted-foreground">•</span>
                            <span className="text-xs font-semibold text-foreground">{a.project}</span>
                          </div>
                          <p className="text-sm font-semibold">{a.title}</p>
                          <p className="text-sm text-muted-foreground">{a.reason}</p>
                          <div className="rounded-lg border bg-background p-3">
                            <p className="text-xs text-muted-foreground">Acción recomendada</p>
                            <p className="mt-1 text-sm font-medium">{a.recommendedAction}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </section>
      </main>
    </div>
  )
}

