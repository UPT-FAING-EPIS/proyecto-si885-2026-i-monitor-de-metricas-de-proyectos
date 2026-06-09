# [OPEN] Debug Session: trello-buttons-dead

## Resumen
- Sintoma: en `/trello` los botones `Autorizar con Trello`, `Abrir Trello`, `Usar token manual` y `Conectar manualmente` no responden.
- Evidencia del usuario: al usar el flujo manual la URL llegaba a `/trello?csrf=...&token=` y actualmente la interfaz sigue sin reaccionar.
- Objetivo: identificar por que el frontend de Trello no enlaza eventos o no ejecuta sus handlers.

## Hipotesis Iniciales
1. El modulo `assets/js/trello.js` no esta cargando en produccion y por eso ningun `addEventListener` se ejecuta.
2. El script carga, pero falla durante `init()` antes de enlazar eventos por una excepcion de runtime.
3. La pagina renderiza botones con estado deshabilitado o cubiertos por otra capa visual y el click no llega al handler.
4. El flujo de autorizacion genera una URL vacia o invalida y el handler termina sin feedback visible.
5. El formulario manual sigue ejecutando submit nativo porque el listener nunca se registra.

## Plan
1. Instrumentar la pagina y el JS de Trello para registrar carga, init y clicks.
2. Ejecutar localmente y reproducir con el usuario de prueba.
3. Analizar la evidencia y confirmar la hipotesis real.
4. Aplicar un fix minimo.
5. Verificar post-fix y luego limpiar.

## Evidencia acumulada
- `public/` no contiene `assets/`; solo expone `index.php` y `.htaccess`.
- La vista `app/Views/pages/trello.php` sigue cargando `"/assets/css/app.css"` y `"/assets/js/trello.js"`.
- Con el servidor PHP embebido en `public`, la hipotesis principal sigue siendo que el navegador recibe `404` para `"/assets/js/trello.js"` y por eso ningun handler de click llega a registrarse.

## Reproduccion local
- `GET /trello` => `200`
- `GET /assets/js/trello.js` => `404` antes del fix, `200` despues del fix
- `GET /assets/css/app.css` => `404` antes del fix, `200` despues del fix
- `POST /login` con `prueba@gmail.com / 12345678` => `302 /dashboard`

## Fix aplicado
- Se agrego en `public/index.php` un despachador minimo para servir `"/assets/*"` desde la carpeta real `assets/` del proyecto.
- El fix incluye validacion para evitar path traversal y asigna `Content-Type` segun extension.

## Estado de hipotesis
1. `trello.js` no carga por publicacion incorrecta de assets: confirmada.
2. `init()` falla antes de enlazar eventos: sin evidencia nueva; ya no es la causa primaria.
3. Hay una capa visual bloqueando clicks: descartada como causa primaria.
4. La URL de autorizacion esta vacia: posible escenario secundario, pero no explica los botones muertos.
5. El submit nativo del formulario domina el flujo: era un efecto de que el JS no se cargaba.
