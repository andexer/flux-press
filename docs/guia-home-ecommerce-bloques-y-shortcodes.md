# Guia Home Ecommerce V2 (Bloques Gutenberg + Shortcodes Elementor)

Esta guia explica como usar y configurar el Home Ecommerce V2 del tema Flux Press en espanol.

## 1. Que incluye esta implementacion

- Secciones redisenadas:
  - Categorias destacadas
  - Marcas destacadas
  - Promociones destacadas
- Edicion drag-and-drop con bloques nativos de Gutenberg.
- Soporte para Elementor por medio de shortcodes.
- Prioridad de datos:
  1. Tarjetas manuales del bloque (si existen)
  2. JSON del Customizer
  3. Fallback automatico WooCommerce
  4. Fallback assets locales del tema

## 2. Requisitos

- WordPress activo.
- Tema Flux Press activo.
- WooCommerce activo (recomendado para data automatica).
- Para editar con bloques: Gutenberg.
- Para editar con Elementor: widget `Shortcode`.

## 3. Modo de contenido del Home Ecommerce

Ruta:

- `Apariencia > Personalizar > Flux Press: Home Ecommerce > Modo de contenido Home Ecommerce`

Opciones:

- `Builder del tema`: solo secciones del builder.
- `Hibrido (editor + builder)`: mezcla contenido editor + builder.
- `Solo editor de bloques/Elementor`: renderiza solo contenido del editor.

### Regla anti-duplicados en modo Hibrido

Si en el contenido del Home existe alguno de estos bloques/shortcodes:

- `sage/featured-categories` o `[flux_featured_categories]`
- `sage/featured-brands` o `[flux_featured_brands]`
- `sage/featured-promos` o `[flux_featured_promos]`
- `sage/home-sections-carousel` o `[flux_home_sections_carousel]`

el builder ocultara automaticamente esa seccion para no duplicarla.

## 4. Bloques Gutenberg disponibles

### Bloques padre (secciones)

- `sage/featured-categories`
- `sage/featured-brands`
- `sage/featured-promos`
- `sage/home-sections-carousel`

Cada bloque padre permite:

- `title` (titulo)
- `subtitle` (subtitulo)
- `limit` (cantidad maxima de tarjetas)

El bloque `sage/home-sections-carousel` permite:

- `sections` (`categories,brands,promos` en el orden que quieras)
- `autoplay` (`true/false`)
- `interval` (2500 a 20000 ms)
- `show_controls` (`true/false`)

### Bloques hijo (tarjetas)

- `sage/category-card` (hijo de featured-categories)
- `sage/brand-card` (hijo de featured-brands)
- `sage/promo-card` (hijo de featured-promos)

Puedes arrastrar/reordenar tarjetas dentro de cada bloque padre usando `InnerBlocks`.

## 5. Contrato de datos por tarjeta

## 5.1 Categoria (`sage/category-card`)

Campos:

- `name` (requerido)
- `url`
- `image_url`
- `badge`

## 5.2 Marca (`sage/brand-card`)

Campos:

- `name` (requerido)
- `url`
- `image_url` (fondo)
- `logo_url`
- `badge`

## 5.3 Promo (`sage/promo-card`)

Campos:

- `eyebrow`
- `title` (requerido)
- `description`
- `cta_label`
- `cta_url`
- `image_url`
- `theme` (`dark`, `light`, `accent`)

## 6. Shortcodes para Elementor

Inserta estos shortcodes en el widget `Shortcode` de Elementor.

## 6.1 Categorias

```txt
[flux_featured_categories title="Categorias destacadas" subtitle="Explora las mejores tendencias del momento" limit="8"]
```

Con tarjetas manuales por JSON:

```txt
[flux_featured_categories title="Categorias destacadas" subtitle="Top categorias" limit="6" cards_json='[{"name":"Tecnologia","url":"/tienda/","image_url":"category-tecnologia.jpg","badge":"Tendencia"},{"name":"Hogar","url":"/tienda/","image_url":"category-hogar.jpg","badge":"Popular"}]']
```

## 6.2 Marcas

```txt
[flux_featured_brands title="Tus marcas favoritas" subtitle="Inicia sesion para beneficios exclusivos" limit="8"]
```

Con tarjetas manuales por JSON:

```txt
[flux_featured_brands title="Marcas top" subtitle="Afiliadas oficiales" limit="8" cards_json='[{"name":"Adidas","url":"/tienda/","image_url":"brand-adidas.jpg","logo_url":"brand-adidas-logo.png","badge":"Marca afiliada"},{"name":"Nike","url":"/tienda/","image_url":"brand-nike.jpg","logo_url":"brand-nike-logo.png","badge":"Marca afiliada"}]']
```

## 6.3 Promociones

```txt
[flux_featured_promos title="Promociones destacadas" subtitle="Ofertas y lanzamientos" limit="2"]
```

Con tarjetas manuales por JSON:

```txt
[flux_featured_promos title="Promos" subtitle="Semana activa" limit="2" cards_json='[{"eyebrow":"Live now","title":"Novedades y lanzamientos","description":"Descubre productos nuevos.","cta_label":"Explorar todo","cta_url":"/tienda/","image_url":"promo-launches.jpg","theme":"light"},{"eyebrow":"Oferta flash","title":"Ofertas relampago","description":"Descuentos por tiempo limitado.","cta_label":"Ver ofertas","cta_url":"/tienda/","image_url":"promo-flash.jpg","theme":"dark"}]']
```

## 6.4 Carrusel de secciones completas (nuevo)

