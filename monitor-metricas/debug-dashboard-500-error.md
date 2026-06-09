# [OPEN] Debug Session: dashboard-500-error

## Resumen
- Sintoma: al ingresar a `/dashboard` en Render, la aplicacion responde con HTTP 500.
- Esperado: cargar el dashboard con metricas reales del usuario autenticado.

## Hipotesis Iniciales
1. El contenedor DI no esta resolviendo `DashboardController` o `IMonitoringService` correctamente en produccion.
2. `MonitoringService` esta ejecutando una consulta SQL incompatible con el esquema actual desplegado en Supabase.
3. Faltan migraciones nuevas en Render/Supabase y el dashboard falla al consultar columnas `user_id` o indices aun no aplicados.
4. Una vista del dashboard espera claves que no existen en el payload real y lanza una excepcion PHP al renderizar.
5. La autenticacion existe, pero el `user_id` de sesion llega vacio o en formato inesperado y rompe la consulta.

## Plan
1. Instrumentar la ruta `/dashboard` y su controller para emitir diagnostico controlado.
2. Reproducir el error en Render y capturar el mensaje exacto.
3. Confirmar o descartar hipotesis con evidencia.
4. Aplicar correccion minima.
5. Verificar post-fix y limpiar instrumentacion cuando confirmes.
