# CODEX 02 — Descarga autenticada de facturas

Fecha: 24 de octubre de 2026, America/Panama. Implementación en checkout local, sin commits ni despliegue. **No está lista para despliegue:** faltan las pruebas HTTP con Sanctum y la validación manual de visualización, impresión y guardado exigidas para cerrar la fase.

## Resultado y alcance

Se protegió exclusivamente GET `/api/invoices/{invoice}/download` con `auth:sanctum`. Se conserva método, URL, model binding y generación Dompdf inline. Cualquier usuario autenticado puede solicitar cualquier factura: no se añadieron filtros por propietario, creador o rol.

Las cuatro acciones de factura se sustituyeron por llamadas a un utilitario compartido. Este reserva una pestaña durante el click, usa el Axios global configurado en App.js, solicita un Blob PDF y rechaza JSON, HTML y respuestas sin firma `%PDF-`. No coloca credenciales en URL, no incorpora tickets ni cambia a cookies/stateful. Las otras cinco descargas y reportes quedaron fuera del cambio.

## Estado previo y preservación de trabajo existente

Antes de editar se ejecutó `git status --short` en ambos repositorios:

```text
ia-system-cloud
?? docs/

ia-system-web
(sin salida)
```

Dentro de docs ya existían `ANALISIS_PROYECTO.md`, `AUDITORIA_DESCARGAS_SMI_AJAX.md`, `CODEX_01_Auditoria_Descargas_SMI_AJAX.md` y `CODEX_02_Fase1_Descarga_Segura_Facturas_SMI_AJAX.md`. Se preservaron. A diferencia del estado de la fase anterior, los documentos previos ahora estaban en docs; no se movieron durante esta implementación. No había modificaciones rastreadas al comenzar. No se encontraron instrucciones AGENTS.md en los proyectos; la revisión anterior tampoco encontró instrucciones en ascendentes. La evidencia de la auditoría se corroboró contra rutas, middleware, controlador y enlaces actuales.

## Inventario y referencias

Referencias relativas a cada repositorio. Las líneas corresponden al resultado local.

| Repositorio / archivo | Estado de esta fase | Cambio |
|---|---|---|
| cloud: `routes/api.php:179` | Modificado | Middleware `auth:sanctum` y nombre `invoices.download` para una sola ruta. No se cambiaron las otras cinco declaraciones de descarga. |
| cloud: `app/Http/Middleware/Authenticate.php:11` | Modificado | Sobrescribe la respuesta de fallo de autenticación únicamente cuando `routeIs('invoices.download')`: JSON 401 `Unauthenticated.`, sin redirigir a login. Para las demás rutas delega al método original del framework. |
| cloud: `app/Http/Controllers/InvoiceController.php:213` | Modificado | Añade `Cache-Control: private, no-store` a la respuesta existente `stream('invoice_{id}.pdf')`. No cambia datos, consulta, plantilla ni generación. |
| cloud: `tests/Feature/InvoiceDownloadAuthenticationTest.php:1` | Nuevo | Cuatro pruebas Laravel preparadas: anónimo/inválido, revocado, propietario/otro usuario y factura inexistente autenticada. |
| cloud: `docs/RESULTADO_CODEX_02_DESCARGAS_FACTURAS.md:1` | Nuevo | Este informe. |
| web: `src/utils/documentDownload.js:1` | Nuevo | Petición binaria, ventana, MIME/firma, mensajes y ciclo de object URL centralizados. |
| web: `src/pages/InvoicePage.js:264` | Modificado | Botón del listado usa utilitario. Añade ToastContainer en `:344`, consistente con las otras pantallas. |
| web: `src/pages/ServiceHistory.js:336` y `:357` | Modificado | Botones de estados paid y pending usan el mismo utilitario. |
| web: `src/pages/NewServicePage.js:1595` | Modificado | Botón del resultado de creación usa el mismo utilitario y el ID ya obtenido; no cambia el alta ni el avance de pasos. |
| web: `tests/documentDownload.test.cjs:1` | Nuevo | Pruebas aisladas Node del módulo real con navegador/Axios simulados, más comprobaciones estáticas de alcance. No requiere paquetes npm. |

