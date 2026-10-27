# SMIAJAX — Resultado del diagnóstico de GET /api/last-payroll

Fecha: 26 de octubre de 2026, America/Panama. **Primera fase: diagnóstico estático y documento; sin corrección implementada.**

## Conclusión y nivel de certeza

En el código revisado, **un solicitante sin autenticar puede alcanzar la consulta y recibir la última planilla no cancelada, si existe**, sin aportar token. La conclusión no se basa únicamente en la posición de la ruta: se inspeccionaron middleware global y de API, controlador concreto y base, modelo, proveedores, configuración y consumidor React. No se encontró una comprobación adicional de identidad o permiso en esas capas.

La respuesta esperada contiene metadatos de la planilla e importe total, no un PDF. No se ejecutó una petición anónima ni se consultó una base de datos. **No está verificada la exposición externa del Droplet, la respuesta real, la existencia de registros ni una filtración ocurrida.** Un proxy, mantenimiento, firewall, caché de rutas distinto o controles ajenos al checkout podrían cambiar lo observable.

Evaluación acotada: **riesgo medio de confidencialidad**, fundamentado en el acceso sin autenticación a un registro financiero administrativo. No se asigna criticidad ni CVSS, ni se atribuye exposición de SSN, cuentas bancarias o todos los pagos a este endpoint. Debe reevaluarse si el esquema/respuesta desplegados difieren. Recomendación: protegerlo con Sanctum y 401 JSON explícito en una siguiente fase autorizada, conservando URL, consulta y acceso de cualquier usuario autenticado.

## 1. Fuentes, alcance y estado inicial

- cloud HEAD: `a79b140c806ad332511494e7063e197f93d1fb48`.
- web HEAD: `db1d7b91faaf473e4fcc0a949c64f25677f89246`.
- Estado inicial cloud: únicamente `?? docs/ANALISIS_SEGURIDAD_LAST_PAYROLL_SMIAJAX.md`, contrato aportado por el usuario. Se preservó sin editar.
- Estado inicial web: limpio. No se encontraron AGENTS.md en los repositorios ni ascendentes consultados.

Se examinó código local, migraciones como evidencia del esquema esperado, configuración versionada y dependencias de la pantalla. La salida anterior de route:list referida en el contrato acredita la declaración GET|HEAD, pero no es una ejecución nueva ni un listado detallado de middleware de esta revisión. No se conectó a producción ni se leyeron .env, credenciales o registros reales.

## 2. Implementación, consulta y dependencias

`routes/api.php:197` declara:

```php
Route::get('/last-payroll', [PayrollController::class, 'lastPayroll']);
```

`app/Http/Controllers/PayrollController.php:39–42` implementa:

```php
public function lastPayroll()
{
    //return last in array
    return Payroll::where('status', '!=', 'cancelled')->orderBy('id', 'desc')->first();
}
```

No recibe parámetros de ruta ni usa query/body, usuario, token, sesión, rol, gate o policy. Depende del modelo `App\Models\Payroll`, de Eloquent y de la conexión de BD configurada en ejecución, cuyo valor/credenciales no se inspeccionaron. La estructura equivalente de consulta es:

```sql
SELECT * FROM payrolls
WHERE status <> 'cancelled'
ORDER BY id DESC
LIMIT 1;
```

Es una representación, **no SQL ejecutado**. El valor del filtro es constante, sin interpolación del input HTTP. “Última” significa mayor ID entre registros no cancelados; no ordena por fecha ni usuario. Devuelve como máximo un registro, no el listado completo. No hay joins, carga de relaciones ni lectura de archivos. No hay escrituras de negocio, generación de documentos ni numeración en este método. El throttle puede actualizar su caché técnica, sin que ello sea una modificación de planillas.

El modelo (`app/Models/Payroll.php:8–47`) extiende directamente Eloquent Model; no declara scopes globales, `$hidden`, `$visible`, `$with`, `$appends`, casts propios ni overrides de serialización. Sus métodos user/request/bankChecks/invoices/car_wizards definen relaciones, pero lastPayroll no las carga. `$fillable` regula asignación masiva al escribir: **no limita los campos de salida**. Los proveedores AppServiceProvider y AuthServiceProvider no registran controles adicionales para Payroll; EventServiceProvider no declara observers de este modelo.

