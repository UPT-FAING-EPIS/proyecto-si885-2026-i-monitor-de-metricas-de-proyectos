# Manual de instalación (local + Supabase)

## Requisitos
- PHP 8.1+ (con extensión cURL habilitada).
- Un proyecto en Supabase (Database + Auth + Storage).

## 1) Supabase (DB + RLS + Storage)
1. En Supabase abre **SQL Editor**.
2. Ejecuta el contenido de [schema.sql]
3. Si estás usando PostgREST/RPC y acabas de crear funciones, recarga el caché:

```sql
notify pgrst, 'reload schema';
```

4. Verifica que exista el bucket de Storage `task-evidences` (el script lo crea si no existe).

## 2) Variables de entorno
Este proyecto carga variables desde el archivo `.env` en la raíz del repo.

Edita [/.env](file:///c:/xampp/htdocs/Segundo%20Intento/.env) y configura:
- `SUPABASE_URL` = `https://TU-PROYECTO.supabase.co`
- `SUPABASE_ANON_KEY` = tu Anon Public Key (Project Settings → API)
- `APP_NAME` (opcional)
- `APP_BASE_URL` (opcional) (ej: `http://localhost:8001`)

## 3) Ejecutar localmente
En la raíz del proyecto:

```powershell
php -S localhost:8001 -t public public/router.php
```

Abre:
- `http://localhost:8001`

## 4) Flujo rápido
1. Regístrate / inicia sesión.
2. Crea un proyecto.
3. Dentro del proyecto crea una tarea y asígnala a otro usuario por email.
4. El usuario asignado entra a **Mis tareas**, actualiza estado, registra tiempo y sube evidencias.

## Problemas comunes

### RPC/funciones no aparecen (schema cache)
Si ves errores tipo “Could not find the function … in the schema cache”, ejecuta:

```sql
notify pgrst, 'reload schema';
```

### No se puede crear proyecto (FK hacia profiles)
El `projects.owner_id` referencia `public.profiles(id)`. Asegúrate de haber ejecutado el `schema.sql` completo (incluye la función `ensure_my_profile()` y políticas RLS).
