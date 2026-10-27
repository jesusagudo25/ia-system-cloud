# CODEX 04 — Reportes PDF privados

Fecha: 26 de octubre de 2026, America/Panama.

**IMPLEMENTADA LOCALMENTE — PENDIENTE DE VALIDACIÓN DEL USUARIO**

No se desplegó, no se hicieron commits/tags/push, no se instalaron dependencias y no se consultaron servidores, bases de datos ni documentos reales. La fase no está cerrada funcionalmente. Las pruebas Laravel preparadas y la validación del navegador siguen pendientes.

## Diagnóstico y preservación del workspace

Se consultaron el análisis del proyecto, la auditoría de descargas y los resultados CODEX 02/03, contrastándolos con rutas, middleware, cuatro generadores, configuración de filesystem/scheduler, utilitario Axios y su consumidor React actual.

Al comenzar, CODEX 03 ya estaba integrado en los commits base cloud `22bfe7c` y web `6fc4294`. Los informes anteriores conservan su estado histórico pendiente; el contrato recibido para esta fase comunica que el responsable validó CODEX 02/03. No se modificaron esos informes.

`git status --short` inicial:

```text
cloud:
?? docs/CODEX_03_DESCARGAS_SEGURAS_SOLICITUDES_PLANILLAS_CHEQUES_SMI_AJAX.md
?? docs/CODEX_04_REPORTES_PRIVADOS_SMI_AJAX.md

web:
(limpio)
```

Ambos documentos previos se preservaron. No se encontraron instrucciones AGENTS.md en los proyectos/ascendentes consultados.

Se confirmó que POST reports/generate ya tenía Sanctum y GET reports/{report}/download era público. Las cuatro modalidades usaban nombres derivados de fechas y guardaban en `storage/app/public/reports`; el GET resolvía ese nombre y eliminaba el archivo tras enviarlo. React abría `file_path` como enlace sin Bearer y borraba el resultado al pulsarlo. No había limpieza de reportes; el scheduler solo declaraba `database:backup` cada hora.

## Diseño e inventario de cambios

Referencias relativas a cada repositorio, según el resultado local.

| Archivo | Motivo / referencias |
|---|---|
| cloud `routes/api.php:194` | Solo GET de reportes añade auth:sanctum y nombre reports.download. Las cinco descargas anteriores quedan idénticas. |
| cloud `app/Http/Middleware/Authenticate.php:19` | Añade reports.download al contrato JSON 401 existente; conserva las otras cinco selecciones. |
| cloud `config/filesystems.php:33` | Disco local reports, privado, raíz storage/app/private/reports-v1; errores de escritura lanzan excepción. No modifica disco público ni sus links. |
| cloud `config/reports.php:1` | Nuevo: política de retención aislada, REPORT_RETENTION_HOURS, valor predeterminado 24. |
| cloud `app/Services/PrivateReportStorage.php:1` | Nuevo: UUID por generación, publicación del archivo completo, validación de identificadores/rutas, lectura, retención y limpieza confinada. |
| cloud `app/Http/Controllers/ReportController.php:118` | Mismos cuatro casos de generación, con fallo de generación controlado. Sustituye solo almacenamiento/respuesta de cada caso (:276, :393, :507, :704) por helper :715; descarga privada :730. |
| cloud `app/Console/Commands/PrunePrivateReports.php:1` | Nuevo comando reports:prune, informa número de artefactos retirados o error controlado con salida distinta de cero. |
| cloud `app/Console/Kernel.php:20` | Programa reports:prune cada hora con withoutOverlapping; backup previo intacto. |
| cloud `tests/Feature/ReportDownloadAuthenticationTest.php:1` | Nuevo: ocho pruebas con SQLite en memoria, discos fake y datos sintéticos. |
| cloud `docs/RESULTADO_CODEX_04_REPORTES_PRIVADOS_SMI_AJAX.md:1` | Este informe. |
| web `src/pages/ReportPage.js:58` | Importa helper existente; handler :129, generación :176 y botón :663. Mantiene modal/archivo para reintentar y conserva filtros. |
| web `tests/documentDownload.test.cjs:175` | Ajusta antiguas exclusiones de reportes al nuevo alcance; conserva regresión anterior y agrega pruebas de handlers/visor/contrato y preservación de cálculo. |

No cambian SQL, cálculos, filtros enviados, modelos, migraciones, plantillas, tamaño/orientación PDF ni las otras pantallas documentales. La comparación automatizada confirma que los cuerpos de las cuatro funciones de reporte son idénticos a HEAD salvo sus cuatro bloques de almacenamiento/respuesta. El utilitario documentDownload.js permanece idéntico, al igual que App.js y las cinco rutas anteriores.

