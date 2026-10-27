# CODEX 04 — Seguridad de reportes PDF y almacenamiento privado | SMI AJAX

**Estado:** Contrato de implementación local para Codex.  
**Proyectos:** `ia-system-cloud` (Laravel 10) y `ia-system-web` (React).  
**Precedentes:** CODEX 01 (auditoría), CODEX 02 (facturas, validada por el responsable), CODEX 03 (solicitudes, planillas y cheques, validada por el responsable).  
**Fuera de alcance:** despliegue productivo, commits automáticos, cambios de infraestructura y modificaciones de los demás flujos documentales.

## 1. Objetivo y regla de negocio

Corregir el acceso público a reportes PDF y la gestión insegura de sus archivos **sin cambiar las reglas de cálculo, filtros ni diseño de los reportes**.

**Regla innegociable:** cualquier usuario autenticado mediante Laravel Sanctum puede consultar cualquier reporte; ningún usuario anónimo debe poder obtener documentos. **No implementar permisos por propietario, roles ni usuario generador.**

Se conserva la experiencia del portal administrativo: generar reporte desde React y visualizarlo, imprimirlo y guardarlo desde el navegador utilizando el utilitario de descarga autenticada ya implementado en CODEX 02/03.

## 2. Contexto confirmado por auditoría

- `POST /api/reports/generate` ya pertenece al grupo `auth:sanctum`.
- `GET /api/reports/{report}/download` es la sexta ruta documental pendiente: actualmente pública.
- `ReportController::generate` selecciona cuatro clases de reporte PDF. Su salida incluye `file_name` y `file_path`; React lo consume en `src/pages/ReportPage.js` y muestra un enlace de apertura en otra pestaña.
- Los PDFs se guardan en `storage/app/public/reports/`, con nombres predecibles basados en tipo y fechas, que pueden colisionar si cambian otros filtros o usuarios.
- `ReportController::pdf($file_name)` resuelve una ruta de archivo y utiliza `deleteFileAfterSend(true)`, por lo que abrir/reintentar puede fallar y se pueden producir condiciones de carrera.
- Puede existir una vía de exposición directa `/storage/reports/{nombre}` si el despliegue publica `storage/app/public`; la auditoría no verificó si el alias/symlink existe en producción.
- `src/pages/ReportPage.js` actualmente abre `file.file_path` sin Bearer; el flujo no aprovecha el utilitario Axios/Blob autenticado.

**Antes de editar:** verificar en el checkout actual todas estas rutas, contratos y nombres de función. Si hubo cambios, adaptar el diseño y documentar diferencias. Consultar `ANALISIS_PROYECTO.md`, `docs/AUDITORIA_DESCARGAS_SMI_AJAX.md`, los resultados CODEX 02/03 y el utilitario real de descargas. No asumir que los informes representan exactamente el árbol actual.

## 3. Alcance obligatorio

### 3.1 Backend: autenticación en descarga

1. Proteger **solo** `GET /api/reports/{report}/download` con `auth:sanctum`, siguiendo el patrón ya probado en las cinco rutas de CODEX 02/03.
2. Reutilizar el contrato de **401 JSON** para solicitudes no autenticadas, incluidas solicitudes directas sin token. Mantener `POST /api/reports/generate` protegido.
3. Ningún dato del token se añade a rutas, query strings, logs o nombre de archivo.
4. Responder con `application/pdf` adecuado y encabezados preventivos de caché (`private, no-store` según compatibilidad con visor). Gestionar fallos como error controlado, no como PDF falso.

### 3.2 Backend: almacenamiento privado e identificación única

1. Guardar nuevas generaciones en un directorio **privado** del almacenamiento local Laravel, fuera de `public/`, `storage/app/public/` y cualquier ruta/alias servido directamente por Nginx.
2. Introducir un identificador opaco/único por **generación** (p. ej. UUID), de modo que dos reportes del mismo tipo y rango de fechas, pero distintos filtros, no se sobreescriban.
3. Mantener un contrato claro entre respuesta de `generate` y acción `download` del frontend; no introducir una URL que por sí sola conceda acceso anónimo.
4. Validar de forma estricta el identificador solicitado y resolverlo únicamente dentro del área privada de reportes. Bloquear nombres arbitrarios, extensiones no PDF, rutas absolutas, `..` y variantes codificadas. No utilizar concatenación insegura del input HTTP con rutas del sistema.
5. Manejar inexistencia, expiración o falta de lectura con una respuesta segura y comprensible (404/410 según política justificada), sin exponer rutas internas ni secretos.
6. No usar `user_id` o el creador como filtro de autorización para descargar: ambos usuarios autenticados deben poder acceder a una generación existente.
7. No introducir migraciones/tablas nuevas salvo necesidad demostrada. Si se necesita metadato para identificar archivos, evaluar primero una solución local sencilla y documentar durabilidad y concurrencia.

### 3.3 Retención y reintentos

- **Política base propuesta para implementación local:** permitir reapertura/reintento del PDF generado durante un período de **24 horas desde su creación**; no eliminarlo automáticamente tras el primer GET. Es una decisión técnica provisional, no una política del cliente confirmada: aislarla en configuración para modificarla fácilmente.
- Implementar limpieza segura de artefactos caducados sin depender de que el usuario descargue, reutilizando el scheduler existente si está disponible, **sin borrar archivos históricos ni ejecutar limpieza destructiva en producción**.
- Evitar cron/persistencia complejos si no aportan valor. No afirmar que el scheduler de producción está activo: documentar cómo verificarlo y las consecuencias si no se ejecuta.
- La limpieza solo debe afectar al nuevo directorio privado administrado por esta fase. Nunca borrar indiscriminadamente archivos de `storage/app/public/reports/`.

