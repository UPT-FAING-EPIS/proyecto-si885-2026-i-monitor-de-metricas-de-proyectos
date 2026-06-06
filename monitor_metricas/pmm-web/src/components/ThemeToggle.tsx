import { Moon, Sun } from 'lucide-react'
import { useEffect, useMemo, useState } from 'react'

import { Button } from './ui/button'

type Theme = 'light' | 'dark'

function getInitialTheme(): Theme {
  const stored = localStorage.getItem('theme')
  if (stored === 'light' || stored === 'dark') return stored
  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

function applyTheme(theme: Theme) {
  const root = document.documentElement
  root.classList.toggle('dark', theme === 'dark')
  localStorage.setItem('theme', theme)
}

export function ThemeToggle() {
  const [theme, setTheme] = useState<Theme>(() => getInitialTheme())

  useEffect(() => {
    applyTheme(theme)
  }, [theme])

  const label = useMemo(
    () => (theme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'),
    [theme],
  )

  return (
    <Button
      type="button"
      variant="ghost"
      size="icon"
      aria-label={label}
      onClick={() => {
        const next: Theme = theme === 'dark' ? 'light' : 'dark'
        setTheme(next)
        applyTheme(next)
      }}
    >
      {theme === 'dark' ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
    </Button>
  )
}
