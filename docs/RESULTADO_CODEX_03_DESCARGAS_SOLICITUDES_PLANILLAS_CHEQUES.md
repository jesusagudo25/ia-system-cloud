# CODEX 03 — Descargas de solicitudes, planillas y cheques

Fecha: 26 de octubre de 2026, America/Panama.

Estado: implementación local terminada; **validación funcional del usuario pendiente, fase sin cerrar**. No se hicieron commits, push, deploy ni operaciones sobre producción. Las pruebas aisladas no certifican por sí solas la autenticación HTTP real ni el visor del navegador.

## Alcance y estado inicial

Se ejecutó `git status --short` en ambos repositorios antes de editar: ambos estaban limpios. Bases consultadas:

- cloud: `436b81e` — Implement authenticated download for invoices via API.
- web: `9de35ed` — Implement document download functionality with error handling and refactor invoice download links.

CODEX 02 ya estaba integrado en HEAD. Se revisaron sus rutas, middleware, utilitario, consumidores, pruebas e informe. No se encontraron AGENTS.md en ambos repositorios ni en los ascendentes consultados. No se sobrescribieron cambios del usuario.

Plan ejecutado: proteger cuatro declaraciones GET en `routes/api.php`, ampliar la selección de rutas del 401 existente en `Authenticate.php`, migrar cuatro acciones en `PayrollPage.js` y seis en `RequestPage.js`, agregar pruebas Laravel aisladas, actualizar/ampliar las pruebas Node y escribir este informe. No se modificó `documentDownload.js`.

## Matriz de rutas y seguridad

Todas las rutas siguientes mantienen GET, URL, parámetros, controlador, método `pdf` y route-model binding. Cada una tiene `->middleware('auth:sanctum')` y un nombre explícito para reutilizar el 401 de CODEX 02.

| Ruta relativa a /api | Controlador / binding | Nombre | Referencia |
|---|---|---|---|
| `/payrolls/{payroll}/download` | `PayrollController::pdf(Payroll $payroll)` | `payrolls.download` | `routes/api.php:182` |
| `/bank-checks/{payroll}/download` | `BankCheckController::pdf(Payroll $payroll)` | `bank-checks.download` | `routes/api.php:185` |
| `/requests/{request}/download` | `RequestController::pdf(Request $request)` | `requests.download` | `routes/api.php:188` |
| `/bank-checks-preview/{request}/download` | `BankCheckPreviewController::pdf(Request $request)` | `bank-checks-preview.download` | `routes/api.php:191` |

`app/Http/Middleware/Authenticate.php:13` conserva `invoices.download` y añade esos cuatro nombres al `routeIs`. Ante fallo de autenticación se reutiliza exactamente la respuesta JSON `{"message":"Unauthenticated."}`, HTTP 401 y `Cache-Control: private, no-store`, incluso con Accept PDF o HTML, sin redirección a login. Las otras rutas conservan el comportamiento anterior.

No se añadieron roles, políticas, filtros por creador/propietario, tokens temporales, cookies ni cambios de Sanctum. El ID de cheques definitivos sigue siendo el de planilla; el de preliminares sigue siendo el de solicitud. El acceso entre usuarios autenticados se conserva por diseño y tiene pruebas preparadas, pendientes de ejecución HTTP.

## Matriz de las diez acciones React

Las referencias son relativas a ia-system-web y corresponden al resultado local. Se retiró únicamente el enlace de descarga que envolvía cada botón y se llamó directamente al utilitario desde `onClick`, preservando el gesto necesario para abrir la pestaña. Se mantienen textos, iconos, colores, condiciones por estado y controles de botón accesibles por teclado. Los botones Checks conservan el azul anterior mediante `sx`.

| Pantalla / contexto | Documento | ID enviado | Referencia |
|---|---|---|---|
| Payroll / listado `created` | Planilla | `id` | `src/pages/PayrollPage.js:392` |
| Payroll / listado `created` | Cheques definitivos | `id` de planilla | `src/pages/PayrollPage.js:395` |
| Payroll / modal de resultado | Planilla | `payroll.id` | `src/pages/PayrollPage.js:539` |
| Payroll / modal de resultado | Cheques definitivos | `payroll.id` | `src/pages/PayrollPage.js:550` |
| Request / listado `pending` | Solicitud | `id` | `src/pages/RequestPage.js:484` |
| Request / listado `pending` | Cheques preliminares | `id` de solicitud | `src/pages/RequestPage.js:486` |
| Request / listado `completed` | Solicitud | `id` | `src/pages/RequestPage.js:503` |
| Request / listado `completed` | Cheques preliminares | `id` de solicitud | `src/pages/RequestPage.js:506` |
| Request / modal de resultado | Solicitud | `request.id` | `src/pages/RequestPage.js:662` |
| Request / modal de resultado | Cheques preliminares | `request.id` | `src/pages/RequestPage.js:673` |

