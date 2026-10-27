# CODEX 03 — Descargas seguras de solicitudes, planillas y cheques | SMI AJAX

**Tipo:** Contrato de implementación local para Codex  
**Estado:** Pendiente de ejecución  
**Proyectos del workspace:** `ia-system-cloud` (Laravel 10, Sanctum) y `ia-system-web` (React)  
**Predecesor:** CODEX 02, protección de descargas de facturas, implementada y validada funcionalmente por el propietario del proyecto.

## 1. Objetivo

Extender **sin rediseñar** el patrón de descarga autenticada de facturas completado en CODEX 02 (Bearer + Axios + Blob/visor) a las descargas de **solicitudes, planillas y cheques**. Mantener su comportamiento de visualización, impresión y guardado.

**Regla de negocio invariable:** cualquier usuario autenticado del portal administrativo puede acceder a cualquiera de esos documentos, sin restricciones por creador, propietario ni rol. Un usuario sin autenticación válida no debe poder obtener bytes PDF desde los endpoints protegidos.

## 2. Alcance exacto

### Backend — cinco rutas GET que actualmente requieren protección

Revisar primero `ia-system-cloud/routes/api.php` y aplicar el patrón ya adoptado en CODEX 02 a:

| Documento | Ruta | Método actual | Observación |
|---|---|---|---|
| Planilla | `GET /api/payrolls/{payroll}/download` | `PayrollController::pdf` | PDF quincenal definitivo |
| Cheques definitivos | `GET /api/bank-checks/{payroll}/download` | `BankCheckController::pdf` | `{payroll}` es ID de planilla |
| Solicitud | `GET /api/requests/{request}/download` | `RequestController::pdf` | PDF quincenal preliminar |
| Cheques preliminares | `GET /api/bank-checks-preview/{request}/download` | `BankCheckPreviewController::pdf` | `{request}` es ID de solicitud |

**Nota:** Son **cuatro** rutas de esta fase, no cinco. La quinta descarga aún pendiente del inventario original es **reportes**, expresamente excluida de CODEX 03 y reservada para CODEX 04. Las seis rutas originales se distribuyen así: facturas (CODEX 02), cuatro rutas aquí (CODEX 03), reportes (CODEX 04).

- Aplicar `auth:sanctum` a las cuatro rutas, siguiendo exactamente el estilo y manejo de `401` JSON empleado en la descarga de facturas.
- Preservar route-model binding y los parámetros/semántica actuales.
- No tocar renderizadores, vistas Blade, cálculos financieros, filtros, estados de solicitud/planilla, tamaño u orientación del PDF salvo defecto demostrado que impida la protección; documentar cualquier desviación antes de ejecutarla.
- Los métodos de planilla y solicitud pueden devolver **HTTP 201 con JSON `success=false`** cuando fallan reglas de rango de fecha. El cliente de descargas debe reconocer esos casos como error, jamás abrirlos como PDF válido. Dar prioridad a un ajuste mínimo del contrato de error solo si es necesario, conservando la semántica funcional y dejando evidencias.

### Frontend — diez enlaces de esta fase

Revisar y migrar al **mismo utilitario de descarga autenticada** creado en CODEX 02, sin duplicar código:

- `ia-system-web/src/pages/PayrollPage.js`: **4** acciones (planilla y cheques en listado y modal).
- `ia-system-web/src/pages/RequestPage.js`: **6** acciones (solicitud y cheques preliminares en listado por estado y modal).

Para cada acción conservar el ID correspondiente y el contexto (solicitud, planilla, preview), la posibilidad de abrir PDF en nueva pestaña, imprimir y guardar; mantener tratamiento visual coherente de carga y errores. Si una ruta del modal parece inalcanzable, migrarla sin reestructurar su lógica de creación y dejarlo señalado en el informe.

## 3. Exclusiones y protección del trabajo previo

