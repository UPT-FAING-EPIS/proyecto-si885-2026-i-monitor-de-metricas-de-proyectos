# Project Metrics Monitor - Runbook de Produccion

## 1. Alcance y ruta correcta del proyecto

Este runbook aplica al subproyecto:

- `project-metrics-monitor/`

No uses el `render.yaml` de la raiz del repositorio para este despliegue. Ese archivo pertenece a otro sistema del monorepo.

Ruta exacta del Blueprint que debes usar en Render:

- `project-metrics-monitor/render.yaml`

## 2. Requisitos minimos

### 2.1 Software local

- Python `3.12.x` o `3.13.x`
- Git
- Power BI Desktop
- SQLite CLI opcional para inspeccion
- ODBC SQLite opcional para conexion directa desde Power BI

### 2.2 Cuentas

- Cuenta de GitHub con acceso al repositorio
- Cuenta de Render
- Cuenta de Power BI

## 3. Variables reales que debes obtener

| Variable | Que es | Para que sirve | Donde se configura | Valor de ejemplo | Valor real que debes reemplazar |
|---|---|---|---|---|---|
| `GITHUB_TOKEN` | Token personal de GitHub | Autenticar REST y GraphQL para evitar limites bajos | `.env`, GitHub Secret o Render env var | `github_pat_xxxxxxxxx` | El token que tu mismo generas en GitHub |
| `GITHUB_OWNER` | Owner u organizacion del repo analizado | Indica la organizacion base para la ETL | GitHub Variable o Render env var | `microsoft` | Tu owner real, por ejemplo `tu-org` |
| `GITHUB_REPOS` | Lista de repositorios separada por comas | Define el conjunto a extraer | GitHub Variable o Render env var | `vscode,terminal` | Tus repos reales, por ejemplo `repo-a,repo-b` |
| `ETL_SINCE` | Fecha inicial ISO-8601 | Punto de arranque de la primera carga | GitHub Variable o Render env var | `2026-01-01T00:00:00Z` | La fecha historica desde la cual quieras cargar |
| `DB_PATH` | Ruta del SQLite | Ubicacion fisica de la base | CLI, GitHub workflow o Render env var | `database/project_metrics.db` | En Render usa `storage/project_metrics.db` |
| `EXPORT_DIR` | Carpeta de exportaciones | Salida de CSV y Parquet | CLI o Render env var | `exports` | En Render usa `storage/exports` |

## 4. Preparacion local desde cero

### 4.1 Crear entorno e instalar dependencias

Ejecuta desde `project-metrics-monitor/`:

```powershell
py -3.12 -m venv .venv
.venv\Scripts\Activate.ps1
python -m pip install --upgrade pip
pip install -r requirements.txt
```

Alternativa si prefieres instalar desde `pyproject.toml`:

```powershell
pip install ".[dev]"
```

### 4.2 Crear `.env`

```powershell
Copy-Item .env.example .env
notepad .env
```

Contenido minimo:

```env
GITHUB_TOKEN=github_pat_REEMPLAZAR
```

### 4.3 Probar CLI

```powershell
python -m src.run --help
```

Resultado esperado:

- Debes ver opciones `--owner`, `--repos`, `--since`, `--db-path` y `--export-dir`

## 5. Base de datos SQLite

### 5.1 Que es

- Archivo SQLite local con tablas dimensionales, tablas de hechos, tablas puente, vistas analiticas y control incremental.

### 5.2 Para que sirve

- Almacena el resultado reproducible de la ETL.
- Permite inspeccion manual.
- Alimenta exportaciones y Power BI.

### 5.3 Donde se configura

- Local: `--db-path database/project_metrics.db`
- GitHub Actions: `DB_PATH` dentro del workflow
- Render: env var `DB_PATH=storage/project_metrics.db`

### 5.4 Como crearla

Primera carga manual:

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01 --db-path database/project_metrics.db
```

### 5.5 Como verificar que las tablas fueron generadas

```powershell
python verify.py --offline-sample
```

Si tienes `sqlite3` instalado:

```powershell
sqlite3 database/project_metrics.db ".tables"
sqlite3 database/project_metrics.db "SELECT name, type FROM sqlite_master WHERE type IN ('table','view') ORDER BY type, name;"
```

Resultado esperado:

- Tablas como `dim_repo`, `fact_commits`, `etl_control`
- Vistas como `vw_repo_summary`, `vw_quality_metrics`, `vw_public_repo_summary`

### 5.6 Como inspeccionar datos

```powershell
sqlite3 database/project_metrics.db "SELECT COUNT(*) AS total FROM dim_repo;"
sqlite3 database/project_metrics.db "SELECT repo_name, commits, prs_merged, issues_closed FROM vw_repo_summary;"
sqlite3 database/project_metrics.db "SELECT source_name, last_loaded_at FROM etl_control;"
```

## 6. ETL

### 6.1 Carga manual completa

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01T00:00:00Z --db-path database/project_metrics.db --export-dir exports --export-csv --export-parquet --public-export
```

