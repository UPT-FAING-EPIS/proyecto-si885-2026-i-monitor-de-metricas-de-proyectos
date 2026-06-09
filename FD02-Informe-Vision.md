<center>

**UNIVERSIDAD PRIVADA DE TACNA**

**FACULTAD DE INGENIERIA**

**Escuela Profesional de Ingenieria de Sistemas**

**Proyecto: Project Metrics Monitor**

**Curso: Inteligencia de Negocios**

Docente: Mag. Patrick Cuadros Quiroga

Integrantes:

**Serrano Ibanez, Nestor Juice Yomar (2022075474)**  
**Jimenez Romero, Josue Andre (2022074259)**

**Tacna - Peru**  
**2026**

</center>

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

| CONTROL DE VERSIONES | | | | | |
| :-: | :- | :- | :- | :- | :- |
| Version | Hecha por | Revisada por | Aprobada por | Fecha | Motivo |
| 1.0 | NSI, JJR | MPV | MPV | 05/04/2026 | Version inicial |
| 2.0 | NSI, JJR | Pendiente | Pendiente | 09/06/2026 | Actualizacion alineada al producto ETL real |

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

# Project Metrics Monitor

## Documento de Vision

## Version 2.0

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

# INDICE GENERAL

1. [Introduccion](#introduccion)
2. [Posicionamiento](#posicionamiento)
3. [Descripcion de los interesados y usuarios](#descripcion-de-los-interesados-y-usuarios)
4. [Vista General del Producto](#vista-general-del-producto)
5. [Caracteristicas del producto](#caracteristicas-del-producto)
6. [Restricciones](#restricciones)
7. [Rangos de calidad](#rangos-de-calidad)
8. [Precedencia y Prioridad](#precedencia-y-prioridad)
9. [Otros requerimientos del producto](#otros-requerimientos-del-producto)
10. [Conclusiones](#conclusiones)
11. [Recomendaciones](#recomendaciones)
12. [Bibliografia](#bibliografia)
13. [Webgrafia](#webgrafia)

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

# Informe de Vision

## Introduccion

### 1.1 Proposito

Definir la vision del producto Project Metrics Monitor como solucion de inteligencia de negocios para medir actividad y calidad de repositorios GitHub mediante una canalizacion ETL reproducible, exportaciones analiticas y consumo en Power BI.

### 1.2 Alcance

El alcance real del producto comprende:

- extraccion de repositorios GitHub por owner y lista de repositorios;
- captura de commits, pull requests, issues, releases, workflows y contribuyentes;
- transformacion a un modelo dimensional para BI;
- persistencia en SQLite;
- exportacion a CSV y Parquet;
- dataset publico anonimizado para presentacion;
- ejecucion local, programada por GitHub Actions y publicacion en Render.

No forma parte del alcance actual la gestion operativa de proyectos, asignacion de tareas a usuarios finales ni autenticacion web multiusuario.

### 1.3 Definiciones, siglas y abreviaturas

- **ETL**: Extract, Transform, Load.
- **BI**: Business Intelligence.
- **PAT**: Personal Access Token de GitHub.
- **SQLite**: motor de base de datos embebido usado como almacenamiento analitico.
- **CSV / Parquet**: formatos de exportacion para intercambio de datos.
- **Render**: plataforma cloud usada para despliegue gratuito o persistente.

### 1.4 Referencias

- `FD01-Informe-Factibilidad.md`
- `project-metrics-monitor/README.md`
- `project-metrics-monitor/docs/production_runbook.md`
- `project-metrics-monitor/docs/power_bi.md`
- GitHub REST API y GraphQL API
- Documentacion oficial de Render y Power BI

### 1.5 Vision general

El producto busca convertir la actividad tecnica de GitHub en informacion analitica lista para explotacion en dashboards, reduciendo el trabajo manual de consolidacion y mejorando la trazabilidad del desempeno de proyectos de software.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Posicionamiento

### 2.1 Oportunidad de negocio

Existe una necesidad clara en contextos academicos y de pequenos equipos de disponer de indicadores comparables entre repositorios sin construir una plataforma compleja ni pagar infraestructura obligatoria. Project Metrics Monitor cubre esa oportunidad con una solucion ligera, automatizable y reutilizable.

### 2.2 Definicion del problema

La informacion util para seguimiento de proyectos de software esta dispersa en GitHub y no se presenta de forma analitica por defecto. Esto genera dificultades para:

- comparar repositorios;
- medir throughput y calidad;
- preparar reportes ejecutivos;
- demostrar avances del proyecto de forma objetiva.

El producto resuelve ese problema al centralizar y modelar los datos en un formato consumible por herramientas de BI.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Descripcion de los interesados y usuarios

### 3.1 Resumen de los interesados

- Equipo desarrollador del proyecto.
- Docente del curso.
- Jurado o evaluadores academicos.
- Responsables de los repositorios analizados.

### 3.2 Resumen de los usuarios

- **Analista BI**: ejecuta ETL, valida datos y construye dashboards.
- **Docente o evaluador**: consulta indicadores y evidencia del trabajo en GitHub.
- **Estudiante o equipo de proyecto**: revisa actividad, flujo y calidad de sus repositorios.
- **Publico externo**: visualiza dataset anonimizado o dashboard publicado.

### 3.3 Entorno de usuario

El producto se usa en tres entornos principales:

- desarrollo local con Python y terminal;
- automatizacion en GitHub Actions;
- consumo analitico en Power BI Desktop o Power BI Service.

### 3.4 Perfiles de los interesados

Los interesados requieren evidencia verificable de que los indicadores provienen de una fuente oficial, que las cargas son repetibles y que el despliegue puede demostrarse sin infraestructura compleja.

### 3.5 Perfiles de los usuarios

- El analista BI necesita control sobre fechas, repositorios, exportaciones y revisiones de calidad.
- El evaluador necesita acceso simple a resultados, preferiblemente mediante CSV publicos o dashboard listo.
- El equipo de proyecto necesita comparar actividad entre repositorios y periodos.

### 3.6 Necesidades de los interesados y usuarios

- obtener metricas reales desde GitHub;
- generar datasets consistentes para Power BI;
- ejecutar cargas incrementales sin reprocesar todo el historial;
- compartir resultados sin exponer secretos;
- disponer de documentacion paso a paso para despliegue.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Vista General del Producto

### 4.1 Perspectiva del producto

Project Metrics Monitor es un subproyecto analitico dentro del monorepo. Su arquitectura se organiza por capas (`extract`, `transform`, `load`, `services`, `repositories`, `utils`) y expone un comando principal `python -m src.run` para orquestar la ETL completa.

### 4.2 Resumen de capacidades

- extraccion de repositorios GitHub;
- integracion REST y GraphQL;
- reintentos y manejo de rate limits;
- carga incremental con `etl_control`;
- exportaciones CSV, Parquet y dataset publico;
- CI de calidad y pruebas;
- despliegue gratuito en Render para publicar CSV;
- despliegue pagado opcional con worker y disco persistente.

### 4.3 Suposiciones y dependencias

- acceso a internet y disponibilidad de GitHub API;
- token GitHub valido para mejor capacidad operativa;
- Power BI Desktop para modelado del dashboard;
- Render opcional si se desea publicar resultados en una URL.

### 4.4 Costos y precios

El producto puede operar con costo obligatorio cero en entorno academico usando desarrollo local, GitHub Actions y Render Free. Los costos pasan a ser opcionales cuando se desea persistencia 24/7 en Render o capacidades avanzadas de Power BI Service.

### 4.5 Licenciamiento e instalacion

El stack principal usa herramientas y librerias abiertas en Python. La instalacion se realiza mediante `requirements.txt` o `pyproject.toml`, y la documentacion del repositorio detalla configuracion local, GitHub y Render.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Caracteristicas del producto

- Extraccion de datos de GitHub por owner y multiples repositorios.
- Modelo dimensional con `dim_repo`, `dim_author`, `dim_date`, `dim_label` y tablas de hechos.
- Vistas analiticas como `vw_repo_summary`, `vw_quality_metrics`, `vw_throughput` y `vw_public_*`.
- Exportaciones a SQLite, CSV y Parquet.
- Anonimizacion de autores para publicacion.
- Workflow CI con lint, formato, pruebas y verificacion offline.
- Workflow ETL programado con artifacts descargables.
- Publicacion de CSV publicos desde Render.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Restricciones

- Dependencia de la estructura y disponibilidad de GitHub API.
- Menor capacidad operativa si no existe `GITHUB_TOKEN`.
- SQLite no es un motor orientado a alta concurrencia multiusuario.
- Render Free usa filesystem efimero y no sustituye una base persistente de produccion.
- La calidad de algunos indicadores depende de que el repositorio use issues, labels, releases y workflows de forma consistente.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Rangos de calidad

- **Correctitud:** los datos deben provenir de APIs oficiales y pasar validaciones basicas de integridad.
- **Mantenibilidad:** el proyecto debe permanecer modular y con pruebas automatizadas.
- **Portabilidad:** la ETL debe correr localmente y en GitHub Actions.
- **Seguridad:** secretos fuera del repositorio y exportaciones anonimizadas para publicacion.
- **Disponibilidad:** el modo demo debe poder regenerar datos cuando el servicio se reinicie.
- **Calidad tecnica:** el pipeline debe mantener `ruff`, `black` y `pytest` en estado verde.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Precedencia y Prioridad

Prioridad alta:

- extraccion GitHub confiable;
- carga incremental;
- exportaciones CSV publicas;
- consistencia del modelo analitico;
- documentacion de despliegue y uso.

Prioridad media:

- exportaciones Parquet;
- despliegue persistente opcional en Render;
- automatizacion adicional de presentacion en Power BI.

Prioridad baja:

- monetizacion o empaquetado empresarial;
- soporte multiusuario operativo dentro del mismo producto.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Otros requerimientos del producto

- uso de secretos seguros en GitHub Actions y Render;
- publicacion solo de datasets `public/` cuando el dashboard sea abierto;
- comandos reproducibles para ejecucion local;
- runbook operativo para despliegue;
- compatibilidad con Power BI mediante Web, CSV, Parquet u ODBC SQLite.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Conclusiones

Project Metrics Monitor tiene una vision clara y acotada: convertir eventos tecnicos de GitHub en informacion analitica lista para BI. Su propuesta de valor se sustenta en automatizacion, bajo costo operativo, facilidad de despliegue y capacidad de presentacion academica verificable.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Recomendaciones

- mantener la operacion base con exportaciones `public/` para presentaciones abiertas;
- usar GitHub Actions como mecanismo principal de ejecucion programada;
- reservar Render persistente solo si se requiere almacenamiento durable;
- ampliar el dashboard de Power BI a partir de las vistas analiticas ya existentes.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Bibliografia

- Informe de Factibilidad del proyecto.
- Documentacion interna del repositorio `project-metrics-monitor`.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Webgrafia

- GitHub Docs - REST API y GraphQL API.
- Render Docs - Blueprints, Free Web Services y Persistent Disks.
- Microsoft Learn - Power BI Desktop y Power BI Service.
