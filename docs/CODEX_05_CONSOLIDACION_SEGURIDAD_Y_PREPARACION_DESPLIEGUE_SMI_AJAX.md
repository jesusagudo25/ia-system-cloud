# CODEX 05 — Consolidación de descargas seguras y preparación del despliegue | SMI AJAX

**Estado:** contrato de trabajo para Codex. **No autoriza despliegue.**  
**Repositorios:** `ia-system-cloud` (Laravel 10) y `ia-system-web` (React).  
**Antecedentes:** CODEX 01 (auditoría), CODEX 02 (facturas, validada por el propietario técnico), CODEX 03 (solicitudes, planillas y cheques, validada), CODEX 04 (reportes privados, validada por el propietario técnico).

## Objetivo

Consolidar y auditar la implementación conjunta de las fases 02–04, comprobar que no quedan rutas de descarga documental anónimas ni accesos estáticos indebidos, e identificar exactamente qué se deberá desplegar en el backend, frontend y servidor. **No volver a implementar las fases anteriores ni cambiar la lógica de negocio.**

## Regla de negocio inalterable

Cualquier usuario **autenticado** del portal administrativo puede ver y descargar **cualquier** documento. Un visitante sin autenticación no puede obtener documentos por enlace directo, ID conocido, archivo público o nombre de reporte. **No introducir permisos por propietario, roles ni restricciones documentales individuales.**

## Límites y seguridad del trabajo

1. Primero inspeccionar los cambios locales reales, los informes de resultado y el estado Git de ambos repositorios. No asumir que un archivo existe o que un cambio fue realizado sin verificarlo.
2. No modificar producción, no conectar ni escribir sobre bases de datos reales, no ejecutar migraciones/seeders, no borrar documentos existentes, no tocar credenciales o `.env`, no ejecutar deploy ni commits/push.
3. No actualizar PHP, Laravel, React, Composer, npm, Node ni dependencias. No cambiar flujos financieros, cálculos, numeración, plantillas PDF o autenticación general fuera del alcance.
4. El usuario realiza las pruebas funcionales finales. Codex puede ejecutar verificaciones automáticas **locales, no destructivas**, solo si el entorno está disponible y sin depender de datos sensibles; documentar lo no ejecutado sin simular aprobaciones.
5. Si se descubre un problema significativo, documentar el hallazgo y la propuesta de corrección; **no aplicar cambios funcionales nuevos sin aprobación**. Correcciones menores solo si son estrictamente necesarias para la consolidación y su alcance está claro; de otro modo dejar pendientes.

## Trabajo requerido

### A. Inventario final extremo a extremo

- Comparar las **seis familias de endpoints** originalmente auditadas con las rutas Laravel actuales: factura; planilla; cheques definitivos; solicitud; cheques preliminares; reporte.
- Confirmar middleware efectivo `auth:sanctum`, respuesta de acceso denegado sin PDF, y que ninguna ruta alternativa duplique la descarga sin autenticación.
- Comprobar las **15 acciones de descarga** del frontend identificadas originalmente y su integración con el utilitario Axios/Blob. No asumir que una acción está cubierta solo porque comparte URL; mapearla por pantalla y ubicación real.
- Verificar que las acciones preserven la apertura/impresión/guardado y que las respuestas JSON o HTML de error no se presenten como PDFs válidos.
- Comprobar que los reportes generados se almacenen de forma privada, posean nombres únicos y tengan una estrategia consistente de relectura, expiración y limpieza según lo efectivamente implementado en CODEX 04. No inventar comportamiento ni modificar la retención sin aprobación.

### B. Cierre de exposición pública

- Auditar las rutas de archivos estáticos y la configuración versionada de almacenamiento; verificar que el código nuevo no escriba reportes bajo `storage/app/public/reports` ni devuelva URLs públicas.
- Identificar dependencias de despliegue que no estén en Git: posibles archivos históricos bajo `storage/app/public/reports`, symlinks `public/storage`, `location`/aliases de Nginx, cachés de proxy/CDN y tareas programadas de limpieza.
- **No borrar, mover ni leer documentos productivos**. Proponer una lista de comprobaciones operativas para la ventana de despliegue y una estrategia segura para manejar históricos, conservando evidencia y respaldos.
- Revisar sin ampliar el alcance si `/api/last-payroll` sigue público; reportarlo como hallazgo adyacente, no mezclar su corrección con esta entrega.

