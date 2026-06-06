import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation } from '@tanstack/react-query'
import type { AxiosError } from 'axios'
import { Eye, EyeOff, Loader2 } from 'lucide-react'
import { useEffect, useId, useMemo, useState, type SVGProps } from 'react'
import { useForm, useWatch } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import { toast } from 'sonner'
import { z } from 'zod'

import hero from '../assets/hero.png'
import { ThemeToggle } from '../components/ThemeToggle'
import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { Checkbox } from '../components/ui/checkbox'
import { Input } from '../components/ui/input'
import { Label } from '../components/ui/label'
import { login, loginWithGoogle } from '../services/authApi'
import { supabase } from '../services/supabaseClient'
import type { AuthResponse } from '../types/auth'
import { setSession } from '../utils/session'

function MicrosoftIcon(props: SVGProps<SVGSVGElement>) {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {...props}>
      <path fill="currentColor" d="M3 3h8v8H3V3Zm10 0h8v8h-8V3ZM3 13h8v8H3v-8Zm10 0h8v8h-8v-8Z" />
    </svg>
  )
}

function GoogleIcon(props: SVGProps<SVGSVGElement>) {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" {...props}>
      <path
        fill="currentColor"
        d="M12 10.2v3.9h5.4c-.2 1.2-1.4 3.4-5.4 3.4A6.3 6.3 0 0 1 12 5a5.7 5.7 0 0 1 4 1.6l2.7-2.7A9.5 9.5 0 0 0 12 1.8C6.8 1.8 2.6 6 2.6 11.2S6.8 20.6 12 20.6c5.8 0 9.1-4.1 9.1-9.8 0-.7-.1-1.2-.2-1.7H12Z"
      />
    </svg>
  )
}

function LogoMark(props: SVGProps<SVGSVGElement>) {
  return (
    <svg viewBox="0 0 40 40" aria-hidden="true" focusable="false" {...props}>
      <path
        fill="currentColor"
        d="M20 3a17 17 0 1 0 0 34 17 17 0 0 0 0-34Zm-7 22.2V14.8c0-.7.8-1.1 1.4-.7l10.7 5.2c.7.3.7 1.2 0 1.5l-10.7 5.2c-.6.4-1.4 0-1.4-.8Z"
      />
    </svg>
  )
}

function getApiErrorMessage(error: unknown) {
  const axiosError = error as AxiosError<{ title?: string; detail?: string }> | undefined
  const status = axiosError?.response?.status
  const detail = axiosError?.response?.data?.detail
  if (detail) return detail
  if (status === 401) return 'Credenciales inválidas.'
  return axiosError?.response?.data?.title
}

