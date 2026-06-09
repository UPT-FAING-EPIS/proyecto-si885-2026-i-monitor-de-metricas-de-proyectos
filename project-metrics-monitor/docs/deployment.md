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

Blueprint correcto:

- `project-metrics-monitor/render.yaml`

Servicio correcto:

- `type: worker`
- `rootDir: project-metrics-monitor`
- `disk.mountPath: /opt/render/project/src/storage`

Variables clave:

- `GITHUB_TOKEN`
- `GITHUB_OWNER`
- `GITHUB_REPOS`
- `ETL_SINCE`
- `DB_PATH=storage/project_metrics.db`
- `EXPORT_DIR=storage/exports`
- `ETL_INTERVAL_HOURS=12`

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