### C. Revisión de regresión y consistencia

- Revisar `git diff`, `git status`, archivos nuevos y cambios inesperados en ambos repositorios. Separar cambios de las fases 02–04 de archivos previos no relacionados.
- Validar que facturas, solicitudes, planillas, cheques definitivos/preliminares y reportes respeten la misma política de error, carga y gestión de pestañas/Blob.
- Revisar específicamente el modal de planillas previamente documentado: **registrar su situación, sin corregir su creación ni ejecutar escrituras de prueba**.
- Realizar `git diff --check` y pruebas estáticas o aisladas disponibles. Si PHP, Composer, node_modules u otros requisitos faltan, reportarlo; no instalar paquetes ni modificar dependencias solo para producir una marca de PASS.

### D. Plan de despliegue — solo documento

Preparar una secuencia **ordenada, reversible y específica** que incluya:

1. Precondiciones: aprobación del usuario, respaldos verificados (snapshot y respaldo de datos si corresponde), ventana de mantenimiento, inventario y estado Git, ramas/commits que se decida publicar.
2. Qué se publicará en backend y frontend y cómo evitar un período en que se proteja el backend mientras el frontend siga abriendo enlaces antiguos sin Bearer. Proponer una secuencia controlada o ventana única, sin ejecutarla.
3. Configuración operativa necesaria para directorios privados de reportes, permisos, limpieza, TTL, Nginx y cache, distinguiendo lo confirmado en código de lo que debe comprobarse en el Droplet.
4. Comandos o procedimientos de verificación **propuestos** con resultados esperados, sin ejecutarlos en producción. No incluir valores secretos ni URLs que expongan tokens.
5. Rollback coordinado frontend/backend y tratamiento seguro de archivos generados en la nueva ubicación. No suponer que revertir código por sí solo restaura datos borrados.
6. Lista de riesgos residuales fuera del alcance (recuperación de contraseñas, validación de tokens en URL, token lifetime, NodeSource/actualizaciones del SO, espacio de disco), distinguiendo los que podrían bloquear la publicación.

## Matriz de aceptación para entregar al usuario

Presentar una tabla con cada una de las 15 acciones originales: pantalla, documento, endpoint, estado backend, estado frontend, resultado de validación automática local (si existe), resultado manual **pendiente de usuario** o **confirmado por el usuario en fases previas**, y observaciones.

Incluir pruebas propuestas sin efectuarlas a nombre del usuario: usuario autenticado A, usuario autenticado B sobre documento de A, anonimato/incógnito, token inválido, enlace directo, reportes concurrentes con filtros diferentes, reapertura antes/después de TTL, visualización/impresión/guardado en nueva pestaña y control de acceso a archivos estáticos.

## Entregables

Crear un informe Markdown en `ia-system-cloud/docs/RESULTADO_CODEX_05_CONSOLIDACION_Y_PREPARACION_DESPLIEGUE.md`, con:

- Resumen ejecutivo: **listo / listo con pendientes / no listo para desplegar**, con evidencia y sin dar por aprobadas pruebas no ejecutadas.
- Inventario trazable de rutas y 15 acciones; archivos y líneas relevantes.
- Verificación de almacenamiento privado y superficie de exposición estática, con límites del análisis local.
- Estado Git de ambos repositorios, comandos de validación ejecutados y resultados.
- Checklist operativo de despliegue y rollback, **sin desplegar**.
- Pendientes y riesgos clasificados como bloqueantes/no bloqueantes, y decisiones que debe tomar el usuario.

## Criterio de cierre

CODEX 05 termina con el **informe de consolidación y preparación**. No crear commits ni publicar nada. El usuario evalúa el informe y decide personalmente la fecha, las pruebas finales y si autoriza una fase posterior específica de despliegue.
