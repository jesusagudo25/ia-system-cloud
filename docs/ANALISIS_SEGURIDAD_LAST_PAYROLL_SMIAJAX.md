# SMIAJAX — Análisis de seguridad del endpoint `last-payroll`

**Proyecto:** `ia-system-cloud` (Laravel 10) / `ia-system-web` (React)  
**Contexto:** revisión posterior al despliegue de CODEX 04 (`853e628`)  
**Alcance:** análisis estático de la declaración de rutas compartida; **no se ha inspeccionado todavía la implementación de `PayrollController::lastPayroll` ni el consumidor React**.  
**Estado:** hallazgo por validar, sin cambios autorizados en producción.

## 1. Hallazgo confirmado

En `routes/api.php` se observa:

```php
Route::get('/last-payroll', [PayrollController::class, 'lastPayroll']);
```

La ruta está **fuera** del grupo `Route::middleware('auth:sanctum')->group(...)` y no declara `auth:sanctum` de forma individual. En la salida anterior de `php artisan route:list --path=api` figura `GET|HEAD api/last-payroll`.

**Implicación confirmada del código mostrado:** la declaración de esa ruta no exige autenticación Sanctum explícitamente. **No está confirmado todavía** que una solicitud anónima pueda obtener datos: podrían existir controles en el controlador, middleware global o filtros adicionales. Tampoco sabemos qué datos devuelve ni si utiliza información sensible.

## 2. Riesgo potencial

Si `lastPayroll` responde con datos internos de planillas, identificadores o numeraciones, y no existe otro control de acceso efectivo, un cliente anónimo podría consultarlos. El riesgo dependerá de la respuesta real, las reglas de autorización y el uso funcional del endpoint.

**Clasificación provisional:** posible exposición de endpoint sin autenticación. Severidad pendiente de inspección; no asignar criticidad ni afirmar fuga de datos sin evidencia.

## 3. Comprobaciones obligatorias (solo lectura)

En el backend, dentro de una copia de trabajo o en producción **sin editar**:

```bash
cd /var/www/ia-system-cloud
php artisan route:list --path=last-payroll -v
grep -Rni --exclude-dir=vendor --exclude-dir=node_modules 'function lastPayroll\|lastPayroll' app routes
```

Revisar en `PayrollController::lastPayroll`:

- Qué devuelve exactamente, con qué parámetros y qué tablas consulta.
- Si aplica autenticación/autorización propia (`auth()`, `request()->user()`, policies, gates o middleware de controlador).
- Si permite modificar datos o únicamente consulta.
- Cómo responde sin usuario (401/403, error, datos o redirección).

En la copia de código fuente React de LEGION, localizar las llamadas:

```bash
grep -Rni --exclude-dir=node_modules --exclude-dir=build 'last-payroll' src
```

Revisar si se invoca con token Bearer/Sanctum, en qué pantalla o flujo, y si depende de un resultado antes de iniciar sesión.

**Evitar pruebas sobre registros reales o difusión de respuestas con información sensible.** Una prueba HTTP anónima puede diseñarse cuando conozcamos el comportamiento y se autorice; comprobar que devuelve 401 no basta para concluir que todo el control de acceso es correcto.

## 4. Propuesta de corrección mínima (condicionada)

**Solo si** se confirma que el endpoint debe ser exclusivo de usuarios autenticados y que el frontend ya envía credenciales válidas:

```php
Route::get('/last-payroll', [PayrollController::class, 'lastPayroll'])
    ->middleware('auth:sanctum');
```

Esto conserva la URL actual `GET /api/last-payroll` y reduce el alcance del cambio. Si además requiere un perfil o permiso específico, agregar autorización por rol/policy según las reglas reales, no solamente autenticación.

Implementar **en repositorio local**, con pruebas, revisión y despliegue controlado; **no editar directamente la ruta en producción**.

## 5. Pruebas de regresión esperadas

1. Sin token: devuelve 401 (salvo que exista una regla funcional documentada distinta).
2. Con token válido y permiso: mantiene respuesta y formato previo.
3. Con token válido pero sin permiso (si existe autorización de negocio): 403.
4. Token vencido o inválido: no entrega datos.
5. Flujo React consumidor: funciona con su mecanismo de autenticación actual, sin regresiones.
6. Confirmar que los demás endpoints y descargas protegidas de CODEX 02–04 siguen operando.

## 6. Resultado y decisión

- **Confirmado:** la ruta está declarada sin `auth:sanctum` explícito y fuera del grupo protegido.
- **No confirmado:** ausencia total de seguridad, filtración de datos, sensibilidad de la respuesta o necesidad de acceso público.
- **Pendiente:** examinar el controlador, la llamada React y los middleware efectivos.
- **Recomendación:** aplicar el cambio mínimo anterior **solo después** de confirmar los puntos pendientes y documentar el resultado de las pruebas.

## 7. Instrucción para Codex

> Analiza `GET /api/last-payroll` de SMIAJAX en los repositorios `ia-system-cloud` y `ia-system-web`. Identifica su implementación, dependencias, consultas, datos retornados, middleware efectivos y consumidores React. Determina con evidencia si un usuario no autenticado puede acceder y qué impacto tendría. No asumas que la ruta es pública únicamente por estar fuera del grupo Sanctum: comprueba las capas restantes. Propón el cambio mínimo compatible, conservando la URL, y las pruebas necesarias. **Primera fase: solo diagnóstico y documento de resultados, sin modificar código, bases de datos ni producción.** No ejecutar acciones destructivas.