### 6.2 Carga incremental

Usa el mismo comando y la tabla `etl_control` resolvera el ultimo `last_loaded_at`:

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01T00:00:00Z --db-path database/project_metrics.db --export-dir exports --export-csv --export-parquet --public-export
```

### 6.3 Como verificar que los datos se descargaron correctamente

```powershell
sqlite3 database/project_metrics.db "SELECT COUNT(*) FROM fact_commits;"
sqlite3 database/project_metrics.db "SELECT COUNT(*) FROM fact_prs;"
sqlite3 database/project_metrics.db "SELECT COUNT(*) FROM fact_issues;"
sqlite3 database/project_metrics.db "SELECT COUNT(*) FROM fact_releases;"
sqlite3 database/project_metrics.db "SELECT COUNT(*) FROM fact_workflows;"
sqlite3 database/project_metrics.db "SELECT * FROM etl_control;"
```

Validacion rapida adicional:

```powershell
python verify.py --owner TU_OWNER --repos TU_REPO1 --since 2026-01-01
```

## 7. Exportaciones

### 7.1 CSV

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01 --db-path database/project_metrics.db --export-dir exports --export-csv
```

### 7.2 Parquet

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01 --db-path database/project_metrics.db --export-dir exports --export-parquet
```

### 7.3 Dataset publico anonimizado

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01 --db-path database/project_metrics.db --export-dir exports --export-csv --export-parquet --public-export
```

### 7.4 Como validar archivos exportados

```powershell
Get-ChildItem exports
Get-ChildItem exports\public
```

Validar CSV:

```powershell
Get-Content exports\vw_repo_summary.csv -TotalCount 5
Import-Csv exports\vw_repo_summary.csv | Select-Object -First 5
```

Validar Parquet:

```powershell
python - <<'PY'
import pandas as pd
for path in ["exports/vw_repo_summary.parquet", "exports/public/vw_public_repo_summary.parquet"]:
    df = pd.read_parquet(path)
    print(path, df.shape)
    print(df.head(3).to_string(index=False))
PY
```

## 8. GitHub

### 8.1 Crear Personal Access Token

Ruta exacta:

- GitHub avatar arriba a la derecha
- `Settings`
- `Developer settings`
- `Personal access tokens`
- `Fine-grained tokens`
- `Generate new token`

Valores recomendados:

- Token name: `project-metrics-monitor-etl`
- Expiration: `90 days`
- Resource owner: tu usuario u organizacion real
- Repository access: `Only select repositories`
- Repositories: selecciona solo el repo del proyecto y los repos que la ETL consultara si pertenecen a otra org accesible

Permisos minimos recomendados:

- `Metadata: Read-only`
- `Contents: Read-only`
- `Issues: Read-only`
- `Pull requests: Read-only`
- `Actions: Read-only`

Si tu organizacion no permite fine-grained PAT, usa un PAT classic solo como ultimo recurso:

- Scope minimo: `repo`

### 8.2 Guardar token como Secret

Ruta exacta en el repositorio:

- `Repository`
- `Settings`
- `Secrets and variables`
- `Actions`
- Tab `Secrets`
- `New repository secret`

Crear:

- Name: `PMM_GH_TOKEN`
- Secret: pega tu token

### 8.3 Crear Variables de Actions

En la misma pantalla:

- Tab `Variables`
- `New repository variable`

Crea estas variables:

- `PMM_OWNER` = `TU_OWNER`
- `PMM_REPOS` = `TU_REPO1,TU_REPO2`
- `PMM_ETL_SINCE` = `2026-01-01T00:00:00Z`

### 8.4 Verificar que GitHub Actions puede usar el token

Ruta exacta:

- `Repository`
- `Actions`
- Workflow `etl-scheduled`
- `Run workflow`

Valores:

- owner: `TU_OWNER`
- repos: `TU_REPO1,TU_REPO2`
- since: `2026-01-01T00:00:00Z`

Validaciones obligatorias en logs:

- No debe aparecer el warning `github_token_missing`
- Debe terminar con exit code `0`
- Debe subir artefactos `project-metrics-db` y `project-metrics-exports`

Verificacion por interfaz:

- `Actions` -> selecciona la corrida -> seccion `Artifacts`
- Debes ver ambos artefactos descargables

## 9. GitHub Actions

### 9.1 Workflows reales del repo

- `project-metrics-monitor/.github/workflows/ci.yml`
- `project-metrics-monitor/.github/workflows/etl-scheduled.yml`