## Decisiones de comportamiento

### Autenticación y caché

El middleware de autenticación de Laravel se mantiene como mecanismo efectivo; su fallo para esta ruta lanza una respuesta JSON 401 antes de usar `redirectTo`. Funciona por diseño tanto con `Accept: application/pdf` como con una navegación que acepte HTML, sin depender del nombre de ruta web login. Las pruebas Laravel preparadas deben confirmar también la precedencia de autenticación frente al binding: un anónimo no debe obtener 404 distinto según exista la factura.

Se eligió `private, no-store` en el PDF para impedir su almacenamiento persistente por caches HTTP compartidas o locales. Se incluye también en el 401 específico. No cambia `Content-Type`, `Content-Disposition` ni cuerpo PDF. El visor recibe un Blob en memoria y puede conservarlo hasta cerrar la pestaña: no-store no retira bytes entregados, ni impide que el usuario imprima/guarde. Su efecto real debe validarse en navegador y servidor; no se afirma que un proxy desplegado respete la configuración sin probarlo.

### Nueva pestaña, errores y nombre

- `openAuthenticatedDocument` se llama directamente desde onClick. Valida origen y HTTPS antes de abrir; permite solo el origen del backend configurado y rechaza query strings. Los paths usados por las cuatro acciones no contienen credenciales.
- Reserva `about:blank` antes del primer await, desacopla `opener` y muestra texto de carga. Si se bloquea, muestra un toast con instrucciones para permitir pop-ups y volver a pulsar Download; no inicia la petición ni promete apertura automática.
- Usa el mismo singleton Axios importado por App.js: conserva el interceptor que añade Bearer desde localStorage, sin volver a leer ni registrar el token en el helper. Solicita `responseType: 'blob'`, `Accept: application/pdf`, timeout de 60 segundos.
- Solo acepta status 200, Blob, MIME `application/pdf` y cabecera `%PDF-`. Esta comprobación evita abrir respuestas JSON/HTML de error; no es un validador completo de estructura PDF. Una factura cuyo render tarde más de 60 segundos se informa como fallo recuperable; medir el máximo real antes del despliegue.
- Los errores 401, 403, 404, 429 y 5xx usan mensajes controlados en inglés, como la UI existente. Se decodifican hasta 4096 bytes de JSON Blob para identificar `Unauthenticated.` incluso en un 2xx. Otros textos del servidor, HTML, URLs, trazas y objetos Axios nunca se muestran ni se registran por este utilitario, evitando exponer información sensible. Red/timeout usa un mensaje de conexión y reintento.
- Ante fallo se cierra la pestaña reservada y se muestra toast en la pantalla de origen. Si el usuario ya cerró la pestaña durante la petición, no se crea Blob URL al resolver. No se agregaron botones redundantes; se eliminaron wrappers de enlace, conservando los botones existentes.
- Se usa un File `invoice_{id}.pdf` y el mismo título en la pestaña/iframe. El nombre en Guardar depende del visor del navegador: un Blob URL no conserva necesariamente el nombre HTTP de Dompdf. No se garantiza idéntico nombre de archivo en todos los navegadores. No se añadió un segundo botón de descarga para compensarlo.
- El PDF se muestra en un iframe que contiene el visor nativo. Impresión/guardado dependen de ese visor y deben validarse manualmente, especialmente Safari/móvil y navegadores con visor deshabilitado.

### Ciclo de vida Blob

La URL se mantiene mientras la pestaña del visor siga activa. No se revoca al cargar el iframe ni con una espera arbitraria, porque eso podría romper imprimir/guardar después. Se libera en `pagehide` de la pestaña contenedora o cuando un intervalo de un segundo observa que está cerrada; la limpieza es idempotente y cancela su intervalo. Cada descarga tiene su URL/intervalo independiente. Varias pestañas abiertas consumen memoria deliberadamente mientras se usan; al cerrarlas se liberan los recursos.