Los imports nuevos están en PayrollPage:52 y RequestPage:55. Se usan nombres de archivo `payroll_{id}.pdf`, `bank_checks_{id}.pdf`, `request_{id}.pdf` y `bank_checks_preview_{id}.pdf`.

## Reutilización del flujo y errores

El utilitario compartido de CODEX 02 y el interceptor Bearer de `App.js` permanecen idénticos a HEAD. No hay duplicación del código Axios/Blob. El flujo existente reserva una pestaña antes de esperar la petición, desacopla opener, solicita Blob con Accept PDF y solo acepta HTTP 200, MIME application/pdf y firma `%PDF-`. Usa HTTPS y el origen configurado del backend; no coloca credenciales en URLs.

Se conservan el visor nativo dentro del iframe, las posibilidades de impresión/guardado, los toast de error, el texto de carga y la instrucción de reintento si se bloquean ventanas emergentes. Los errores 401/404/429/500, red y timeout cierran la pestaña reservada y no muestran el cuerpo recibido como PDF. Los JSON HTTP 201 con `success=false` de PayrollController y RequestController ya son rechazados por el helper: **no se cambió su contrato ni lógica financiera**.

La URL Blob se conserva mientras el visor siga abierto y se libera en pagehide o al detectar el cierre, sin revocación prematura que impida imprimir/guardar. Hay cobertura aislada para limpieza repetida, cierre durante la petición y bloqueo de ventanas.

Limitación heredada explícita: algunos textos internos del helper dicen “invoice” (carga, 404, error de servidor y conexión) también al abrir los nuevos documentos. Se mantuvieron para respetar la exclusión de cambios al utilitario compartido salvo corrección imprescindible; esto no afecta la clasificación de errores, los nombres enviados ni los bytes. El nombre final al guardar depende del visor del navegador. La prueba visual de carga, errores, impresión y guardado sigue pendiente.

## Hallazgo del modal de planillas

`PayrollPage.js:619` y :620 pasan `setPayroll` y `setOpenPayroll` al `ReviewListToolbar` importado de `sections/@payroll/table`. Ese componente espera `setRequest` y `setOpenRequest` (:61–62), publica solicitudes y usa esos setters (:102–103). El flujo inspeccionado no establece el estado `payroll` del modal como esperan sus botones. Se migraron igualmente ambas descargas del modal; no se corrigió ni ejecutó ese flujo de creación, conforme al contrato. RequestPage utiliza los setters esperados. No se hicieron POST/PUT para probarlo.

## Pruebas y evidencias

| Comprobación | Resultado y límite |
|---|---|
| `git status --short` inicial, ambos repositorios | Limpios; CODEX 02 ya integrado. |
| `node --experimental-vm-modules --test tests/documentDownload.test.cjs` en web | **28 pruebas, 28 aprobadas, 0 fallos**. Módulo real con Axios/ventanas simulados y fixtures sintéticos, sin paquetes nuevos. |
| Regresión de facturas | Conservadas pruebas de utilitario, cuatro acciones y configuración estática del interceptor. Consumidores, helper, controlador y Blade de factura coinciden con HEAD. No equivale a validación funcional real. |
| Cuatro rutas / diez acciones | Comprobaciones estáticas de middleware, nombres, parámetros, controlador, ID/contexto y ausencia de enlaces directos. |
| HTTP 201 JSON | Cuatro casos aislados rechazan success=false sin crear visor PDF. Dos controladores reales conservan el 201, comprobados en código. |
| Exclusiones | Pruebas comparan HEAD con ReportPage, ReportController, ruta de reportes, controladores PDF de esta fase, vistas fortnightly/bank-checks, CORS y archivos de factura. Sin cambios. |
| `git diff --check`, ambos repositorios | Aprobado, sin errores de whitespace. Git puede advertir normalización LF/CRLF en pruebas; no constituye error del check. |
| PHP / PHPUnit / Artisan / listado efectivo de middleware | No ejecutados: PHP no está en PATH y cloud no tiene vendor. No se instalaron dependencias. |
| Build / ESLint React | No ejecutados: web no tiene node_modules. No se instalaron herramientas ni dependencias. |
| Entorno aislado / Playwright MCP | No ejecutado: no hay entorno de aplicación aislado listo. `docker ps --format '{{.Names}}'` no encontró contenedores activos; primera consulta restringida por permisos, repetida con autorización elevada. No se iniciaron contenedores. |
| Producción, documentos y datos reales | No se consultaron ni modificaron; no se leyeron .env ni se enviaron peticiones al backend productivo. |

La primera ejecución Node después de proteger rutas dio 16 aprobadas y un fallo: la antigua prueba de alcance de CODEX 02 exigía que las otras cinco rutas permanecieran idénticas. Se actualizó esa expectativa para mantener la exclusión de reportes y verificar positivamente las cuatro rutas de esta fase; no se eliminó la regresión de facturas. La ejecución final completa dio 28/28.