### 9.2 Que hace `ci.yml`

- Checkout
- Python 3.12
- `pip install -r requirements.txt`
- `ruff check .`
- `black --check .`
- `pytest`
- `python verify.py --offline-sample`

### 9.3 Que hace `etl-scheduled.yml`

- Soporta `workflow_dispatch`
- Ejecuta tests
- Corre ETL con `PMM_GH_TOKEN`
- Sube base SQLite y exportaciones como artifacts

### 9.4 Comandos para reproducir localmente el workflow

```powershell
ruff check .
black --check .
pytest
python verify.py --offline-sample
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01T00:00:00Z --db-path database/project_metrics_ci.db --export-dir exports --export-csv --export-parquet --public-export
```

## 10. Render

### 10.1 Veredicto tecnico

No uses un `cron job` de Render para este proyecto con SQLite persistente.

Motivo:

- Los cron jobs de Render no pueden usar persistent disk.
- SQLite necesita almacenamiento persistente para mantener `etl_control`, la base y las exportaciones entre reinicios.

Por eso el `render.yaml` del proyecto fue corregido para usar:

- `type: worker`
- `rootDir: project-metrics-monitor`
- `disk.mountPath: /opt/render/project/src/project-metrics-monitor/storage`

### 10.2 Como crear el servicio

Ruta exacta:

- Render Dashboard
- `New`
- `Blueprint`
- `Connect` tu repositorio

Valores exactos:

- Blueprint Name: `project-metrics-monitor`
- Branch: `main` o tu rama productiva real
- Blueprint Path: `project-metrics-monitor/render.yaml`

Haz clic en:

- `Deploy Blueprint`

### 10.3 Variables de entorno en Render

Las pide el Blueprint porque `sync: false`:

- `GITHUB_OWNER`
- `GITHUB_REPOS`
- `GITHUB_TOKEN`

Y deja estos valores recomendados:

- `ETL_SINCE=2026-01-01T00:00:00Z`
- `DB_PATH=storage/project_metrics.db`
- `EXPORT_DIR=storage/exports`
- `ETL_INTERVAL_HOURS=12`
- `ETL_RETRY_MINUTES=15`
- `ETL_RUN_ON_START=true`
- `ETL_EXPORT_CSV=true`
- `ETL_EXPORT_PARQUET=true`
- `ETL_PUBLIC_EXPORT=true`

### 10.4 Que significa cada variable de Render

| Variable | Que es | Para que sirve | Donde se crea | Valor recomendado |
|---|---|---|---|---|
| `GITHUB_OWNER` | Owner GitHub | ETL objetivo | Render service -> Environment | `TU_OWNER` |
| `GITHUB_REPOS` | Repos separados por comas | ETL objetivo | Render service -> Environment | `TU_REPO1,TU_REPO2` |
| `GITHUB_TOKEN` | PAT GitHub | Evitar rate limit y habilitar GraphQL | Render service -> Environment | `github_pat_REEMPLAZAR` |
| `ETL_SINCE` | Fecha inicial | Primera corrida | Render service -> Environment | `2026-01-01T00:00:00Z` |
| `DB_PATH` | Ruta del SQLite | Persistencia en disco montado | Render service -> Environment | `storage/project_metrics.db` |
| `EXPORT_DIR` | Carpeta de export | Persistencia de CSV/Parquet | Render service -> Environment | `storage/exports` |
| `ETL_INTERVAL_HOURS` | Frecuencia del worker | Repeticion periodica | Render service -> Environment | `12` |
| `ETL_RETRY_MINUTES` | Tiempo entre reintentos | Recuperacion ante fallo | Render service -> Environment | `15` |

### 10.5 Como obtener los valores reales

- `GITHUB_OWNER`: abre el repo en GitHub y copia el owner de `https://github.com/OWNER/REPO`
- `GITHUB_REPOS`: usa los nombres exactos de los repos que quieras medir
- `GITHUB_TOKEN`: generarlo en GitHub segun la seccion 8
- `ETL_SINCE`: define la fecha historica de inicio de tu proyecto

### 10.6 Como verificar que el despliegue fue exitoso

Ruta exacta:

- Render Dashboard
- servicio `project-metrics-monitor-worker`
- tab `Logs`

Validaciones:

- Debes ver una ejecucion de `python -m src.run`
- Debes ver salida JSON final con `loaded_at`
- No debes ver `github_token_missing`
- No debes ver errores de permisos en `storage/`

Validaciones persistentes:

- servicio -> `Disks`
- Debe existir el disco `project-metrics-data`
- Debe mostrar actividad y uso mayor a `0`

Verificacion operativa:

- Reinicia el worker desde `Manual Deploy` o `Restart service`
- Confirma en logs que la segunda corrida usa incremental y la base sigue existiendo