El comportamiento del visor, navegación atrás/BFCache, aislamiento de ventanas del navegador y recarga/cierre completo de la página que creó el Blob requieren prueba real. Una recarga completa del origen puede invalidar URLs creadas por ese documento; no se promete un enlace persistente o compartible. Un cambio de ruta SPA no revoca anticipadamente el Blob. No se agregó un listener global que revoque al navegar dentro de React. No se soporta recuperar una URL Blob revocada: volver a pulsar el botón hace otra petición autenticada.

### HTTPS y CORS

Revisión estática: `src/config.json` usa `https://smiajax.com` y `https://cloud.smiajax.com`; App.js fija el segundo como base de Axios. Son orígenes distintos. El helper verifica HTTPS y mismo origen del backend y no usa withCredentials. `config/cors.php` ya declara `api/*`, headers y métodos permitidos; no se modificó. Leer el nombre de Content-Disposition no es necesario, porque el helper deriva `invoice_{id}.pdf` del ID.

Esto no certifica preflight ni respuesta CORS reales. Debe verificarse OPTIONS con Authorization y GET desde el origen frontend en un entorno de ensayo, incluidos errores 401/404/429/500 y headers de proxy. No se hicieron solicitudes al host productivo, ni se ampliaron orígenes/headers sin evidencia de necesidad.

## Pruebas y matriz de aceptación

Entorno disponible: Node sí; PHP no está en PATH; `vendor` y `node_modules` no existen. No se instalaron dependencias ni se modificaron lockfiles. No se ejecutaron Artisan, migraciones, seeders, peticiones productivas ni lecturas de .env/BD/PDFs reales.

Comando ejecutado en ia-system-web:

```text
node --experimental-vm-modules --test tests/documentDownload.test.cjs
tests 17
pass 17
fail 0
```

Las pruebas ejecutan el módulo ES real; Axios, pestaña, iframe y gestión de URLs se simulan. No prueban transporte HTTP, aplicación de un Bearer real ni renderizado de PDF. Dos casos son inspecciones estáticas explícitas: migración de cuatro acciones e invariancia de otras rutas/reportes/CORS/plantilla frente a HEAD. La comprobación Git de la prueba usa safe.directory limitado al comando y al checkout hermano, sin modificar configuración global; se corrigió una primera ejecución que falló por la protección de propiedad de Git. El resultado final es 17/17.

Las pruebas PHP crean únicamente tablas sintéticas en SQLite `:memory:` después de configurar explícitamente esa conexión; no usan RefreshDatabase, migraciones de aplicación, factories con datos preexistentes ni BD de negocio. Usan tokens Sanctum de fixtures generados durante la prueba. El controlador PDF se sustituye por una respuesta sintética para aislar ruta, guard y binding: incluso cuando puedan ejecutarse, su PASS no acreditará la generación Dompdf ni una factura visual real. Requieren PHP con pdo_sqlite, vendor y entorno de pruebas aislado, sin config cache productiva ni proveedores personalizados con conexiones externas durante bootstrap. Debe inspeccionarse esa precondición antes de ejecutarlas.

