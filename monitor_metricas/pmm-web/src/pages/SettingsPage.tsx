import {
  Bell,
  ChevronDown,
  Kanban,
  KeyRound,
  Link2,
  Lock,
  ShieldCheck,
  User,
} from 'lucide-react'
import { useId, useMemo, useState } from 'react'
import { useMutation } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { toast } from 'sonner'

import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { Checkbox } from '../components/ui/checkbox'
import { Input } from '../components/ui/input'
import { Label } from '../components/ui/label'
import { cn } from '../lib/utils'
import { logout } from '../services/authApi'
import { clearSession, getSession } from '../utils/session'

type TabKey = 'perfil' | 'seguridad' | 'integraciones' | 'trello' | 'powerbi' | 'notificaciones'

type Tab = {
  key: TabKey
  label: string
  icon: React.ComponentType<{ className?: string }>
  description: string
}

function TabButton({
  active,
  tab,
  onClick,
}: {
  active: boolean
  tab: Tab
  onClick: () => void
}) {
  const Icon = tab.icon
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        'flex w-full items-center gap-3 rounded-xl border px-3 py-2 text-left text-sm font-medium transition-colors',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
        active ? 'border-primary/40 bg-primary/5 text-foreground' : 'bg-background hover:bg-muted',
      )}
      aria-current={active ? 'page' : undefined}
    >
      <span className="flex h-9 w-9 items-center justify-center rounded-lg border bg-muted/30">
        <Icon className="h-4 w-4 text-muted-foreground" />
      </span>
      <span className="min-w-0">
        <span className="block truncate">{tab.label}</span>
        <span className="block truncate text-xs text-muted-foreground">{tab.description}</span>
      </span>
    </button>
  )
}

function HeaderCard({
  title,
  description,
}: {
  title: string
  description: string
}) {
  return (
    <Card className="shadow-sm">
      <CardHeader>
        <CardTitle className="text-base">{title}</CardTitle>
        <CardDescription>{description}</CardDescription>
      </CardHeader>
    </Card>
  )
}

