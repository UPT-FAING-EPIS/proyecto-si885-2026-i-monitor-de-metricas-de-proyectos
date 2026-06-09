# Monitor de Metricas de Proyectos

Solucion ETL completa para extraer metricas de proyectos de software desde GitHub, transformarlas a un modelo analitico en SQLite y publicarlas en Power BI Desktop o Power BI Service.

## Objetivo

El repositorio construye un flujo reproducible para analizar salud, actividad, flujo y calidad de proyectos de software mediante:

- Extraccion desde GitHub REST API v3 y GraphQL API v4
- Transformacion a un modelo estrella para BI
- Carga incremental en SQLite
- Exportaciones a CSV y Parquet
- Consumo en Power BI Desktop y Power BI Service

## Arquitectura

Se aplica una Clean Architecture ligera con separacion por capas:

- `src/extract`: acceso a GitHub REST y GraphQL
- `src/transform`: normalizacion y modelo dimensional
- `src/load`: persistencia en SQLite
- `src/repositories`: repositorio de control ETL incremental
- `src/services`: orquestacion ETL y exportaciones
- `src/cli`: parseo y validacion de argumentos
- `src/utils`: configuracion, fechas, logs, validaciones y hash

Principios aplicados:

- SOLID
- Repository Pattern
- Dependency Injection simple
- Separation of Concerns
- Tipado con `typing`

## Estructura del proyecto

```text
project-metrics-monitor/
├── .github/
│   └── workflows/
│       └── ci.yml
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── views.sql
├── docs/
│   └── power_bi.md
├── exports/
├── src/
│   ├── cli/
│   │   ├── __init__.py
│   │   └── parser.py
│   ├── extract/
│   │   ├── __init__.py
│   │   └── github_client.py
│   ├── load/
│   │   ├── __init__.py
│   │   └── sqlite_loader.py
│   ├── models/
│   │   ├── __init__.py
│   │   └── entities.py
│   ├── repositories/
│   │   ├── __init__.py
│   │   └── control_repository.py
│   ├── services/
│   │   ├── __init__.py
│   │   ├── etl_service.py
│   │   └── export_service.py
│   ├── transform/
│   │   ├── __init__.py
│   │   └── dataset_builder.py
│   ├── utils/
│   │   ├── __init__.py
│   │   ├── config.py
│   │   ├── dates.py
│   │   ├── hash_utils.py
│   │   ├── logging_utils.py
│   │   └── validators.py
│   ├── __init__.py
│   └── run.py
├── tests/
│   ├── __init__.py
│   ├── test_etl_service.py
│   ├── test_github_client.py
│   ├── test_loader.py
│   └── test_transform.py
├── .env.example
├── .gitignore
├── pyproject.toml
├── README.md
├── requirements.txt
└── verify.py
```

## Fuente y estrategia de extraccion

### GraphQL API v4

Se usa para:

- Pull Requests
- Reviews
- Issues
- Labels

### REST API v3

Se usa para:

- Repositorios
- Commits
- Releases
- GitHub Actions
- Endpoints no disponibles en GraphQL

### Capacidad operativa

- Paginacion automatica
- Retry automatico
- Exponential backoff
- Manejo de rate limits
- Logs estructurados JSON
- Autenticacion por `GITHUB_TOKEN`
- Fallback a endpoints publicos si no existe token

## Modelo de datos BI

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

### Tablas auxiliares

- `bridge_issue_labels`
- `bridge_pr_labels`
- `etl_control`

### Vistas analiticas

- `vw_throughput`
- `vw_commits_daily`
- `vw_pr_status_summary`
- `vw_issue_status_summary`
- `vw_release_cadence`
- `vw_flow_trends_weekly`
- `vw_lead_time`
- `vw_cycle_time`
- `vw_aging`
- `vw_repo_summary`
- `vw_author_activity`
- `vw_release_summary`
- `vw_quality_metrics`

Definicion oficial de Throughput:

- Throughput semanal = PRs mergeadas + Issues cerradas

## Instalacion

### Requisitos

- Windows
- Python 3.12 o superior
- Power BI Desktop
- SQLite ODBC Driver si se usara conexion directa desde Power BI

### Instalacion local

```bash
python -m venv .venv
.venv\Scripts\activate
python -m pip install --upgrade pip
pip install -r requirements.txt
```

## Configuracion

### Variables de entorno

Crear archivo `.env` a partir de `.env.example`:

```env
GITHUB_TOKEN=tu_token_personal
```

### Reglas de seguridad

- Nunca hardcodear secretos
- Nunca imprimir tokens
- Variables solo por `.env`
- Consultas SQL parametrizadas
- Validacion de owner, repos y fechas

## Ejecucion

### Comando principal

```bash
python -m src.run --owner microsoft --repos vscode,terminal --since 2026-01-01
```

### Opciones disponibles

- `--db-path`: cambia la ruta del SQLite
- `--dry-run`: ejecuta extraccion y transformacion sin persistir
- `--no-token`: ignora `GITHUB_TOKEN`
- `--export-csv`: exporta tablas y vistas a CSV
- `--export-parquet`: exporta tablas y vistas a Parquet
- `--public-export`: exporta un dataset anonimo a `exports/public/`

### Ejemplos

```bash
python -m src.run --owner microsoft --repos vscode,terminal --since 2026-01-01 --export-csv --export-parquet
python -m src.run --owner microsoft --repos vscode --since 2026-01-01 --db-path database\custom_metrics.db
python -m src.run --owner microsoft --repos terminal --since 2026-01-01 --dry-run
python -m src.run --owner microsoft --repos terminal --since 2026-01-01 --no-token
python -m src.run --owner microsoft --repos vscode,terminal --since 2026-01-01T00:00:00Z --export-csv --public-export
```