| Aceptación | Estado | Evidencia y límite |
|---|---|---|
| 1. GET anónimo → 401 sin PDF | **NO EJECUTADO** funcional | Middleware/ruta revisados; caso PHP `test_anonymous_and_invalid_tokens_receive_json_401_even_accepting_pdf` preparado. Falta Laravel en ejecución. |
| 2. Token inválido/revocado → 401 | **NO EJECUTADO** funcional | Dos casos PHP preparados; Node sí prueba la UX al recibir un 401 simulado. |
| 3. Usuario A → 200 PDF inline | **NO EJECUTADO** funcional | Caso PHP preparado con renderer sintético; generación existente preservada por diff. Falta factura sintética completa y render real. |
| 4. Usuario B descarga factura de A | **NO EJECUTADO** funcional | Caso PHP preparado usa tokens de ambos usuarios y misma factura; no hay filtro nuevo por propietario. |
| 5. Autenticado + factura inexistente → 404 | **NO EJECUTADO** funcional | Caso PHP preparado con binding real. No se afirma resultado HTTP observado. |
| 6. Listado, historial paid/pending y factura creada | **PASS** revisión estática; **NO EJECUTADO** navegador | Test estático cuenta 1 + 2 + 1 llamadas y ausencia de href de factura; visualización, impresión y guardar pendientes en las cuatro situaciones. |
| 7. 401/403/404/429/500, red/timeout y JSON 2xx | **PASS** aislado; **NO EJECUTADO** HTTP/UI real | Casos Node verifican cierre, mensaje controlado, ausencia de iframe inválido; incluyen HTML 2xx y MIME PDF con cuerpo JSON. |
| 8. Repetición, pop-ups y Blob | **PASS** aislado; **NO EJECUTADO** navegador | Node comprueba reserva antes del request, pop-up bloqueado sin GET, cierre durante carga, ausencia de revocación temprana y liberación por cierre/pagehide; cinco descargas consecutivas liberan cinco URLs/intervalos. |
| 9. Otros endpoints/reportes intactos | **PASS** estático | Test compara las cinco declaraciones, ReportController, config/cors y plantilla de factura con HEAD. Diff rastreado limitado a los archivos listados. |
| 10. git diff --check | **PASS** | Ejecutado sin salida y con código 0 en ambos repositorios. |
| 10. Build/lint React y pruebas Laravel | **NO EJECUTADO** | Sin node_modules/vendor/PHP. No se intentó instalar ni producir un build incompleto. No se certifica sintaxis PHP/compilación JSX por ejecución. |
| CORS real, cache y servidor desplegado | **NO EJECUTADO** | Solo revisión de configuración local; ninguna petición externa. |

## Guía de validación previa al despliegue

1. Preparar checkout/entorno de ensayo con versiones bloqueadas, sin datos reales. Proveer dependencias fuera de esta fase y verificar pdo_sqlite. No usar configuración cacheada de producción ni proveedores que consulten servicios reales al arrancar.
2. Ejecutar la clase `InvoiceDownloadAuthenticationTest` con PHPUnit en ese entorno. No ejecutar suites que usen la BD real. Acreditar 401 tanto para ID existente como inexistente con Accept PDF/HTML, token inexistente y revocado. Completar pruebas con el renderer real usando relaciones/fixtures sintéticos completos; el renderer simulado no sustituye esa prueba.
3. Ejecutar build/lint existentes en frontend. Aislar advertencias/fallos previos de las introducidas; no hacer refactors generales para limpiar reglas antiguas. Las pruebas Node se pueden repetir sin instalar paquetes con el comando anterior.
4. Levantar frontend y backend de ensayo con HTTPS/orígenes equivalentes y credenciales sintéticas. Verificar Authorization en headers mediante herramientas locales sin copiar sus valores a informes, y que URL/query no contengan tokens. Confirmar el preflight y manejo CORS de éxito/error.
5. Probar los cuatro botones, abrir varias pestañas, dejarlas abiertas e imprimir/guardar después de esperar. Comprobar nombre propuesto, contenido completo y formato. Cerrar/navegar las pestañas y observar liberación de recursos. Probar bloqueador, desconexión, timeout, visor deshabilitado, Safari/móvil y recarga del origen/visor.
6. Verificar respuestas con usuario A/B, anónimo, token inválido/revocado y factura inexistente; documento solo para autenticados. Comprobar 200 application/pdf, inline y private/no-store, sin bytes PDF en rechazo. Usar fixtures, no PDFs reales.
7. Registrar evidencias sin tokens/datos personales y actualizar esta matriz. Solo después de cumplir HTTP y validación manual corresponde considerar la fase lista para autorización de despliegue.

## Despliegue y rollback — solo documentación

