# CODEX 01 — Auditoría de descargas y autenticación de SMI AJAX

## Contexto
Workspace de VS Code con dos proyectos: `ia-system-cloud` (backend Laravel 10, API Sanctum) y `ia-system-web` (frontend). Existe un análisis estático previo en `ia-system-cloud/ANALISIS_PROYECTO.md` (si está disponible), que debe tratarse como hipótesis/documentación previa y corroborarse contra el código actual.

**Regla de negocio confirmada:** todos los usuarios debidamente autenticados del portal administrativo pueden visualizar y descargar todos los documentos. No se requiere autorización diferenciada por propietario, rol o usuario para estos documentos. Un usuario no autenticado nunca debe poder obtenerlos por URL directa.

## Objetivo de esta fase
Realizar una **auditoría estrictamente de solo lectura** de los dos proyectos para reconstruir el flujo completo de las descargas de documentos (facturas, solicitudes, planillas, cheques, preliminares y reportes) desde la acción de UI hasta la respuesta HTTP y el archivo generado/servido. Identificar riesgos y opciones de implementación que preserven el comportamiento actual, especialmente abrir PDFs en otra pestaña.

## Restricciones obligatorias
- NO modificar, crear, borrar, formatear ni mover archivos de ninguno de los dos proyectos durante la auditoría, salvo el informe de resultados solicitado abajo.
- NO cambiar rutas, middleware, configuración, dependencias, autenticación, bases de datos ni servicios.
- NO ejecutar migraciones, seeders, instalaciones, despliegues, peticiones a producción ni comandos con efectos sobre datos.
- NO leer ni mostrar secretos de `.env`, claves, tokens reales o datos personales; indicar solo los nombres de variables relevantes.
- No suponer que todos los enlaces necesitan la misma solución; estudiar cada flujo.
- No implementar una solución ni elaborar código de cambio en esta fase.
- No proponer S3 como requisito: el almacenamiento actual puede mantenerse.

## Auditoría solicitada
1. **Inventario backend:** buscar todas las rutas que generan, descargan, visualizan o exponen PDFs/archivos, incluyendo `routes/api.php`, controladores, vistas, `storage`, `public` y configuración relevante. Para cada endpoint registrar método, ruta, middleware efectivo, controlador/método, origen del archivo, Content-Type, Content-Disposition y comportamiento de errores; distinguir evidencia del código y supuestos.
2. **Inventario frontend:** localizar cada acción que abre/descarga un documento (links, `window.open`, `location.href`, `fetch`, Axios, `blob`, `URL.createObjectURL`, etc.). Identificar la URL exacta que construye, método, cliente HTTP, envío de Authorization Bearer/cookies, uso de nueva pestaña y manejo de errores/revocación de URLs blob.
3. **Autenticación real:** rastrear login, almacenamiento y uso del token Sanctum, interceptores, encabezados, expiración/revocación, protección de rutas y cómo se comportaría una navegación de pestaña nueva frente a una solicitud Axios/fetch autenticada. No mostrar tokens ni credenciales.
4. **Mapeo extremo a extremo:** relacionar cada botón/acción visible con su endpoint y documento; listar rutas huérfanas/no utilizadas y casos donde no puede confirmarse el vínculo.
5. **Acceso no autenticado:** determinar, desde rutas/middleware/código, cuáles endpoints parecen públicos y si los documentos podrían estar expuestos directamente bajo `/public` o almacenamiento servido por web. No probar accesos externos reales.
6. **Opciones a evaluar (sin elegir prematuramente):**
   - Descargar con `fetch`/Axios autenticado y abrir un `Blob` en nueva pestaña.
   - URLs temporales con token opaco de un solo uso o vencimiento corto, vinculadas a un usuario autenticado al emitirlas, si las restricciones de UX/infraestructura lo justifican.
   - Cookies de sesión seguras únicamente si encajan con la arquitectura actual; no asumir que Sanctum Bearer equivale a autenticación de navegador en enlaces normales.
   Comparar complejidad, seguridad, expiración, experiencia de usuario, cambios frontend/backend y efecto en PDF inline/descarga.
7. **Pruebas futuras recomendadas:** matriz de usuario autenticado, sin autenticar, token expirado/revocado, abrir en nueva pestaña, recargar/compartir enlace, tamaños de PDF, errores y regresión de los principales documentos.

## Entregable único
Crear en la raíz del workspace (o, si la herramienta solo permite dentro de un proyecto, en `ia-system-cloud/docs/`) un informe Markdown llamado `AUDITORIA_DESCARGAS_SMI_AJAX.md` con:
- Resumen ejecutivo breve.
- Tabla de endpoints backend con referencias `ruta:línea`.
- Tabla de consumos frontend con referencias `ruta:línea` y correspondencia backend.
- Diagrama Mermaid del flujo actual.
- Hallazgos clasificados por criticidad y grado de certeza.
- Comparación objetiva de alternativas; propuesta preferente **condicionada a evidencia**, sin implementación.
- Plan de pruebas y lista exacta de archivos que habría que modificar en una fase posterior.
- Sección “No confirmado / información faltante”.

**Importante:** el informe es el único archivo que se permite crear. Al finalizar, mostrar `git status --short` de ambos repositorios (sin modificar nada) y explicar cualquier archivo que ya estuviera modificado antes de la auditoría. No ejecutar commits.