## 3. Datos que puede devolver

Al devolver un modelo existente, Laravel lo serializa automáticamente como JSON, respetando atributos ocultos si se hubieran definido; aquí no los hay. Referencia: [serialización oficial Eloquent, Laravel 10](https://github.com/laravel/docs/blob/10.x/eloquent-serialization.md). Esto es el comportamiento esperado por código, no una captura HTTP.

| Campo del esquema versionado | Información revelada |
|---|---|
| id | ID interno de planilla |
| suffix_id | Identificador/numeración visible de planilla |
| user_id | ID del usuario asociado, sin nombre/correo cargados |
| request_id | ID de solicitud relacionada; nullable |
| month | Periodo declarado |
| start_date, end_date | Rango de fechas de la planilla |
| status | Estado de negocio; el filtro excluye cancelled |
| total_amount | Importe agregado, decimal(8,2) en la migración |
| created_at, updated_at | Marcas de tiempo |

Evidencia: `database/migrations/2023_03_26_001111_create_payrolls_table.php:14–24`, `2023_10_15_000325_add_suffix_id_to_payrolls_table.php:15`, `2023_10_15_123849_add_request_id_to_payrolls_table.php:15–21`, `2023_10_15_144942_add_status_to_payrolls_table.php:15`. Son once columnas esperadas; no se comprobó el esquema real. Como la consulta selecciona todos los atributos y no hay filtro de salida, otras columnas que existan en una BD distinta también podrían serializarse.

No se obtiene aquí el detalle de facturas, personas, direcciones, cheques o datos bancarios por el solo hecho de que existan relaciones. Tampoco se entregan bytes PDF, rutas privadas de reportes o credenciales. Los IDs pueden facilitar correlación con otros datos, pero conocerlos no elimina el Sanctum de las rutas documentales ya protegidas.

Si no hay planilla elegible, `first()` devuelve null y el controlador no define un contrato vacío explícito. No afirmar 404, JSON null o un formato concreto sin probar la respuesta del framework. Si falla la BD, no hay manejo específico en el método: actúa el handler general. Estos escenarios deben verificarse con fixtures, sin cambiar aquí la regla de selección.

## 4. Comprobación de las capas de acceso

| Capa revisada | Evidencia | Resultado |
|---|---|---|
| Registro/prefijo | RouteServiceProvider.php:29–35 | api.php se registra con prefijo /api y grupo api. |
| Declaración individual y grupos | api.php:34,170,197 | last-payroll está fuera del grupo Sanctum y no tiene middleware individual de autenticación. |
| Kernel global | app/Http/Kernel.php:16–24 | TrustProxies, CORS, mantenimiento, tamaño de request y normalización; ninguna exigencia de identidad. |
| Grupo api | Kernel.php:41–45 | throttle:api y SubstituteBindings; middleware stateful de Sanctum comentado. No son autenticación del endpoint. |
| Rate limit | RouteServiceProvider.php:44–45 | 60/minuto por usuario reconocido o IP; no exige que exista usuario. Puede dar 429, no demuestra autorización. |
| Mantenimiento/proxies/CORS | Middleware propios y config/cors.php | Mantenimiento puede impedir tráfico temporalmente; proxies/CORS no constituyen una comprobación de acceso a esta consulta. No se inspeccionó configuración efectiva del servidor. |
| Controlador concreto y base | PayrollController.php:13,39–42; Controller.php:9–12 | Sin constructor/middleware de controlador ni auth/authorize invocado. Incorporar AuthorizesRequests al base no ejecuta una autorización por sí mismo. |
| Modelo/proveedores | Payroll.php; AppServiceProvider.php; AuthServiceProvider.php; EventServiceProvider.php | Sin scope/observer/control de usuario que filtre este acceso. Políticas propias no configuradas para el caso. |
| Excepciones | app/Exceptions/Handler.php:45–50 | Registro de reportable vacío, sin autenticación añadida. |
| Authenticate propio | app/Http/Middleware/Authenticate.php:13–34 | Solo actúa cuando se aplica auth; su 401 explícito actual cubre seis rutas de descarga y no last-payroll. |

No hay caché local routes*.php en bootstrap/cache. No hay PHP/Composer en PATH ni vendor: no se pudo ejecutar `php artisan route:list --path=last-payroll -v`. Por ello se reconstruye la pila **definida en código**, sin presentarla como middleware observado de una aplicación arrancada.

Con el código inspeccionado, petición aceptada por infraestructura/limitador y BD disponible, la consulta no distingue anónimo, token inválido o token válido. Si existe registro, se espera 200 JSON del modelo también para el anónimo. Esa inferencia es más fuerte que la declaración aislada de ruta, pero no equivale a una prueba de disponibilidad desde Internet. No se comprobó ni se afirma caché pública de respuestas.

## 5. Consumidor React y dependencia de autenticación

La búsqueda de todo src encontró **una sola llamada** al endpoint: `src/pages/CAndRWizardPage.js:300`.

| Paso del flujo | Referencia / comportamiento |
|---|---|
| Ruta del asistente | src/routes.js:38,52: `/dashboard/payroll-panel/cr-wizard`, bajo PrivateRoute y DashboardLayout. |
| Inicio de sesión normal | LoginForm.js:40–43 guarda token/ID y navega al dashboard. |
| Petición inicial | CAndRWizardPage.js:298–309 define getLastPayroll; useEffect :324–326 lo invoca al montar. Es GET Axios, no enlace ni Blob. |
| Credencial enviada | src/App.js:11–14 configura baseURL e interceptor global con Authorization Bearer tomado de localStorage. Sin token construye Bearer null; eso no es autenticación válida. |
| Negociación de respuesta | No hay headers sobrescritos en getLastPayroll; el Axios instalado declara Accept application/json, text/plain, */* (node_modules/axios/lib/defaults/index.js:153). No se modificó la dependencia. |
| Uso de respuesta | :302–305 introduce response.data en una lista para el selector; :376–380 muestra id, suffix_id y end_date. El payload backend incluye más columnas que esas tres. |
| Selección/detalle | :369–370 y :312–321 consultan GET `/api/payrolls/{id}`, ya protegido con Sanctum. Ese detalle alimenta el resto del asistente. |
| Acciones posteriores | :177 consulta revisión protegida; :282 usa POST `/api/car-wizard`, protegido. No se invocaron esas operaciones de negocio. |

No se encontró uso funcional de last-payroll en LoginPage ni necesidad de mostrarlo públicamente antes de iniciar sesión. Su uso administrativo y los endpoints vecinos ya autenticados respaldan exigir Sanctum; la regla operativa final debe confirmarla el responsable, no deducir roles inexistentes.

**Matiz del guard:** `PrivateRoute.js:6–28` comprueba/redirige dentro de un useEffect y valida el token mediante otra petición, pero :32 retorna children siempre, sin esperar esa comprobación. Por tanto, una visita directa al asistente sin sesión o con token inválido puede montar temporalmente sus hijos e iniciar getLastPayroll antes de completar la redirección. El orden exacto de peticiones/redirección no se midió en navegador; el guard no constituye una barrera de seguridad para la API ni justifica dejarla sin Sanctum.

**Riesgo de compatibilidad al protegerlo:** el catch de getLastPayroll (:307–309) solo hace console.log; no pone isLoading=false ni muestra recuperación. El Backdrop depende de isLoading (:858–863). Ante 401/red/500 puede permanecer la carga si la pantalla sigue montada. La redirección posterior del guard puede desmontarla, pero no debe dependerse de ello. Con un token válido, el formato de consulta/selector es compatible con añadir middleware: no hace falta cambiar URL, payload ni mecanismo Bearer.

En ausencia de registro, el selector omite entradas sin payroll?.id (:377), pero no hay mensaje de estado vacío específico. No se ha demostrado un crash por null en ese bloque. No se propone alterar el contrato vacío dentro de un arreglo de autenticación.

## 6. Impacto y límites del hallazgo

Lo confirmado es una falta de autenticación en la lógica completa del endpoint inspeccionado, con salida potencial de un registro financiero. Un tercero con acceso de red podría conocer periodo, total, estado e IDs, y repetir consultas para observar cambios; no hay polling periódico implementado en el consumidor, esa repetición sería un uso externo posible. No se probó explotación ni se accedió a ningún dato.

Este GET no cancela/regenera planillas, no permite cambiar numeraciones y no entrega por sí mismo la totalidad de las facturas o un cheque. No se encontró SQL construido a partir de input del solicitante en el método. La exposición es distinta del acceso a PDFs de CODEX 02–04 y su corrección no debe modificar esos flujos ni convertir el JSON de last-payroll en un flujo Axios/Blob.

Pendientes fuera del análisis local: middleware/caché efectivos del release desplegado, Nginx/proxy/firewall, accesibilidad de red, estado de mantenimiento, esquema real y existencia de registros. No se rebaja el defecto de código por la posibilidad de un control externo, pero tampoco se afirma fuga productiva sin evidencia.

## 7. Propuesta mínima compatible — NO aplicada

Para una fase de implementación autorizada, conservar URL y método, añadir Sanctum sin roles/ownership y conservar consulta/cuerpo de éxito. Cualquier usuario autenticado debe obtener el mismo resultado, incluso si la planilla pertenece a otro usuario; no hay política por perfil que justifique 403 en el código revisado.

La modificación de una línea propuesta en el contrato —añadir auth:sanctum— protege el acceso. Para obtener **401 JSON también al copiar la URL al navegador con Accept HTML**, se recomienda completar el patrón existente en dos archivos:

```php
// Propuesta para routes/api.php, sin ejecutar:
Route::get('/last-payroll', [PayrollController::class, 'lastPayroll'])
    ->middleware('auth:sanctum')
    ->name('payrolls.last');
```

Añadir `'payrolls.last'` a los nombres que activan el JSON 401 en `app/Http/Middleware/Authenticate.php:13`, conservando exactamente la respuesta actual Unauthenticated. y Cache-Control private, no-store. Así no se cambia autenticación general ni comportamiento de las seis descargas.

Motivo del complemento: la ruta todavía no pertenece a esa lista y `redirectTo()` (:34) intenta `route('login')` cuando no espera JSON; routes/web.php solo declara `/`, sin nombre login. Añadir únicamente el middleware deja pendiente ese camino de error y podría producir fallo de resolución de ruta en una navegación HTML. Debe verificarse, no dar por garantizado un 401 uniforme solo por el cambio de una línea. La API Axios normalmente negocia JSON y ya envía Bearer.

Propuesta adicional separada de robustez React: en getLastPayroll, liberar carga en finally y presentar un error controlado ante 401/red/5xx, manteniendo el selector y el flujo existente. No se requiere rediseñar PrivateRoute para asegurar el endpoint; su render antes de validación puede evaluarse aparte. No se aplicaron estas modificaciones ni se escribió una prueba nueva en esta fase diagnóstica.

Reducir a tres campos la salida sería otra decisión de contrato/minimización, no imprescindible para autenticar y no incluida en la propuesta mínima. Tampoco se propone alterar la selección de última planilla, estados, permisos o política de tokens.

## 8. Pruebas necesarias en una fase posterior

Antes de implementar, el responsable confirma que no existe un consumidor público legítimo fuera del código fuente examinado. Después de aprobar el alcance, preparar tests en SQLite :memory: con tablas mínimas/fixtures sintéticas, sin migraciones de aplicación ni conexión a datos reales.

| Caso propuesto | Resultado requerido tras la corrección |
|---|---|
| Sin token, con y sin planilla elegible; Accept JSON/HTML | 401 JSON, sin objeto de planilla ni redirect/error de login. No basta probar solo una BD vacía. |
| Token inválido y revocado | Mismo 401, sin datos; respetar límite de peticiones. |
| Token vencido, cuando aplique | Rechazo; activar caducidad solo en fixture/configuración de test si se necesita, sin cambiar lifetime productivo. |
| Usuario A válido | Conserva formato/campos y registro seleccionado del contrato previo. |
| Usuario B válido sobre planilla de A | Permitido con el mismo resultado; no añadir roles ni esperar 403 sin una regla nueva aprobada. |
| Varios IDs/fechas/estados sintéticos | Devuelve mayor ID no cancelled, aunque las fechas estén en otro orden; no cambiar la regla financiera. |
| Todas canceladas o tabla vacía, con autenticación | Caracterizar primero la respuesta actual y preservar el comportamiento acordado; no convertirla implícitamente en 404. |
| Lectura sin efectos de negocio | No cambia planillas, solicitudes, cheques ni numeración. |
| Asistente con sesión válida | Carga selector con id/suffix_id/end_date y puede consultar detalle protegido, sin ejecutar Cancel/Regenerate en producción. |
| Visita directa sin sesión/token inválido | Puede haber request antes de redirigir, pero backend no devuelve datos; sin carga persistente si sigue montado. |
| Errores 401/red/500 y resultado vacío en UI | Estado de carga resuelto y feedback coherente, si se aprueba el ajuste de error. |
| Regresión documental | Seis rutas y quince acciones CODEX 02–04 conservan protección y flujo; mismo helper, sin cambios de render o negocio. |

Comprobación de middleware propuesta, **no ejecutada**:

```text
php artisan route:list --path=last-payroll -v
```

Comparar antes/después en un checkout de ensayo ya provisionado. Una eventual prueba HTTP anónima queda para cuando el usuario la autorice y exista una fixture inocua; no usar registros reales para demostrar el defecto. Un 401 aislado no certifica acceso de A/B, selección correcta, ausencia de efectos ni protección de otras rutas. No se solicita ni presupone autorización para implementar o desplegar con este informe.

## 9. Comprobaciones ejecutadas e integridad

- Lectura local de ruta, controlador/base, modelo, migraciones, kernel/middleware, providers, handler, bootstrap, consumidor, router/guard, interceptor y flujo de login. Búsqueda completa de src: una llamada a last-payroll. Sin consultas HTTP a la aplicación.
- composer.lock declara Laravel v10.4.1 y Sanctum v3.2.1; no hay vendor ni PHP/Composer en PATH. No se ejecutaron Artisan, PHPUnit, SQL, migraciones, seeders ni pruebas anónimas reales. La consulta de fuente remota del framework no estuvo disponible; las conclusiones locales y de serialización no dependen de ella.
- node_modules sí existe ahora en web, a diferencia del entorno de algunos informes previos. No se instaló, actualizó ni compiló nada. No se ejecutó build porque solo se autoriza diagnóstico y no hay cambio frontend.
- Se ejecutó la suite aislada ya existente: `node --experimental-vm-modules --test --test-reporter=spec tests/documentDownload.test.cjs`: **42 PASS, 0 FAIL, 0 omitidas**. Comprueba regresión aislada de descargas; no prueba last-payroll ni HTTP Laravel. No se modificó ningún test.
- `git diff --check` en ambos repositorios: correcto; sin cambios rastreados ni staged. Se preservaron todos los archivos previos. No se ejecutaron commits, push, despliegue ni operaciones destructivas.

Estado final:

```text
ia-system-cloud:
?? docs/ANALISIS_SEGURIDAD_LAST_PAYROLL_SMIAJAX.md
?? docs/RESULTADO_ANALISIS_SEGURIDAD_LAST_PAYROLL_SMIAJAX.md

ia-system-web:
(limpio)
```

El primer archivo es el contrato previo del usuario; el segundo es el único entregable nuevo. **Diagnóstico entregado; corrección y validación HTTP pendientes de una autorización posterior.**