Este shortcode crea un carrusel de secciones completas (no solo tarjetas sueltas), ideal para Elementor o bloque Shortcode en Gutenberg.

```txt
[flux_home_sections_carousel title="Descubre" subtitle="Secciones ecommerce en carrusel" sections="categories,brands,promos" autoplay="1" interval="6500" show_controls="1"]
```

Ejemplos:

```txt
[flux_home_sections_carousel sections="brands,promos" autoplay="0" show_controls="1"]
```

```txt
[flux_home_sections_carousel sections="categories" autoplay="0" show_controls="0"]
```

## 7. Configuracion por Customizer (fallback global)

Ruta:

- `Apariencia > Personalizar > Flux Press: Home Ecommerce`

Campos nuevos:

- `Categorias destacadas (JSON)` -> `home_ecommerce_featured_categories_json`
- `Marcas destacadas (JSON)` -> `home_ecommerce_featured_brands_json`
- `Promociones destacadas (JSON)` -> `home_ecommerce_featured_promos_json`

Notas:

- Si no defines tarjetas en bloque, el sistema usara estos JSON.
- Si estos JSON estan vacios, usara WooCommerce y despues assets por defecto del tema.

## 8. Formatos de imagen soportados en `image_url` / `logo_url`

Puedes usar:

- URL completa (`https://...`)
- Solo nombre de archivo (`brand-adidas.jpg`)
- Ruta corta (`ecommerce/reference/brand-adidas.jpg`)
- Ruta relativa de recursos (`resources/images/ecommerce/reference/brand-adidas.jpg`)

## 9. Assets incluidos en el tema

Se copiaron a:

- `resources/images/ecommerce/reference/`

Incluye:

- 8 categorias (`category-*.jpg`)
- 8 marcas fondo (`brand-*.jpg`)
- 8 logos de marca (`brand-*-logo.png`)
- 2 promos (`promo-launches.jpg`, `promo-flash.jpg`)

## 10. Flujo recomendado de uso

## Opcion A: Gutenberg (recomendada)

1. Edita la pagina de inicio.
2. Inserta bloque `Categorias destacadas`.
3. Dentro, agrega/reordena `Tarjeta de categoria`.
4. Repite para `Marcas destacadas` y `Promociones destacadas`.
5. Publica.
6. Usa modo `Hibrido` o `Solo editor` segun tu estrategia.

## Opcion B: Elementor

1. Edita Home con Elementor.
2. Inserta widget `Shortcode`.
3. Pega shortcode `flux_featured_*`.
4. Opcional: agrega `cards_json`.
5. Guarda y revisa frontend.

## Opcion C: Solo builder + Customizer

1. Deja Home sin bloques.
2. Define JSON en Customizer.
3. El builder renderiza con esos datos.

## 11. Troubleshooting rapido

- No aparece una seccion:
  - Revisa `Modo de contenido Home Ecommerce`.
  - Revisa si esta desactivada la seccion en Customizer.
  - En modo `Hibrido`, verifica que no este oculta por duplicado de bloque/shortcode.
- No carga imagen:
  - Verifica `image_url`/`logo_url`.
  - Prueba con nombre de archivo de `resources/images/ecommerce/reference/`.
- En Elementor no toma JSON:
  - Usa comillas simples en `cards_json='[...]'`.
  - Evita saltos de linea dentro del atributo.

## 12. Referencia rapida de nombres tecnicos

- Bloques:
  - `sage/featured-categories`
  - `sage/featured-brands`
  - `sage/featured-promos`
  - `sage/home-sections-carousel`
  - `sage/category-card`
  - `sage/brand-card`
  - `sage/promo-card`
- Shortcodes:
  - `[flux_featured_categories]`
  - `[flux_featured_brands]`
  - `[flux_featured_promos]`
  - `[flux_home_sections_carousel]`
- Theme mods:
  - `home_ecommerce_featured_categories_json`
  - `home_ecommerce_featured_brands_json`
  - `home_ecommerce_featured_promos_json`
  - `home_ecommerce_content_mode`

## 13. Modo claro/oscuro global del tema

- El tema ahora inicia por defecto en `modo claro`.
- El modo oscuro se activa manualmente desde:
  - `Header > boton Configurar Tema > Tema de Interfaz`
  - Opciones disponibles: `Claro` y `Oscuro`.
- La apariencia es global (definida por administracion del tema), no por modo sistema del navegador.

## 14. Flux Visual Builder desde la barra superior de admin (nuevo)

Ahora puedes abrir el editor visual directamente sobre el Home frontend (sin entrar al panel admin).

Ruta rapida:

1. Inicia sesion como administrador.
2. Ve al Home.
3. En la barra superior, haz clic en `Flux Builder`.
4. Se abre un drawer lateral para editar en vivo sobre la pagina.

Funciones incluidas:

- Cambiar `modo de contenido`: `Builder`, `Hybrid`, `Editor`.
- Activar/ocultar secciones del Home.
- Reordenar secciones con arrastrar y soltar.
- Guardado automatico en `theme_mod` (sin JSON manual).
- Aplicacion en vivo sobre el Home sin recarga para orden/visibilidad.
- Cambio de modo (`builder/hybrid/editor`) con recarga controlada.

Theme mods que actualiza este panel:

- `home_ecommerce_content_mode`
- `home_ecommerce_section_order`
- `home_ecommerce_show_hero`
- `home_ecommerce_show_categories`
- `home_ecommerce_show_best_sellers`
- `home_ecommerce_show_top_rated`
- `home_ecommerce_show_brands`
- `home_ecommerce_show_promos`
- `home_ecommerce_show_newsletter`
- `home_ecommerce_show_blog`
