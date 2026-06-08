# Debug Session: trello-render-db
- **Status**: [OPEN]
- **Issue**: Trello falla en Render con error de conexion a Supabase PostgreSQL.
- **Debug Server**: Pending
- **Log File**: .dbg/trae-debug-log-trello-render-db.ndjson

## Reproduction Steps
1. Iniciar sesion en Render.
2. Ir a `Configuracion -> Integraciones -> Trello`.
3. Abrir `/trello`.
4. Observar error de inicializacion de Trello por PostgreSQL.

## Hypotheses & Verification
| ID | Hypothesis | Likelihood | Effort | Evidence |
|----|------------|------------|--------|----------|
| A | El DSN/credenciales del pooler no coinciden con lo configurado en Render | High | Low | Pending |
| B | Las variables llegan con formato inesperado (espacios/backticks/vacias) | High | Low | Pending |
| C | El contenedor no puede usar `pdo_pgsql`/SSL correctamente | Med | Med | Pending |
| D | El pooler rechaza autenticacion por usuario o password | High | Low | Pending |
| E | La inicializacion falla antes de abrir la conexion PDO | Low | Low | Pending |

## Log Evidence
- Instrumentacion agregada en `public/index.php` para exponer diagnostico sanitizado en `/trello`.
- Se registran en Render logs: DSN efectivo, presencia/longitud de variables, extensiones PHP y error real de `PDO`.

## Verification Conclusion
- Pending
