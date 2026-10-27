# CODEX 05 — Consolidación y preparación del despliegue

Fecha: 26 de octubre de 2026, America/Panama. Alcance: auditoría local de ia-system-cloud e ia-system-web y preparación documental; **sin autorización ni ejecución de despliegue**.

## Resumen ejecutivo

**Dictamen: NO LISTO PARA DESPLEGAR hasta resolver los bloqueantes de esta revisión.** Las seis descargas tienen Sanctum definido en código y las quince acciones React utilizan el helper autenticado. Los reportes nuevos usan almacenamiento privado, UUID y retención de 24 horas configurable. No se encontró otra ruta Laravel de descarga PDF ni otra navegación documental directa en src. Esto acredita el árbol local; no certifica la configuración ni los archivos del Droplet/CDN.

La suite Node terminó con **42/42 pruebas aprobadas** tras una corrección menor de su referencia Git. No hay PHP/Composer/vendor ni node_modules disponibles para comprobar HTTP Laravel, compilación o lint. La posible exposición estática histórica, las cachés y el scheduler productivo no se inspeccionaron. Además, sigue presente un riesgo crítico previo: recuperación de contraseña devuelve al solicitante público el token que acepta el restablecimiento. Se recomienda tratarlo como bloqueante de seguridad mediante una corrección o contención autorizada aparte; no se modificó ese flujo.

El contrato CODEX 05 declara que el propietario técnico validó funcionalmente CODEX 02, 03 y 04. Se registra esa confirmación de fase sin atribuirle pruebas individuales no aportadas, verificación de infraestructura ni aprobación de esta publicación conjunta. Los informes anteriores conservan su estado histórico; no se reescribieron.

CODEX 05 termina con este informe y una corrección de prueba, sin cambios funcionales nuevos. El usuario decide las pruebas finales, los cambios adicionales, los commits que publicará y una eventual fase de despliegue.

## 1. Estado inicial, trazabilidad y diferencias reales

Ambos repositorios están en la rama main. HEAD inspeccionados:

| Repositorio | HEAD | Fases integradas |
|---|---|---|
| cloud | `853e628df71d7a3349045e5a67795d4e41833151` | CODEX 02 `436b81e`, CODEX 03 `22bfe7c`, CODEX 04 `853e628` |
| web | `f74bf54face30c26ca4ec1ef8782bc8e8f02eaf8` | CODEX 02 `9de35ed`, CODEX 03 `6fc4294`, CODEX 04 `f74bf54` |

Estos son commits existentes del usuario; Codex no creó commits. Estado inicial:

```text
cloud:
?? docs/CODEX_05_CONSOLIDACION_SEGURIDAD_Y_PREPARACION_DESPLIEGUE_SMI_AJAX.md

web:
(limpio)
```

Se leyeron los resultados CODEX 02/03/04 y se contrastaron con código actual. Se revisaron git diff/status, manifiestos de commits, rutas registradas, todos los consumidores documentales, controlador/servicio de reportes y filesystem/scheduler. No se encontraron AGENTS.md en los proyectos y ascendentes consultados.

### Única corrección menor aplicada

`web/tests/documentDownload.test.cjs:389`: la prueba de preservación de cálculo de reportes comparaba contra HEAD y esperaba encontrar cuatro bloques de almacenamiento público para normalizarlos. Tras integrar CODEX 04, esos bloques ya no están en HEAD: la ejecución inicial dio 41 PASS / 1 FAIL (`0 !== 4`). Se fijó esa comparación al commit anterior de CODEX 03 `22bfe7cf247031aebf80c0127d59914c51c6ff2a`, manteniendo la exigencia de cuatro sustituciones y de igualdad del resto del código. Ejecución final: 42 PASS / 0 FAIL. No se eliminaron aserciones ni se modificó la aplicación.

### Cambio previo adicional que requiere decisión de publicación

El commit cloud de CODEX 02 contiene también `config/config.php:4–5`: cambió el fallback de `appfront` de localhost a la URL del portal, conservando el uso de APPFRONT_URL. No es parte necesaria de las descargas y no figuraba en el inventario de implementación de aquel informe. Se identificó por el diff histórico, sin modificarlo ni leer .env. Debe revisarse y aceptarse o excluirse expresamente del manifiesto de publicación; no aplicar los commits completos suponiendo que solo incluyen descargas. Las modificaciones de docs de análisis/auditoría son evidencia previa, no código de ejecución.

No hay diferencias de dependencias/lockfiles ni plantillas PDF entre la base anterior a CODEX 02 y HEAD. Los controladores de solicitud, planilla y ambos cheques permanecen idénticos a esa base. El controlador de factura añade el encabezado de caché; el de reportes cambia almacenamiento/respuesta y manejo de errores sin alterar los cuatro cuerpos de cálculo/filtros/renderizado.

## 2. Inventario de las seis familias backend

Prefijo común /api. `routes/api.php` se registra con el grupo api en `app/Providers/RouteServiceProvider.php:29–35`. El grupo incluye throttle y bindings (`app/Http/Kernel.php:42–45`); auth remite al middleware propio (:56). Las seis declaraciones añaden auth:sanctum individualmente.

