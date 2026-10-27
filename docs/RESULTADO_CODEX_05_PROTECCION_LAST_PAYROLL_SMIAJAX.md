# CODEX 05 — Protección de GET /api/last-payroll

Fecha: 26 de octubre de 2026, America/Panama.

**IMPLEMENTADA LOCALMENTE — PENDIENTE DE VALIDACIÓN HTTP/LARAVEL Y DEL USUARIO.** Sin despliegue, commits ni push. No se accedió a producción, BD real, credenciales o .env; no hubo migraciones, seeders ni operaciones financieras.

Este informe corresponde a la protección de last-payroll; no sustituye el informe previo CODEX 05 de consolidación/despliegue ni el diagnóstico.

## Estado inicial y alcance confirmado

Se releyeron rutas, middleware, PayrollController, modelo Payroll, consumidor CAndRWizardPage, App.js, PrivateRoute y diagnóstico. La estructura funcional seguía como en el diagnóstico: ruta sin Sanctum, misma consulta y catch React sin liberar carga. El helper de descargas tenía un cambio previo del usuario para usar terminología genérica document; se preservó íntegramente.

Bases iniciales: cloud `6e50387`, web `90b52b3`. `git status --short --untracked-files=all` inicial:

```text
cloud:
?? docs/CODEX_05_PROTECCION_LAST_PAYROLL_SMIAJAX.md

web:
(limpio)
```

El contrato ya existía sin seguimiento y no se editó. No se encontraron AGENTS.md en los repositorios. Se conservaron los informes anteriores y los cambios integrados de CODEX 02–04.

## Archivos cambiados y decisiones

| Repositorio / archivo | Cambio |
|---|---|
| cloud `routes/api.php:197` | Conserva GET /api/last-payroll y PayrollController::lastPayroll; añade auth:sanctum y nombre payrolls.last. Ninguna de las seis rutas documentales cambia. |
| cloud `app/Http/Middleware/Authenticate.php:20` | Añade únicamente payrolls.last al listado de rutas con 401 JSON explícito. Mantiene los seis nombres existentes, mensaje Unauthenticated. y encabezado private, no-store. |
| web `src/pages/CAndRWizardPage.js:298` | Solo getLastPayroll cambia a async/await con try/catch/finally; conserva Axios GET, URL, formato de lista y selector. Libera carga en éxito/error y muestra un toast controlado. |
| cloud `tests/Feature/LastPayrollAuthenticationTest.php:1` | Nuevo: seis métodos de prueba aislada para autenticación, compatibilidad, selección, ausencia de efectos y middleware. Preparados, no ejecutados. |
| web `tests/lastPayroll.test.cjs:1` | Nuevo: quince pruebas Node del handler real con Axios/UI simulados y comprobaciones estáticas de integridad. Ejecutadas. |
| cloud `docs/RESULTADO_CODEX_05_PROTECCION_LAST_PAYROLL_SMIAJAX.md:1` | Este informe. |

No se modificaron PayrollController::lastPayroll, modelo, SQL, datos, numeración, totales, estados ni permisos. La consulta sigue exactamente:

```php
return Payroll::where('status', '!=', 'cancelled')->orderBy('id', 'desc')->first();
```

No hay filtros por propietario/rol ni nuevas policies. Cualquier usuario autenticado mantiene la selección global anterior. La respuesta de éxito y la respuesta autenticada sin registro no se normalizaron ni se cambiaron a otro formato.

## Autenticación y contrato de error

La ruta tiene Sanctum explícito y nombre payrolls.last. Ante fallo de autenticación reutiliza la excepción ya adoptada para descargas:

```text
HTTP 401
Content-Type: application/json
Cache-Control: private, no-store

{"message":"Unauthenticated."}
```

Ese camino no depende de Accept: application/json y evita resolver route('login') al pedir HTML. El mensaje/encabezados anteriores permanecen intactos. No cambia el handler global ni las demás API. Anónimo, token inválido/revocado y token vencido cuando la política lo contemple deben ser rechazados antes de ejecutar el controlador; la prueba HTTP real está pendiente, no se presupone aprobada por una comprobación estática.

No se cambió el lifetime de Sanctum. La prueba de expiración configura temporalmente una política de un minuto **solo en el test** con token sintético antiguo. La negación exacta JSON no contiene atributos de Payroll ni redirección Location; tiene cobertura preparada para tabla vacía y con una planilla existente, y Accept JSON/HTML/PDF.

## React: carga y compatibilidad

getLastPayroll conserva el mismo singleton Axios con el interceptor Bearer de App.js. La consulta continúa siendo JSON, sin Blob, sin tokens en URL ni cambios del mecanismo de sesión. En éxito sigue construyendo una lista con response.data y pasando esa lista a setPayrolls; el selector conserva id/suffix_id/end_date.