- **No modificar** las cuatro acciones de factura ya verificadas en CODEX 02, excepto corrección imprescindible y justificada del utilitario compartido (con verificación de regresión de facturas).
- **No modificar** `ReportPage.js`, `ReportController.php`, almacenamiento de reportes, URLs públicas `/storage/...` ni el endpoint `GET /api/reports/{report}/download` (CODEX 04).
- No introducir roles, ownership, permisos individualizados, sesiones por cookies, URLs firmadas ni nuevos tokens temporales.
- No cambiar login, recovery, ciclo de tokens, dependencias, migraciones, datos, despliegue, Nginx ni DigitalOcean.
- No ejecutar operaciones de negocio sobre producción, incluidos POST/PUT de solicitudes o planillas; no utilizar documentos o datos reales sensibles para pruebas.
- No generar commits ni hacer push o deploy sin autorización.

## 4. Procedimiento de Codex

1. **Inspección inicial:** consultar `git status` de ambos repositorios, CODEX 02 implementado, `documentDownload.js` (o nombre efectivo), rutas y consumidores reales. **No sobrescribir ni descartar cambios existentes.**
2. **Plan mínimo:** enumerar archivos exactos a tocar y confirmar correspondencias de las cuatro rutas y diez puntos React.
3. **Backend:** añadir protección por Sanctum a esas rutas usando la solución de CODEX 02; reutilizar respuesta no autenticada de API/documentos.
4. **Frontend:** reemplazar los enlaces GET directos de esos diez puntos por el utilitario autenticado común; preservar accesibilidad y UX existente. Manejar respuestas no PDF, errores 401/404/429/500, apertura fallida de pestaña y limpieza de objetos Blob sin ruptura de impresión/guardado.
5. **Pruebas técnicas que pueda ejecutar Codex:** pruebas aisladas y estáticas disponibles, `git diff --check` y scripts ya existentes; usar Playwright MCP únicamente si hay entorno local aislado disponible y **sin introducir cambios ni datos de negocio**. Si el entorno no está listo, marcar pendientes sin instalar arbitrariamente nuevas herramientas.
6. **Entrega:** elaborar un informe Markdown local en `ia-system-cloud/docs/RESULTADO_CODEX_03_DESCARGAS_SOLICITUDES_PLANILLAS_CHEQUES.md` con cambios, matriz de rutas y acciones, pruebas ejecutadas/no ejecutadas, riesgos, estados Git y rollback.

## 5. Criterios de aceptación

- Las cuatro rutas de CODEX 03 requieren un token Sanctum válido; solicitudes anónimas y tokens inválidos reciben respuesta no autorizada sin contenido PDF.
- Cualquier usuario autenticado puede obtener cualquier solicitud, planilla y cheque, independientemente del creador.
- Los diez controles React usan el flujo Axios/Bearer/Blob, no navegación directa al endpoint sin encabezados.
- Se mantienen apertura, vista previa, impresión y descarga/guardado de PDF; se conservan contenidos y formatos de los documentos.
- No se interpreta como PDF una respuesta JSON, incluso con HTTP 2xx.
- Facturas (CODEX 02) no sufren regresión; reportes quedan intactos para CODEX 04.
- No se altera el negocio financiero ni se realizan cambios de producción.

**Validación funcional final:** la realiza **el usuario**, no se considera implícitamente aprobada por pruebas aisladas o por Codex. La fase se cierra únicamente después de su confirmación.

## 6. Evidencia mínima que debe entregar Codex

| Ítem | Evidencia esperada |
|---|---|
| Archivos modificados | Rutas y líneas relevantes en ambos repositorios |
| Seguridad | Middleware/401 documentados para cada ruta |
| UI | Los 10 enlaces migrados, agrupados por pantalla |
| Regresión | Confirmación de no alteración de facturas y reportes |
| Pruebas | Comandos, resultados, límites y pruebas manuales pendientes |
| Integridad | `git status` en ambos proyectos, `git diff --check`, ausencia de commit/deploy |
| Reversión | Archivos y pasos para revertir exclusivamente CODEX 03 sin destruir CODEX 02 |

## 7. Instrucción final a Codex

**Ejecuta CODEX 03 únicamente en el workspace local y dentro del alcance descrito.** Prioriza cambios mínimos, compatibilidad con CODEX 02 y trazabilidad. Si descubres un bloqueo que requiera rediseñar autenticación, modificar datos o tocar reportes, documenta el bloqueo y no amplíes el alcance por tu cuenta.