No se ejecutó ninguna acción de esta sección. La autorización de producción es posterior y separada.

**Despliegue futuro:** empaquetar los cambios backend y el build frontend validado como una misma versión; conservar versión anterior e inventario. No hay migraciones ni cambio de dependencias. Coordinar ambos componentes para evitar que frontend viejo intente navegar sin Bearer hacia una ruta ya protegida. Una actualización de pestañas abiertas puede ser necesaria. Publicar con el procedimiento habitual del operador, actualizar los caches de rutas/configuración que ese procedimiento mantenga y verificar solo con fixtures autorizados. No se incluye una orden concreta para DigitalOcean porque no se inspeccionó su configuración.

**Rollback preferido:** mantener el endpoint protegido y corregir/revertir la presentación del frontend a una versión compatible con descarga autenticada. Si el problema exige retirar la UI nueva antes de corregirla, deshabilitar temporalmente esa acción con comunicación al usuario preserva la barrera de autenticación. Restaurar únicamente el frontend anterior produciría enlaces rotos contra la API protegida.

**Rollback completo a la versión previa:** restaurar ambos artefactos/caches coherentes mediante el procedimiento del operador únicamente con autorización expresa y evaluación del impacto: la versión anterior vuelve a dejar pública la descarga de facturas y no cumple la regla de negocio. No se recomienda reabrir esa exposición como fallback automático. No se propone reset, borrado ni cambio de datos; no hay esquema que revertir.

## Riesgos y pendientes

- No hay evidencia funcional de Sanctum, renderer, navegador ni CORS real en este equipo; el criterio de cierre no se cumple todavía.
- Nombre del archivo, impresión/guardar en iframe, visor PDF deshabilitado, aislamiento de ventanas y Safari/móvil son límites prácticos que los mocks no validan.
- Blob mantiene el documento completo en memoria; medir facturas grandes y respuesta del servidor. Timeout de 60 segundos es una decisión inicial, no un rendimiento demostrado.
- Cierre/navegación/BFCache del visor y recarga completa del origen pueden cambiar la disponibilidad de object URLs; no se ofrece URL persistente ni revocación de copias ya guardadas.
- Los otros cinco endpoints siguen con su estado anterior, público según el código auditado; no se corrigieron en esta fase por la restricción de alcance.
- El frontend existente conserva comportamientos ajenos a esta fase, como validación de sesión por query y logs de datos en otras partes. No se tocó login ni se añadieron trazas de credenciales.

## Estado final y estadísticas

`git diff --stat` solo incluye archivos rastreados; los nuevos se enumeran en git status, sin usar git add.

```text
ia-system-cloud — git diff --stat
 app/Http/Controllers/InvoiceController.php |  3 ++-
 app/Http/Middleware/Authenticate.php       | 12 ++++++++++++
 routes/api.php                             |  4 +++-
 3 files changed, 17 insertions(+), 2 deletions(-)

ia-system-web — git diff --stat
 src/pages/InvoicePage.js    | 12 ++++--------
 src/pages/NewServicePage.js | 11 ++---------
 src/pages/ServiceHistory.js | 19 +++----------------
 3 files changed, 9 insertions(+), 33 deletions(-)
```

```text
ia-system-cloud — git status --short
 M app/Http/Controllers/InvoiceController.php
 M app/Http/Middleware/Authenticate.php
 M routes/api.php
?? docs/
?? tests/Feature/InvoiceDownloadAuthenticationTest.php

ia-system-web — git status --short
 M src/pages/InvoicePage.js
 M src/pages/NewServicePage.js
 M src/pages/ServiceHistory.js
?? src/utils/documentDownload.js
?? tests/
```

`--untracked-files=all` identifica dentro de docs los cuatro documentos previos y este informe; en tests del frontend identifica `tests/documentDownload.test.cjs`. Ningún archivo previo fue revertido, movido ni agregado al índice. No se ejecutaron commits, despliegues ni cambios en servicios/datos.
