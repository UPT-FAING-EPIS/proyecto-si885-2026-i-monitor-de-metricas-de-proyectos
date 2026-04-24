# Instalación (MVP)

## 1) Requisitos
- PHP 8.1+ con extensión cURL habilitada.
- Un proyecto en Supabase (PostgreSQL + Auth + Storage).

## 2) Supabase (DB + RLS + Storage)
1. En Supabase, abre **SQL Editor**.
2. Ejecuta el contenido de [schema.sql](file:///c:/Segundo%20Intento/supabase/schema.sql).
3. Verifica que exista el bucket de Storage `task-evidences`.

## 3) Variables de entorno
Configura estas variables en tu entorno (PowerShell / IIS / Apache):
- `SUPABASE_URL` = `https://TU-PROYECTO.supabase.co`
- `SUPABASE_ANON_KEY` = tu Anon Public Key (Project Settings → API)
- `APP_NAME` (opcional)
- `APP_BASE_URL` (opcional)

Ejemplo PowerShell:
```powershell
$env:SUPABASE_URL="https://xxxxx.supabase.co"
$env:SUPABASE_ANON_KEY="eyJ..."
```

## 4) Ejecutar localmente
En la raíz del proyecto:
```powershell
php -S localhost:8000 -t public public/router.php
```
Luego abre:
- `http://localhost:8000`

## 5) Flujo rápido
1. Regístrate / inicia sesión.
2. Crea un proyecto.
3. Dentro del proyecto crea una tarea y asígnala a otro usuario por email.
4. El usuario asignado entra a **Mis tareas**, actualiza estado, registra tiempo y sube evidencias.