| Documento | Endpoint GET | Controlador / parámetro efectivo | Referencia de ruta | Nombre para 401 |
|---|---|---|---|---|
| Factura | `/api/invoices/{invoice}/download` | InvoiceController::pdf, binding Invoice | routes/api.php:179 | invoices.download |
| Planilla | `/api/payrolls/{payroll}/download` | PayrollController::pdf, binding Payroll | routes/api.php:182 | payrolls.download |
| Cheques definitivos | `/api/bank-checks/{payroll}/download` | BankCheckController::pdf, ID/binding de planilla | routes/api.php:185 | bank-checks.download |
| Solicitud | `/api/requests/{request}/download` | RequestController::pdf, binding del modelo Request | routes/api.php:188 | requests.download |
| Cheques preliminares | `/api/bank-checks-preview/{request}/download` | BankCheckPreviewController::pdf, ID/binding de solicitud | routes/api.php:191 | bank-checks-preview.download |
| Reporte | `/api/reports/{report}/download` | ReportController::pdf(string $report), UUID de generación; no ID del catálogo | routes/api.php:194 | reports.download |

`Authenticate.php:13–23` incluye los seis nombres y devuelve JSON 401 `{"message":"Unauthenticated."}` con private, no-store, sin redirect a login incluso con Accept PDF/HTML. En condiciones sin rate limit, token ausente/inválido/revocado debe terminar allí, sin PDF. El limitador común puede devolver 429 antes; las pruebas deben respetar el límite de 60/minuto por usuario/IP (`RouteServiceProvider.php:44–45`).

No se añadieron roles, ownership ni filtros de autorización por creador. Las seis familias conservan acceso a cualquier documento para cualquier usuario autenticado. POST reports/generate sigue en el grupo Sanctum (`routes/api.php:34,128–129`).

Se buscaron declaraciones de rutas, métodos pdf, streams, respuestas file/download y escrituras PDF en routes, controllers y services. RouteServiceProvider registra api.php y web.php; web.php solo presenta la página welcome. **No se identificó un endpoint PDF alternativo público en el código inspeccionado.** No hay caché local routes*.php en bootstrap/cache. Sin vendor/PHP no se ejecutó route:list: el middleware efectivo de una aplicación arrancada, y especialmente el de producción, queda pendiente de comprobación.

## 3. Matriz de las quince acciones originales

En esta tabla **B** significa Sanctum y JSON 401 definidos en el endpoint anterior; prueba HTTP real pendiente. **PASS local** significa mapeo estático del control y pruebas del helper/handler con navegador y Axios simulados, no un clic real ni una impresión. F02/F03/F04 indican confirmación global de la fase por el usuario según el contrato; la aceptación conjunta C05 y la infraestructura siguen pendientes.

Rutas de archivos frontend relativas a ia-system-web. Cada ubicación se inspeccionó individualmente; el recuento automatizado es 1+2+1+4+6+1 = **15**.

| # | Pantalla / ubicación real | Documento / endpoint | Estado backend | Estado frontend | Automática local | Manual | Observación |
|---|---|---|---|---|---|---|---|
| 1 | InvoicePage.js:264, listado | Factura, `/api/invoices/{id}/download` | B | Helper directo | PASS local | F02 confirmada; C05 pendiente | id de la fila |
| 2 | ServiceHistory.js:336, paid | Factura, `/api/invoices/{id}/download` | B | Helper directo | PASS local | F02 confirmada; C05 pendiente | Rama paid conservada |
| 3 | ServiceHistory.js:357, pending | Factura, `/api/invoices/{id}/download` | B | Helper directo | PASS local | F02 confirmada; C05 pendiente | Rama pending conservada |
| 4 | NewServicePage.js:1595, resultado | Factura, `/api/invoices/{invoiceId}/download` | B | Helper directo | PASS local | F02 confirmada; C05 pendiente | Usa invoiceId obtenido; no se probó creación |
| 5 | PayrollPage.js:392, listado created | Planilla, `/api/payrolls/{id}/download` | B | Helper directo | PASS local | F03 confirmada; C05 pendiente | id de planilla |
| 6 | PayrollPage.js:395, listado created | Cheques definitivos, `/api/bank-checks/{id}/download` | B | Helper directo | PASS local | F03 confirmada; C05 pendiente | id de planilla, no de cheque |
| 7 | PayrollPage.js:539, modal | Planilla, `/api/payrolls/{payroll.id}/download` | B | Helper directo | PASS local; alcance estático | F03 global; este modal pendiente | Inconsistencia previa de creación, ver §6 |
| 8 | PayrollPage.js:550, modal | Cheques definitivos, `/api/bank-checks/{payroll.id}/download` | B | Helper directo | PASS local; alcance estático | F03 global; este modal pendiente | Misma limitación de alcance del modal |
| 9 | RequestPage.js:484, pending | Solicitud, `/api/requests/{id}/download` | B | Helper directo | PASS local | F03 confirmada; C05 pendiente | Rama pending |
| 10 | RequestPage.js:486, pending | Cheques preliminares, `/api/bank-checks-preview/{id}/download` | B | Helper directo | PASS local | F03 confirmada; C05 pendiente | id de solicitud |
| 11 | RequestPage.js:503, completed | Solicitud, `/api/requests/{id}/download` | B | Helper directo | PASS local | F03 confirmada; C05 pendiente | Rama completed |
| 12 | RequestPage.js:506, completed | Cheques preliminares, `/api/bank-checks-preview/{id}/download` | B | Helper directo | PASS local | F03 confirmada; C05 pendiente | id de solicitud |
| 13 | RequestPage.js:662, modal | Solicitud, `/api/requests/{request.id}/download` | B | Helper directo | PASS local | F03 confirmada; C05 pendiente | request.id conservado |
| 14 | RequestPage.js:673, modal | Cheques preliminares, `/api/bank-checks-preview/{request.id}/download` | B | Helper directo | PASS local | F03 confirmada; C05 pendiente | Preview conservado |
| 15 | ReportPage.js:663–664, resultado; handler :129–132 | Reporte, `/api/reports/{UUID}/download`, recibido en file.file_path | B | Handler usa helper, LoadingButton | PASS local | F04 confirmada; C05 pendiente | Mantiene file/modal/filtros para reintentar |

