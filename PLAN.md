## Flux Builder 100% Reactivo (Live Preview + Guardado Confiable)

### Resumen
- Corregir el flujo completo `editar -> guardar -> refrescar preview` para que los cambios del panel se vean en vivo y queden persistidos sin recarga manual.
- Mantener la UX actual del drawer, pero hacerla más robusta con autosave real, feedback de guardado y refresco Livewire determinista.
- Implementar todo sobre la base existente (sin migraciones ni storage nuevo), usando `theme_mod` y Livewire.

### Cambios de Implementación
- En [⚡ecommerce-home-builder.blade.php](/home/andexer/Escritorio/docker/test/wp-content/themes/flux-press/resources/views/components/⚡ecommerce-home-builder.blade.php):
  - Mantener `refreshTick` como gatillo central de rerender.
  - Incluir `refreshTick` en las keys de cada subcomponente (`hero`, `categories`, etc.) para forzar remount real en cada refresh del builder.
  - Dejar un único método de refresh (`refreshBuilder`) y usarlo como punto de entrada para “live preview refresh”.

- En [⚡home-visual-builder.blade.php](/home/andexer/Escritorio/docker/test/wp-content/themes/flux-press/resources/views/components/⚡home-visual-builder.blade.php):
  - Unificar persistencia + notificación en un pipeline único (`persist -> status -> dispatch refresh`), evitando rutas paralelas inconsistentes.
  - Asegurar autosave en cambios de slides/autoplay/intervalo con debounce estable.
  - Hacer que `refreshHome` fuerce sincronización de estado pendiente antes de refrescar preview.
  - Mejorar feedback al usuario: estado “guardando/guardado/error” y timestamp consistente.
  - Mantener botón “Aplicar hero” como guardado explícito adicional, pero con comportamiento coherente con autosave.

- En [app.js](/home/andexer/Escritorio/docker/test/wp-content/themes/flux-press/resources/js/app.js):
  - Simplificar el bridge de eventos a un solo evento canónico de refresh.
  - Conservar fallback JS para llamar `refreshBuilder()` en el componente raíz si existe.
  - Evitar dispatch duplicado de eventos equivalentes para reducir condiciones de carrera.

### Interfaces Públicas / Eventos
- Estandarizar evento de refresco en `flux-home-builder-refresh` como contrato principal entre panel/editor y preview.
- Mantener compatibilidad temporal con `fluxHomeBuilderRefresh` solo como alias durante la transición interna.
- No se cambian URLs públicas ni estructura de datos de `theme_mod` (solo se hace más fiable su escritura y lectura en vivo).

### Plan de Pruebas
1. `?flux_builder=1` como admin: editar título/subtítulo/imagen de slide y confirmar cambio visible en hero sin recargar página.
2. Duplicar, eliminar y reordenar slides: validar preview inmediato y orden persistido tras recarga dura.
3. Toggle/reorden de secciones: validar actualización visual en vivo y persistencia tras recarga.
4. Cambiar autoplay/intervalo: validar que el carrusel respeta la nueva configuración en preview.
5. Verificación por WP-CLI en DDEV:
   - Confirmar que `home_ecommerce_hero_slide_*` y/o `home_ecommerce_hero_slides_json` cambian al editar.
   - Confirmar que el estado persiste entre requests.
6. Smoke técnico:
   - `php -l` en archivos modificados.
   - Build JS/CSS del tema para asegurar que no se rompe frontend.

### Supuestos y Defaults
- Se mantiene `theme_mod` como almacenamiento oficial del builder (sin tabla nueva).
- Se mantiene límite actual de 6 slides.
- Se prioriza comportamiento “reactivo + autosave” (como pediste), con botón manual de aplicar como respaldo UX.
- No se modifica el diseño visual general del panel, solo su confiabilidad y reactividad.