export function SettingsPage() {
  const navigate = useNavigate()
  const tabs: Tab[] = useMemo(
    () => [
      { key: 'perfil', label: 'Perfil', icon: User, description: 'Datos del usuario y preferencias' },
      { key: 'seguridad', label: 'Seguridad', icon: ShieldCheck, description: 'Acceso, MFA y sesiones' },
      { key: 'integraciones', label: 'Integraciones', icon: Link2, description: 'Conectores y permisos' },
      { key: 'trello', label: 'Trello', icon: Kanban, description: 'Sincronización y tableros' },
      { key: 'powerbi', label: 'Power BI', icon: KeyRound, description: 'Embedded, workspaces y reportes' },
      { key: 'notificaciones', label: 'Notificaciones', icon: Bell, description: 'Alertas y comunicaciones' },
    ],
    [],
  )

  const [active, setActive] = useState<TabKey>('perfil')
  const current = tabs.find((t) => t.key === active) ?? tabs[0]

  const nameId = useId()
  const emailId = useId()
  const roleId = useId()

  const currentPwdId = useId()
  const newPwdId = useId()

  const session = getSession()
  const [fullName, setFullName] = useState(session?.user.fullName ?? 'Patricia M.')
  const [email, setEmail] = useState(session?.user.email ?? 'patricia@empresa.com')
  const [role] = useState('Gerencia')

  const [mfaEnabled, setMfaEnabled] = useState(true)
  const [notifyCritical, setNotifyCritical] = useState(true)
  const [notifyWeekly, setNotifyWeekly] = useState(false)
  const [notifyDigest, setNotifyDigest] = useState(true)

  const [autoSync, setAutoSync] = useState(true)
  const [syncFrequency, setSyncFrequency] = useState<'15m' | '1h' | '6h' | '24h'>('1h')

  const logoutMutation = useMutation({
    mutationFn: logout,
    onSuccess: () => {
      clearSession()
      toast.success('Sesión cerrada')
      navigate('/login', { replace: true })
    },
    onError: () => {
      clearSession()
      navigate('/login', { replace: true })
    },
  })

  return (
    <div className="min-h-svh bg-background text-foreground">
      <header className="border-b bg-background/70 backdrop-blur">
        <div className="mx-auto max-w-6xl px-4 py-5 sm:px-6">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div className="flex items-start gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl border bg-muted/30">
                <Lock className="h-5 w-5 text-primary" />
              </div>
              <div className="min-w-0">
                <h1 className="text-lg font-semibold tracking-tight">Configuración</h1>
                <p className="mt-1 text-sm text-muted-foreground">
                  Administra perfil, seguridad e integraciones de la plataforma.
                </p>
              </div>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <Button type="button" variant="outline" className="gap-2">
                <span className="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true" />
                Guardado automático
                <ChevronDown className="h-4 w-4 text-muted-foreground" />
              </Button>
              <Button
                type="button"
                variant="destructive"
                disabled={logoutMutation.isPending}
                onClick={() => logoutMutation.mutate()}
              >
                Cerrar sesión
              </Button>
              <Button type="button">Guardar cambios</Button>
            </div>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        <div className="grid grid-cols-1 gap-4 lg:grid-cols-[320px,1fr]">
          <aside className="space-y-2" aria-label="Pestañas de configuración">
            <div className="flex gap-2 overflow-x-auto lg:hidden" role="tablist" aria-label="Secciones">
              {tabs.map((t) => {
                const Icon = t.icon
                const isActive = t.key === active
                return (
                  <button
                    key={t.key}
                    type="button"
                    onClick={() => setActive(t.key)}
                    className={cn(
                      'inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-2 text-sm font-medium',
                      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                      isActive ? 'border-primary/40 bg-primary/5' : 'bg-background hover:bg-muted',
                    )}
                    role="tab"
                    aria-selected={isActive}
                  >
                    <Icon className="h-4 w-4 text-muted-foreground" />
                    {t.label}
                  </button>
                )
              })}
            </div>

            <div className="hidden lg:block">
              {tabs.map((t) => (
                <TabButton key={t.key} tab={t} active={t.key === active} onClick={() => setActive(t.key)} />
              ))}
            </div>
          </aside>

          <section className="space-y-4" aria-label="Contenido de configuración">
            <HeaderCard title={current.label} description={current.description} />

            {active === 'perfil' ? (
              <Card className="shadow-sm">
                <CardHeader>
                  <CardTitle className="text-base">Perfil</CardTitle>
                  <CardDescription>Información básica del usuario</CardDescription>
                </CardHeader>
                <CardContent className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div className="space-y-2">
                    <Label htmlFor={nameId}>Nombre</Label>
                    <Input id={nameId} value={fullName} onChange={(e) => setFullName(e.target.value)} />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor={emailId}>Correo</Label>
                    <Input id={emailId} type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
                  </div>
                  <div className="space-y-2 sm:col-span-2">
                    <Label htmlFor={roleId}>Rol</Label>
                    <Input id={roleId} readOnly value={role} aria-label="Rol (solo lectura)" />
                  </div>
                </CardContent>
              </Card>
            ) : null}

            {active === 'seguridad' ? (
              <div className="space-y-4">
                <Card className="shadow-sm">
                  <CardHeader>
                    <CardTitle className="text-base">Autenticación</CardTitle>
                    <CardDescription>Configura MFA y administración de sesión</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="flex items-start justify-between gap-4 rounded-xl border bg-muted/10 p-4">
                      <div className="min-w-0">
                        <p className="text-sm font-semibold">MFA (doble factor)</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                          Incrementa la seguridad del acceso a la plataforma.
                        </p>
                      </div>
                      <Checkbox checked={mfaEnabled} onCheckedChange={(v) => setMfaEnabled(Boolean(v))} />
                    </div>
                    <div className="rounded-xl border bg-muted/10 p-4">
                      <p className="text-sm font-semibold">Sesiones</p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        Cierra sesiones activas desde dispositivos no reconocidos.
                      </p>
                      <div className="mt-3 flex flex-wrap gap-2">
                        <Button type="button" variant="outline">
                          Ver sesiones
                        </Button>
                        <Button type="button" variant="outline">
                          Cerrar otras sesiones
                        </Button>
                      </div>
                    </div>
                  </CardContent>
                </Card>

                <Card className="shadow-sm">
                  <CardHeader>
                    <CardTitle className="text-base">Cambiar contraseña</CardTitle>
                    <CardDescription>Buenas prácticas: 12+ caracteres y gestor de contraseñas</CardDescription>
                  </CardHeader>
                  <CardContent className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div className="space-y-2">
                      <Label htmlFor={currentPwdId}>Contraseña actual</Label>
                      <Input id={currentPwdId} type="password" autoComplete="current-password" placeholder="••••••••" />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor={newPwdId}>Nueva contraseña</Label>
                      <Input id={newPwdId} type="password" autoComplete="new-password" placeholder="••••••••••••" />
                    </div>
                    <div className="sm:col-span-2">
                      <Button type="button" className="w-full sm:w-auto">
                        Actualizar contraseña
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </div>
            ) : null}

            {active === 'integraciones' ? (
              <Card className="shadow-sm">
                <CardHeader>
                  <CardTitle className="text-base">Integraciones</CardTitle>
                  <CardDescription>Conectores habilitados para tu organización</CardDescription>
                </CardHeader>
                <CardContent className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                  <div className="rounded-xl border bg-muted/10 p-4">
                    <p className="text-sm font-semibold">Trello</p>
                    <p className="mt-1 text-sm text-muted-foreground">Sincroniza boards y tarjetas.</p>
                    <div className="mt-3 flex flex-wrap gap-2">
                      <Button type="button" variant="outline">
                        Administrar
                      </Button>
                      <Button type="button">Conectar</Button>
                    </div>
                  </div>
                  <div className="rounded-xl border bg-muted/10 p-4">
                    <p className="text-sm font-semibold">Power BI</p>
                    <p className="mt-1 text-sm text-muted-foreground">Dashboards embebidos y exportaciones.</p>
                    <div className="mt-3 flex flex-wrap gap-2">
                      <Button type="button" variant="outline">
                        Administrar
                      </Button>
                      <Button type="button">Conectar</Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ) : null}

            {active === 'trello' ? (
              <Card className="shadow-sm">
                <CardHeader>
                  <CardTitle className="text-base">Trello</CardTitle>
                  <CardDescription>Preferencias de sincronización y alcance</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex items-start justify-between gap-4 rounded-xl border bg-muted/10 p-4">
                    <div className="min-w-0">
                      <p className="text-sm font-semibold">Sincronización automática</p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        Mantén métricas actualizadas sin intervención.
                      </p>
                    </div>
                    <Checkbox checked={autoSync} onCheckedChange={(v) => setAutoSync(Boolean(v))} />
                  </div>

                  <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div className="space-y-2">
                      <Label>Frecuencia de actualización</Label>
                      <select
                        className={cn(
                          'flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm',
                          'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                        )}
                        value={syncFrequency}
                        onChange={(e) => setSyncFrequency(e.target.value as typeof syncFrequency)}
                        disabled={!autoSync}
                        aria-label="Frecuencia de sincronización"
                      >
                        <option value="15m">Cada 15 minutos</option>
                        <option value="1h">Cada 1 hora</option>
                        <option value="6h">Cada 6 horas</option>
                        <option value="24h">Cada 24 horas</option>
                      </select>
                      <p className="text-xs text-muted-foreground">Disponible solo si automático está habilitado.</p>
                    </div>
                    <div className="rounded-xl border bg-muted/10 p-4">
                      <p className="text-sm font-semibold">Alcance</p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        Define qué workspaces/tableros se sincronizan.
                      </p>
                      <div className="mt-3 flex flex-wrap gap-2">
                        <Button type="button" variant="outline">
                          Seleccionar boards
                        </Button>
                        <Button type="button">Sincronizar ahora</Button>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ) : null}

            {active === 'powerbi' ? (
              <Card className="shadow-sm">
                <CardHeader>
                  <CardTitle className="text-base">Power BI</CardTitle>
                  <CardDescription>Configuración de Embedded y permisos de exportación</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="rounded-xl border bg-muted/10 p-4">
                    <p className="text-sm font-semibold">Embedded</p>
                    <p className="mt-1 text-sm text-muted-foreground">
                      Configura workspace, reporte por defecto y políticas de acceso.
                    </p>
                    <div className="mt-3 flex flex-wrap gap-2">
                      <Button type="button" variant="outline">
                        Seleccionar reporte
                      </Button>
                      <Button type="button">Probar conexión</Button>
                    </div>
                  </div>
                  <div className="flex items-start justify-between gap-4 rounded-xl border bg-muted/10 p-4">
                    <div className="min-w-0">
                      <p className="text-sm font-semibold">Exportación</p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        Permite exportar a PDF/Excel según políticas de la organización.
                      </p>
                    </div>
                    <Checkbox checked={true} onCheckedChange={() => {}} aria-label="Permitir exportación" />
                  </div>
                </CardContent>
              </Card>
            ) : null}

            {active === 'notificaciones' ? (
              <Card className="shadow-sm">
                <CardHeader>
                  <CardTitle className="text-base">Notificaciones</CardTitle>
                  <CardDescription>Controla alertas críticas y resúmenes</CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                  <div className="flex items-start justify-between gap-4 rounded-xl border bg-muted/10 p-4">
                    <div className="min-w-0">
                      <p className="text-sm font-semibold">Alertas críticas</p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        Notifica cuando hay riesgos altos o vencimientos masivos.
                      </p>
                    </div>
                    <Checkbox checked={notifyCritical} onCheckedChange={(v) => setNotifyCritical(Boolean(v))} />
                  </div>

                  <div className="flex items-start justify-between gap-4 rounded-xl border bg-muted/10 p-4">
                    <div className="min-w-0">
                      <p className="text-sm font-semibold">Digest diario</p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        Recibe un resumen diario de actividad y métricas.
                      </p>
                    </div>
                    <Checkbox checked={notifyDigest} onCheckedChange={(v) => setNotifyDigest(Boolean(v))} />
                  </div>

                  <div className="flex items-start justify-between gap-4 rounded-xl border bg-muted/10 p-4">
                    <div className="min-w-0">
                      <p className="text-sm font-semibold">Reporte semanal</p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        Envia un reporte ejecutivo semanal a gerencia.
                      </p>
                    </div>
                    <Checkbox checked={notifyWeekly} onCheckedChange={(v) => setNotifyWeekly(Boolean(v))} />
                  </div>
                </CardContent>
              </Card>
            ) : null}
          </section>
        </div>
      </main>
    </div>
  )
}