La búsqueda completa de src encontró solo esas quince llamadas al helper y ninguna navegación directa restante a /download o /storage documental. No se asumió cobertura de un segundo botón por compartir endpoint.

### Consistencia real del flujo

`src/App.js:11–14` configura Axios baseURL y Bearer desde localStorage. `src/utils/documentDownload.js:32` es el único utilitario de apertura: valida origen/HTTPS y ausencia de query, reserva about:blank antes de esperar red (:52), desacopla opener, solicita Blob con Accept PDF/timeout 60 s (:61), y exige status 200 + Blob + MIME application/pdf + firma %PDF- (:70). El interceptor no coloca el token en URLs.

Solo el PDF validado llega al iframe (:79). JSON, HTML y JSON de rango inválido con HTTP 201 de PayrollController:144–147 y RequestController:362–365 se rechazan. Los toast son controlados para 401/403/404/429/5xx y red; no muestran trazas/cuerpos internos. Otros errores HTTP de las cinco familias originales pueden ser HTML o JSON del framework: la uniformidad garantizada por código es el 401, no todos los contratos backend. El helper rechaza ambos formatos.

Carga: todas las acciones muestran el texto de la pestaña reservada; ReportPage añade loading en su botón. Algunos textos internos todavía dicen invoice también en otras familias. No se corrigió ese detalle cosmético. Abrir varias pestañas conserva recursos independientes; URLs Blob se liberan en pagehide o al detectar cierre (:89–92), sin revocar al cargar el iframe. Se mantiene posibilidad de imprimir/guardar mediante visor nativo, pendiente de validación real conjunta, incluidos guardado tardío, BFCache, cerrar origen y recargar. El nombre final de guardado depende del visor.

**Diferencia de caché a comprobar:** factura (`InvoiceController.php:214`) y reporte (`ReportController.php:747`) fijan private, no-store en éxito. Planilla, solicitud y ambos cheques conservan el stream previo sin ese encabezado explícito en sus controladores. Esto no demuestra una filtración por sí solo ni elimina su Sanctum; impide afirmar uniformidad de no-store sin medir respuestas reales/proxy. Propuesta, sin aplicar: acordar un no-store homogéneo o demostrar una configuración equivalente para las seis rutas; en cualquier caso excluir documentos y respuestas autorizadas de cachés compartidas e invalidar respuestas públicas antiguas.

## 4. Reportes: privacidad, identidad, reapertura y limpieza