`finally` libera isLoading incluso si falla la petición o su procesamiento. Un 401 presenta el mensaje discreto de sesión expirada y otros fallos muestran un mensaje de reintento. Se retiró únicamente el console.log de este catch para no exponer objetos Axios/cuerpos internos. No se muestran mensajes de servidor ni credenciales. Se preserva la lista anterior ante error; no se añadieron botones, redirecciones ni cambios visuales ajenos al error.

Las respuestas vacías mantienen la representación previa de la lista; la condición existente payroll?.id evita una opción inválida. No se inventó un nuevo contrato 404/null ni mensaje de tabla vacía. Otras operaciones de la pantalla y su manejo de carga no se tocaron.

PrivateRoute permanece intacto: aún retorna children antes de concluir su comprobación asíncrona. Puede iniciarse una consulta del asistente antes de redirigir al login. La protección ahora se aplica en backend con independencia de esa navegación; el posible rediseño del guard queda separado y no fue implementado. La prueba de navegador de ese orden concreto sigue pendiente.

## Validaciones ejecutadas

En ia-system-web:

```text
node --experimental-vm-modules --test --test-reporter=spec tests/documentDownload.test.cjs tests/lastPayroll.test.cjs
tests 57
pass 57
fail 0
skipped 0
```

Las 42 pruebas previas de descargas y las 15 nuevas pasan. Las nuevas ejecutan el cuerpo real de getLastPayroll extraído del archivo, sin renderizar React ni usar red. Cubren éxito y conservación del objeto/lista, null/cadena vacía/undefined, 401/429/500/503, red/timeout/fallo síncrono, carga mientras la promesa sigue pendiente, reintento por la misma función, mensajes sin detalles sensibles y ausencia de cambios a otros bloques de la pantalla.

Las comprobaciones estáticas verifican ruta Sanctum/nombre, contrato 401 existente, las seis declaraciones documentales idénticas a HEAD y ausencia de cambios a controlador/modelo, App.js, PrivateRoute y helper. Las pruebas de descargas mantienen su cobertura MIME/firma, JSON/HTML/201, errores, apertura, reintento y ciclo Blob. Esto acredita regresión aislada, **no pruebas HTTP de las seis descargas ni impresión real**.

Compilación ejecutada:

```text
npm.cmd run build
exit code 0
Compiled with warnings.
```

Se generó el build local, ignorado por Git; no se publicó ni se inició un servidor. No se instalaron dependencias ni se modificaron package.json, package-lock.json o yarn.lock.

### Advertencias de compilación, separadas del cambio

El build informó caniuse-lite desactualizado (Browserslist) y 271 advertencias ESLint en 37 archivos: no-unused-vars, react/prop-types, react/no-unused-prop-types, arrow-body-style, prefer-destructuring y react-hooks/exhaustive-deps. Son diagnósticos sobre código fuera del cambio de getLastPayroll; no hubo correcciones de lint ni actualizaciones para ocultarlos. No se ejecutó un build adicional de la base para comparar salidas completas.

En CAndRWizardPage las advertencias son el useEffect de review (:343, dependencias payrollData?.invoices/selected.length) y un index sin uso (:757). Ambos bloques están intactos respecto de HEAD; la prueba estática compara todo el archivo salvo getLastPayroll. No se reportó advertencia dentro de la función cambiada. No se ejecutó lint global por separado: la validación ejecutada fue el build con sus diagnósticos.

`git diff --check` pasó en ambos repositorios. Se revisó el diff final y la integridad de controlador/modelo/descargas/dependencias; no hay cambios staged.

## Pruebas backend preparadas y limitación de ejecución

No hay PHP/Composer en PATH ni vendor en cloud. **No se ejecutaron PHPUnit, PHP lint ni route:list** y no se instalaron herramientas para obtener un PASS. No se hicieron solicitudes HTTP a producción ni a una BD real.

`LastPayrollAuthenticationTest.php` configura SQLite :memory: y tablas sintéticas mínimas, sin RefreshDatabase/migraciones de aplicación. Usa tokens Sanctum reales del entorno de test, controlador y modelo reales. La comprobación de archivos utiliza discos fake y exige que no se invoque el renderer PDF.