## Contratos HTTP antes/después

POST `/api/reports/generate` conserva Sanctum, body `report_id`, `user_id`, `filters` y las cuatro modalidades. `report_id` sigue siendo el ID del catálogo; no es el nuevo identificador de generación.

| Contrato | Antes | Después |
|---|---|---|
| Generación exitosa | HTTP 200 JSON con file_name predecible y file_path absoluto al GET por nombre | HTTP 200 JSON conserva file_name/file_path/status/message; añade generation_id y expires_at. file_name es UUID.pdf; file_path es ruta API relativa por UUID. Cache-Control private, no-store. |
| GET `/api/reports/{report}/download` | Público, parámetro nombre de archivo, lectura pública y deleteFileAfterSend | Sanctum, parámetro UUID v4, área privada exclusivamente; sin eliminación al descargar. |
| PDF exitoso | Archivo público enviado directamente | HTTP 200, application/pdf, Content-Disposition inline con nombre report_UUID.pdf, Cache-Control private, no-store, X-Content-Type-Options nosniff. |
| Anónimo / token inválido o revocado | Podía alcanzar la lectura de archivo | JSON 401 Unauthenticated., no-store, sin redirección a login incluso con Accept PDF/HTML. |
| ID inválido, ausente, expirado, corrupto o no legible | Fallo de archivo dependiente del framework | JSON 404 controlado en el endpoint coincidente; sin bytes PDF ni rutas internas. URLs que no coincidan con una ruta pueden recibir el 404 normal del router. |
| Infraestructura de lectura inaccesible | Error sin contrato específico | JSON 503 controlado; reintento permitido. |
| Error de salida/almacenamiento/generador | Error no controlado en esos bloques | JSON 500 controlado, sin mensaje interno de excepción. Reglas de rango/sin datos mantienen sus respuestas anteriores. |

Ejemplo sintético de respuesta nueva:

```json
{
  "generation_id": "00000000-0000-4000-8000-000000000000",
  "file_name": "00000000-0000-4000-8000-000000000000.pdf",
  "file_path": "/api/reports/00000000-0000-4000-8000-000000000000/download",
  "expires_at": "2026-10-27T12:00:00-04:00",
  "status": "success",
  "message": "Report generated successfully"
}
```

El desfase de expires_at lo determina la zona horaria configurada de Laravel; la retención usa segundos Unix transcurridos. El ejemplo no es un documento generado ni una URL pública válida. Conocer el UUID no concede acceso anónimo. Cualquier usuario con Sanctum válido puede descargar cualquier generación: no hay comparación con user_id, propietario ni roles. No se introducen cookies, tickets, URLs firmadas ni tokens en URL/nombres/logs.

Se elige 404 uniforme, en lugar de distinguir 410: una generación eliminada no conserva registro de existencia y no hace falta una tabla/tombstone. El mensaje permite regenerar y no revela si existió el documento.

## Área privada, durabilidad y concurrencia

Raíz nueva: `storage/app/private/reports-v1/`, disco reports. No está bajo public ni bajo storage/app/public. El link estándar public/storage solo apunta a storage/app/public y no cubre esa raíz. No se crean links ni se modifican aliases Nginx.

Cada generación obtiene UUID v4 aleatorio, independiente de fechas, filtros, usuario y catálogo. Primero se escribe UUID.tmp con visibilidad privada y después se renombra a UUID.pdf en el mismo disco local. El UUID se devuelve solo tras terminar esa publicación. Así, generaciones paralelas no comparten nombres de fechas y las descargas no ven salidas parciales anunciadas por la API. Los temporales se retiran al fallar; los que deje una caída del proceso entran en la limpieza por antigüedad.

Solo se admiten UUID v4 canónicos en minúsculas, sin extensión ni separadores. Se rechazan nombres arbitrarios, extensión .pdf incluida en el ID, rutas absolutas, .., barras y variantes codificadas. Tras validar el ID, el servicio construye internamente UUID.pdf; comprueba archivo regular/legible, rechaza symlinks de archivo y verifica con realpath que su directorio coincide exactamente con la raíz del disco. No hay concatenación de rutas HTTP arbitrarias.