- Disco reports definido en `config/filesystems.php:33–39`, local, privado y con throw; raíz `storage/app/private/reports-v1`. El link estándar `public/storage` sigue apuntando solo a storage/app/public (:81).
- Los cuatro generadores llaman a storeGeneratedReport (`ReportController.php:276,393,507,704`); no queda escritura de reportes a app/public/reports ni deleteFileAfterSend. El helper del controlador (:715) publica JSON con generation_id, file_name UUID.pdf, file_path relativo a API, expires_at, status y message.
- `PrivateReportStorage.php:25–54` produce UUID v4 y archivo temporal UUID.tmp, luego rename en el mismo disco. No usa fechas/filtros/usuario como nombre. Las generaciones no comparten el nombre predecible anterior; la concurrencia real queda por probar en ensayo.
- El identificador debe cumplir patrón UUID v4 canónico en minúsculas (:12,:72). Se construye internamente UUID.pdf; la resolución verifica archivo regular, lectura, ausencia de symlink de archivo y directorio real exacto (:57–67). No hay fallback a nombres históricos, extensiones arbitrarias ni rutas del cliente. La raíz/volumen y aliases del servidor también deben comprobarse: la validación de archivo no certifica por sí sola que un administrador no haya publicado el directorio mediante alias.
- Cada GET válido lee bytes antes de responder (:70–82) para evitar la carrera de una eliminación entre preparar y enviar un archivo. MIME, firma y no-store se conservan. No se elimina ni se renueva la fecha al descargar.
- La antigüedad usa mtime fijado al crear; no hay tabla/sidecar. `config/reports.php:5` propone 24 h; el servicio admite enteros 1–8760 h y usa 24 ante valor inválido. Desde creación+retención devuelve 404 uniforme, aun sin limpieza. No se cambió esta política. Una modificación futura de configuración afecta generaciones existentes y puede desactualizar expires_at ya comunicado; restauraciones deben preservar mtime.
- `reports:prune` (`PrunePrivateReports.php:11–26`) limpia solo entradas inmediatas UUID.pdf/UUID.tmp vencidas de esa raíz. Ignora subcarpetas, nombres ajenos y archivos que no superen las comprobaciones de lectura. Fallos operativos devuelven salida no cero sin rutas internas. No limpia históricos públicos.
- `Console/Kernel.php:20` lo programa cada hora con withoutOverlapping, junto al backup existente (:19). El bloqueo depende de la caché de Laravel. No hay prueba de cron/servicio activo en el Droplet. Si no corre, la API sigue rechazando expirados, pero los archivos consumen disco. En mantenimiento el scheduler normalmente omite tareas; no asumir limpieza durante esa ventana. [Documentación oficial de scheduling Laravel 10](https://laravel.com/framework/docs/10.x/scheduling).

Durabilidad: archivos locales deben sobrevivir al cambio de release; un volumen efímero o instancias con discos distintos producirían 404. Lectura/escritura y Blob usan memoria: medir tamaño máximo y consumo de reportes grandes. Una vez entregados, no se revocan bytes del navegador ni impresiones/copias por expirar en el servidor.

## 5. Superficie estática y dependencias fuera de Git

Inspección local por existencia/metadatos, sin abrir documentos:

| Elemento | Resultado local | Pendiente del servidor |
|---|---|---|
| storage/app/public/reports | No existe | Inventario histórico y política de conservación |
| public/storage | No existe | Symlink/alias real y destinos canónicos |
| storage/app/private/reports-v1 | No existe aún; no se generaron artefactos | Volumen duradero, permisos y ausencia de publicación estática |
| public/storage → app/public | Link declarado en config, sin cambio | Evitar que ese destino histórico sirva reports |
| Nginx / reverse proxy / CDN | No hay configuración de sitio/despliegue identificada en Git | Locations, aliases, rewrites, caches y acceso directo al origen |
| Scheduler / PHP-FPM / OS | Código de tarea; sin estado operativo | Cron/timer, usuario, caché de locks, runtime y espacio |

**No se puede certificar cierre de exposición estática productiva con este checkout.** Sanctum solo controla solicitudes que llegan a Laravel; los archivos estáticos pueden atenderse directamente en Nginx. Deben revisarse coincidencia y precedencia de locations/aliases, además de cualquier cache externa. [Procesamiento de solicitudes de Nginx](https://nginx.org/en/docs/http/request_processing.html).

Estrategia propuesta para históricos, sin ejecutarla: inventario restringido de nombres/tamaños/fechas/permisos, evidencia y respaldo verificado; retirar acceso HTTP a reports en todos los aliases/orígenes y caches antes de reabrir; conservar originales fuera de publicación. Si se necesita acceso desde portal, autorizar por separado importación privada con UUID y retención histórica acordada, o regeneración en entorno autorizado. No moverlos a la raíz nueva para que expire silenciosamente su contenido ni borrar el link global storage sin evaluar otros módulos. No se leyeron, movieron ni eliminaron históricos locales o productivos.

## 6. Hallazgos y riesgos, sin corrección funcional

| Hallazgo | Evidencia / certeza | Clasificación para publicar y propuesta |
|---|---|---|
| Recuperación entrega token al solicitante público | routes/api.php:173–174; AuthController.php:87,110–124,135–143,179–185. El mismo secreto devuelto se acepta para restablecer. Confirmado en código, no explotado ni probado en producción. | **Bloqueante de seguridad recomendado, fuera del alcance documental.** Si el endpoint es accesible y el envío de correo se completa, permite eludir la posesión del correo. Autorizar una fase para retirar esa devolución y revisar recuperación/revocación, o contención verificable por separado. No declarar el portal seguro por proteger los PDFs. |
| Históricos estáticos y cachés no verificados | §5; no acceso al Droplet/CDN | **Bloqueante** para afirmar que un anónimo no obtiene documentos. Evidencia de bloqueo en todos los caminos, con fixture inocua conocida y comprobación de caches. |
| HTTP Laravel/build/lint no ejecutados | §7 | **Bloqueante de preparación técnica** hasta ejecutar en un entorno ya provisionado; las validaciones previas del usuario no sustituyen la compilación del artefacto a publicar ni la revisión operativa. |
| Permisos, persistencia, scheduler y espacio desconocidos | Disco y tareas definidos, runtime no inspeccionado | **Bloqueante operativo** hasta verificarlos y acordar retención/observación. Sin cron hay expiración lógica pero acumulación física. |
| Caché de cuatro streams sin no-store explícito | §3 | **Condición bloqueante de aceptación de caché**, no filtración demostrada. Verificar respuesta y proxy de las seis rutas; acordar cambio posterior si es necesario. No se añadió encabezado aquí. |
| `/api/last-payroll` público | routes/api.php:197; PayrollController.php:39–42 devuelve última planilla no cancelada | Adyacente: expone registro estructurado, no bytes PDF. **No bloquea por sí solo esta consolidación de seis PDFs**, pero el responsable debe aceptar/priorizar su tratamiento en otra fase. Propuesta: evaluar Sanctum, sin implementarlo. |
| Modal de planillas sigue inconsistente | PayrollPage.js:619–620 pasa setPayroll/setOpenPayroll y getPayrolls; ReviewListToolbar.js:52,61–62,96–106 espera getRequests/setRequest/setOpenRequest, publica requests y usa esos setters | Preexistente, **no bloquea protección de listados**, pero bloquea afirmar probado ese modal o aceptar corrección de su creación. Registrar excepción o aprobar tarea independiente. No se ejecutó POST/PUT ni se corrigió. |
| Validadores reciben token por GET | AuthController.php:199,248; rutas públicas validate-token-reset/access; acceso a tokenable_id antes de comprobar null en :256–263 | Riesgo fuera de alcance de URLs/logs y error con token desconocido; **no bloquea la ruta PDF por sí solo**, requiere fase de autenticación. No se usaron tokens reales ni se registraron valores. |
| Token lifetime / revocación | config/sanctum.php:49 expiration=null; reset no revoca accesos previos, logout elimina tokens | Riesgo persistente: decidir política y respuesta a credenciales comprometidas en fase separada. **Bloqueante si no puede contenerse un incidente**, no se cambió lifetime ni login. |
| NodeSource / actualizaciones SO | docker/7.4 y 8.0 usan NODE_VERSION=16; 8.1 y 8.2 usan 18, ejecutan script NodeSource (:30–31). Uso real del Droplet desconocido. | Fuera de alcance. Node 16/18 están EOL según [tabla oficial](https://nodejs.org/en/about/previous-releases). No reconstruir ni actualizar OS/Node/dependencias incidentalmente en esta publicación. **Bloqueante si el build depende de esas imágenes/scripts sin un proceso soportado ya validado**; no prueba que el servidor los use. |
| CORS y origen HTTPS | App.js base/interceptor; config/cors.php permite api/* y headers; withCredentials no usado por helper | Verificar preflight Authorization y errores desde el origen exacto; **bloqueante si falla la integración**. No se amplió CORS ni se cambió a cookies. |
| Mensajes invoice, nombre de guardado y memoria | Helper compartido; visor nativo | No bloqueantes cosméticos salvo fallo funcional demostrado. Medir memoria/tamaños, red lenta y varias pestañas; no ampliar funcionalidad sin autorización. |

La recomendación de no publicar no significa que se hayan observado ataques o filtraciones productivas. Distingue defectos de código confirmados, riesgos condicionales y evidencias aún faltantes. No se aplicaron correcciones de estos hallazgos significativos.

## 7. Validaciones locales ejecutadas y límites

| Comando / procedimiento | Resultado |
|---|---|
| git status --short --untracked-files=all; git log/show/diff en ambos repositorios | Fases integradas y único contrato C05 previo sin seguimiento; manifiesto revisado. Sin staged/commit nuevo. |
| `node --experimental-vm-modules --test --test-reporter=spec tests/documentDownload.test.cjs` en web | Inicial 41/42; corrección de baseline; final **42/42, 0 fallos, 0 omitidas**. |
| Inspección Node sin archivos de salida: seis Route::get ... /download y llamadas reales al helper por pantalla | 6/6 con Sanctum explícito; Invoice 1, ServiceHistory 2, NewService 1, Payroll 4, Request 6, Report 1 = 15. |
| rg sobre routes, controllers, services y src: download, pdf, stream, file, storage y window.open | Sin alternativa pública PDF encontrada ni enlaces documentales directos restantes. window.open centralizado en helper. |
| git diff --exit-code de dependencias/lockfiles/plantillas y cuatro controladores no modificados, entre base previa C02 y HEAD | Sin cambios. web conserva package.json, package-lock.json, yarn.lock y App.js. |
| Prueba de cuerpos de los cuatro reportes contra commit C03 | PASS: solo cuatro bloques de salida/almacenamiento difieren; cálculo, filtros y render preservados. |
| git diff --check en ambos repositorios | PASS. Git advierte LF/CRLF para el test web; sin error de whitespace. |
| Existencia de carpetas, caché local de rutas y herramientas | No hay vendor, node_modules ni directorios de reportes/symlink local. Sin routes*.php local. Node v22.23.1 disponible; PHP/Composer no están en PATH. |

Reproducción de búsquedas principales (lectura de código, no peticiones):

```text
cloud: rg -n "download|loadView|deleteFileAfterSend|app/public/reports" routes app/Http/Controllers app/Services
web:   rg -n "openAuthenticatedDocument|/download|/storage/|window.open|createObjectURL" src
ambos: git diff --check
```

Las pruebas Node ejecutan helper real con Axios/ventanas simuladas y cuerpos extraídos de handlers de ReportPage, no el árbol React renderizado. Acreditan MIME/firma, errores 401/404/429/500, JSON/HTML, 201 JSON, popup, reintento y recursos Blob. Las comprobaciones contra HEAD son control de integridad del árbol actual; la comparación histórica del cálculo usa ahora una base fija. Requieren ambos repositorios hermanos, Git y el commit C03 disponible; una exportación sin .git o clone superficial puede necesitar otra preparación de CI autorizada.

Pruebas Laravel existentes: InvoiceDownloadAuthenticationTest (4), AdministrativeDocumentDownloadAuthenticationTest (4), ReportDownloadAuthenticationTest (8): **16 métodos preparados, NO ejecutados aquí**. Usan SQLite :memory:, discos fake y datos sintéticos; cubren tokens, acceso entre usuarios, binding, UUID/colisiones, relectura/TTL, limpieza y errores; Dompdf se simula en las respuestas de éxito. El test de symlinks puede omitirse por limitaciones de la plataforma. No certifican diseño real del PDF ni aliases Nginx externos a Laravel.

No ejecutados: php artisan test, route:list, schedule:list, PHP lint, build/lint React, render Dompdf, Playwright ni ninguna prueba HTTP productiva. No se instaló nada, no se arrancaron servicios, no se ejecutaron migraciones/seeders ni se leyeron .env, credenciales o documentos reales. Una búsqueda rg inicial con comodín de archivo no expandido en PowerShell se repitió usando -g; el inventario final de pruebas se corroboró leyendo los tres archivos.

## 8. Qué se publicaría — manifiesto por aprobar

Este manifiesto describe los cambios relativos a las bases previas a C02: cloud `f218c98` y web `cb86216a`. **No acredita que esos sean los commits hoy desplegados**; el operador debe obtener y comparar el commit real antes de elegir release. No se crearon ramas/tags ni commits candidatos.

Backend, nueve archivos funcionales necesarios:

```text
routes/api.php
app/Http/Middleware/Authenticate.php
app/Http/Controllers/InvoiceController.php
app/Http/Controllers/ReportController.php
app/Services/PrivateReportStorage.php
app/Console/Commands/PrunePrivateReports.php
app/Console/Kernel.php
config/filesystems.php
config/reports.php
```

Frontend, siete fuentes funcionales para compilar y publicar como artefacto conjunto:

```text
src/utils/documentDownload.js
src/pages/InvoicePage.js
src/pages/ServiceHistory.js
src/pages/NewServicePage.js
src/pages/PayrollPage.js
src/pages/RequestPage.js
src/pages/ReportPage.js
```

Evidencia para revisión/CI: los tres tests Laravel, tests/documentDownload.test.cjs con la corrección C05 y docs de auditoría/resultados. No son archivos PDF ni deben exponerse en el webroot. `config/config.php` es una decisión adicional separada (§1). App.js, CORS, dependencias/locks, plantillas, migraciones y otros módulos son contexto conservado, no cambios de esta entrega.

Fuera de Git, por comprobar/aprobar: configuración efectiva Nginx/CDN y purge selectivo; volumen/permisos de storage privado; scheduler/usuario/caché; política de retención efectiva; artefacto React y configuración de origen; FPM/opcache; respaldos e históricos. No hay SQL ni migración de datos requerida por las fases documentales.

## 9. Secuencia propuesta de despliegue — NO ejecutada

1. **Aprobación y bloqueantes.** El usuario autoriza una fase específica posterior, fecha/ventana en America/Panama y responsables. Resolver o contener el hallazgo de recuperación por un trabajo autorizado; completar pruebas técnicas y resolver exposición estática. Acordar 24 h, excepciones conocidas del modal y selección de config/config.php. No abrir publicación mientras falten los criterios de seguridad.
2. **Congelar el par de releases.** Registrar commits completos reales de origen/destino, ramas que el usuario decida publicar y estado Git de ambos lados. Revisar el manifiesto, incluido el diff frente al servidor real. Integrar la corrección de prueba mediante el proceso de commits del usuario, sin pedir que Codex la publique automáticamente.
3. **Preparar en ensayo.** Usar PHP/dependencias y pipeline ya provisionados; ejecutar las suites/compilación con las versiones y lockfile del gestor elegido por el proyecto. Existen package-lock.json y yarn.lock: confirmar cuál usa el pipeline, sin mezclarlos ni hacer updates. Construir el frontend fuera de producción, verificar los orígenes HTTPS esperados y registrar hash del artefacto. No ejecutar composer update/npm update ni scripts NodeSource/actualizaciones del SO como parte incidental del release.
4. **Respaldos verificados.** Obtener snapshot y respaldo de datos si corresponde a la ventana, respaldo privado de storage histórico/nuevo y de configuración operativa. Verificar restauración en entorno autorizado; registrar hashes, permisos y retención de los respaldos sin publicar contenido. No suponer que un snapshot sustituye una prueba de restauración o que el código recrea PDFs borrados por TTL. No hay necesidad documental de migrar/sembrar BD.
5. **Ventana única controlada.** Aplicar el mecanismo de mantenimiento aprobado que cubra portal, API, origen y exposición estática/CDN; verificarlo antes de activar cualquiera de los dos releases. Laravel down por sí solo no bloquea archivos que Nginx/CDN sirvan directamente. Mantener esa barrera mientras se cambia backend y frontend para evitar un intervalo visible de enlaces antiguos sin Bearer contra backend nuevo. Solicitar recarga del portal al salir; pestañas con JS antiguo pueden seguir fallando 401/404 y no justifican reabrir descargas anónimas.
6. **Contener históricos y caches.** Revisar inventario de aliases/symlinks/locations y retirar únicamente la publicación HTTP de reports/área privada según configuración validada. No borrar/mover históricos ni romper el link storage global. Invalidar de manera selectiva PDFs antes públicos, las seis familias documentales, sus variantes y estáticos históricos en proxy/CDN; confirmar que ningún cache siga entregando bytes sin pasar por autenticación. Definir bypass/no-cache para documentos y Authorization, revisar también fastcgi_cache si existe. Las purgas y cambios de servidor requieren la aprobación futura, no se hicieron aquí.
7. **Activar backend completo bajo la barrera.** Publicar el release aprobado con los nueve archivos y sus dependencias existentes compatibles. Mantener .env y claves existentes por el mecanismo de releases; no sobrescribir secretos. Apuntar storage a volumen duradero, configurar propietario y permisos mínimos para PHP-FPM y CLI; no usar chmod 777. Verificar que reports-v1 y su raíz real no estén bajo ninguna publicación/alias. Mantener mtime y documentos existentes; no ejecutar limpieza hasta contar con respaldos y validación. El default 24 h no exige cambiar .env; verificar el valor efectivo sin volcar la configuración completa.
8. **Activar frontend emparejado.** Publicar atómicamente el build validado con las quince acciones, invalidar HTML/manifest estáticos según el proceso vigente y preservar assets necesarios para solicitudes en vuelo. No servir una mezcla de bundles viejos y nuevos. Verificar el origen backend y CORS con una sesión de ensayo autorizada.
9. **Actualizar únicamente caches de aplicación necesarias.** Validar y regenerar la configuración/caché de rutas correspondiente al release según el procedimiento ya probado en ensayo; verificar después route:list. config:cache/route:cache son procedimientos propuestos, no ejecutados aquí. Si la aplicación no permite route:cache, acordar route:clear y comprobar rutas de origen; nunca conservar un cache anterior que omita Sanctum. No lanzar optimize:clear/cache:clear indiscriminadamente: pueden afectar otra caché y locks del scheduler. Revisar recarga de FPM/opcache de acuerdo al mecanismo existente. [Guía oficial Laravel 10 de despliegue](https://laravel.com/framework/docs/10.x/deployment).
10. **Verificar operación y limpieza.** Revisar schedule:list, cron/timer que invoque el scheduler cada minuto, usuario/permisos y resultado de reports:prune en ensayo con artefactos sintéticos expirados. Mantener el backup existente; no usar una ejecución general de schedule:run como simple test porque también puede dispararlo. Comprobar disco y alertas de fallos/crecimiento. No afirmar scheduler activo solo porque la tarea existe en código.
11. **Pruebas de aceptación bajo control.** Completar §10 y guardar evidencia sin tokens/documentos sensibles. En producción usar únicamente fixtures inocuas previamente autorizadas; generación de reportes/casos concurrentes y operaciones de creación se validan en ensayo aislado. No crear solicitudes/planillas ni ejecutar escrituras de negocio para probar la publicación. Si no existe fixture segura, la prueba queda pendiente y no se sustituye por documentos reales.
12. **Reabrir solo con visto bueno.** El responsable confirma resultados, ausencia de bypass y par de releases correcto, y autoriza retirar mantenimiento. Vigilar 401/404/429/5xx, uso de disco, memoria y limpieza sin registrar Authorization ni cuerpos de documentos. Si un criterio falla, conservar la barrera y aplicar rollback coordinado o corregir por una fase autorizada.

## 10. Verificaciones propuestas y resultados esperados

**Todas las siguientes son propuestas, no resultados ejecutados en el Droplet.** Primero ensayo aislado con datos sintéticos. Las pruebas funcionales las realiza/confirma el usuario; la confirmación anterior de fases no se amplía automáticamente a esta matriz de despliegue.

| Prueba | Preparación segura | Resultado requerido |
|---|---|---|
| Usuario A / seis familias | Fixtures de factura, solicitud, planilla/cheques y generación de reporte | 200 application/pdf con firma PDF; visor usable, sin cambio de contenido |
| Usuario B sobre documento de A | Segunda sesión válida e independiente; mismos IDs/UUID de fixtures | Permitido sin roles/ownership; mismo documento |
| Anónimo/incógnito y enlace copiado | Sin token, cookies ni sesión; ID/UUID conocido y existente | 401 JSON sin firma/cuerpo PDF en las seis APIs; no redirect a login |
| Token inválido/revocado | Valores de prueba sin credenciales reales; respetar rate limit | 401, sin PDF. 429 si se excede el límite no acredita por sí solo el caso 401 |
| Cache después de acceso válido | A calienta la URL de fixture; repetir sin credenciales en origen y borde | Nunca devuelve la copia PDF a anónimo; verificar también variantes/headers de cache |
| Quince controles de §3 | Listados y modales alcanzables con fixtures; no escrituras financieras | Reservan pestaña, cargan y manejan error; modal de planillas documentado como excepción pendiente |
| MIME/error/rango | Simulaciones de 401/404/429/500, 201 JSON y HTML inesperado | Ningún error se presenta como PDF; se cierra pestaña de carga y queda mensaje controlado |
| Popup bloqueado / retry | Bloqueo de navegador y repetición consciente del clic | Instrucción útil, sin request cuando se bloquea; reportes conservan UUID/filtros/modal |
| Impresión y guardado | Abrir, esperar, imprimir/guardar, varias pestañas y cierre/navegación | Visor nativo funciona y no se revocan Blob prematuramente; recursos se liberan al terminar |
| Cuatro modalidades / concurrencia | Generaciones en ensayo de mismo tipo/fechas con filtros distintos, también simultáneas | UUID/nombres distintos, contenido correcto separado, sin sobrescritura |
| Reapertura y TTL | Fixture de ensayo con reloj/mtime controlado, sin cambiar política productiva | Mismo UUID reabre antes del límite, 404 al vencer aunque todavía exista físicamente; GET no renueva TTL |
| Limpieza | Disco aislado con PDF/tmp vencidos, nombres ajenos y histórico fake | Solo artefactos administrados inmediatos vencidos se retiran; históricos intactos; salida de comando controlada |
| Estáticos/aliases | Fixture no sensible conocida en réplica del antiguo destino; revisión de cada origen/CDN/alias | 403/404 sin PDF. Un 404 de nombre inexistente no demuestra cierre. No leer PDFs históricos reales para comprobarlo |
| IDs malformados de reporte | UUID inexistente, nombre .pdf, .., rutas/codificaciones de prueba | Error seguro sin salir del área privada, sin rutas internas ni fallback público |

Comandos orientativos, a adaptar por el operador autorizado; no se proporcionan secretos:

```text
# En checkouts de ensayo ya provisionados:
php artisan test --filter=DownloadAuthenticationTest
node --experimental-vm-modules --test tests/documentDownload.test.cjs
npm.cmd run build
npm.cmd run lint

# En el release elegido, solo cuando esté autorizado:
php artisan route:list --path=api -v
php artisan schedule:list
nginx -t
df -h <RAIZ_STORAGE_PRIVADO>
stat <RAIZ_STORAGE_PRIVADO>/private/reports-v1
```

Esperado: suites/build/lint aprobados; seis GET con auth:sanctum y POST generate protegido; limpieza horaria listada; configuración Nginx válida y almacenamiento con permisos/espacio adecuados. El listado de tareas no acredita ejecución: observar por separado el servicio programador y sus salidas. El administrador debe revisar la configuración Nginx efectiva sin adjuntar volcados que puedan contener secretos; no basta con nginx -t para demostrar control de acceso.

Ejemplo de comprobación anónima sobre fixture de ensayo, **no ejecutado**:

```sh
curl -sS -D headers-fixture.txt -o body-fixture.bin \
  -H 'Accept: application/pdf' \
  'https://api-ensayo.example.invalid/api/invoices/ID_FIXTURE/download'
```

Esperado: 401 JSON y ausencia de firma PDF; no seguir redirecciones para ocultar un 302. Para acceso válido usar el navegador o un fichero de configuración curl privado del operador que incluya Authorization; no escribir tokens en URL, historial de terminal, argumentos visibles, evidencias o este informe. El hostname e ID anteriores son marcadores sin servicio real. Las salidas solo deben contener fixtures, nunca documentos reales.

## 11. Rollback coordinado propuesto

No se ejecutó. Registrar primero el par de releases y respaldos válidos antes de cualquier publicación; el HEAD local actual no prueba cuál es la versión anterior del servidor.

1. Si fallan aceptación o privacidad, mantener/reponer mantenimiento y el bloqueo de estáticos/documentos en el borde/origen. No volver a abrir rutas anónimas para que funcione un frontend viejo.
2. Restaurar como par frontend y backend al release anterior aprobado y sus caches/configuración correspondientes, usando el mecanismo de releases respaldado. Si ese release tenía descargas públicas, mantener denegadas las seis APIs/documentos y la exposición estática hasta una solución autorizada; un rollback que restaure la vulnerabilidad no es recuperación segura.
3. Conservar el volumen privado y todos los históricos. No copiar UUID.pdf al directorio público para compatibilidad, no vaciar storage y no revertir la BD: no hay migración de esta entrega. Los reportes nuevos pueden quedar temporalmente inaccesibles para el código antiguo; preservar artefactos hasta decidir su relectura/migración.
4. Coordinar el scheduler con el release restaurado; al retirar la tarea puede crecer el disco, y al mantenerla puede continuar eliminando vencidos. No ampliar TTL ni detener/reanudar limpieza sin decisión operativa y evidencia. PDFs ya purgados solo se recuperan de respaldo válido o regeneración autorizada; revertir código no los restaura.
5. Revalidar anonimato, caches y operaciones de control con fixtures antes de reabrir. Mantener la contención de históricos también al revertir Nginx/CDN; no restaurar ciegamente aliases vulnerables.

Para revertir solo el trabajo local CODEX 05: respaldar y retirar únicamente el hunk del baseline en tests/documentDownload.test.cjs y este informe si se desea. No borrar el contrato C05 previo ni restaurar fases 02–04. Restaurar esa prueba anterior reproduciría su fallo tras el commit C04; conservar el informe como evidencia es preferible. No se ejecutaron reset/clean ni borrados.

## 12. Estado final, decisiones y cierre de CODEX 05

Estado final esperado y comprobado tras escribir este informe:

```text
ia-system-cloud:
?? docs/CODEX_05_CONSOLIDACION_SEGURIDAD_Y_PREPARACION_DESPLIEGUE_SMI_AJAX.md
?? docs/RESULTADO_CODEX_05_CONSOLIDACION_Y_PREPARACION_DESPLIEGUE.md

ia-system-web:
 M tests/documentDownload.test.cjs
```

El contrato C05 ya existía sin seguimiento al comenzar. El único archivo rastreado modificado en esta fase es la prueba web (tres líneas añadidas, una sustituida). No se modificó ningún archivo funcional, documento real, configuración de servidor, credencial o .env; no hubo migraciones, instalaciones, cambios del índice, commits ni push/deploy.

Decisiones del usuario antes de publicar: autorizar el tratamiento separado de recuperación; resolver evidencia de estáticos/caches/cron/permisos; completar suites/artefactos en ensayo; confirmar retención y durabilidad; aceptar o separar config/config.php y la excepción del modal; elegir commits/ramas y ventana; y autorizar una fase específica de despliegue. Este informe entrega la consolidación solicitada, no concede ni presupone esa autorización.
