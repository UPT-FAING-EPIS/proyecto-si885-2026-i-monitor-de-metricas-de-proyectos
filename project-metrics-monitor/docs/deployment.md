# Despliegue Inicial

## Arquitectura de despliegue

```text
GitHub API
  ↓
ETL Python
  ↓
SQLite
  ↓
Exportaciones CSV / Parquet / Public Export
  ↓
Power BI Desktop
  ↓
Power BI Service
  ↓
Publish to Web
```

## Requisitos de infraestructura

- CPU minima: 2 vCPU
- RAM minima: 4 GB
- Almacenamiento minimo: 20 GB SSD
- Sistema operativo recomendado: Ubuntu 22.04 LTS o Windows Server 2022

## Despliegue en Render

Archivo generado: `render.yaml`

### Variables de entorno

- `GITHUB_TOKEN`
- `GITHUB_OWNER`
- `GITHUB_REPOS`
- `ETL_SINCE`
- `DB_PATH`
- `PYTHON_VERSION`

### Build command

```bash
python -m pip install --upgrade pip
pip install -r requirements.txt
```

### Start command

```bash
python -m src.run --owner ${GITHUB_OWNER} --repos ${GITHUB_REPOS} --since ${ETL_SINCE} --db-path ${DB_PATH} --export-csv --export-parquet --public-export
```

### Perfiles de automatizacion

- Cada 6 horas: `0 */6 * * *`
- Cada 12 horas: `0 */12 * * *`
- Diario: `0 2 * * *`

### Recomendacion

- Recomendada: cada 12 horas

## GitHub Actions

Workflows:

- `ci.yml`: lint, tests, cobertura minima y validacion offline
- `etl-scheduled.yml`: tests, cobertura, ETL programada y artefactos

### Variables requeridas en GitHub

- Repository Variable `GITHUB_OWNER`
- Repository Variable `GITHUB_REPOS`
- Repository Variable `ETL_SINCE`
- Repository Secret `GITHUB_TOKEN_ETL`

## Backups

### SQLite

- Frecuencia recomendada: diaria
- Retencion recomendada: 30 dias
- Estrategia: copiar `database/*.db` luego de cada corrida programada

### CSV

- Frecuencia recomendada: cada ejecucion ETL
- Retencion recomendada: 14 dias
- Estrategia: versionar por timestamp en almacenamiento de objetos o artefactos

### Parquet

- Frecuencia recomendada: cada ejecucion ETL
- Retencion recomendada: 30 dias
- Estrategia: conservar exportaciones para reprocesos y consumo BI

## Monitoreo

- [ ] ETL exitosa
- [ ] Exportaciones generadas
- [ ] Tamaño DB dentro del umbral esperado
- [ ] Errores API GitHub = 0 o controlados por retry
- [ ] Tiempo de ejecucion dentro de SLA
- [ ] Conteo de tablas fact mayor a 0
- [ ] Public export generado en `exports/public/`

## Publicacion Power BI

Validaciones antes de publicar:

- [ ] Relaciones del modelo cargadas
- [ ] Medidas DAX cargadas
- [ ] Filtros globales configurados
- [ ] Dataset actualizado desde SQLite o exportaciones
- [ ] Publish to Web solo con dataset anonimo

## Checklist Go Live

### Base de datos

- [x] Schema SQLite generado
- [x] Vistas analiticas generadas
- [x] Carga incremental implementada
- [ ] Backup diario automatizado

### ETL

- [x] Extraccion REST y GraphQL implementada
- [x] Retry y rate limit implementados
- [x] CLI productiva disponible
- [x] Workflow ETL programado generado

### Exportaciones

- [x] Export CSV implementado
- [x] Export Parquet implementado
- [x] Public export implementado
- [ ] Versionado externo de exportaciones configurado

### Seguridad

- [x] Variables por entorno
- [x] Sin hardcodeo de secretos
- [x] Hash de autores implementado
- [ ] Rotacion de token operativa

### Dashboard

- [x] Modelo estrella definido
- [x] Medidas DAX definidas
- [x] Relaciones documentadas
- [ ] PBIX final publicado en workspace productivo

### Publicacion

- [x] Guía Power BI generada
- [x] Publish to Web documentado
- [ ] Publish to Web habilitado en Power BI Service

## Veredicto Final

- ¿Puede desplegarse hoy?: NO

Falta exactamente:

- Configurar `GITHUB_TOKEN_ETL` en GitHub Actions o `GITHUB_TOKEN` en Render
- Definir `GITHUB_OWNER`, `GITHUB_REPOS` y `ETL_SINCE` en entorno
- Configurar backup externo para `database/*.db`
- Publicar el archivo `.pbix` en Power BI Service
- Habilitar `Publish to Web` sobre el dataset anonimo