Se añadió `tests/Feature/AdministrativeDocumentDownloadAuthenticationTest.php:1` con cuatro métodos de prueba que recorren las cuatro rutas:

- :89 — anónimo, token inválido y revocado; Accept PDF/HTML; IDs existentes/inexistentes; JSON 401 sin bytes PDF.
- :114 — propietario y otro usuario con tokens Sanctum reales sobre documentos sintéticos; route binding y respuesta PDF simulada.
- :133 — usuario autenticado y documento inexistente: 404.
- :144 — controladores reales de solicitud/planilla con rango de fecha inválido: 201 JSON success=false, sin renderer.

Estas pruebas solo crean tablas sintéticas en SQLite `:memory:`, sin migraciones de aplicación, seeders ni acceso a BD real. Los renderizadores PDF se simulan salvo en la rama de error de fecha. **Están preparadas, no ejecutadas**. En un entorno local aislado ya provisionado con PHP, vendor y SQLite, ejecutar:

```text
php artisan test --filter=DownloadAuthenticationTest
```

El filtro incluye las pruebas de facturas de CODEX 02 y las nuevas. En web, con dependencias ya disponibles, ejecutar los scripts existentes `npm.cmd run build` y `npm.cmd run lint`; no se certifican compilación ni lint con estas pruebas Node.

## Validación funcional pendiente del usuario

En un entorno de ensayo con fixtures sintéticos, confirmar las cuatro rutas con tokens válidos, inválidos y revocados, incluido acceso por usuario distinto del creador. Confirmar los diez controles y sus ID, los estados pending/completed/created y los modales cuando sean alcanzables, sin crear operaciones de negocio en producción. Probar el error de rango y 401/404/429/500 con fixtures o simulaciones del entorno aislado.

Verificar apertura de pestaña, carga, visor, impresión, guardado tras una espera, cierre/navegación, reintento de pop-ups bloqueados y varias descargas. Comprobar CORS/Bearer en navegador y regresión funcional de las cuatro acciones de factura. El helper mantiene su restricción HTTPS; un ensayo HTTP sin TLS no demuestra el flujo completo. Los PDF y sus encabezados de caché exitosos permanecen como estaban, porque no se tocaron controladores; no se afirma una nueva política de caché ni se certifica el comportamiento de proxies.

Reportes quedan reservados para CODEX 04. Ninguna prueba pendiente autoriza modificar autenticación, datos o reportes ni cerrar la fase sin confirmación del usuario.

## Estado Git final e integridad

```text
ia-system-cloud — git status --short
 M app/Http/Middleware/Authenticate.php
 M routes/api.php
?? docs/RESULTADO_CODEX_03_DESCARGAS_SOLICITUDES_PLANILLAS_CHEQUES.md
?? tests/Feature/AdministrativeDocumentDownloadAuthenticationTest.php

ia-system-web — git status --short
 M src/pages/PayrollPage.js
 M src/pages/RequestPage.js
 M tests/documentDownload.test.cjs
```

Archivos de implementación modificados: dos backend y dos frontend. Evidencia adicional: prueba Laravel nueva, pruebas Node ampliadas (`tests/documentDownload.test.cjs:175–274`) y este informe. No se alteraron dependencias, lockfiles, migraciones, login/recovery, tokens, almacenamiento, URLs /storage, vistas, cálculos, estados, filtros ni configuración de despliegue. Sin cambios al índice, commits, push o deploy.

## Reversión exclusiva de CODEX 03

No se ejecutó rollback. Como ambos repositorios estaban limpios y CODEX 02 está en las bases citadas, para revertir únicamente esta fase:

1. Revisar/respaldar el diff y cualquier trabajo posterior. Si hay nuevas modificaciones en estos archivos, revertir solo los hunks CODEX 03, no restaurar archivos completos.
2. Sin trabajo posterior, restaurar desde la base cloud `436b81e` únicamente `routes/api.php` y `app/Http/Middleware/Authenticate.php`. Esto conserva la ruta y 401 de facturas de CODEX 02.
3. Sin trabajo posterior, restaurar desde la base web `9de35ed` únicamente `src/pages/PayrollPage.js`, `src/pages/RequestPage.js` y `tests/documentDownload.test.cjs`.
4. Tras respaldarlos, retirar únicamente los dos archivos nuevos de esta fase (prueba Laravel e informe) si también se quiere retirar su evidencia. Preservar los documentos y pruebas CODEX 02.
5. Repetir `git diff --check`, revisar `git status --short` y ejecutar las pruebas de CODEX 02 en el entorno disponible.

No usar reset/clean global ni borrar docs/tests en conjunto. No hay cambios de datos o infraestructura que revertir.
