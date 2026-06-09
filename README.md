# Proyecto SI885 2026-I

Monorepo del proyecto academico `proyecto-si885-2026-i-monitor-de-metricas-de-proyectos`.

## Descripcion General

Este repositorio contiene los entregables y componentes tecnicos del curso de Inteligencia de Negocios. Actualmente, el componente mas completo y operativo es `project-metrics-monitor`, una solucion ETL que extrae metricas reales desde GitHub, las transforma a un modelo analitico y las publica para consumo en Power BI.

El monorepo tambien incluye un subproyecto web independiente en `app-web`, que no corresponde al flujo ETL principal documentado para despliegue y analitica.

## Integrantes

- `Serrano Ibanez, Nestor Juice Yomar`
- `Jimenez Romero, Josue Andre`

## Docente

- `Mag. Patrick Cuadros Quiroga`

## Institucion

- `Universidad Privada de Tacna`
- `Facultad de Ingenieria`
- `Escuela Profesional de Ingenieria de Sistemas`
## Enlace Power BI
https://app.powerbi.com/reportEmbed?reportId=dc55a052-7a47-41aa-8666-151b48ebc993&autoAuth=true&ctid=b6b466ee-468d-4011-b9fc-fbdcf82ac90a


## Estructura del Repositorio

```text
proyecto-si885-2026-i-monitor-de-metricas-de-proyectos/
├── .github/
│   └── workflows/
│       ├── project-metrics-monitor-ci.yml
│       └── project-metrics-monitor-etl-scheduled.yml
├── app-web/
│   ├── app/
│   ├── docs/
│   └── public/
├── project-metrics-monitor/
│   ├── database/
│   ├── docs/
│   ├── src/
│   ├── tests/
│   ├── README.md
│   ├── render.yaml
│   └── render.paid.yaml
├── FD01-Informe-Factibilidad.md
├── FD02-Informe-Vision.md
└── README.md
```

## Subproyecto Principal

### `project-metrics-monitor`

Es el subproyecto principal para la parte de inteligencia de negocios y despliegue. Implementa:

- extraccion de datos desde GitHub REST API y GraphQL API;
- transformacion a un modelo dimensional;
- carga incremental en SQLite;
- exportaciones CSV y Parquet;
- dataset publico anonimizado;
- integracion con Power BI;
- automatizacion mediante GitHub Actions;
- publicacion gratuita de CSV en Render.

## Funcionalidades Reales de `project-metrics-monitor`

- Extraccion de:
  - commits
  - pull requests
  - issues
  - releases
  - workflows
  - contribuyentes
- Carga incremental con `etl_control`
- Generacion de vistas analiticas como:
  - `vw_repo_summary`
  - `vw_quality_metrics`
  - `vw_throughput`
  - `vw_public_repo_summary`
  - `vw_public_quality_metrics`
- Exportaciones listas para Power BI
- Despliegue gratuito en Render para consumo via URL

## Tecnologias Utilizadas

### `project-metrics-monitor`

- Python `3.12+`
- SQLite
- Pandas
- PyArrow
- Requests
- Pytest
- Ruff
- Black
- GitHub Actions
- Render
- Power BI

### `app-web`

- PHP
- MVC basico
- vistas web

## GitHub Actions

Los workflows activos del monorepo para `project-metrics-monitor` estan en la raiz del repositorio:

- `/.github/workflows/project-metrics-monitor-ci.yml`
- `/.github/workflows/project-metrics-monitor-etl-scheduled.yml`

Estos workflows validan calidad, ejecutan pruebas y permiten correr la ETL de forma programada o manual.

## Render

Para `project-metrics-monitor` existen dos blueprints:

- `project-metrics-monitor/render.yaml`
  - despliegue gratis
  - publica CSV por URL
- `project-metrics-monitor/render.paid.yaml`
  - despliegue persistente
  - usa worker y almacenamiento durable

## Power BI

El flujo recomendado actualmente consiste en consumir los CSV publicos generados por `project-metrics-monitor` desde Render o importar los archivos locales exportados por la ETL.

Ejemplo de consumo web:

```text
https://TU-SERVICIO.onrender.com/public/vw_public_repo_summary.csv
https://TU-SERVICIO.onrender.com/public/vw_public_quality_metrics.csv
```

## Documentacion Importante

- Documentacion del subproyecto principal: `project-metrics-monitor/README.md`
- Guia de despliegue: `project-metrics-monitor/docs/deployment.md`
- Runbook operativo: `project-metrics-monitor/docs/production_runbook.md`
- Guia de Power BI: `project-metrics-monitor/docs/power_bi.md`
- Factibilidad: `FD01-Informe-Factibilidad.md`
- Vision: `FD02-Informe-Vision.md`

## Primeros Pasos

Si vas a trabajar con la parte principal del proyecto:

```powershell
cd project-metrics-monitor
py -3.12 -m venv .venv
.venv\Scripts\Activate.ps1
python -m pip install --upgrade pip
pip install -r requirements.txt
Copy-Item .env.example .env
```

Luego configura `GITHUB_TOKEN` en `.env` y ejecuta:

```powershell
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01T00:00:00Z --db-path database/project_metrics.db --export-dir exports --export-csv --export-parquet --public-export
```

## Nota Importante

La documentacion operativa y los despliegues preparados durante esta etapa se centran en `project-metrics-monitor`. El `README.md` de ese subproyecto contiene el detalle tecnico actualizado.