## Ejecucion incremental

La tabla `etl_control` almacena:

- `source_name`
- `last_loaded_at`

Funcionamiento:

1. La primera corrida usa `--since` (fecha o timestamp ISO 8601).
2. Las corridas siguientes comparan `--since` contra `etl_control.last_loaded_at` (timestamp completo).
3. La ETL recupera solo datos estrictamente posteriores al ultimo timestamp registrado.

Formato de `source_name`:

- `github:microsoft/vscode`
- `github:microsoft/terminal`

## Exportaciones

### SQLite

- Archivo principal: `database/project_metrics.db`
- Uso recomendado para modelo unico, trazabilidad y vistas SQL

### CSV

Generacion:

```bash
python -m src.run --owner microsoft --repos vscode,terminal --since 2026-01-01 --export-csv
```

Salida:

- `exports/*.csv`

### Parquet

Generacion:

```bash
python -m src.run --owner microsoft --repos vscode,terminal --since 2026-01-01 --export-parquet
```

Salida:

- `exports/*.parquet`

## Script de validacion

### Verificacion offline reproducible

```bash
python verify.py --offline-sample
```

### Verificacion con GitHub publico

```bash
python verify.py --owner microsoft --repos terminal --since 2026-01-01
```

El script:

- crea el schema
- ejecuta una carga de ejemplo
- muestra conteos por tabla
- ejecuta consultas de validacion
- verifica integridad referencial

## Testing

El proyecto incluye:

- Unit tests
- Integration tests ligeros
- Cobertura minima configurada en 80%

Ejecucion:

```bash
pytest
```

## CI/CD

Workflow incluido en `.github/workflows/ci.yml` con:

- Ruff
- Black
- Pytest con cobertura
- Validacion ETL offline mediante `verify.py --offline-sample`

## Power BI

La guia completa esta en `docs/power_bi.md`.

### Conexion SQLite

#### Opcion A

- Usar ODBC con driver SQLite para Windows
- Instalar [SQLite ODBC Driver](http://www.ch-werner.de/sqliteodbc/)
- Conectar Power BI Desktop por `ODBC`

#### Opcion B

- Cargar los CSV exportados desde `exports/`

#### Opcion C

- Cargar los Parquet exportados desde `exports/`

### Relacion recomendada

- Estrella con `dim_repo`, `dim_author`, `dim_date`, `dim_label` como tablas de dimension
- Hechos separados por dominio operativo
- Puentes para labels

### Dashboard de 5 paginas

1. Resumen Ejecutivo
2. Pull Requests
3. Issues
4. Releases
5. Flujo

### Filtros globales

- Repositorio
- Fecha
- Autor
- Label

## Publicacion publica

### Power BI Service

Flujo sugerido:

1. Ejecutar ETL incremental
2. Exportar SQLite o archivos
3. Refrescar dataset
4. Publicar reporte
5. Validar anonimizado

### Publish to Web

Antes de publicar:

- revisar que no existan secretos
- usar autores anonimizados
- usar `--public-export` y publicar solo `exports/public/`
- confirmar que el dataset sea publicable

## Anonimizacion y seguridad

### Que datos no deben publicarse

- `GITHUB_TOKEN`
- cuentas internas
- correos privados
- mensajes con informacion confidencial
- nombres reales si el reporte sera publico

### Como anonimizar autores

El ETL genera `dim_author.anonymized_login` usando SHA-256. Para dashboards publicos:

- usar `anonymized_login` en lugar de `login`
- ocultar `login` y `display_name`
 - excluir mensajes de commit (usar `exports/public/`)

### Como verificar ausencia de datos sensibles

- revisar exports antes de publicar
- buscar cadenas como `token`, `secret`, `password`, `key`
- revisar mensajes de commit
- revisar nombres de labels y releases

## Troubleshooting

### Rate limits

Sin `GITHUB_TOKEN`, GitHub aplica un limite mucho menor para REST. La ETL continua y registra warnings si no puede recuperar alguna fuente.

- usar `GITHUB_TOKEN`
- aumentar ventana temporal con cargas incrementales frecuentes
- evitar intervalos historicos demasiado amplios en una sola corrida

### Errores comunes

#### SQLite driver

Problema:

- Power BI no detecta SQLite directamente

Solucion:

- instalar driver ODBC SQLite
- crear DSN correcto
- validar arquitectura 64 bits

#### GitHub authentication

Problema:

- respuestas `403`, `429` o limite agotado

Solucion:

- definir `GITHUB_TOKEN` en `.env`
- si se usa `--no-token`, la ETL evita GraphQL y usa REST cuando esta disponible
- reintentar luego del reset del rate limit

#### Fechas invalidas

Problema:

- error en `--since`

Solucion:

- usar formato `YYYY-MM-DD`
 - o timestamp `YYYY-MM-DDTHH:MM:SSZ`

#### Sin datos exportados

Problema:

- no aparecen archivos en `exports/`

Solucion:

- usar `--export-csv` o `--export-parquet`
- ejecutar sin `--dry-run`

## Comandos utiles

```bash
ruff check .
black --check .
pytest
python verify.py --offline-sample
python -m src.run --owner microsoft --repos vscode,terminal --since 2026-01-01 --export-csv --export-parquet
```
