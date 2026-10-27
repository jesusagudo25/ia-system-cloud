# CODEX 05 — Protección de `GET /api/last-payroll` (SMIAJAX)

**Tipo:** Contrato de implementación y validación local.  
**Alcance:** Backend Laravel (`ia-system-cloud`) y ajuste acotado de consumidor React (`ia-system-web`).  
**Estado:** Pendiente de ejecución por Codex. **No desplegar a producción.**

## 1. Contexto y evidencia

El diagnóstico previo `docs/RESULTADO_ANALISIS_SEGURIDAD_LAST_PAYROLL_SMIAJAX.md` concluyó, mediante revisión estática, que `GET /api/last-payroll` está registrado fuera de `auth:sanctum` y que no hay validación complementaria de identidad en la implementación examinada. El método `PayrollController::lastPayroll()` devuelve el último registro `Payroll` cuyo `status != 'cancelled'`, ordenado por `id` descendente, sin filtrar por usuario. Si hay un registro, Eloquent puede serializar sus atributos, incluidos identificadores y `total_amount`. **No se realizó una prueba HTTP anónima contra producción y no se afirma una fuga efectiva.**

El frontend consume esta API en `src/pages/CAndRWizardPage.js` mediante Axios, cuyo interceptor agrega `Authorization: Bearer <token>`. El consumidor está en una pantalla privada; su manejo de errores actual puede mantener el indicador de carga ante 401/red/5xx. El diagnóstico informó 42 pruebas existentes de descargas aprobadas; esas pruebas no cubren específicamente `last-payroll`.

**Fuentes locales para releer antes de cambiar:**
- `ia-system-cloud/routes/api.php`
- `ia-system-cloud/app/Http/Controllers/PayrollController.php`
- `ia-system-cloud/app/Http/Middleware/Authenticate.php`
- `ia-system-cloud/app/Models/Payroll.php`
- `ia-system-web/src/pages/CAndRWizardPage.js`
- `ia-system-web/src/App.js` y `src/components/auth/PrivateRoute.js`
- `ia-system-cloud/docs/RESULTADO_ANALISIS_SEGURIDAD_LAST_PAYROLL_SMIAJAX.md` (si existe)

## 2. Objetivo

Cerrar la lectura anónima de `GET /api/last-payroll` **sin cambiar su contrato funcional para usuarios autenticados**, y garantizar una respuesta controlada ante errores de autenticación o red en el consumidor React.

## 3. Cambios autorizados

### A. Laravel: ruta protegida

Agregar `auth:sanctum` exclusivamente a `GET /api/last-payroll`. Mantener método HTTP, path, controlador y consulta existente. Se puede añadir un nombre explícito, por ejemplo `payrolls.last`, si es útil para reutilizar el tratamiento especial de 401, siempre que no cambie el path ni otros contratos:

```php
Route::get('/last-payroll', [PayrollController::class, 'lastPayroll'])
    ->middleware('auth:sanctum')
    ->name('payrolls.last');
```

No introducir filtros por roles, propiedad del registro o usuario. Con una sesión/token válido, cualquier usuario autenticado debe obtener el mismo resultado que antes. No modificar `PayrollController::lastPayroll()`, su ordenamiento por ID, ni el JSON salvo que una prueba exponga un problema indispensable; en ese caso, documentarlo y detener la ampliación del alcance.

### B. Laravel: error 401 consistente

Revisar la implementación real de `app/Http/Middleware/Authenticate.php`, en particular el tratamiento actual de las seis rutas protegidas de descarga. Incorporar `last-payroll` al mismo patrón de excepción **401 JSON** para solicitudes sin credenciales, con token inválido o revocado, tanto si `Accept` solicita JSON como HTML. Conservar intactos el mensaje y encabezados de seguridad que ya utilice ese middleware (por ejemplo `Cache-Control: private, no-store`, si corresponde). Evitar resolver una ruta web de login inexistente.

No cambiar globalmente los handlers de autenticación ni la semántica de otras API. Mantener las seis rutas documentales existentes sin regresiones.

### C. React: gestión de errores del consumidor

En `src/pages/CAndRWizardPage.js`, cambiar **solo lo necesario** en `getLastPayroll` para que una respuesta 401, error de red o 5xx no deje el `Backdrop`/indicador de carga permanente. Usar un cierre garantizado de carga (`finally` o equivalente), compatible con las demás operaciones de la pantalla. Proporcionar feedback discreto y coherente con la UI actual; **no** añadir botones redundantes, cambios visuales ajenos al error, ni exponer información sensible al usuario.

Conservar endpoint, método GET, Axios/interceptor Bearer, formato de la lista y selección de `id`, `suffix_id` y `end_date`. No transformar esta consulta JSON en descarga Blob. No modificar `PrivateRoute` en esta fase; registrar por separado cualquier hallazgo sobre su render inicial.

