import {
  AlertCircle,
  Building2,
  CheckCircle2,
  Clock,
  Kanban,
  Link2,
  Mail,
  RefreshCw,
  Settings2,
  Unlink,
  User,
} from 'lucide-react'
import { useId, useMemo, useState } from 'react'

import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { Checkbox } from '../components/ui/checkbox'
import { Input } from '../components/ui/input'
import { Label } from '../components/ui/label'
import { cn } from '../lib/utils'

type Frequency = '15m' | '1h' | '6h' | '24h'
type SyncMode = 'auto' | 'manual'

type TrelloAccount = {
  name: string
  email: string
  workspaces: string[]
  lastSync: Date | null
}

function formatDateTime(value: Date) {
  return new Intl.DateTimeFormat('es-PE', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(value)
}

function StatusPill({ connected }: { connected: boolean }) {
  return (
    <span
      className={cn(
        'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium',
        connected
          ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
          : 'border-border bg-muted text-muted-foreground',
      )}
      role="status"
      aria-label={connected ? 'Conectado' : 'No conectado'}
    >
      {connected ? <CheckCircle2 className="h-4 w-4" /> : <AlertCircle className="h-4 w-4" />}
      {connected ? 'Conectado' : 'No conectado'}
    </span>
  )
}

function FieldRow({
  icon: Icon,
  label,
  value,
}: {
  icon: React.ComponentType<{ className?: string }>
  label: string
  value: React.ReactNode
}) {
  return (
    <div className="grid grid-cols-[24px,1fr] gap-3">
      <div className="mt-0.5 flex h-6 w-6 items-center justify-center rounded-md border bg-muted/30">
        <Icon className="h-3.5 w-3.5 text-muted-foreground" />
      </div>
      <div className="min-w-0">
        <p className="text-xs text-muted-foreground">{label}</p>
        <div className="truncate text-sm font-medium">{value}</div>
      </div>
    </div>
  )
}

export function TrelloIntegrationPage() {
  const [connected, setConnected] = useState(false)
  const [isConnecting, setIsConnecting] = useState(false)
  const [isSyncing, setIsSyncing] = useState(false)

  const [syncMode, setSyncMode] = useState<SyncMode>('auto')
  const [autoSyncEnabled, setAutoSyncEnabled] = useState(true)
  const [frequency, setFrequency] = useState<Frequency>('1h')

  const [account, setAccount] = useState<TrelloAccount>({
    name: '—',
    email: '—',
    workspaces: [],
    lastSync: null,
  })

  const modeId = useId()
  const freqId = useId()
  const autoId = useId()

  const frequencyLabel = useMemo(() => {
    switch (frequency) {
      case '15m':
        return 'Cada 15 minutos'
      case '1h':
        return 'Cada 1 hora'
      case '6h':
        return 'Cada 6 horas'
      case '24h':
        return 'Cada 24 horas'
    }
  }, [frequency])

  async function connect() {
    setIsConnecting(true)
    await new Promise((r) => setTimeout(r, 700))
    setConnected(true)
    setAccount({
      name: 'Patricia M.',
      email: 'patricia@empresa.com',
      workspaces: ['PMO', 'Delivery', 'Data & Analytics'],
      lastSync: new Date(),
    })
    setIsConnecting(false)
  }

  async function syncNow() {
    setIsSyncing(true)
    await new Promise((r) => setTimeout(r, 900))
    setAccount((p) => ({ ...p, lastSync: new Date() }))
    setIsSyncing(false)
  }

  function disconnect() {
    setConnected(false)
    setIsConnecting(false)
    setIsSyncing(false)
    setAccount({ name: '—', email: '—', workspaces: [], lastSync: null })
  }

  return (
    <div className="min-h-svh bg-background text-foreground">
      <header className="border-b bg-background/70 backdrop-blur">
        <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
              <Kanban className="h-5 w-5 text-primary" />
            </div>
            <div className="leading-tight">
              <h1 className="text-base font-semibold tracking-tight">Integración con Trello</h1>
              <p className="text-sm text-muted-foreground">
                Conecta tu cuenta para sincronizar proyectos y tableros.
              </p>
            </div>
          </div>
          <StatusPill connected={connected} />
        </div>
      </header>

      <main className="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">
        <section className="grid grid-cols-1 gap-4 lg:grid-cols-3">
          <Card className="lg:col-span-2">
            <CardHeader className="flex flex-row items-start justify-between space-y-0">
              <div className="space-y-1">
                <CardTitle className="text-base">Estado de conexión</CardTitle>
                <CardDescription>
                  Autoriza el acceso para detectar espacios de trabajo y sincronizar métricas.
                </CardDescription>
              </div>
              <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                <Link2 className="h-5 w-5 text-muted-foreground" />
              </div>
            </CardHeader>
            <CardContent className="space-y-5">
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-2">
                  <StatusPill connected={connected} />
                  <span className="text-xs text-muted-foreground">
                    {connected ? 'Permisos activos' : 'Sin permisos'}
                  </span>
                </div>

                <Button
                  type="button"
                  className="gap-2"
                  disabled={connected || isConnecting}
                  onClick={connect}
                >
                  <Link2 className="h-4 w-4" />
                  Conectar con Trello
                </Button>
              </div>

              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <FieldRow icon={User} label="Nombre de la cuenta" value={account.name} />
                <FieldRow icon={Mail} label="Correo" value={account.email} />
                <FieldRow
                  icon={Building2}
                  label="Espacios de trabajo detectados"
                  value={
                    account.workspaces.length > 0 ? account.workspaces.join(' · ') : '—'
                  }
                />
                <FieldRow
                  icon={Clock}
                  label="Fecha última sincronización"
                  value={account.lastSync ? formatDateTime(account.lastSync) : '—'}
                />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-start justify-between space-y-0">
              <div className="space-y-1">
                <CardTitle className="text-base">Acciones</CardTitle>
                <CardDescription>Control operativo de la integración.</CardDescription>
              </div>
              <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                <RefreshCw className="h-5 w-5 text-muted-foreground" />
              </div>
            </CardHeader>
            <CardContent className="space-y-3">
              <Button
                type="button"
                className="w-full gap-2"
                disabled={!connected || isSyncing}
                onClick={syncNow}
              >
                <RefreshCw className={cn('h-4 w-4', isSyncing && 'animate-spin')} />
                Sincronizar ahora
              </Button>

              <Button
                type="button"
                variant="outline"
                className="w-full gap-2"
                disabled={!connected}
                onClick={disconnect}
              >
                <Unlink className="h-4 w-4" />
                Desconectar
              </Button>

              <div className="rounded-lg border bg-muted/20 p-3 text-xs text-muted-foreground">
                La sincronización puede tardar unos minutos según la cantidad de tableros y
                tarjetas.
              </div>
            </CardContent>
          </Card>
        </section>

        <section>
          <Card>
            <CardHeader className="flex flex-row items-start justify-between space-y-0">
              <div className="space-y-1">
                <CardTitle className="text-base">Configuración de sincronización</CardTitle>
                <CardDescription>
                  Define cómo y cuándo se actualizan los datos desde Trello.
                </CardDescription>
              </div>
              <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                <Settings2 className="h-5 w-5 text-muted-foreground" />
              </div>
            </CardHeader>
            <CardContent className="grid grid-cols-1 gap-6 lg:grid-cols-3">
              <div className="space-y-3 lg:col-span-2">
                <div className="space-y-2">
                  <Label htmlFor={modeId}>Modo</Label>
                  <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <button
                      type="button"
                      className={cn(
                        'flex items-start gap-3 rounded-xl border p-4 text-left transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                        syncMode === 'auto'
                          ? 'border-primary/40 bg-primary/5'
                          : 'hover:bg-muted/40',
                      )}
                      onClick={() => setSyncMode('auto')}
                      aria-pressed={syncMode === 'auto'}
                    >
                      <div
                        className={cn(
                          'mt-0.5 h-4 w-4 rounded-full border',
                          syncMode === 'auto' && 'border-primary bg-primary',
                        )}
                        aria-hidden="true"
                      />
                      <div className="min-w-0">
                        <p className="text-sm font-semibold">Sincronización automática</p>
                        <p className="mt-1 text-xs text-muted-foreground">
                          Actualiza en segundo plano según la frecuencia configurada.
                        </p>
                      </div>
                    </button>

                    <button
                      type="button"
                      className={cn(
                        'flex items-start gap-3 rounded-xl border p-4 text-left transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                        syncMode === 'manual'
                          ? 'border-primary/40 bg-primary/5'
                          : 'hover:bg-muted/40',
                      )}
                      onClick={() => setSyncMode('manual')}
                      aria-pressed={syncMode === 'manual'}
                    >
                      <div
                        className={cn(
                          'mt-0.5 h-4 w-4 rounded-full border',
                          syncMode === 'manual' && 'border-primary bg-primary',
                        )}
                        aria-hidden="true"
                      />
                      <div className="min-w-0">
                        <p className="text-sm font-semibold">Sincronización manual</p>
                        <p className="mt-1 text-xs text-muted-foreground">
                          Actualiza solo cuando se ejecuta “Sincronizar ahora”.
                        </p>
                      </div>
                    </button>
                  </div>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div className="space-y-2">
                    <Label htmlFor={freqId}>Frecuencia de actualización</Label>
                    <div className="relative">
                      <Input
                        id={freqId}
                        readOnly
                        value={frequencyLabel}
                        aria-label="Frecuencia de actualización (solo lectura)"
                        className="pr-28"
                      />
                      <select
                        className={cn(
                          'absolute right-1 top-1/2 h-8 -translate-y-1/2 rounded-md border bg-background px-2 text-xs text-foreground',
                          'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                        )}
                        aria-label="Seleccionar frecuencia"
                        value={frequency}
                        onChange={(e) => setFrequency(e.target.value as Frequency)}
                        disabled={syncMode !== 'auto' || !connected}
                      >
                        <option value="15m">15m</option>
                        <option value="1h">1h</option>
                        <option value="6h">6h</option>
                        <option value="24h">24h</option>
                      </select>
                    </div>
                    <p className="text-xs text-muted-foreground">
                      Disponible solo en modo automático.
                    </p>
                  </div>

                  <div className="space-y-2">
                    <Label>Sincronización automática</Label>
                    <div className="flex items-center gap-2 rounded-xl border p-4">
                      <Checkbox
                        id={autoId}
                        checked={autoSyncEnabled}
                        onCheckedChange={(v) => setAutoSyncEnabled(Boolean(v))}
                        disabled={syncMode !== 'auto' || !connected}
                      />
                      <div className="min-w-0">
                        <Label htmlFor={autoId} className="text-sm">
                          Habilitar
                        </Label>
                        <p className="mt-1 text-xs text-muted-foreground">
                          Ejecuta sincronizaciones periódicas sin intervención.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div className="space-y-4">
                <div className="rounded-xl border bg-muted/20 p-4">
                  <p className="text-sm font-semibold">Recomendación</p>
                  <p className="mt-1 text-xs text-muted-foreground">
                    Para uso ejecutivo, mantén “Automática” con frecuencia 1h para equilibrar
                    actualidad y carga.
                  </p>
                </div>

                <div className="rounded-xl border bg-muted/20 p-4">
                  <p className="text-sm font-semibold">Seguridad</p>
                  <p className="mt-1 text-xs text-muted-foreground">
                    Los tokens se almacenan de forma segura del lado servidor. Esta pantalla solo
                    gestiona la configuración.
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </section>
      </main>
    </div>
  )
}

