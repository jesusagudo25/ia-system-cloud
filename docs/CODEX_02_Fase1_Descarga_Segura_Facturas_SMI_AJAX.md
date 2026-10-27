# CODEX 02 — Fase 1: descarga autenticada de facturas (SMI AJAX)

**Proyectos:** `ia-system-cloud` (Laravel 10 / Sanctum) y `ia-system-web` (React).  
**Modalidad:** implementación acotada y comprobable en checkout local. **No desplegar** ni ejecutar cambios en producción.

## Objetivo y regla de negocio

Proteger `GET /api/invoices/{invoice}/download` de modo que **solo un usuario autenticado con Sanctum pueda obtener el PDF**, manteniendo que **cualquier usuario autenticado puede descargar cualquier factura**, sin filtros por propietario, rol ni creador.

El usuario debe poder abrir, visualizar, imprimir y guardar la factura con una experiencia equivalente a la actual. Conservar la generación PDF existente y no alterar datos, plantillas ni reglas contables.

## Evidencia de partida

Auditoría previa: `ia-system-cloud/docs/AUDITORIA_DESCARGAS_SMI_AJAX.md` y `ANALISIS_PROYECTO.md`. Confirmar contra el código actual antes de editar:

- Backend: `routes/api.php` declara la descarga pública cerca de línea 179; `InvoiceController::pdf` produce PDF `inline` con Dompdf y model binding.
- Frontend: enlaces de factura en `src/pages/InvoicePage.js`, `src/pages/ServiceHistory.js` (dos estados) y `src/pages/NewServicePage.js` (al finalizar alta). Son enlaces `target="_blank"` y no transportan Bearer.
- Frontend: `src/App.js` configura interceptor Axios con token Sanctum desde `localStorage`.
- Los demás cinco endpoints de documentos aún no se modifican en esta fase: planillas, cheques definitivos, solicitudes, cheques preliminares y reportes.

## Restricciones estrictas

1. Antes de editar, revisar `git status` en **ambos repositorios**, identificar cambios existentes y preservarlos; no revertir ni sobrescribir trabajo ajeno. Revisar instrucciones de repo si existen.
2. Cambios mínimos; **no** actualizar Composer, npm, PHP, Laravel ni dependencias; no migraciones, cambios SQL, refactors generales, ni rediseño del login.
3. No leer ni mostrar valores de `.env`, claves, tokens, datos privados, PDFs reales ni información bancaria. No crear endpoints de diagnóstico públicos.
4. **No poner Bearer en URL/query string**, no usar URL firmada de canje anónimo, no cambiar a cookies/Sanctum stateful ni introducir restricciones por usuario.
5. No modificar los cinco endpoints de descarga restantes ni el sistema de reportes en esta fase. No modificar generación/plantillas de facturas salvo que surja un impedimento demostrado; reportarlo antes de ampliar alcance.
6. No tocar el servidor DigitalOcean, no desplegar, no ejecutar migraciones/seeders, ni pruebas que muten BD real o generen documentos con datos productivos.
7. Si falta entorno de pruebas, producir implementación + pruebas estáticas/aisladas que sean posibles y marcar expresamente lo no validado. No afirmar éxito de producción.

## Implementación requerida

### Backend — Laravel

- Mover o proteger **únicamente** `GET /api/invoices/{invoice}/download` mediante `auth:sanctum` efectivo. Conservar URL, método, model binding, respuesta PDF y `Content-Disposition: inline`.
- Garantizar que petición no autenticada a ese endpoint devuelva **401**, sin bytes PDF y sin depender de una ruta web `login` inexistente. Preferir una respuesta JSON clara para el contexto API, sin cambiar la conducta de otras rutas por accidente.
- Un token Sanctum válido debe permitir obtener **cualquier factura**, incluso creada por otro usuario.
- Revisar si procede `Cache-Control: private, no-store` en la respuesta PDF sin interferir con visor/impresión; documentar decisión.
- No alterar ni exponer información sensible en trazas.

