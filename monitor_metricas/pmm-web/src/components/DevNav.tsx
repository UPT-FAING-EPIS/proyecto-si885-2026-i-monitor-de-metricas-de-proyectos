import { BarChart3, Bell, Kanban, LineChart, Lock, Settings, ShieldCheck, User } from 'lucide-react'
import { useMemo, useState } from 'react'
import { NavLink } from 'react-router-dom'

import { Button } from './ui/button'
import { cn } from '../lib/utils'

type LinkItem = {
  to: string
  label: string
  icon: React.ComponentType<{ className?: string }>
}

export function DevNav() {
  const [open, setOpen] = useState(true)

  const links = useMemo<LinkItem[]>(
    () => [
      { to: '/login', label: 'Login', icon: User },
      { to: '/dashboard', label: 'Dashboard', icon: BarChart3 },
      { to: '/projects', label: 'Proyectos', icon: Kanban },
      { to: '/projects/demo', label: 'Detalle', icon: ShieldCheck },
      { to: '/analytics', label: 'Analítica', icon: LineChart },
      { to: '/alerts', label: 'Alertas', icon: Bell },
      { to: '/integrations/trello', label: 'Trello', icon: Kanban },
      { to: '/powerbi', label: 'Power BI', icon: LineChart },
      { to: '/settings', label: 'Config', icon: Settings },
    ],
    [],
  )

  return (
    <div className="fixed bottom-4 left-4 z-50">
      <div className="flex items-center gap-2">
        <Button type="button" variant="outline" className="gap-2" onClick={() => setOpen((p) => !p)}>
          <Lock className="h-4 w-4 text-muted-foreground" />
          Navegación
        </Button>
      </div>

      {open ? (
        <div className="mt-2 w-[280px] rounded-2xl border bg-background/90 p-2 shadow-lg backdrop-blur">
          <div className="grid grid-cols-2 gap-2">
            {links.map((l) => {
              const Icon = l.icon
              return (
                <NavLink
                  key={l.to}
                  to={l.to}
                  className={({ isActive }) =>
                    cn(
                      'flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium transition-colors',
                      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
                      isActive ? 'border-primary/40 bg-primary/5 text-foreground' : 'bg-background hover:bg-muted',
                    )
                  }
                >
                  <Icon className="h-4 w-4 text-muted-foreground" />
                  <span className="truncate">{l.label}</span>
                </NavLink>
              )
            })}
          </div>
          <p className="mt-2 px-1 text-[11px] text-muted-foreground">
            Menú de pruebas para navegar entre pantallas.
          </p>
        </div>
      ) : null}
    </div>
  )
}
