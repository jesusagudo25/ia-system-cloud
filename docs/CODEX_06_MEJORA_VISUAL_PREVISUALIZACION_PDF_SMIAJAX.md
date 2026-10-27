# CODEX 06 — Mejora visual del estado de carga de PDF en SMIAJAX

## Objetivo
Mejorar **únicamente la experiencia visual de la pestaña de espera** que muestra `openAuthenticatedDocument(path, filename)` mientras se descarga el PDF autenticado. La nueva presentación debe **heredar la identidad visual real del tema actual de SMIAJAX** (Material Kit React / componentes y tokens que ya existen), sin inventar una nueva paleta ni rediseñar el visor de documentos.

## Instrucciones para Codex
1. Ubica la implementación actual de `openAuthenticatedDocument` y sus consumidores en `ia-system-web`. Lee primero el tema vigente: `theme`, paleta, tipografías, espaciados, modos claro/oscuro, botones, iconografía y estados de carga utilizados en las pantallas actuales. **Reutiliza patrones existentes**; no asumas colores a partir de este documento.
2. Conserva el flujo original: llamada directa desde `onClick`, apertura inmediata de pestaña antes de cualquier `await`, validación de URL y mismo origen HTTPS, uso del singleton Axios e interceptor Bearer, `responseType: blob`, timeout, verificación `Content-Type` y firma `%PDF-`, creación/revocación del `ObjectURL`, visor PDF nativo mediante `iframe` y limpieza al cerrar la pestaña. **No relajes controles de seguridad** ni cambies rutas, nombres de archivo o contrato con Laravel.
3. Sustituye el texto plano `Loading document...` por un **estado de espera limpio y ligero, en español**, consistente con el diseño actual. Muestra título breve (p. ej. «Preparando documento»), indicador de progreso **indeterminado** y explicación («El PDF se abrirá aquí cuando esté listo»). No muestres porcentajes ficticios, datos sensibles, URLs, respuestas de servidor o detalles internos.
4. La nueva pestaña `about:blank` no hereda automáticamente los estilos de React. Si se necesita insertar DOM/CSS en ella, usa estilos mínimos definidos a partir de los tokens reales del tema y **construcción segura con APIs DOM** (`createElement`, `textContent`), sin interpolar `filename` ni datos externos dentro de `innerHTML`/`document.write`. No añadas dependencias ni cargues recursos CDN. Asegura legibilidad móvil, accesibilidad básica (estado `role=status`, contraste y etiqueta) y evita animaciones invasivas.
5. Tras la respuesta válida, reemplaza el estado de carga con el `iframe` del PDF como hasta ahora. En error, mantén el comportamiento controlado de cerrar la pestaña y notificar al usuario mediante `react-toastify`; revisa que los textos visibles al usuario queden en español, sin mostrar mensajes internos del servidor. Preserva la liberación del Blob URL también cuando el usuario cierre la pestaña.
6. No agregues botones de imprimir, descargar, reintentar o cerrar si duplican los del navegador. No modifiques autenticación, middleware, backend Laravel, Nginx, base de datos, reglas de negocio ni pantallas ajenas a esta experiencia.

## Validación obligatoria
- Comprueba que los PDF válidos se abren en el visor nativo y que el estado de carga desaparece.
- Verifica bloqueo de popup, URL inválida/no HTTPS/origen diferente, error 401/403/404/429/5xx, timeout, Blob no PDF y cierre anticipado de pestaña; sin fugas de Object URLs ni errores visibles de JavaScript.
- Ejecuta las pruebas existentes pertinentes a descarga documental; añade o ajusta **solo pruebas focalizadas** para la visual si es posible. Ejecuta `npm run build` y registra avisos preexistentes separadamente de errores nuevos.
- Revisa diferencias finales para confirmar que solo se cambiaron los archivos imprescindibles y que el tema sigue siendo consistente.

## Entrega esperada
Genera `docs/RESULTADO_CODEX_06_MEJORA_VISUAL_PDF_SMIAJAX.md` con: archivos modificados, tokens/patrones del tema reutilizados, explicación visual breve, pruebas ejecutadas y resultados, riesgos y pasos manuales de verificación. **Implementar únicamente en el repositorio local. No commit, push, despliegue a DigitalOcean ni cambios productivos sin autorización posterior.**

## Criterio de aceptación
Una pestaña de carga visualmente integrada con el **tema existente de SMIAJAX**, en español, que transiciona al PDF nativo sin afectar ninguna protección o funcionalidad de CODEX 02–04.