### Frontend — React

- Crear un utilitario compartido de descargas autenticadas (p. ej. `src/utils/documentDownload.js`) usando el **Axios configurado que añade Bearer**, con `responseType: 'blob'` y `Accept: application/pdf`.
- En errores HTTP, o si se recibe JSON/HTML, **no** abrirlo como PDF: identificar respuesta no-PDF, recuperar mensaje sin exponer tokens y mostrar error útil al usuario.
- Abrir una pestaña/ventana a partir del gesto directo del usuario **antes de esperar la respuesta asíncrona**, para minimizar bloqueos; si el navegador la bloquea, ofrecer salida comprensible sin prometer apertura automática.
- Usar `Blob` / `URL.createObjectURL` de forma segura; diseñar liberación (`revokeObjectURL`) **sin invalidar prematuramente** la vista, impresión o guardado. Evitar fugas en repetidas descargas y pestañas vacías tras error. Preservar el comportamiento de nombre de archivo tanto como sea viable, documentando limitaciones del visor.
- Migrar **todas las acciones de factura** identificadas (InvoicePage, ServiceHistory paid/pending, NewServicePage), evitando alterar creación, navegación y lógica de negocio.
- No duplicar lógica por pantalla. Mantener consistencia UX con los componentes/mecanismos de mensajes ya usados en el proyecto. No agregar botones redundantes.
- Verificar el origen HTTPS y comportamiento CORS de la petición autenticada; no ampliar CORS sin evidencia de necesidad.

## Pruebas y aceptación

Implementar o preparar pruebas aisladas que demuestren:

1. GET anónimo → **401**, cero bytes de factura.
2. GET con token inválido/revocado → **401**.
3. GET con usuario autenticado A → PDF 200, `application/pdf`, contenido reconocible como PDF, disposición inline.
4. GET con usuario B autenticado para factura de A → **permitido**.
5. Factura inexistente, autenticado → 404 (sin filtrar documentos a anónimos).
6. Las cuatro situaciones de interfaz: listado facturas, historial paid, historial pending, factura creada. Documento visible, imprimible y guardable.
7. Errores 401, 403/404, 429, 500, desconexión y respuesta que sea JSON aunque tenga código 2xx: no abrir archivo inválido ni dejar ventanas vacías.
8. Varias descargas consecutivas y bloqueo de pop-ups: manejo consistente de ventanas y URLs Blob.
9. Verificar que los otros endpoints, sus rutas y el flujo de reportes **no fueron modificados**.
10. `git diff --check`, tests/build/lint existentes aplicables si se dispone de dependencias; no instalar ni modificar lockfiles sin permiso.

Usar fixtures sintéticos y entornos aislados; si no están disponibles, distinguir claramente pruebas ejecutadas, revisiones estáticas y pruebas manuales pendientes.

## Entregables de Codex

1. Resumen de implementación (qué cambió y por qué), con rutas y líneas.
2. Inventario de archivos creados/modificados, `git status` y `git diff --stat` por repositorio.
3. Matriz de aceptación con **PASS / FAIL / NO EJECUTADO** y evidencia concreta; no confundir revisión estática con verificación funcional.
4. Riesgos pendientes, especialmente visualización/impresión/descarga, gestión de Blob, CORS y comportamiento del servidor desplegado.
5. Guía de despliegue y rollback **documental**, sin ejecutar acciones en producción.
6. Guardar informe Markdown en `ia-system-cloud/docs/RESULTADO_CODEX_02_DESCARGAS_FACTURAS.md`. No crear commits ni desplegar.

**Criterio de cierre:** la fase solo se considera lista para despliegue tras probar sin credenciales y con credenciales, y verificar manualmente visualización, impresión y guardado de factura en navegador. La autorización de producción es una decisión posterior y separada.