No hay tablas ni sidecars: el archivo y su mtime inicial constituyen el artefacto y su fecha de creación técnica. Se fija esa marca antes del rename; los GET no la modifican. La conservación dura mientras se conserve el filesystem local. Respaldos/restauraciones deben preservar timestamps; una manipulación externa de mtime altera el vencimiento. No se está diseñando almacenamiento distribuido: varias instancias necesitarían acceso al mismo volumen privado o una estrategia posterior explícita.

La respuesta toma una copia de los bytes antes de enviarlos, evitando la carrera de borrar un archivo entre preparar un BinaryFileResponse y enviarlo. Lectura y escritura comprueban firma %PDF-; el frontend además comprueba status y MIME. Esto no es un validador completo de estructura PDF. Los bytes se mantienen en memoria durante el envío: se debe medir memoria/tamaño con reportes grandes en ensayo.

## Retención y limpieza

Política técnica provisional: 24 horas desde la escritura inicial, configurable con `REPORT_RETENTION_HOURS` en config/reports.php. Se aceptan enteros de 1 a 8760 horas; valores inválidos usan 24. No se modificó .env. Un cambio de política se aplica a los archivos existentes desde su mtime original; expires_at comunicado antes del cambio es informativo y puede quedar desactualizado.

GET permite repetir la descarga antes del límite y rechaza desde el instante `creación + retención`, aunque el scheduler no haya borrado físicamente el archivo. No borra ni renueva su vida al acceder. Un PDF ya entregado al navegador no se revoca al expirar en el servidor.

`reports:prune` recorre solo entradas inmediatas de la nueva raíz y solo nombres UUID.pdf/UUID.tmp vencidos. No recorre subdirectorios ni elimina archivos de nombre desconocido. No toca reportes históricos/públicos. Archivos ilegibles se omiten; fallos del disco/operación informan error de comando sin rutas internas. Los permisos incorrectos requieren atención operativa.

Se añadió `hourly()->withoutOverlapping()` al scheduler existente. Ese bloqueo usa la caché de Laravel; no se configuró cron, servicio persistente ni infraestructura. No se ejecutó el comando sobre artefactos reales.

Para un futuro entorno autorizado: comprobar `php artisan schedule:list`, el mecanismo existente que invoca schedule:run cada minuto, usuario/permisos de PHP y de la caché, y observar hora/resultado del comando en una ejecución con fixtures. No ejecutar indiscriminadamente schedule:run para comprobar esta fase: también puede lanzar el backup existente. No se afirma que el scheduler productivo esté activo. Si no corre, los GET siguen rechazando vencidos pero el espacio ocupado aumenta; con funcionamiento horario, el borrado puede demorarse hasta el siguiente ciclo o más si hay retrasos/fallos.