### 3.4 Frontend: visualización autenticada

1. Migrar **solo** el enlace de reportes en `ia-system-web/src/pages/ReportPage.js` al utilitario Axios/Blob existente. Respetar el interceptor Bearer.
2. Conservar las cuatro modalidades de generación, filtros y el comportamiento visual de la pantalla. Permitir abrir nueva pestaña, ver, guardar e imprimir PDF.
3. No interpretar JSON/HTML de error como PDF: verificar estado y tipo de contenido, mostrar mensaje útil y permitir reintentar sin regenerar siempre que el archivo aún sea válido.
4. No cerrar ni limpiar el modal prematuramente si falla la apertura. Reutilizar el patrón validado de ventanas, errores y liberación de Blob, sin duplicar código de CODEX 02/03.
5. No asumir que los enlaces generados en el backend son permanentes ni utilizarlos como navegación desprotegida.

## 4. Compatibilidad y migración

- Mantener el contrato de respuesta de generación tanto como sea razonablemente posible; si `file_path` cambia, actualizar el único consumidor y documentar el cambio explícitamente.
- **Los reportes previos** que pudieran existir bajo `storage/app/public/reports/` deben quedar inventariados con una propuesta de migración/retirada del acceso público, **sin moverlos ni eliminarlos automáticamente**. Señalar que cerrar la API no basta si el servidor los sigue exponiendo por URL estática.
- No modificar plantillas Blade ni cálculos, rangos, SQL, campos financieros, PDFs de facturas, planillas, solicitudes o cheques.
- No instalar nuevas dependencias ni cambiar versiones de framework sin necesidad aprobada.

## 5. Pruebas exigidas a Codex en entorno local seguro

Agregar pruebas automatizadas enfocadas y deterministas (filesystem fake/temporal y datos sintéticos; no tocar información real):

1. Descarga anónima de reporte → 401 JSON, cero bytes PDF.
2. Token inválido → rechazo; token válido → descarga PDF.
3. Usuario A genera y usuario B, autenticado, descarga el mismo reporte → permitido.
4. Mismo tipo/fechas con filtros diferentes y doble generación → identificadores y contenido separados, sin colisiones.
5. ID inexistente, malformado, nombre con `..` o no PDF → error seguro, sin salir del directorio privado.
6. Reintento/segunda descarga dentro de retención → PDF disponible; expiración → rechazo y limpieza confinada.
7. Enlaces/archivos nuevos no se crean en rutas públicas ni se pueden servir por bypass estático del directorio habitual.
8. Frontend maneja éxito `application/pdf`, 401, 404, 429, 500, JSON/HTML inesperado, bloqueo de popup y reintento sin perder los filtros.
9. Regression mínima de las cinco rutas documentales de CODEX 02/03 sin modificarlas.

**Responsabilidad de validación funcional:** el usuario ejecutará manualmente las pruebas en navegador y dará el visto bueno de la fase. Codex puede ejecutar pruebas automatizadas disponibles, pero **no declarar la fase cerrada por sí solo**. Si no hay PHP, dependencias o entorno para correr suites, señalarlo honestamente, no instalar ni desplegar sin autorización.

## 6. Restricciones y seguridad operativa

- Trabajar solo en el workspace local; **prohibido desplegar**, conectarse al servidor productivo, ejecutar SQL/migraciones sobre producción o usar documentos/datos reales.
- Antes de editar: `git status` en ambos repositorios; respetar cambios previos, sin revertir ni sobrescribir trabajo del usuario.
- Cambios quirúrgicos: no refactorizar login, administración de usuarios, permisos/roles, lógica financiera ni otros módulos.
- No crear commits, tags ni pushes por cuenta propia.
- Evitar cambios en la configuración Nginx real: solo documentar validaciones de exposición estática necesarias al desplegar.
- Si la política de 24 horas o la estrategia de limpieza impacta dependencias existentes, preservar seguridad por defecto, explicar opciones y dejarla configurable.

## 7. Entregables

1. Código local de backend/frontend estrictamente relacionado con reportes.
2. Pruebas automatizadas aisladas apropiadas y resultados verificables, indicando las que no pudieron ejecutarse.
3. Archivo `ia-system-cloud/docs/RESULTADO_CODEX_04_REPORTES_PRIVADOS_SMI_AJAX.md` que incluya:
   - Diagnóstico breve y diseño elegido.
   - Inventario exacto de archivos modificados y motivo.
   - Contratos HTTP antes/después para generar/descargar, MIME/401/404.
   - Ruta privada y esquema de identidad de generación.
   - Retención, tareas de limpieza y cómo se controla su ejecución.
   - Compatibilidad de frontend e impacto en reportes históricos.
   - Evidencias/resultados de pruebas y **pendientes de validación manual por el usuario**.
   - `git status`, `git diff --check` y guía de rollback, sin borrar artefactos históricos.
   - Checklist de despliegue futuro (solo informativo).
4. Presentar resumen final y marcar estado **IMPLEMENTADA LOCALMENTE — PENDIENTE DE VALIDACIÓN DEL USUARIO**.

## 8. Criterio de aceptación funcional del responsable

Solo el usuario puede cerrar la fase tras comprobar en navegador: generar las cuatro clases de reporte, abrir nueva pestaña, descargar e imprimir, volver a abrir dentro de la ventana de retención, errores claros, y rechazo anónimo por API y por posible URL estática. Hasta entonces no afirmar que CODEX 04 está completada funcionalmente.
