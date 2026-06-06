import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation } from '@tanstack/react-query'
import type { AxiosError } from 'axios'
import { Loader2, Mail } from 'lucide-react'
import { useId, useMemo, type SVGProps } from 'react'
import { useForm } from 'react-hook-form'
import { Link } from 'react-router-dom'
import { toast } from 'sonner'
import { z } from 'zod'

import hero from '../assets/hero.png'
import { ThemeToggle } from '../components/ThemeToggle'
import { Button } from '../components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { Input } from '../components/ui/input'
import { Label } from '../components/ui/label'
import { forgotPassword } from '../services/authApi'

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
  const detail = axiosError?.response?.data?.detail
  if (detail) return detail
  return axiosError?.response?.data?.title
}

export function ForgotPasswordPage() {
  const emailId = useId()

  const schema = useMemo(
    () =>
      z.object({
        email: z.string().min(1, 'Ingresa tu correo electrónico.').email('Ingresa un correo válido.'),
      }),
    [],
  )

  type FormValues = z.infer<typeof schema>

  const form = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: '' },
    mode: 'onTouched',
  })

  const mutation = useMutation({
    mutationFn: forgotPassword,
    onSuccess: () => toast.success('Enlace enviado. Revisa tu correo.'),
    onError: (error) => toast.error(getApiErrorMessage(error) ?? 'No se pudo enviar el enlace.'),
  })

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
                <p className="text-xs text-muted-foreground">Recuperar acceso</p>
              </div>
            </div>

            <div className="max-w-md">
              <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">Recuperar contraseña</h1>
              <p className="mt-4 text-sm leading-6 text-muted-foreground sm:text-base">
                Enviamos un enlace seguro para restablecer tu contraseña.
              </p>
            </div>

            <div className="overflow-hidden rounded-xl border bg-background/60 p-3">
              <img
                src={hero}
                alt="Vista previa del dashboard ejecutivo"
                className="h-auto w-full rounded-lg object-cover"
                loading="lazy"
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
              <CardTitle>Enviar enlace</CardTitle>
              <CardDescription>Te enviaremos un enlace de recuperación.</CardDescription>
            </CardHeader>
            <CardContent>
              <form
                className="space-y-5"
                onSubmit={form.handleSubmit((values) => mutation.mutate(values))}
                noValidate
              >
                <div className="space-y-2">
                  <Label htmlFor={emailId}>Correo electrónico</Label>
                  <div className="relative">
                    <Mail className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                      id={emailId}
                      type="email"
                      inputMode="email"
                      placeholder="tu@empresa.com"
                      className="pl-9"
                      {...form.register('email')}
                      aria-invalid={Boolean(form.formState.errors.email)}
                    />
                  </div>
                  {form.formState.errors.email?.message ? (
                    <p className="text-xs text-destructive" role="alert">
                      {form.formState.errors.email.message}
                    </p>
                  ) : null}
                </div>

                <Button type="submit" className="w-full" disabled={mutation.isPending}>
                  {mutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : null}
                  Enviar enlace
                </Button>

                <p className="text-center text-xs text-muted-foreground">
                  <Link className="font-medium text-primary underline-offset-4 hover:underline" to="/login">
                    Volver a iniciar sesión
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
