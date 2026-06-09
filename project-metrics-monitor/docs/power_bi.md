# Power BI Guide

## Conexion de datos

### Opcion A - SQLite directo

1. Instalar el driver ODBC de SQLite para Windows:
   - [SQLite ODBC Driver (Christian Werner)](http://www.ch-werner.de/sqliteodbc/)
2. Crear un DSN apuntando a `database/project_metrics.db`.
3. En Power BI Desktop:
   - `Obtener datos` -> `ODBC`
   - Seleccionar el DSN SQLite
   - Importar tablas y vistas requeridas
4. Ventaja:
   - Modelo unico y consistente
5. Consideracion:
   - Requiere driver instalado en cada equipo o gateway

### Opcion B - CSV exportados

1. Ejecutar:

```bash
python -m src.run --owner microsoft --repos vscode,terminal --since 2026-01-01 --export-csv
```

2. En Power BI Desktop:
   - `Obtener datos` -> `Texto/CSV`
   - Cargar los archivos de `exports/`
3. Ventaja:
   - Sin drivers externos
4. Consideracion:
   - Refresh depende de regenerar los archivos

### Opcion C - Parquet exportados

1. Ejecutar:

```bash
python -m src.run --owner microsoft --repos vscode,terminal --since 2026-01-01 --export-parquet
```

2. En Power BI Desktop:
   - `Obtener datos` -> `Parquet`
   - Cargar los archivos de `exports/`
3. Ventaja:
   - Mejor compresion y tipado
4. Consideracion:
   - Requiere refresh de archivos exportados

## Modelo de datos

### Tablas a importar

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

### Relaciones exactas

- `dim_repo[repo_id]` 1 -> * `fact_commits[repo_id]`
- `dim_repo[repo_id]` 1 -> * `fact_prs[repo_id]`
- `dim_repo[repo_id]` 1 -> * `fact_issues[repo_id]`
- `dim_repo[repo_id]` 1 -> * `fact_releases[repo_id]`
- `dim_repo[repo_id]` 1 -> * `fact_workflows[repo_id]`
- `dim_repo[repo_id]` 1 -> * `fact_contributors[repo_id]`
- `dim_author[author_id]` 1 -> * `fact_commits[author_id]`
- `dim_author[author_id]` 1 -> * `fact_prs[author_id]`
- `dim_author[author_id]` 1 -> * `fact_issues[author_id]`
- `dim_author[author_id]` 1 -> * `fact_contributors[author_id]`
- `dim_date[date_id]` 1 -> * `fact_commits[date_id]`
- `dim_date[date_id]` 1 -> * `fact_prs[created_date_id]`
- `dim_date[date_id]` 1 -> * `fact_prs[merged_date_id]`
- `dim_date[date_id]` 1 -> * `fact_issues[created_date_id]`
- `dim_date[date_id]` 1 -> * `fact_issues[closed_date_id]`
- `dim_date[date_id]` 1 -> * `fact_releases[date_id]`
- `dim_date[date_id]` 1 -> * `fact_workflows[date_id]`
- `dim_date[date_id]` 1 -> * `fact_contributors[first_seen_date_id]`
- `dim_date[date_id]` 1 -> * `fact_contributors[last_seen_date_id]`
- `dim_label[label_id]` 1 -> * `bridge_issue_labels[label_id]`
- `dim_label[label_id]` 1 -> * `bridge_pr_labels[label_id]`
- `fact_issues[issue_id]` 1 -> * `bridge_issue_labels[issue_id]`
- `fact_prs[pr_id]` 1 -> * `bridge_pr_labels[pr_id]`

### Tabla calendario en DAX

```DAX
Calendar =
ADDCOLUMNS(
    CALENDAR(DATE(2020,1,1), DATE(2035,12,31)),
    "DateId", VALUE(FORMAT([Date], "yyyymmdd")),
    "Year", YEAR([Date]),
    "Quarter", "Q" & FORMAT([Date], "Q"),
    "MonthNumber", MONTH([Date]),
    "MonthName", FORMAT([Date], "MMMM"),
    "YearMonth", FORMAT([Date], "YYYY-MM"),
    "WeekOfYear", WEEKNUM([Date], 2),
    "DayOfWeek", WEEKDAY([Date], 2)
)
```

## Medidas DAX

Definicion oficial de Throughput:

- Throughput semanal = PRs mergeadas + Issues cerradas

```DAX
Throughput =
CALCULATE(COUNTROWS(fact_prs), fact_prs[state] = "MERGED")
+ CALCULATE(COUNTROWS(fact_issues), fact_issues[state] = "CLOSED")

Lead Time Promedio =
AVERAGE(fact_prs[lead_time_hours])

Lead Time Mediano =
MEDIAN(fact_prs[lead_time_hours])

Cycle Time =
AVERAGE(fact_issues[cycle_time_hours])

Aging Promedio =
AVERAGE(fact_issues[current_age_hours])

Aging Maximo =
MAX(fact_issues[current_age_hours])

Ratio Bugs =
DIVIDE(
    CALCULATE(COUNTROWS(fact_issues), fact_issues[is_bug] = 1),
    COUNTROWS(fact_issues)
)

Releases por Mes =
COUNTROWS(fact_releases)

Contribuyentes Activos =
DISTINCTCOUNT(fact_commits[author_id])

PRs Mergeadas =
CALCULATE(COUNTROWS(fact_prs), fact_prs[state] = "MERGED")

PRs Abiertas =
CALCULATE(COUNTROWS(fact_prs), fact_prs[state] = "OPEN")

PRs Cerradas =
CALCULATE(COUNTROWS(fact_prs), fact_prs[state] = "CLOSED")

Issues Abiertas =
CALCULATE(COUNTROWS(fact_issues), fact_issues[state] = "OPEN")

Issues Cerradas =
CALCULATE(COUNTROWS(fact_issues), fact_issues[state] = "CLOSED")

Commits =
COUNTROWS(fact_commits)

Tiempo Promedio Entre Releases (Dias) =
AVERAGEX(
    FILTER(vw_release_summary, NOT(ISBLANK(vw_release_summary[days_since_previous_release]))),
    vw_release_summary[days_since_previous_release]
)

WIP =
CALCULATE(COUNTROWS(fact_prs), ISBLANK(fact_prs[closed_at]))
+ CALCULATE(COUNTROWS(fact_issues), fact_issues[state] = "OPEN")

Items Abiertos (Tendencia) =
CALCULATE(
    COUNTROWS(fact_prs),
    USERELATIONSHIP(Calendar[DateId], fact_prs[created_date_id])
)
+ CALCULATE(
    COUNTROWS(fact_issues),
    USERELATIONSHIP(Calendar[DateId], fact_issues[created_date_id])
)

Items Cerrados (Tendencia) =
CALCULATE(
    COUNTROWS(fact_prs),
    USERELATIONSHIP(Calendar[DateId], fact_prs[merged_date_id])
)
+ CALCULATE(
    COUNTROWS(fact_issues),
    USERELATIONSHIP(Calendar[DateId], fact_issues[closed_date_id])
)
```

## Paginas del dashboard

### Pagina 1 - Resumen Ejecutivo

- Tarjetas KPI:
  - `COUNTROWS(fact_commits)`
  - `[PRs Mergeadas]`
  - `CALCULATE(COUNTROWS(fact_issues), NOT(ISBLANK(fact_issues[closed_at])))`
  - `COUNTROWS(fact_releases)`
- Visuales:
  - Linea: `vw_throughput[year_week]` vs `vw_throughput[throughput_total]`
  - Barras: `dim_repo[repo_name]` vs commits
  - Barras: `dim_author[anonymized_login]` vs commits
  - Area mensual: releases, issues cerradas y PRs mergeadas

### Pagina 2 - Pull Requests

- Histograma: `fact_prs[lead_time_hours]`
- Linea: promedio semanal de `fact_prs[review_time_hours]`
- Columnas: throughput semanal de PRs mergeadas
- Scatter: `fact_prs[changed_files]` vs `fact_prs[lead_time_hours]`
- Tarjetas:
  - PRs abiertas
  - PRs cerradas
  - PRs mergeadas
  - Lead time promedio
  - Lead time mediano

### Pagina 3 - Issues

- Linea: aging promedio por semana
- Linea: cycle time promedio por semana de cierre
- Treemap o dona: distribucion por `dim_label[label_name]`
- Tarjetas:
  - issues abiertas
  - issues cerradas
  - aging maximo
  - ratio bugs

### Pagina 4 - Releases

- Columnas: releases por mes
- Linea: `vw_release_summary[days_since_previous_release]`
- Tabla:
  - repositorio
  - tag
  - fecha release
  - dias desde release anterior

### Pagina 5 - Flujo

- Area: throughput semanal
- Tarjeta: WIP
- Linea doble:
  - tendencia de apertura de issues/PRs
  - tendencia de cierre/merge
- Tabla detalle:
  - repo
  - throughput
  - WIP
  - fallos de workflow

### Filtros globales

- `dim_repo[repo_name]`
- `Calendar[Date]`
- `dim_author[anonymized_login]`
- `dim_label[label_name]`

## Publicacion en Power BI Service

### Publicacion

1. Guardar el archivo `.pbix`.
2. En Power BI Desktop, `Publicar`.
3. Seleccionar el workspace destino.

### Actualizacion

1. Para SQLite directo:
   - Instalar gateway personal o empresarial.
   - Registrar el DSN ODBC en el host del gateway.
2. Para CSV/Parquet:
   - Publicar los archivos en OneDrive, SharePoint o un almacenamiento corporativo.
   - Configurar `Refresh` desde Power BI Service.

### Refresh

- Recomendacion:
  - Programar la ETL cada dia o cada hora en GitHub Actions, Task Scheduler o un servidor Windows.
- Secuencia:
  - Ejecutar ETL incremental
  - Generar exportaciones
  - Refrescar dataset

### Publish to Web

1. Verificar que el dataset no contiene datos sensibles.
2. En Power BI Service:
   - Abrir el reporte
   - `Archivo` -> `Insertar informe` -> `Publicar en web`
3. Copiar la URL publica o el `iframe`.

### Seguridad de publicacion

- No publicar:
  - tokens
  - correos privados
  - nombres reales no anonimizados
  - ramas privadas o mensajes internos si contienen informacion sensible
- Anonimizacion:
  - usar `dim_author[anonymized_login]`
  - ocultar `dim_author[login]` y `dim_author[display_name]` del reporte publico
- Hash de usuarios:
  - ya se genera en ETL con SHA-256 truncado a 16 caracteres
- Verificacion previa:
  - revisar `fact_commits[message]`
  - revisar descripciones de issues/PRs si se amplian en el futuro
  - ejecutar busqueda de patrones sensibles antes de publicar

## Checklist final

- Importar modelo con relaciones correctas
- Ocultar claves tecnicas en el modelo
- Marcar `Calendar` como tabla de fechas
- Definir formato decimal para horas y ratios
- Validar filtros cruzados por repositorio, fecha, autor y label
- Publicar solo el dataset anonimizado
