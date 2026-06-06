import {
  AlertTriangle,
  BarChart3,
  Calendar,
  CheckCircle2,
  Eye,
  Kanban,
  LineChart,
  ListTodo,
  Search,
  Users,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react'
import { useId, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'

import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { Input } from '../components/ui/input'
import { Label } from '../components/ui/label'
import { cn } from '../lib/utils'

type ProjectStatus = 'Activo' | 'En riesgo' | 'En pausa' | 'Completado'

type Member = {
  name: string
}

type ProjectBoard = {
  id: string
  name: string
  status: ProjectStatus
  owner: string
  updatedAt: string
  progress: number
  tasksTotal: number
  tasksDone: number
  tasksOverdue: number
  members: Member[]
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

function initials(name: string) {
  const parts = name.trim().split(/\s+/).slice(0, 2)
  const letters = parts.map((p) => p[0]?.toUpperCase()).filter(Boolean)
  return letters.join('')
}

function clamp(n: number, min: number, max: number) {
  return Math.min(max, Math.max(min, n))
}

function formatDate(value: string) {
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return new Intl.DateTimeFormat('es-PE', { year: 'numeric', month: 'short', day: '2-digit' }).format(d)
}

function ProjectCard({
  board,
  onViewDetails,
  onAnalytics,
  onPowerBi,
}: {
  board: ProjectBoard
  onViewDetails: () => void
  onAnalytics: () => void
  onPowerBi: () => void
}) {
  const progress = clamp(board.progress, 0, 100)
  const doneRatio = board.tasksTotal > 0 ? Math.round((board.tasksDone / board.tasksTotal) * 100) : 0

  return (
    <Card className="shadow-sm">
      <CardHeader className="space-y-2">
        <div className="flex items-start justify-between gap-3">
          <div className="min-w-0">
            <CardTitle className="truncate text-base">{board.name}</CardTitle>
            <CardDescription className="mt-1 flex flex-wrap items-center gap-2">
              <StatusBadge status={board.status} />
              <span className="text-xs text-muted-foreground">Responsable: {board.owner}</span>
              <span className="text-xs text-muted-foreground">Actualizado: {formatDate(board.updatedAt)}</span>
            </CardDescription>
          </div>
          <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
            <Kanban className="h-5 w-5 text-muted-foreground" />
          </div>
        </div>

        <div className="space-y-2">
          <div className="flex items-center justify-between">
            <span className="text-xs text-muted-foreground">Avance</span>
            <span className="text-xs font-semibold tabular-nums">{progress}%</span>
          </div>
          <div className="h-2 w-full rounded-full bg-muted">
            <div className="h-2 rounded-full bg-primary" style={{ width: `${progress}%` }} aria-hidden="true" />
          </div>
          <div className="flex items-center justify-between text-[11px] text-muted-foreground">
            <span>Completadas</span>
            <span className="tabular-nums">{doneRatio}%</span>
          </div>
        </div>
      </CardHeader>

      <CardContent className="space-y-4">
        <div className="grid grid-cols-3 gap-3">
          <div className="rounded-lg border bg-muted/20 p-3">
            <div className="flex items-center gap-2 text-xs text-muted-foreground">
              <ListTodo className="h-4 w-4" />
              <span>T. totales</span>
            </div>
            <div className="mt-2 text-lg font-semibold tabular-nums">{board.tasksTotal}</div>
          </div>
          <div className="rounded-lg border bg-muted/20 p-3">
            <div className="flex items-center gap-2 text-xs text-muted-foreground">
              <CheckCircle2 className="h-4 w-4" />
              <span>Completadas</span>
            </div>
            <div className="mt-2 text-lg font-semibold tabular-nums">{board.tasksDone}</div>
          </div>
          <div className="rounded-lg border bg-muted/20 p-3">
            <div className="flex items-center gap-2 text-xs text-muted-foreground">
              <AlertTriangle className="h-4 w-4" />
              <span>Vencidas</span>
            </div>
            <div className="mt-2 text-lg font-semibold tabular-nums">{board.tasksOverdue}</div>
          </div>
        </div>

        <div className="flex items-center justify-between gap-3">
          <div className="flex min-w-0 items-center gap-2">
            <Users className="h-4 w-4 text-muted-foreground" />
            <span className="truncate text-xs text-muted-foreground">
              Miembros: {board.members.length}
            </span>
          </div>
          <div className="flex -space-x-2">
            {board.members.slice(0, 5).map((m) => (
              <span
                key={m.name}
                className="grid h-7 w-7 place-items-center rounded-full border bg-background text-[10px] font-semibold text-muted-foreground"
                title={m.name}
                aria-label={m.name}
              >
                {initials(m.name)}
              </span>
            ))}
            {board.members.length > 5 ? (
              <span className="grid h-7 w-7 place-items-center rounded-full border bg-muted/30 text-[10px] font-semibold text-muted-foreground">
                +{board.members.length - 5}
              </span>
            ) : null}
          </div>
        </div>

        <div className="grid grid-cols-1 gap-2 sm:grid-cols-3">
          <Button type="button" variant="outline" className="w-full gap-2" onClick={onViewDetails}>
            <Eye className="h-4 w-4" />
            Ver detalles
          </Button>
          <Button type="button" variant="outline" className="w-full gap-2" onClick={onAnalytics}>
            <BarChart3 className="h-4 w-4" />
            Analítica
          </Button>
          <Button type="button" variant="outline" className="w-full gap-2" onClick={onPowerBi}>
            <LineChart className="h-4 w-4" />
            Power BI
          </Button>
        </div>
      </CardContent>
    </Card>
  )
}

export function ProjectsPage() {
  const navigate = useNavigate()
  const searchId = useId()
  const statusId = useId()
  const ownerId = useId()
  const fromId = useId()
  const toId = useId()

  const [query, setQuery] = useState('')
  const [status, setStatus] = useState<ProjectStatus | 'Todos'>('Todos')
  const [owner, setOwner] = useState<string | 'Todos'>('Todos')
  const [from, setFrom] = useState<string>('')
  const [to, setTo] = useState<string>('')

  const [page, setPage] = useState(1)
  const pageSize = 8

  const boards: ProjectBoard[] = useMemo(
    () => [
      {
        id: 'b1',
        name: 'Plataforma Comercial',
        status: 'Activo',
        owner: 'Patricia',
        updatedAt: '2026-06-05',
        progress: 78,
        tasksTotal: 184,
        tasksDone: 142,
        tasksOverdue: 6,
        members: [{ name: 'Ana Torres' }, { name: 'Luis Ramos' }, { name: 'María Paredes' }, { name: 'Diego Soto' }],
      },
      {
        id: 'b2',
        name: 'Data Warehouse',
        status: 'En pausa',
        owner: 'Luis',
        updatedAt: '2026-06-02',
        progress: 64,
        tasksTotal: 276,
        tasksDone: 177,
        tasksOverdue: 14,
        members: [{ name: 'Luis Ramos' }, { name: 'Carla Vega' }, { name: 'Jorge Silva' }, { name: 'María Paredes' }, { name: 'Ana Torres' }],
      },
      {
        id: 'b3',
        name: 'Migración Jira',
        status: 'En riesgo',
        owner: 'María',
        updatedAt: '2026-06-04',
        progress: 52,
        tasksTotal: 198,
        tasksDone: 103,
        tasksOverdue: 22,
        members: [{ name: 'María Paredes' }, { name: 'Diego Soto' }, { name: 'Sofía León' }],
      },
      {
        id: 'b4',
        name: 'App Mobile',
        status: 'En riesgo',
        owner: 'Diego',
        updatedAt: '2026-06-01',
        progress: 46,
        tasksTotal: 152,
        tasksDone: 68,
        tasksOverdue: 19,
        members: [{ name: 'Diego Soto' }, { name: 'Ana Torres' }, { name: 'Bruno Rojas' }, { name: 'Camila Ríos' }],
      },
      {
        id: 'b5',
        name: 'Seguridad & Compliance',
        status: 'En riesgo',
        owner: 'Carla',
        updatedAt: '2026-05-30',
        progress: 38,
        tasksTotal: 121,
        tasksDone: 41,
        tasksOverdue: 25,
        members: [{ name: 'Carla Vega' }, { name: 'Jorge Silva' }],
      },
      {
        id: 'b6',
        name: 'Observabilidad',
        status: 'Activo',
        owner: 'Ana',
        updatedAt: '2026-06-06',
        progress: 66,
        tasksTotal: 96,
        tasksDone: 63,
        tasksOverdue: 3,
        members: [{ name: 'Ana Torres' }, { name: 'Bruno Rojas' }, { name: 'Sofía León' }, { name: 'Jorge Silva' }],
      },
      {
        id: 'b7',
        name: 'Onboarding',
        status: 'Activo',
        owner: 'Patricia',
        updatedAt: '2026-05-29',
        progress: 59,
        tasksTotal: 74,
        tasksDone: 44,
        tasksOverdue: 4,
        members: [{ name: 'Patricia M.' }, { name: 'María Paredes' }, { name: 'Camila Ríos' }],
      },
      {
        id: 'b8',
        name: 'FinOps',
        status: 'Completado',
        owner: 'Luis',
        updatedAt: '2026-05-20',
        progress: 100,
        tasksTotal: 58,
        tasksDone: 58,
        tasksOverdue: 0,
        members: [{ name: 'Luis Ramos' }, { name: 'Carla Vega' }, { name: 'Ana Torres' }],
      },
      {
        id: 'b9',
        name: 'Customer Insights',
        status: 'Activo',
        owner: 'Sofía',
        updatedAt: '2026-06-03',
        progress: 41,
        tasksTotal: 112,
        tasksDone: 46,
        tasksOverdue: 9,
        members: [{ name: 'Sofía León' }, { name: 'Jorge Silva' }, { name: 'Bruno Rojas' }, { name: 'María Paredes' }, { name: 'Ana Torres' }, { name: 'Carla Vega' }],
      },
      {
        id: 'b10',
        name: 'OKRs & Reporting',
        status: 'Activo',
        owner: 'Patricia',
        updatedAt: '2026-06-02',
        progress: 73,
        tasksTotal: 89,
        tasksDone: 65,
        tasksOverdue: 2,
        members: [{ name: 'Patricia M.' }, { name: 'Luis Ramos' }, { name: 'Sofía León' }],
      },
      {
        id: 'b11',
        name: 'Integración SSO',
        status: 'En pausa',
        owner: 'Carla',
        updatedAt: '2026-05-27',
        progress: 28,
        tasksTotal: 67,
        tasksDone: 19,
        tasksOverdue: 7,
        members: [{ name: 'Carla Vega' }, { name: 'Diego Soto' }],
      },
      {
        id: 'b12',
        name: 'Automatización QA',
        status: 'Activo',
        owner: 'María',
        updatedAt: '2026-06-05',
        progress: 61,
        tasksTotal: 105,
        tasksDone: 64,
        tasksOverdue: 5,
        members: [{ name: 'María Paredes' }, { name: 'Camila Ríos' }, { name: 'Bruno Rojas' }],
      },
    ],
    [],
  )

  const owners = useMemo(() => {
    const set = new Set<string>()
    boards.forEach((b) => set.add(b.owner))
    return Array.from(set).sort((a, b) => a.localeCompare(b))
  }, [boards])

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase()
    const fromDate = from ? new Date(from) : null
    const toDate = to ? new Date(to) : null
    if (toDate) toDate.setHours(23, 59, 59, 999)

    return boards.filter((b) => {
      if (q && !b.name.toLowerCase().includes(q)) return false
      if (status !== 'Todos' && b.status !== status) return false
      if (owner !== 'Todos' && b.owner !== owner) return false

      const updated = new Date(b.updatedAt)
      if (fromDate && updated < fromDate) return false
      if (toDate && updated > toDate) return false

      return true
    })
  }, [boards, query, status, owner, from, to])

  const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize))
  const safePage = clamp(page, 1, totalPages)

  const paged = useMemo(() => {
    const start = (safePage - 1) * pageSize
    return filtered.slice(start, start + pageSize)
  }, [filtered, safePage])

  const rangeLabel = useMemo(() => {
    if (filtered.length === 0) return '0'
    const start = (safePage - 1) * pageSize + 1
    const end = Math.min(filtered.length, safePage * pageSize)
    return `${start}–${end} de ${filtered.length}`
  }, [filtered.length, safePage])

  return (
    <div className="min-h-svh bg-background text-foreground">
      <header className="border-b bg-background/70 backdrop-blur">
        <div className="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-5 sm:px-6">
          <div className="flex items-start justify-between gap-4">
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                <Kanban className="h-5 w-5 text-primary" />
              </div>
              <div className="leading-tight">
                <h1 className="text-base font-semibold tracking-tight">Proyectos</h1>
                <p className="text-sm text-muted-foreground">
                  Boards sincronizados desde Trello (vista de tarjetas).
                </p>
              </div>
            </div>
            <div className="hidden sm:flex items-center gap-2 text-xs text-muted-foreground">
              <Calendar className="h-4 w-4" />
              <span>Última actualización: hoy</span>
            </div>
          </div>

          <div className="grid grid-cols-1 gap-3 lg:grid-cols-[1fr,auto] lg:items-end">
            <div className="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
              <div className="lg:col-span-2">
                <Label htmlFor={searchId} className="text-xs text-muted-foreground">
                  Buscador
                </Label>
                <div className="relative mt-1">
                  <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                  <Input
                    id={searchId}
                    value={query}
                    onChange={(e) => {
                      setQuery(e.target.value)
                      setPage(1)
                    }}
                    placeholder="Buscar por nombre del board…"
                    className="pl-9"
                  />
                </div>
              </div>

              <div>
                <Label htmlFor={statusId} className="text-xs text-muted-foreground">
                  Estado
                </Label>
                <select
                  id={statusId}
                  className={cn(
                    'mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                  )}
                  value={status}
                  onChange={(e) => {
                    setStatus(e.target.value as ProjectStatus | 'Todos')
                    setPage(1)
                  }}
                >
                  <option value="Todos">Todos</option>
                  <option value="Activo">Activo</option>
                  <option value="En riesgo">En riesgo</option>
                  <option value="En pausa">En pausa</option>
                  <option value="Completado">Completado</option>
                </select>
              </div>

              <div>
                <Label htmlFor={ownerId} className="text-xs text-muted-foreground">
                  Responsable
                </Label>
                <select
                  id={ownerId}
                  className={cn(
                    'mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                  )}
                  value={owner}
                  onChange={(e) => {
                    setOwner(e.target.value)
                    setPage(1)
                  }}
                >
                  <option value="Todos">Todos</option>
                  {owners.map((o) => (
                    <option key={o} value={o}>
                      {o}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <Label className="text-xs text-muted-foreground">Fecha</Label>
                <div className="mt-1 grid grid-cols-2 gap-2">
                  <Input
                    id={fromId}
                    type="date"
                    value={from}
                    onChange={(e) => {
                      setFrom(e.target.value)
                      setPage(1)
                    }}
                    aria-label="Desde"
                  />
                  <Input
                    id={toId}
                    type="date"
                    value={to}
                    onChange={(e) => {
                      setTo(e.target.value)
                      setPage(1)
                    }}
                    aria-label="Hasta"
                  />
                </div>
              </div>
            </div>

            <div className="flex flex-wrap items-center justify-between gap-3 lg:justify-end">
              <div className="text-xs text-muted-foreground">
                Mostrando <span className="font-semibold text-foreground">{rangeLabel}</span>
              </div>
              <div className="flex items-center gap-2">
                <Button
                  type="button"
                  variant="outline"
                  size="icon"
                  aria-label="Página anterior"
                  disabled={safePage <= 1}
                  onClick={() => setPage((p) => clamp(p - 1, 1, totalPages))}
                >
                  <ChevronLeft className="h-4 w-4" />
                </Button>
                <span className="min-w-[92px] text-center text-xs text-muted-foreground tabular-nums">
                  Página {safePage} / {totalPages}
                </span>
                <Button
                  type="button"
                  variant="outline"
                  size="icon"
                  aria-label="Página siguiente"
                  disabled={safePage >= totalPages}
                  onClick={() => setPage((p) => clamp(p + 1, 1, totalPages))}
                >
                  <ChevronRight className="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        {paged.length === 0 ? (
          <div className="rounded-xl border bg-muted/20 p-8 text-center">
            <p className="text-sm font-semibold">No hay boards para mostrar</p>
            <p className="mt-1 text-sm text-muted-foreground">
              Ajusta el buscador o los filtros para ver resultados.
            </p>
          </div>
        ) : (
          <section className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3" aria-label="Tarjetas de proyecto">
            {paged.map((b) => (
              <ProjectCard
                key={b.id}
                board={b}
                onViewDetails={() => navigate(`/projects/${b.id}`)}
                onAnalytics={() => navigate('/analytics')}
                onPowerBi={() => navigate('/powerbi')}
              />
            ))}
          </section>
        )}

        <div className="mt-6 flex flex-wrap items-center justify-between gap-3 border-t pt-4">
          <div className="text-xs text-muted-foreground">
            <span className="font-semibold text-foreground tabular-nums">{filtered.length}</span> boards encontrados
          </div>
          <div className="flex items-center gap-2">
            <Button
              type="button"
              variant="outline"
              disabled={safePage <= 1}
              onClick={() => setPage(1)}
            >
              Primera
            </Button>
            <Button
              type="button"
              variant="outline"
              disabled={safePage <= 1}
              onClick={() => setPage((p) => clamp(p - 1, 1, totalPages))}
            >
              Anterior
            </Button>
            <Button
              type="button"
              variant="outline"
              disabled={safePage >= totalPages}
              onClick={() => setPage((p) => clamp(p + 1, 1, totalPages))}
            >
              Siguiente
            </Button>
            <Button
              type="button"
              variant="outline"
              disabled={safePage >= totalPages}
              onClick={() => setPage(totalPages)}
            >
              Última
            </Button>
          </div>
        </div>
      </main>
    </div>
  )
}