| Método / línea | Comprobación preparada, pendiente |
|---|---|
| :105 | Anónimo, inválido y revocado con planilla existente; Accept JSON/HTML/PDF; 401 JSON exacto, sin atributos financieros, sin redirect y con private/no-store. |
| :111 | Mismos casos con tabla vacía; no se acepta una respuesta vacía anónima como sustituto de 401. |
| :117 | Token expirado según configuración solo de test, con planilla existente. |
| :127 | Usuario A y B reciben idéntica selección global de A; mayor ID no cancelado aunque haya otra fecha posterior y un ID mayor cancelado. Campos/cuerpo esperados y planillas sin cambios; sin renderer/archivos en discos fake. |
| :155 | Tabla vacía y todas canceladas autenticadas: compara status/cuerpo/MIME con la conversión Laravel de la respuesta del controlador intacto, sin adivinar el formato de null. |
| :176 | Ruta registrada conserva acción, nombre payrolls.last y middleware auth:sanctum. |

No se observó un defecto que exigiera modificar el controlador ni ampliar el contrato funcional. La caracterización vacía queda pendiente de ejecutar, pero el controlador se conserva literalmente y se prueba frente a su propia respuesta sin middleware.

En un entorno de ensayo ya provisionado, con ambos repositorios y datos sintéticos, ejecutar:

```text
php artisan test --filter='LastPayrollAuthenticationTest|DownloadAuthenticationTest'
php artisan route:list --path=last-payroll -v
```

El filtro incluye los seis métodos nuevos y los dieciséis de las seis familias documentales previas. Resultado esperado, **no obtenido aquí**: suites aprobadas y ruta GET|HEAD con auth:sanctum/nombre payrolls.last. La prueba de registro no sustituye la inspección del middleware efectivo del release desplegado.

## Validación manual y riesgos pendientes

Con fixtures inocuas en ensayo, el usuario debe confirmar carga del selector con token válido, acceso de A y B a la misma selección, ausencia de datos sin token/inválido/revocado y JSON 401 incluso con Accept HTML. Verificar también resultado vacío, error de red/5xx y sesión expirada sin Backdrop persistente, más la regresión funcional de las seis descargas. No crear/cancelar/regenerar planillas en producción para probar esta fase.

La caché de rutas o de un proxy desplegado podría seguir sirviendo comportamiento previo hasta publicarse/configurarse el release correctamente; no se inspeccionó infraestructura ni se invalidó caché productiva. La protección del código local no demuestra cierre de la URL pública actual del servidor. Los hallazgos anteriores de recuperación, históricos estáticos y scheduler no se resolvieron ni se vuelven a declarar aprobados por esta entrega.

## Publicación futura — solo documentada

1. El usuario revisa el diff, ejecuta/autoriza la validación pendiente en ensayo y decide si crea commits y autoriza una fase de publicación separada. Preservar los contratos e informes previos.
2. Preparar backend con routes/api.php y Authenticate.php, y frontend con el cambio de CAndRWizardPage compilado en el artefacto validado. No hay cambios de dependencias, SQL o migraciones requeridos. Tests/informe son evidencia para revisión/CI.
3. Publicar por el procedimiento aprobado de releases, verificando caché de rutas/configuración/opcache y cualquier respuesta de last-payroll previamente cacheada. No ofrecer fallback público para compatibilidad. El frontend actual ya envía Bearer; conviene activar también su manejo de error para clientes con sesión inválida.
4. Confirmar route:list y las pruebas de aceptación con fixtures autorizadas antes de dar por cerrada la protección del servidor. No se incluye ni se ejecutó ninguna modificación de Nginx, .env o reinicio de servicios aquí.

Reversión local, si se solicita después: respaldar diff/evidencia y revertir solo los hunks de routes/api.php/Authenticate.php desde cloud 6e50387 y de getLastPayroll desde web 90b52b3; retirar únicamente los tests nuevos si corresponde. No usar reset/clean global ni borrar documentación previa. Revertir la ruta restauraría la lectura anónima: no trasladar ese rollback a producción sin una contención de acceso aprobada. No hay datos o artefactos de negocio que revertir en esta fase.

## Estado Git final

```text
ia-system-cloud:
 M app/Http/Middleware/Authenticate.php
 M routes/api.php
?? docs/CODEX_05_PROTECCION_LAST_PAYROLL_SMIAJAX.md
?? docs/CODEX_06_MEJORA_VISUAL_PREVISUALIZACION_PDF_SMIAJAX.md
?? docs/RESULTADO_CODEX_05_PROTECCION_LAST_PAYROLL_SMIAJAX.md
?? tests/Feature/LastPayrollAuthenticationTest.php

ia-system-web:
 M src/pages/CAndRWizardPage.js
?? tests/lastPayroll.test.cjs
```

El contrato CODEX 05 sin seguimiento ya existía. En la última inspección apareció también el contrato CODEX 06; no fue creado, leído ni modificado por esta implementación y se preservó. El build se regeneró localmente y permanece ignorado. Únicamente se modificaron tres archivos funcionales: ruta, middleware y función del consumidor. **Sin cambios en producción, credenciales, BD real, respaldos, commits ni push.**
