# Project Metrics Monitor

Solucion ETL para extraer metricas de repositorios GitHub, transformarlas a un modelo analitico y publicarlas para consumo en Power BI mediante SQLite, CSV o Parquet.

## Estado real del proyecto

El subproyecto `project-metrics-monitor/` ya incluye:

- ETL funcional con GitHub REST API y GraphQL API.
- Carga incremental con tabla `etl_control`.
- Persistencia en SQLite.
- Exportaciones CSV, Parquet y dataset publico anonimizado.
- Workflows activos en la raiz del monorepo para CI y ETL programado.
- Despliegue gratis en Render para publicar CSV por URL.
- Opcion de despliegue pagado en Render con persistencia.
- Guia operativa y de Power BI dentro de `docs/`.

## Que resuelve

GitHub expone informacion operativa, pero no ofrece por defecto un dataset listo para analitica entre varios repositorios. Este proyecto resuelve ese problema mediante un flujo ETL reproducible que consolida:

- commits;
- pull requests;
- issues;
- releases;
- workflows;
- contribuyentes;
- indicadores agregados por repositorio y tiempo.

## Arquitectura

```text
GitHub REST + GraphQL
        |
        v
src/extract/github_client.py
        |
        v
src/services/etl_service.py
        |
        v
src/transform/dataset_builder.py
        |
        v
src/load/sqlite_loader.py
        |
        +--> SQLite
        +--> CSV
        +--> Parquet
        +--> public/ (dataset anonimizado)
```

Capas principales:

- `src/extract`: acceso a GitHub.
- `src/transform`: construccion del modelo analitico.
- `src/load`: carga a SQLite.
- `src/repositories`: control incremental.
- `src/services`: orquestacion ETL y exportaciones.
- `src/cli`: parseo de argumentos.
- `src/render_web.py`: despliegue gratis en Render.
- `src/render_worker.py`: despliegue persistente opcional.

## Estructura del subproyecto

```text
project-metrics-monitor/
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── views.sql
├── docs/
│   ├── deployment.md
│   ├── power_bi.md
│   └── production_runbook.md
├── src/
│   ├── cli/
│   ├── extract/
│   ├── load/
│   ├── models/
│   ├── repositories/
│   ├── services/
│   ├── transform/
│   ├── utils/
│   ├── render_web.py
│   ├── render_worker.py
│   └── run.py
├── tests/
├── .env.example
├── pyproject.toml
├── render.yaml
├── render.paid.yaml
├── requirements.txt
└── verify.py
```

## Requisitos

### Software

- Python `3.12` o superior.
- Git.
- Power BI Desktop.
- SQLite CLI opcional.
- ODBC SQLite opcional si usaras conexion directa desde Power BI.

### Cuentas

- GitHub.
- Render opcional.
- Power BI opcional para publicar dashboards.

## Instalacion local

Desde `project-metrics-monitor/`:

```powershell
py -3.12 -m venv .venv
.venv\Scripts\Activate.ps1
python -m pip install --upgrade pip
pip install -r requirements.txt
```

Alternativa con extras de desarrollo:

```powershell
pip install ".[dev]"
```

## Configuracion

### 1. Crear `.env`

```powershell
Copy-Item .env.example .env
notepad .env
```

Contenido minimo:

```env
GITHUB_TOKEN=github_pat_REEMPLAZAR
```

### 2. Verificar CLI

```powershell
python -m src.run --help
```

## Uso rapido

### Carga local completa

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01T00:00:00Z --db-path database/project_metrics.db --export-dir exports --export-csv --export-parquet --public-export
```

### Carga incremental

Usa el mismo comando. La ETL revisa `etl_control` y solo trae datos posteriores al ultimo `last_loaded_at`.

### Modo prueba sin persistir

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1 --since 2026-01-01 --dry-run
```

### Ejecucion sin token

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1 --since 2026-01-01 --no-token
```

## Salidas generadas

### Base SQLite

- Archivo por defecto: `database/project_metrics.db`

### Exportaciones privadas

- `exports/*.csv`
- `exports/*.parquet`

### Exportaciones publicas

- `exports/public/*.csv`
- `exports/public/*.parquet`

Las vistas publicas usan anonimizado para que puedan consumirse en dashboards abiertos.

## Modelo analitico

### Dimensiones

- `dim_date`
- `dim_repo`
- `dim_author`
- `dim_label`

### Hechos

- `fact_commits`
- `fact_prs`
- `fact_issues`
- `fact_releases`
- `fact_workflows`
- `fact_contributors`

### Tablas auxiliares

- `bridge_issue_labels`
- `bridge_pr_labels`
- `etl_control`

### Vistas destacadas

- `vw_repo_summary`
- `vw_quality_metrics`
- `vw_throughput`
- `vw_release_summary`
- `vw_public_repo_summary`
- `vw_public_quality_metrics`

## Validacion y pruebas

### Lint, formato y tests

```powershell
ruff check .
python -m black --check .
pytest
```

### Validacion offline reproducible

```powershell
python verify.py --offline-sample
```

### Verificacion con GitHub real

```powershell
python verify.py --owner TU_OWNER --repos TU_REPO1 --since 2026-01-01
```

## GitHub Actions

Los workflows que realmente ejecuta GitHub en este monorepo estan en la raiz:

- `/.github/workflows/project-metrics-monitor-ci.yml`
- `/.github/workflows/project-metrics-monitor-etl-scheduled.yml`

### CI

Valida:

- `ruff`
- `black --check`
- `pytest`
- `python verify.py --offline-sample`

### ETL programado

- corre cada 12 horas;
- permite `workflow_dispatch`;
- usa `PMM_GH_TOKEN`, `PMM_OWNER`, `PMM_REPOS`, `PMM_ETL_SINCE`;
- sube artifacts con SQLite y exportaciones.

## Render

### Despliegue gratis

Archivo:

- `render.yaml`

Modo real:

- servicio `web`
- plan `free`
- publica archivos CSV desde URL
- la raiz muestra estado del servicio
- ruta manual ETL: `/run?allow=true`

Uso recomendado para demo:

- abrir `https://TU-SERVICIO.onrender.com/public/`
- consumir desde Power BI los CSV publicos

### Despliegue persistente de pago

Archivo:

- `render.paid.yaml`

Modo real:

- servicio `worker`
- disco persistente
- ejecucion periodica con almacenamiento durable

## Power BI

### Opcion mas simple y real para este proyecto

Consumir desde Web los CSV publicos generados por Render, por ejemplo:

```text
https://TU-SERVICIO.onrender.com/public/vw_public_repo_summary.csv
https://TU-SERVICIO.onrender.com/public/vw_public_quality_metrics.csv
```

### Otras opciones

- CSV o Parquet locales desde `exports/`.
- ODBC SQLite contra `database/project_metrics.db`.

Guia completa:

- `docs/power_bi.md`

## Seguridad

- Nunca subas `.env` al repositorio.
- Nunca publiques `GITHUB_TOKEN`.
- Para dashboards abiertos usa solo `exports/public/`.
- Revisa mensajes y metadatos antes de usar `Publish to Web`.

## Documentacion del proyecto

- Guia de despliegue: `docs/deployment.md`
- Runbook operativo: `docs/production_runbook.md`
- Guia de Power BI: `docs/power_bi.md`

## Comandos utiles

```powershell
python -m src.run --help
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01T00:00:00Z --db-path database/project_metrics.db --export-dir exports --export-csv --export-parquet --public-export
ruff check .
python -m black --check .
pytest
python verify.py --offline-sample
```
