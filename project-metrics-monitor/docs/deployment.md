# Despliegue

## Documento principal

Usa como fuente operativa principal:

- `docs/production_runbook.md`

## Resumen tecnico actualizado

Arquitectura:

```text
GitHub API
  -> ETL Python
  -> SQLite
  -> CSV / Parquet / public export
  -> Power BI Desktop / Service
```

## GitHub Actions

Workflows vigentes:

- `ci.yml`
- `etl-scheduled.yml`

Variables requeridas:

- Repository Secret `PMM_GH_TOKEN`
- Repository Variable `PMM_OWNER`
- Repository Variable `PMM_REPOS`
- Repository Variable `PMM_ETL_SINCE`

## Render

Blueprints disponibles:

- Gratis (sin tarjeta): `project-metrics-monitor/render.yaml`
- Con persistencia (requiere pago): `project-metrics-monitor/render.paid.yaml`

Servicio gratis:

- `type: web`
- `plan: free`
- `rootDir: project-metrics-monitor`

Servicio con persistencia:

- `type: worker`
- `rootDir: project-metrics-monitor`
- `disk.mountPath: /opt/render/project/src/project-metrics-monitor/storage`

Variables clave (ambos):

- `GITHUB_TOKEN`
- `GITHUB_OWNER`
- `GITHUB_REPOS`
- `ETL_SINCE`

Importante:

- No usar `cron` de Render para este proyecto con SQLite persistente
- Los cron jobs de Render no pueden usar persistent disk

## Validacion minima antes de producir

```bash
ruff check .
black --check .
pytest
python verify.py --offline-sample
python -m src.run --owner TU_OWNER --repos TU_REPO1,TU_REPO2 --since 2026-01-01T00:00:00Z --db-path database/project_metrics.db --export-dir exports --export-csv --export-parquet --public-export
```