Referencia consultada: documentación oficial Laravel 10 de [filesystem](https://github.com/laravel/docs/blob/10.x/filesystem.md) y [scheduling](https://github.com/laravel/docs/blob/10.x/scheduling.md), mediante Context7. No se cambiaron versiones ni dependencias.

## React y compatibilidad

El único enlace de ReportPage se sustituyó por LoadingButton y el helper compartido Axios/Blob con el interceptor Bearer ya existente. La ruta relativa file_path se resuelve contra la base del backend. Se reserva la pestaña durante el clic, antes de esperar la red; se conserva el visor nativo, impresión, guardado y ciclo de vida Blob existente.

No se cierra ni vacía automáticamente el modal al abrir, tanto si falla como si funciona: se puede repetir el mismo GET sin regenerar. El botón muestra carga. El botón Close continúa siendo una acción explícita del usuario. Se conserva la selección de filtros tras generar o fallar; ante fallo de generación permanece disponible el formulario con mensaje controlado. No se cambiaron las cuatro modalidades ni el payload de filtros. El modal incluye indicación para reintentar o regenerar si expiró.

JSON/HTML de error no se abre como PDF. Se mantienen los mensajes/limpieza/pop-ups del helper validado. Limitación heredada: algunos toast y el texto de carga del helper aún dicen invoice; el texto de ReportPage da contexto de reporte y regeneración. No se modificó el helper para evitar alterar los otros flujos. El nombre al guardar depende del visor nativo, igual que en CODEX 02/03. Impresión/guardado reales requieren validación manual.

La compatibilidad de reportes históricos es deliberadamente limitada: las antiguas URLs API por nombre ya no se aceptan; no hay fallback al directorio público. Su recuperación requiere regeneración o una migración privada aprobada.

## Inventario histórico y propuesta de retirada pública

Inventario local por metadatos y existencia, sin abrir PDFs:

| Elemento | Resultado local |
|---|---|
| storage/app/public/reports | No existe; cero artefactos locales inventariables en esa ubicación. |
| public/storage | No existe en este checkout. |
| Configuración del link estándar | Existe en filesystems.php y se preservó: apunta a storage/app/public. |
| Archivos/aliases/symlinks del servidor productivo | No inspeccionados; exposición y cantidad desconocidas. |

**Proteger la API no retira una URL estática histórica que siga publicada.** El código nuevo evita ese destino para generaciones futuras, pero no certifica rechazo de las URLs /storage/reports históricas del servidor.

Propuesta para una intervención futura autorizada: inventariar nombres, tamaños, fechas, permisos y aliases efectivos sin difundir contenido; respaldar los originales; decidir retención histórica con el responsable; retirar/bloquear exclusivamente la publicación de reports en Nginx/CDN; y, si se necesita conservar descargas, importar cada histórico como artefacto privado con UUID y política de antigüedad explícita. Verificar tanto URL histórica como posibles aliases y cachés con una fixture inocua. No activar fallback público ni asumir que borrar el link global storage es compatible con otros módulos. **No se movió, eliminó, limpió ni migró ningún histórico en esta fase.**

## Pruebas ejecutadas y pendientes

En ia-system-web:

```text
node --experimental-vm-modules --test --test-reporter=spec tests/documentDownload.test.cjs
tests 42
pass 42
fail 0
skipped 0
```

Se ejecuta el helper real con Axios/ventanas simulados, y los cuerpos reales extraídos de los handlers React en VM, sin instalar dependencias. Se cubren: PDF, 401/404/429/500, JSON/HTML inesperado, bloqueo de pestaña, reintento con mismo archivo, conservación de filtros y modal, errores de generación/red, MIME/firma, cierre y liberación Blob. Se conservan regresiones CODEX 02/03. Las comparaciones con Git confirman las cinco rutas previas, sus consumidores/controladores/plantillas, el helper y las cuatro plantillas de reporte intactas. Una prueba adicional compara los cuatro cuerpos de generación salvo sus bloques de salida/almacenamiento.

La ejecución intermedia con esa prueba adicional tuvo un fallo por safe.directory de Git al consultar el repositorio hermano desde el sandbox. Se corrigió la invocación de lectura con `git -c safe.directory=<workspace cloud> show`, igual que la prueba previa, sin modificar configuración Git global. La ejecución final completa dio 42/42.

`git diff --check` pasó en ambos repositorios. Git advierte normalización LF/CRLF en el archivo de pruebas Node; no son errores de whitespace. Se revisó el diff del controlador para confirmar el alcance y los índices Git no tienen cambios staged.

Las ocho pruebas Laravel nuevas están **preparadas, no ejecutadas**:

| Método / línea en ReportDownloadAuthenticationTest.php | Cobertura prevista |
|---|---|
| :123 | GET anónimo, token inválido y revocado; Accept PDF/HTML; JSON 401 sin PDF. POST generate sigue protegido. |
| :144 | Usuario A genera las cuatro modalidades con controlador/consultas reales y renderer simulado; A/B descargan repetidamente; nuevos archivos solo privados. |
| :167 | Mismas fechas, filtros distintos y doble generación: UUID separados, contenido distinto/conservado. |
| :183 | IDs inexistentes, malformados, codificados, rutas absolutas, .. y extensiones: errores seguros. |
| :199 | Reintento a 24 h menos un segundo; rechazo a 24 h; limpieza por comando de PDF y temporal vencidos, dejando público/archivos desconocidos/subcarpetas intactos. |
| :226 | PDF corrupto, fallo de disco y fallo de escritura: respuestas controladas sin texto interno. |
| :248 | Retención configurable y fallback seguro. |
| :259 | Symlink de archivo fuera de la raíz rechazado; se omite si la plataforma no permite crear symlinks. |

Estas pruebas usan SQLite :memory: sin migraciones/seeders de aplicación, Storage::fake para reports/public, tokens Sanctum reales y nombres/datos sintéticos. Solo se simula Dompdf; el contenido con firma PDF no pretende validar diseño ni impresión. La prueba de /storage bajo el kernel Laravel no certifica un alias de Nginx que evite PHP.

No hay PHP/Composer en PATH ni vendor en cloud, y web no tiene node_modules. No se ejecutaron PHPUnit/Artisan, lint/build, renderizado Dompdf ni Playwright; no hay aplicación local aislada lista. No se instalaron herramientas ni se utilizaron datos de negocio para resolver esa limitación.

En un entorno de ensayo ya provisionado, ejecutar:

```text
cloud: php artisan test --filter=DownloadAuthenticationTest
web:   node --experimental-vm-modules --test tests/documentDownload.test.cjs
web:   npm.cmd run build
web:   npm.cmd run lint
```

El filtro Laravel incluye reportes y las pruebas de las cinco descargas anteriores. Validar PHP lint de los nuevos archivos, SQLite, permisos, y el listado efectivo de middleware/scheduler. Los resultados de estas comprobaciones no se presuponen.

## Checklist informativo de despliegue futuro y validación del usuario

- Confirmar en ensayo la generación de las cuatro clases con fixtures, contenidos/diseños, filtros y respuesta UUID.
- Ejecutar las suites pendientes y confirmar usuario A genera / B descarga; anónimo e inválido reciben 401 sin PDF, incluso al conocer el UUID.
- Verificar en navegador HTTPS/CORS/Bearer, nueva pestaña, impresión/guardado tardíos, popup bloqueado, cierre, errores y reapertura sin perder filtros dentro de retención. Confirmar rechazo tras expirar sin necesidad de limpiar primero.
- Verificar raíz y permisos privados, almacenamiento duradero disponible y ausencia de symlinks/aliases que publiquen esa raíz. Medir memoria/tamaño con reportes grandes.
- Resolver expresamente el inventario/exposición estática de históricos y posibles cachés; verificar URL estática con fixture antes del visto bueno. No queda certificado por esta implementación local.
- Acordar la política provisional de 24 horas; revisar configuración efectiva y cache de configuración/rutas del futuro despliegue, sin dar por actualizados esos caches aquí.
- Verificar scheduler existente y resultados de reports:prune en ensayo, sin ejecutar backups accidentalmente. Documentar observación de disco y errores de limpieza.
- Confirmar regresión funcional de facturas, solicitudes, planillas y cheques. Solo el usuario puede dar el visto bueno final de CODEX 04.

## Estado Git final

```text
ia-system-cloud:
 M app/Console/Kernel.php
 M app/Http/Controllers/ReportController.php
 M app/Http/Middleware/Authenticate.php
 M config/filesystems.php
 M routes/api.php
?? app/Console/Commands/PrunePrivateReports.php
?? app/Services/PrivateReportStorage.php
?? config/reports.php
?? docs/CODEX_03_DESCARGAS_SEGURAS_SOLICITUDES_PLANILLAS_CHEQUES_SMI_AJAX.md
?? docs/CODEX_04_REPORTES_PRIVADOS_SMI_AJAX.md
?? docs/RESULTADO_CODEX_04_REPORTES_PRIVADOS_SMI_AJAX.md
?? tests/Feature/ReportDownloadAuthenticationTest.php

ia-system-web:
 M src/pages/ReportPage.js
 M tests/documentDownload.test.cjs
```

Los dos contratos CODEX_03/CODEX_04 sin seguimiento ya existían al comenzar. El resto corresponde a esta implementación. No se añadieron archivos al índice ni se ejecutaron commits, push o despliegue.

## Rollback local sin afectar CODEX 02/03 ni históricos

No se ejecutó rollback. Respaldar primero el diff, las pruebas y este informe; revisar cambios posteriores antes de restaurar archivos completos. Si existen cambios posteriores, retirar únicamente los hunks de esta fase.

Con la base inicial intacta, restaurar desde cloud `22bfe7c` únicamente routes/api.php, Authenticate.php, ReportController.php, config/filesystems.php y Console/Kernel.php; desde web `6fc4294` únicamente ReportPage.js y tests/documentDownload.test.cjs. Retirar, tras respaldar, solo los archivos nuevos PrivateReportStorage.php, PrunePrivateReports.php, config/reports.php y ReportDownloadAuthenticationTest.php, conservando la evidencia si se desea. No retirar los contratos previos ni documentación/pruebas CODEX 02/03 en conjunto.

No borrar ni mover storage/app/public/reports ni artefactos privados generados; no hay migraciones/datos que revertir. Un rollback del código anterior reintroduce la ruta pública y los nombres públicos: **no trasladar ese rollback a producción manteniendo la descarga expuesta**; antes haría falta una contención autorizada de acceso/generación y conservar el área privada. Si se retira el scheduler, sus artefactos privados necesitarán una política posterior de limpieza aprobada.

Revisar git status/diff/check y repetir las pruebas CODEX 02/03 tras cualquier reversión. No usar reset/clean global.