export function LoginPage() {
  const navigate = useNavigate()
  const emailId = useId()
  const passwordId = useId()
  const rememberId = useId()

  const [showPassword, setShowPassword] = useState(false)
  const [heroLoaded, setHeroLoaded] = useState(false)
  const schema = useMemo(
    () =>
      z.object({
        email: z.string().min(1, 'Ingresa tu correo electrónico.').email('Ingresa un correo válido.'),
        password: z.string().min(8, 'Usa al menos 8 caracteres.'),
        remember: z.boolean(),
      }),
    [],
  )

  type FormValues = z.infer<typeof schema>

  const form = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: '', password: '', remember: true },
    mode: 'onTouched',
  })
  const rememberValue = useWatch({ control: form.control, name: 'remember' })

  const loginMutation = useMutation<AuthResponse, unknown, FormValues>({
    mutationFn: (values: FormValues) => login({ email: values.email, password: values.password }),
    onSuccess: (data, variables) => {
      setSession(data, variables.remember)
      toast.success(`Bienvenido, ${data.user.fullName}`)
      navigate('/dashboard', { replace: true })
    },
    onError: (error) => toast.error(getApiErrorMessage(error) ?? 'No se pudo iniciar sesión.'),
  })

  const googleExchangeMutation = useMutation<AuthResponse, unknown, { supabaseAccessToken: string }>({
    mutationFn: loginWithGoogle,
    onSuccess: (data) => {
      setSession(data, true)
      toast.success(`Bienvenido, ${data.user.fullName}`)
      navigate('/dashboard', { replace: true })
    },
    onError: (error) => toast.error(getApiErrorMessage(error) ?? 'No se pudo completar el inicio con Google.'),
  })

  useEffect(() => {
    let mounted = true
    supabase.auth.getSession().then(({ data }) => {
      const token = data.session?.access_token
      if (!mounted || !token || googleExchangeMutation.isPending) return
      googleExchangeMutation.mutate({ supabaseAccessToken: token })
    })

    const { data: sub } = supabase.auth.onAuthStateChange((_event, session) => {
      const token = session?.access_token
      if (!token || googleExchangeMutation.isPending) return
      googleExchangeMutation.mutate({ supabaseAccessToken: token })
    })

    return () => {
      mounted = false
      sub.subscription.unsubscribe()
    }
  }, [googleExchangeMutation])

  return (
    <div className="min-h-svh bg-background text-foreground">
      <div className="mx-auto grid min-h-svh max-w-6xl grid-cols-1 lg:grid-cols-2">
        <section className="relative overflow-hidden border-b lg:border-b-0 lg:border-r">
          <div className="absolute inset-0 bg-gradient-to-br from-primary/12 via-transparent to-transparent dark:from-primary/20" />
          <div className="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-primary/10 blur-3xl dark:bg-primary/20" />
          <div className="absolute -bottom-28 -right-24 h-72 w-72 rounded-full bg-sky-500/10 blur-3xl dark:bg-sky-500/10" />

          <div className="relative flex h-full flex-col justify-between gap-10 p-8 sm:p-12">
            <div className="flex items-center gap-3">
              <div className="flex h-11 w-11 items-center justify-center rounded-xl border bg-background/70 backdrop-blur">
                <LogoMark className="h-6 w-6 text-primary" />
              </div>
              <div className="leading-tight">
                <p className="text-sm font-medium text-muted-foreground">Project Metrics Monitor</p>
                <p className="text-xs text-muted-foreground">Acceso seguro</p>
              </div>
            </div>

            <div className="max-w-md">
              <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">Project Metrics Monitor</h1>
              <p className="mt-4 text-sm leading-6 text-muted-foreground sm:text-base">
                Monitorea el rendimiento de tus proyectos en Trello mediante métricas, indicadores y dashboards
                ejecutivos.
              </p>

              <div className="mt-6 flex flex-wrap gap-2">
                <span className="inline-flex items-center rounded-full border bg-background/60 px-3 py-1 text-xs text-muted-foreground">
                  Notion-grade UX
                </span>
                <span className="inline-flex items-center rounded-full border bg-background/60 px-3 py-1 text-xs text-muted-foreground">
                  Linear-like focus
                </span>
                <span className="inline-flex items-center rounded-full border bg-background/60 px-3 py-1 text-xs text-muted-foreground">
                  Power BI insights
                </span>
              </div>
            </div>

            <div className="overflow-hidden rounded-xl border bg-background/60 p-3">
              {!heroLoaded ? <div className="h-[200px] w-full animate-pulse rounded-lg bg-muted" /> : null}
              <img
                src={hero}
                alt="Vista previa del dashboard ejecutivo"
                className={heroLoaded ? 'h-auto w-full rounded-lg object-cover' : 'hidden'}
                loading="lazy"
                onLoad={() => setHeroLoaded(true)}
              />
            </div>
          </div>
        </section>

        <section className="relative flex items-center justify-center p-6 sm:p-10">
          <div className="absolute right-3 top-3 sm:right-6 sm:top-6">
            <ThemeToggle />
          </div>

          <Card className="w-full max-w-md">
            <CardHeader>
              <CardTitle>Iniciar sesión</CardTitle>
              <CardDescription>Accede a tus dashboards y métricas de proyecto.</CardDescription>
            </CardHeader>
            <CardContent>
              <form
                className="space-y-5"
                onSubmit={form.handleSubmit((values) => loginMutation.mutate(values))}
                noValidate
              >
                <div className="space-y-2">
                  <Label htmlFor={emailId}>Correo electrónico</Label>
                  <Input
                    id={emailId}
                    type="email"
                    inputMode="email"
                    autoComplete="email"
                    placeholder="tu@empresa.com"
                    {...form.register('email')}
                    aria-invalid={Boolean(form.formState.errors.email)}
                  />
                  {form.formState.errors.email?.message ? (
                    <p className="text-xs text-destructive" role="alert">
                      {form.formState.errors.email.message}
                    </p>
                  ) : null}
                </div>

                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <Label htmlFor={passwordId}>Contraseña</Label>
                    <Link
                      className="text-xs font-medium text-primary underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                      to="/forgot-password"
                    >
                      Olvidé mi contraseña
                    </Link>
                  </div>
                  <div className="relative">
                    <Input
                      id={passwordId}
                      type={showPassword ? 'text' : 'password'}
                      autoComplete="current-password"
                      placeholder="••••••••"
                      className="pr-20"
                      {...form.register('password')}
                      aria-invalid={Boolean(form.formState.errors.password)}
                    />
                    <button
                      type="button"
                      className="absolute right-2 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                      aria-label={showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                      onClick={() => setShowPassword((p) => !p)}
                    >
                      {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                    </button>
                  </div>
                  {form.formState.errors.password?.message ? (
                    <p className="text-xs text-destructive" role="alert">
                      {form.formState.errors.password.message}
                    </p>
                  ) : (
                    <p className="text-xs text-muted-foreground">Mínimo 8 caracteres.</p>
                  )}
                </div>

                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <Checkbox
                      id={rememberId}
                      checked={rememberValue}
                      onCheckedChange={(v) => form.setValue('remember', Boolean(v))}
                    />
                    <Label htmlFor={rememberId} className="text-sm text-muted-foreground">
                      Recordarme
                    </Label>
                  </div>
                </div>

                <Button type="submit" className="w-full" disabled={loginMutation.isPending}>
                  {loginMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : null}
                  Iniciar sesión
                </Button>

                <div className="relative py-1">
                  <div className="absolute inset-0 flex items-center">
                    <span className="w-full border-t" />
                  </div>
                  <div className="relative flex justify-center">
                    <span className="bg-card px-2 text-xs text-muted-foreground">o continúa con</span>
                  </div>
                </div>

                <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                  <Button type="button" variant="outline" className="w-full" disabled>
                    <MicrosoftIcon className="h-4 w-4" />
                    Microsoft
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    className="w-full"
                    disabled={googleExchangeMutation.isPending}
                    onClick={async () => {
                      const url = import.meta.env.VITE_SUPABASE_URL as string | undefined
                      const key = import.meta.env.VITE_SUPABASE_ANON_KEY as string | undefined
                      if (!url || !key) {
                        toast.error('Configura VITE_SUPABASE_URL y VITE_SUPABASE_ANON_KEY para Google OAuth.')
                        return
                      }
                      await supabase.auth.signInWithOAuth({
                        provider: 'google',
                        options: { redirectTo: `${window.location.origin}/login` },
                      })
                    }}
                  >
                    {googleExchangeMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : null}
                    <GoogleIcon className="h-4 w-4" />
                    Continuar con Google
                  </Button>
                </div>

                <p className="text-center text-xs text-muted-foreground">
                  ¿No tienes cuenta?{' '}
                  <Link className="font-medium text-primary underline-offset-4 hover:underline" to="/register">
                    Crear cuenta
                  </Link>
                </p>
              </form>
            </CardContent>
          </Card>
        </section>
      </div>
    </div>
  )
}