## 4. Límites obligatorios

- No tocar `.env`, credenciales, archivos de producción, servidores, dominios ni configuración Nginx.
- No ejecutar conexiones o comandos SQL contra una BD real, migraciones, seeders ni acciones financieras reales.
- No modificar rutas de facturas, solicitudes, planillas, cheques ni reportes, excepto comprobaciones de no regresión.
- No alterar datos de planillas, su numeración, el cálculo de totales ni la regla `status != 'cancelled'` y `orderBy('id', 'desc')`.
- No ejecutar `git push`, merge remoto, despliegue, SSH a DigitalOcean, ni reiniciar servicios.
- No realizar arreglos de ESLint, dependencias o refactorizaciones no vinculadas con el objetivo.
- Preservar cualquier cambio preexistente. Inspeccionar `git status` de ambos repositorios antes de editar; no usar `git reset --hard`, `git clean` ni restauraciones masivas.

## 5. Validaciones requeridas

**Backend, usando entorno de prueba aislado con datos sintéticos (SQLite `:memory:` o equivalente):**

1. Petición anónima con planilla existente: **401 JSON**, sin datos financieros.
2. Petición anónima sin planilla: **401 JSON**, no 200/null.
3. `Accept: application/json` y `Accept: text/html`: mismo resultado 401 JSON sin redirección ni excepción por ruta `login` inexistente.
4. Token inválido y revocado: 401 JSON sin revelar planilla; token vencido si el proyecto contempla expiración.
5. Usuario A autenticado: resultado compatible con el anterior, incluyendo campos y estructura esperados.
6. Usuario B autenticado: misma selección global aun si el registro corresponde a A; no imponer ownership nuevo.
7. Varios IDs/estados/fechas: se devuelve el mayor ID entre registros no cancelados, independientemente de la fecha.
8. Tabla vacía o todas las planillas canceladas: caracterizar y mantener comportamiento previo de la respuesta autenticada.
9. Verificar que la lectura no modifica registros ni genera archivos o documentos.
10. Confirmar middleware efectivo de la ruta con `php artisan route:list --path=last-payroll -v` **si PHP/dependencias están disponibles**.
11. Reejecutar las pruebas pertinentes a las seis descargas seguras y demás tests relacionados con las modificaciones del middleware; sin usar información real.

**Frontend:**

1. Con token válido, el asistente carga el selector como antes.
2. Con 401 o token inválido, la carga termina y muestra manejo controlado.
3. Con error de red/5xx, la carga termina sin dejar el Backdrop bloqueado.
4. Con respuesta vacía, el selector mantiene comportamiento coherente sin fallos.
5. Ejecutar los tests focalizados disponibles y, si el entorno lo permite, compilación de producción; reportar advertencias existentes por separado.

Crear o adaptar **pruebas automatizadas** para la nueva protección. Evitar pruebas que dependan de BD productiva. Si una prueba no puede ejecutarse por falta de herramientas o dependencias, indicarlo explícitamente: no declarar PASS sin ejecución.

## 6. Flujo de trabajo de Codex

1. Verificar estructura real de ambos proyectos y `git status` inicial; leer diagnóstico y código afectado. Si las rutas o archivos han cambiado desde el informe, señalar la diferencia antes de continuar.
2. Implementar cambios mínimos A, B y C.
3. Incorporar pruebas enfocadas y ejecutar validaciones disponibles; corregir solo regresiones originadas por esta fase.
4. Inspeccionar `git diff --check`, diff final y `git status` de cada repositorio.
5. Entregar un informe Markdown `docs/RESULTADO_CODEX_05_PROTECCION_LAST_PAYROLL_SMIAJAX.md` en el repositorio backend, sin modificar los informes previos, con archivos cambiados, decisiones, pruebas ejecutadas y sus resultados, riesgos pendientes e instrucciones de despliegue **solo documentadas**, no ejecutadas.
6. No hacer commit ni push salvo autorización explícita posterior.

## 7. Criterios de aceptación

- El endpoint exige Sanctum de forma verificable.
- Los visitantes anónimos y tokens inválidos reciben 401 JSON incluso con `Accept: text/html`.
- No se revela la última planilla a solicitudes no autenticadas.
- La funcionalidad para usuarios autenticados conserva URL, payload, consulta y selección original.
- React termina el estado de carga en errores 401/red/5xx sin romper el asistente.
- Pruebas de autenticación añadidas y ejecutadas, o limitaciones documentadas fielmente.
- No hay regresiones detectadas en descargas de CODEX 02–04.
- No hubo cambios en producción, credenciales, BD real ni respaldos.

**Entrega esperada:** código local implementado en ambos proyectos cuando corresponda, pruebas y archivo de resultado CODEX 05. La aprobación para publicar se realizará aparte tras revisar el informe.
