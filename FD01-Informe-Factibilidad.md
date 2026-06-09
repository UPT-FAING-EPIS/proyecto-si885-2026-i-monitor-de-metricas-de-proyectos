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

# Project Metrics Monitor

## Informe de Factibilidad

## Version 2.0

| CONTROL DE VERSIONES | | | | | |
| :---: | :--- | :--- | :--- | :--- | :--- |
| Version | Hecha por | Revisada por | Aprobada por | Fecha | Motivo |
| 1.0 | NS, JJR | MPV | MPV | 05/04/2026 | Version inicial |
| 2.0 | NS, JJR | Pendiente | Pendiente | 09/06/2026 | Actualizacion alineada al sistema ETL real desplegado en GitHub, Render y Power BI |

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

# INDICE GENERAL

1. [Descripcion del Proyecto](#descripcion-del-proyecto)
2. [Riesgos](#riesgos)
3. [Analisis de la Situacion Actual](#analisis-de-la-situacion-actual)
4. [Estudio de Factibilidad](#estudio-de-factibilidad)
5. [Analisis Financiero](#analisis-financiero)
6. [Conclusiones](#conclusiones)

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

# Informe de Factibilidad

## Descripcion del Proyecto

### 1.1 Nombre del proyecto

Project Metrics Monitor

### 1.2 Duracion del proyecto

1 ciclo academico para analisis, implementacion, validacion y presentacion.

### 1.3 Descripcion

Project Metrics Monitor es una solucion ETL orientada a inteligencia de negocios que extrae metricas de repositorios GitHub, las transforma a un modelo analitico y las publica para consumo en Power BI. El sistema trabaja con GitHub REST API y GraphQL, persiste resultados en SQLite, genera exportaciones CSV y Parquet, soporta cargas incrementales y cuenta con despliegue reproducible en GitHub Actions y Render.

El producto real implementado no es un sistema de gestion de tareas ni una aplicacion MVC de usuarios finales. Su alcance actual es analitico: consolidar actividad de commits, pull requests, issues, releases, workflows y contribuyentes para generar indicadores comparables por repositorio.

### 1.4 Objetivos

#### 1.4.1 Objetivo general

Implementar una plataforma reproducible de monitoreo de metricas de proyectos de software a partir de repositorios GitHub, con salida analitica lista para Power BI.

#### 1.4.2 Objetivos especificos

- Automatizar la extraccion de datos desde GitHub usando API REST y GraphQL.
- Construir un modelo dimensional con tablas de hechos, dimensiones y vistas analiticas.
- Mantener cargas incrementales mediante control de ultima ejecucion.
- Exportar datasets en SQLite, CSV y Parquet.
- Publicar datasets anonimizados para Power BI y presentacion web.
- Permitir ejecucion local, programada en GitHub Actions y despliegue en Render.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Riesgos

- **Dependencia de GitHub API:** cambios en limites, permisos o disponibilidad pueden afectar la extraccion.
- **Calidad del dato fuente:** si los repositorios no usan issues, labels, releases o workflows, algunos indicadores tendran baja representatividad.
- **Token vencido o mal configurado:** la ausencia de `GITHUB_TOKEN` reduce el rate limit y puede degradar la ejecucion.
- **Persistencia en Render Free:** el despliegue gratuito usa filesystem efimero, por lo que no sirve como almacenamiento permanente.
- **Uso de SQLite:** es adecuado para analitica academica y cargas secuenciales, pero no para alta concurrencia multiusuario.
- **Publicacion en Power BI:** si se publica un dashboard abierto, solo deben usarse exportaciones anonimizadas.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Analisis de la Situacion Actual

### 3.1 Planteamiento del problema

Los equipos academicos o pequenos grupos de desarrollo suelen revisar el estado de sus proyectos directamente en GitHub, de forma manual y fragmentada. Esto genera problemas concretos:

- no existe una vista consolidada entre varios repositorios;
- la informacion historica no esta modelada para analitica;
- comparar throughput, calidad o actividad requiere trabajo manual;
- la presentacion en clase o a interesados depende de capturas sueltas o navegacion manual del repositorio.

El sistema resuelve este problema mediante una canalizacion ETL reproducible que transforma eventos operativos de GitHub en un dataset analitico reutilizable.

### 3.2 Consideraciones de hardware y software

Requisitos reales identificados en el repositorio:

- Python 3.12 o superior.
- Librerias Python: `requests`, `pandas`, `pyarrow`, `pytest`, `ruff`, `black`, `coverage`.
- Base de datos SQLite local.
- GitHub repository con workflows en la raiz del monorepo.
- Render opcional para publicar exportaciones CSV publicas.
- Power BI Desktop para modelado y publicacion.
- Sistema operativo compatible con Python; el proyecto ha sido preparado y validado en Windows para desarrollo local y en Ubuntu para CI.

Arquitectura real del producto:

- `src/extract`: cliente GitHub.
- `src/transform`: constructor del dataset dimensional.
- `src/load`: carga SQLite.
- `src/services`: orquestacion ETL y exportaciones.
- `src/repositories`: control incremental.
- `src/render_web.py`: despliegue gratuito en Render.
- `src/render_worker.py`: despliegue persistente opcional en Render pagado.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Estudio de Factibilidad

### 4.1 Factibilidad Tecnica

La factibilidad tecnica es **alta** porque el sistema ya se encuentra implementado y validado en el repositorio. Los elementos que sustentan esta conclusion son:

- stack estable y de bajo riesgo: Python + SQLite + CSV/Parquet + Power BI;
- integracion directa con GitHub API usando librerias ampliamente adoptadas;
- validaciones automatas con `ruff`, `black`, `pytest` y `coverage`;
- workflow CI y workflow ETL programado en GitHub Actions;
- modo de despliegue gratuito en Render para servir exportaciones publicas;
- modo de despliegue pagado opcional con worker y disco persistente.

Adicionalmente, el proyecto ya cuenta con runbook de produccion, guia de Power BI y blueprint de Render, lo que reduce el riesgo tecnico de instalacion.

### 4.2 Factibilidad Economica

La factibilidad economica es **favorable** para contexto academico porque la version funcional base puede ejecutarse con costo obligatorio cero.

#### 4.2.1 Costos generales

- Desarrollo sobre software libre y servicios con nivel gratuito.
- Documentacion en Markdown dentro del repositorio.
- Costo obligatorio: **S/. 0.00**.

#### 4.2.2 Costos operativos durante el desarrollo

- Computadora personal con Python y acceso a internet.
- GitHub Actions dentro del repositorio ya configurado.
- Costo incremental obligatorio adicional: **S/. 0.00** si se usa el equipamiento del estudiante.

#### 4.2.3 Costos del ambiente

Escenarios reales:

- Local + GitHub Actions + Render Free: **S/. 0.00** obligatorios.
- Render persistente con worker y disco: costo opcional mensual segun plan vigente de Render.
- Power BI Desktop: uso academico local sin costo adicional si ya se dispone del software.

#### 4.2.4 Costos de personal

Corresponde al tiempo de analisis, desarrollo, pruebas y presentacion del equipo del curso. Al tratarse de proyecto academico, el costo se considera esfuerzo formativo y no un gasto monetizado obligatorio para viabilizar la solucion.

#### 4.2.5 Costos totales del desarrollo del sistema

Resumen realista del estado actual:

- Costo obligatorio minimo para operar localmente: **S/. 0.00**.
- Costo obligatorio minimo para CI en GitHub: **S/. 0.00**.
- Costo obligatorio minimo para demo publica en Render Free: **S/. 0.00**.
- Costo opcional para persistencia continua en Render: depende del plan de pago elegido.

### 4.3 Factibilidad Operativa

La factibilidad operativa es **alta** porque el sistema no exige administracion compleja para su uso basico. El flujo operativo real es:

1. configurar `GITHUB_TOKEN`;
2. ejecutar ETL local o en GitHub Actions;
3. generar SQLite, CSV o Parquet;
4. conectar Power BI a los archivos o al dataset publico en Render.

El proyecto cuenta con documentacion paso a paso para usuarios sin experiencia previa en DevOps, lo que favorece su adopcion academica.

### 4.4 Factibilidad Legal

La factibilidad legal es **aceptable**, considerando estas condiciones:

- el sistema consume metadatos de GitHub mediante APIs oficiales;
- no requiere scraping no autorizado;
- el token personal se configura como secreto y no se versiona;
- para publicacion abierta se dispone de exportaciones `public/` con anonimizado de autores.

Debe mantenerse el cumplimiento de:

- Terminos de uso y limites de la API de GitHub;
- buenas practicas de proteccion de datos personales;
- criterio de minima exposicion al publicar dashboards.

### 4.5 Factibilidad Social

El proyecto es socialmente beneficioso porque promueve cultura de medicion, transparencia y toma de decisiones basada en evidencia. En el contexto academico ayuda a que docentes, estudiantes y evaluadores revisen actividad, flujo y calidad de repositorios de forma objetiva.

### 4.6 Factibilidad Ambiental

El impacto ambiental es bajo y favorable frente a reportes manuales o impresos, porque la solucion centraliza resultados digitalmente, reduce impresiones y reutiliza servicios cloud existentes.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Analisis Financiero

### 5.1 Justificacion de la inversion

El proyecto no busca venta directa ni ingresos comerciales en su estado actual, por lo que no corresponde inventar indicadores como VAN o TIR sin un flujo de caja real. La evaluacion financiera adecuada para este caso es de **costo-beneficio operativo**.

#### 5.1.1 Beneficios del proyecto

**Tangibles**

- reduccion del tiempo de consolidacion manual de metricas;
- generacion automatica de dataset listo para Power BI;
- reutilizacion del mismo flujo para multiples repositorios.

**Intangibles**

- mejor presentacion academica del estado de los proyectos;
- mayor trazabilidad del trabajo realizado;
- aprendizaje practico de ETL, CI/CD, despliegue y analitica.

#### 5.1.2 Criterios de inversion

- La inversion monetaria minima es cero usando el stack actual en local, GitHub Actions y Render Free.
- El costo principal es el tiempo del equipo de desarrollo.
- Si se requiere persistencia continua en la nube, el costo pasa a ser opcional y acotado a la plataforma de despliegue.

En consecuencia, la relacion beneficio/costo para el escenario academico es favorable, porque los beneficios analiticos y de presentacion se obtienen sin requerir infraestructura pagada obligatoria.

<div style="page-break-after: always; visibility: hidden">\pagebreak</div>

## Conclusiones

- El proyecto es tecnicamente factible porque ya dispone de implementacion funcional, pruebas automatizadas, workflows y despliegue documentado.
- Es economicamente viable para contexto academico porque puede operar con costo obligatorio cero.
- Es operativamente viable porque su ejecucion puede hacerse mediante comandos simples, GitHub Actions o Render.
- Es legalmente viable siempre que se usen APIs oficiales, secretos protegidos y exportaciones anonimizadas para publicacion.
- Es una solucion adecuada para inteligencia de negocios aplicada al seguimiento de proyectos de software y su valor principal esta en la automatizacion y la trazabilidad analitica.
