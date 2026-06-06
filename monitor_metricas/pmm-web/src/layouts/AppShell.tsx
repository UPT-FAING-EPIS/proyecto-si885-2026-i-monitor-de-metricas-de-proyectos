import { Bell, ChevronDown, Kanban, LayoutDashboard, LineChart, Menu, Search, Settings, ShieldAlert } from 'lucide-react'
import { useState } from 'react'
import { NavLink, Outlet } from 'react-router-dom'

import { Button } from '../components/ui/button'
import { Input } from '../components/ui/input'
import { cn } from '../lib/utils'
import { getSession } from '../utils/session'

type NavItem = {
  label: string
  icon: React.ComponentType<{ className?: string }>
  to: string
}

function Logo() {
  return (
    <div className="flex items-center gap-2">
      <div className="flex h-9 w-9 items-center justify-center rounded-lg border bg-background">
        <Kanban className="h-4 w-4 text-primary" />
      </div>
      <div className="leading-tight">
        <p className="text-sm font-semibold">Project Metrics Monitor</p>
        <p className="text-[11px] text-muted-foreground">Executive Dashboard</p>
      </div>
    </div>
  )
}

function ProgressRow({ label, value }: { label: string; value: number }) {
  return (
    <div className="space-y-1">
      <div className="flex items-center justify-between text-sm">
        <span className="text-muted-foreground">{label}</span>
        <span className="font-medium">{Math.round(value)}%</span>
      </div>
      <div className="h-2 w-full rounded-full bg-muted">
        <div className="h-2 rounded-full bg-primary" style={{ width: `${value}%` }} aria-hidden="true" />
      </div>
    </div>
  )
}

function initialsFromName(fullName: string) {
  const parts = fullName
    .split(' ')
    .map((p) => p.trim())
    .filter(Boolean)
  const first = parts[0]?.[0] ?? ''
  const second = parts[1]?.[0] ?? ''
  const initials = `${first}${second}`.toUpperCase()
  return initials || 'PM'
}

export function AppShell() {
  const [mobileOpen, setMobileOpen] = useState(false)
  const session = getSession()
  const fullName = session?.user.fullName ?? 'Cuenta'
  const firstName = fullName.split(' ').filter(Boolean)[0] ?? fullName
  const initials = initialsFromName(fullName)

  const navItems: NavItem[] = [
    { label: 'Dashboard', icon: LayoutDashboard, to: '/dashboard' },
    { label: 'Proyectos', icon: Kanban, to: '/projects' },
    { label: 'Analítica', icon: LineChart, to: '/analytics' },
    { label: 'Alertas', icon: ShieldAlert, to: '/alerts' },
    { label: 'Power BI', icon: LineChart, to: '/powerbi' },
    { label: 'Configuración', icon: Settings, to: '/settings' },
  ]

  return (
    <div className="min-h-svh bg-background text-foreground">
      <div className="flex min-h-svh">
        <div
          className={cn(
            'fixed inset-0 z-40 bg-background/60 backdrop-blur-sm lg:hidden',
            mobileOpen ? 'block' : 'hidden',
          )}
          aria-hidden="true"
          onClick={() => setMobileOpen(false)}
        />

        <aside
          className={cn(
            'fixed left-0 top-0 z-50 h-svh w-[280px] border-r bg-background p-4 transition-transform lg:sticky lg:translate-x-0',
            mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
          )}
          aria-label="Sidebar"
        >
          <div className="flex items-center justify-between gap-2">
            <Logo />
            <Button
              type="button"
              variant="ghost"
              size="icon"
              className="lg:hidden"
              aria-label="Cerrar menú"
              onClick={() => setMobileOpen(false)}
            >
              <ChevronDown className="h-4 w-4 rotate-90" />
            </Button>
          </div>

          <nav className="mt-6 space-y-1" aria-label="Navegación principal">
            {navItems.map((item) => {
              const Icon = item.icon
              return (
                <NavLink
                  key={item.label}
                  to={item.to}
                  className={({ isActive }) =>
                    cn(
                      'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                      isActive
                        ? 'bg-primary/10 text-primary'
                        : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                    )
                  }
                  onClick={() => setMobileOpen(false)}
                >
                  <Icon className="h-4 w-4" />
                  {item.label}
                </NavLink>
              )
            })}
          </nav>

          <div className="mt-6 rounded-xl border bg-muted/20 p-4">
            <p className="text-sm font-semibold">Resumen</p>
            <div className="mt-3 space-y-3">
              <ProgressRow label="Cumplimiento SLA" value={86} />
              <ProgressRow label="Entrega a tiempo" value={72} />
            </div>
          </div>
        </aside>

        <div className="flex min-w-0 flex-1 flex-col">
          <header className="sticky top-0 z-30 border-b bg-background/70 backdrop-blur">
            <div className="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
              <div className="flex items-center gap-2">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  className="lg:hidden"
                  aria-label="Abrir menú"
                  onClick={() => setMobileOpen(true)}
                >
                  <Menu className="h-4 w-4" />
                </Button>
                <div className="hidden sm:block">
                  <p className="text-sm font-semibold">Project Metrics Monitor</p>
                  <p className="text-xs text-muted-foreground">Monitoreo ejecutivo</p>
                </div>
              </div>

              <div className="flex min-w-0 flex-1 items-center justify-center">
                <div className="relative w-full max-w-xl">
                  <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                  <Input
                    aria-label="Buscar proyectos, equipos o tableros"
                    placeholder="Buscar proyectos, equipos o tableros…"
                    className="pl-9"
                  />
                </div>
              </div>

              <div className="flex items-center gap-2">
                <Button type="button" variant="ghost" size="icon" aria-label="Notificaciones" asChild>
                  <NavLink to="/alerts">
                    <Bell className="h-4 w-4" />
                  </NavLink>
                </Button>
                <Button type="button" variant="outline" className="gap-2" asChild>
                  <NavLink to="/settings">
                    <span className="grid h-7 w-7 place-items-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                      {initials}
                    </span>
                    <span className="hidden md:inline-flex">{firstName}</span>
                    <ChevronDown className="h-4 w-4 text-muted-foreground" />
                  </NavLink>
                </Button>
              </div>
            </div>
          </header>

          <main className="min-w-0 flex-1 px-4 py-6 sm:px-6">
            <Outlet />
          </main>
        </div>
      </div>
    </div>
  )
}