## 11. README, `requirements.txt`, `pyproject.toml`, `render.yaml` y workflows

Estado esperado despues de esta preparacion:

- `requirements.txt`: dependencias runtime + tooling de desarrollo/CI
- `pyproject.toml`: dependencias runtime, extra `dev`, build-system y configuracion de herramientas
- `README.md`: debe referenciar esta guia y usar `--export-dir`
- `render.yaml`: debe usar worker con persistent disk
- workflows: deben seguir instalando desde `requirements.txt` y correr lint, tests y ETL

## 12. Power BI

### 12.1 Conectar la base de datos

Opcion recomendada para defensa de presentacion:

- Usa CSV o Parquet si no quieres depender de ODBC

Opcion directa SQLite:

1. Instala driver ODBC SQLite:
   - [SQLite ODBC Driver](http://www.ch-werner.de/sqliteodbc/)
2. Abre `ODBC Data Sources (64-bit)`
3. `System DSN` -> `Add`
4. Selecciona `SQLite3 ODBC Driver`
5. Database Name / path:
   - `C:\Github\proyecto-si885-2026-i-monitor-de-metricas-de-proyectos\project-metrics-monitor\database\project_metrics.db`

### 12.2 Importar datos

En Power BI Desktop:

- `Inicio` -> `Obtener datos`
- Elige `ODBC`, `Texto/CSV` o `Parquet`

Importa como minimo:

- `dim_date`
- `dim_repo`
- `dim_author`
- `dim_label`
- `fact_commits`
- `fact_prs`
- `fact_issues`
- `fact_releases`
- `fact_workflows`
- `fact_contributors`
- `bridge_issue_labels`
- `bridge_pr_labels`
- `vw_repo_summary`
- `vw_quality_metrics`
- `vw_throughput`

### 12.3 Crear el modelo

Relaciones obligatorias:

- `dim_repo[repo_id]` -> `fact_*[repo_id]`
- `dim_author[author_id]` -> `fact_commits[author_id]`
- `dim_author[author_id]` -> `fact_prs[author_id]`
- `dim_author[author_id]` -> `fact_issues[author_id]`
- `dim_date[date_id]` -> fechas de hechos
- `dim_label[label_id]` -> tablas puente
- `fact_issues[issue_id]` -> `bridge_issue_labels[issue_id]`
- `fact_prs[pr_id]` -> `bridge_pr_labels[pr_id]`

### 12.4 Publicar dashboard

1. Guarda `dashboard.pbix`
2. En Power BI Desktop -> `Publicar`
3. Elige el workspace
4. Espera confirmacion de subida

### 12.5 Habilitar Publish to Web

Solo para dataset anonimo:

1. Publica un reporte basado en `exports/public/` o vistas `vw_public_*`
2. En Power BI Service abre el reporte
3. `Archivo` -> `Insertar informe` -> `Publicar en web`
4. Copia el `iframe` o la URL publica

Validacion previa obligatoria:

- No usar `dim_author[login]`
- No usar `dim_author[display_name]`
- No exponer `fact_commits[message]` si el reporte es publico

## 13. Checklist final de salida a produccion

### 13.1 Validaciones obligatorias

- `ruff check .`
- `black --check .`
- `pytest`
- `python verify.py --offline-sample`
- ETL manual completa exitosa
- ETL incremental exitosa
- Base SQLite creada y consultable
- Exportaciones CSV correctas
- Exportaciones Parquet correctas
- Workflow `ci` en verde
- Workflow `etl-scheduled` en verde
- Worker de Render desplegado y con disk
- Power BI validado con datos reales

### 13.2 Riesgos pendientes

- SQLite no es ideal para concurrencia alta
- Render worker mantiene persistencia pero no reemplaza un motor relacional multiusuario
- Publish to Web expone datos publicamente si publicas un dataset no anonimizado
- La calidad del dataset depende de permisos y rate limits del token GitHub

### 13.3 Pruebas antes de presentar el proyecto

```powershell
ruff check .
black --check .
pytest
python verify.py --offline-sample
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01T00:00:00Z --db-path database/project_metrics.db --export-dir exports --export-csv --export-parquet --public-export
sqlite3 database/project_metrics.db "SELECT repo_name, commits, prs_merged, issues_closed, releases FROM vw_repo_summary;"
sqlite3 database/project_metrics.db "SELECT source_name, last_loaded_at FROM etl_control;"
```

### 13.4 Criterio de salida

Puedes declarar el proyecto listo para produccion academica cuando:

- todos los comandos de validacion pasan
- Render mantiene la base entre reinicios
- GitHub Actions genera artifacts
- Power BI abre y refresca sin errores
- el dashboard publico usa solo dataset anonimizado
