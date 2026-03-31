import domReady from '@wordpress/dom-ready';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { createElement, Fragment } from '@wordpress/element';
import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';

const el = createElement;

const createParentEdit = ({
  description,
  allowedBlocks,
  template,
  limitMin,
  limitMax,
}) => ({ attributes, setAttributes }) => {
  const blockProps = useBlockProps({
    className: 'flux-ecommerce-parent-block rounded-xl border border-zinc-300 p-4',
  });

  return el(
    Fragment,
    null,
    el(
      InspectorControls,
      null,
      el(
        PanelBody,
        { title: __('Contenido', 'sage'), initialOpen: true },
        el(TextControl, {
          label: __('Titulo', 'sage'),
          value: attributes.title || '',
          onChange: (value) => setAttributes({ title: value }),
        }),
        el(TextControl, {
          label: __('Subtitulo', 'sage'),
          value: attributes.subtitle || '',
          onChange: (value) => setAttributes({ subtitle: value }),
        }),
        el(RangeControl, {
          label: __('Limite de tarjetas', 'sage'),
          min: limitMin,
          max: limitMax,
          value: Number(attributes.limit || limitMin),
          onChange: (value) => setAttributes({ limit: Number(value || limitMin) }),
        }),
      ),
    ),
    el(
      'div',
      blockProps,
      el('p', { className: 'm-0 text-xs font-semibold uppercase tracking-wide text-zinc-500' }, description),
      el(InnerBlocks, {
        allowedBlocks,
        template,
        templateLock: false,
      }),
    ),
  );
};

const createCategoryCardEdit = ({ attributes, setAttributes }) => {
  const blockProps = useBlockProps({
    className: 'flux-ecommerce-card-block rounded-lg border border-zinc-300 p-3',
  });

  return el(
    Fragment,
    null,
    el(
      InspectorControls,
      null,
      el(
        PanelBody,
        { title: __('Tarjeta de categoria', 'sage'), initialOpen: true },
        el(TextControl, {
          label: __('Nombre', 'sage'),
          value: attributes.name || '',
          onChange: (value) => setAttributes({ name: value }),
        }),
        el(TextControl, {
          label: __('URL', 'sage'),
          value: attributes.url || '',
          onChange: (value) => setAttributes({ url: value }),
        }),
        el(TextControl, {
          label: __('Imagen (URL o ruta)', 'sage'),
          value: attributes.image_url || '',
          onChange: (value) => setAttributes({ image_url: value }),
        }),
        el(TextControl, {
          label: __('Badge', 'sage'),
          value: attributes.badge || '',
          onChange: (value) => setAttributes({ badge: value }),
        }),
      ),
    ),
    el(
      'div',
      blockProps,
      el('strong', { className: 'block text-sm' }, attributes.name || __('Categoria', 'sage')),
      el('span', { className: 'block text-xs text-zinc-500' }, attributes.badge || __('Tarjeta manual', 'sage')),
    ),
  );
};

const createBrandCardEdit = ({ attributes, setAttributes }) => {
  const blockProps = useBlockProps({
    className: 'flux-ecommerce-card-block rounded-lg border border-zinc-300 p-3',
  });

  return el(
    Fragment,
    null,
    el(
      InspectorControls,
      null,
      el(
        PanelBody,
        { title: __('Tarjeta de marca', 'sage'), initialOpen: true },
        el(TextControl, {
          label: __('Nombre', 'sage'),
          value: attributes.name || '',
          onChange: (value) => setAttributes({ name: value }),
        }),
        el(TextControl, {
          label: __('URL', 'sage'),
          value: attributes.url || '',
          onChange: (value) => setAttributes({ url: value }),
        }),
        el(TextControl, {
          label: __('Imagen de fondo (URL o ruta)', 'sage'),
          value: attributes.image_url || '',
          onChange: (value) => setAttributes({ image_url: value }),
        }),
        el(TextControl, {
          label: __('Logo (URL o ruta)', 'sage'),
          value: attributes.logo_url || '',
          onChange: (value) => setAttributes({ logo_url: value }),
        }),
        el(TextControl, {
          label: __('Badge', 'sage'),
          value: attributes.badge || '',
          onChange: (value) => setAttributes({ badge: value }),
        }),
      ),
    ),
    el(
      'div',
      blockProps,
      el('strong', { className: 'block text-sm' }, attributes.name || __('Marca', 'sage')),
      el('span', { className: 'block text-xs text-zinc-500' }, attributes.badge || __('Tarjeta manual', 'sage')),
    ),
  );
};

const createPromoCardEdit = ({ attributes, setAttributes }) => {
  const blockProps = useBlockProps({
    className: 'flux-ecommerce-card-block rounded-lg border border-zinc-300 p-3',
  });

  return el(
    Fragment,
    null,
    el(
      InspectorControls,
      null,
      el(
        PanelBody,
        { title: __('Tarjeta promocional', 'sage'), initialOpen: true },
        el(TextControl, {
          label: __('Etiqueta', 'sage'),
          value: attributes.eyebrow || '',
          onChange: (value) => setAttributes({ eyebrow: value }),
        }),
        el(TextControl, {
          label: __('Titulo', 'sage'),
          value: attributes.title || '',
          onChange: (value) => setAttributes({ title: value }),
        }),
        el(TextareaControl, {
          label: __('Descripcion', 'sage'),
          value: attributes.description || '',
          onChange: (value) => setAttributes({ description: value }),
        }),
        el(TextControl, {
          label: __('Boton CTA', 'sage'),
          value: attributes.cta_label || '',
          onChange: (value) => setAttributes({ cta_label: value }),
        }),
        el(TextControl, {
          label: __('URL CTA', 'sage'),
          value: attributes.cta_url || '',
          onChange: (value) => setAttributes({ cta_url: value }),
        }),
        el(TextControl, {
          label: __('Imagen (URL o ruta)', 'sage'),
          value: attributes.image_url || '',
          onChange: (value) => setAttributes({ image_url: value }),
        }),
        el(SelectControl, {
          label: __('Tema visual', 'sage'),
          value: attributes.theme || 'dark',
          options: [
            { label: __('Oscuro', 'sage'), value: 'dark' },
            { label: __('Claro', 'sage'), value: 'light' },
            { label: __('Acento', 'sage'), value: 'accent' },
          ],
          onChange: (value) => setAttributes({ theme: value }),
        }),
      ),
    ),
    el(
      'div',
      blockProps,
      el('strong', { className: 'block text-sm' }, attributes.title || __('Promocion', 'sage')),
      el('span', { className: 'block text-xs text-zinc-500' }, attributes.eyebrow || __('Tarjeta manual', 'sage')),
    ),
  );
};

const createSectionBlockEdit = ({ title, description }) => () => {
  const blockProps = useBlockProps({
    className: 'flux-ecommerce-parent-block rounded-xl border border-zinc-300 p-4',
  });

  return el(
    'div',
    blockProps,
    el('p', { className: 'm-0 text-sm font-semibold text-zinc-900' }, title),
    el('p', { className: 'mt-1 mb-0 text-xs text-zinc-500' }, description),
  );
};

domReady(() => {
  registerBlockType('sage/featured-categories', {
    apiVersion: 3,
    title: __('Categorias destacadas', 'sage'),
    description: __('Carrusel compacto de categorias para home ecommerce.', 'sage'),
    category: 'widgets',
    icon: 'screenoptions',
    attributes: {
      title: { type: 'string', default: __('Categorias destacadas', 'sage') },
      subtitle: { type: 'string', default: __('Explora las mejores tendencias del momento', 'sage') },
      limit: { type: 'number', default: 8 },
    },
    edit: createParentEdit({
      description: __('Arrastra y ordena tarjetas de categoria.', 'sage'),
      allowedBlocks: ['sage/category-card'],
      template: [
        ['sage/category-card', { name: __('Tecnologia', 'sage') }],
        ['sage/category-card', { name: __('Hogar', 'sage') }],
      ],
      limitMin: 1,
      limitMax: 24,
    }),
    save: () => el(InnerBlocks.Content),
  });

  registerBlockType('sage/featured-brands', {
    apiVersion: 3,
    title: __('Marcas destacadas', 'sage'),
    description: __('Carrusel de marcas afiliadas para home ecommerce.', 'sage'),
    category: 'widgets',
    icon: 'tag',
    attributes: {
      title: { type: 'string', default: __('Tus marcas favoritas', 'sage') },
      subtitle: { type: 'string', default: __('Inicia sesion para obtener beneficios exclusivos', 'sage') },
      limit: { type: 'number', default: 8 },
    },
    edit: createParentEdit({
      description: __('Arrastra y ordena tarjetas de marca.', 'sage'),
      allowedBlocks: ['sage/brand-card'],
      template: [
        ['sage/brand-card', { name: 'Adidas' }],
        ['sage/brand-card', { name: 'Nike' }],
      ],
      limitMin: 1,
      limitMax: 24,
    }),
    save: () => el(InnerBlocks.Content),
  });

  registerBlockType('sage/featured-promos', {
    apiVersion: 3,
    title: __('Promociones destacadas', 'sage'),
    description: __('Bloque de promociones visuales para home ecommerce.', 'sage'),
    category: 'widgets',
    icon: 'megaphone',
    attributes: {
      title: { type: 'string', default: __('Promociones destacadas', 'sage') },
      subtitle: { type: 'string', default: __('Ofertas y lanzamientos en una vista mas visual', 'sage') },
      limit: { type: 'number', default: 2 },
    },
    edit: createParentEdit({
      description: __('Arrastra y ordena tarjetas promocionales.', 'sage'),
      allowedBlocks: ['sage/promo-card'],
      template: [
        ['sage/promo-card', { title: __('Novedades y lanzamientos', 'sage') }],
        ['sage/promo-card', { title: __('Ofertas relampago', 'sage') }],
      ],
      limitMin: 1,
      limitMax: 6,
    }),
    save: () => el(InnerBlocks.Content),
  });

  registerBlockType('sage/home-sections-carousel', {
    apiVersion: 3,
    title: __('Carrusel de secciones', 'sage'),
    description: __('Carrusel completo para reordenar categorias, marcas y promos en un solo bloque.', 'sage'),
    category: 'widgets',
    icon: 'images-alt2',
    attributes: {
      title: { type: 'string', default: __('Carrusel de secciones', 'sage') },
      subtitle: { type: 'string', default: __('Mueve, activa y reagrupa secciones ecommerce', 'sage') },
      sections: { type: 'string', default: 'categories,brands,promos' },
      autoplay: { type: 'boolean', default: true },
      interval: { type: 'number', default: 6500 },
      show_controls: { type: 'boolean', default: true },
    },
    edit: ({ attributes, setAttributes }) => {
      const blockProps = useBlockProps({
        className: 'flux-ecommerce-parent-block rounded-xl border border-zinc-300 p-4',
      });

      return el(
        Fragment,
        null,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __('Carrusel', 'sage'), initialOpen: true },
            el(TextControl, {
              label: __('Titulo', 'sage'),
              value: attributes.title || '',
              onChange: (value) => setAttributes({ title: value }),
            }),
            el(TextControl, {
              label: __('Subtitulo', 'sage'),
              value: attributes.subtitle || '',
              onChange: (value) => setAttributes({ subtitle: value }),
            }),
            el(TextControl, {
              label: __('Secciones (coma separadas)', 'sage'),
              help: __('Usa: categories,brands,promos', 'sage'),
              value: attributes.sections || 'categories,brands,promos',
              onChange: (value) => setAttributes({ sections: value }),
            }),
            el(ToggleControl, {
              label: __('Autoplay', 'sage'),
              checked: !!attributes.autoplay,
              onChange: (value) => setAttributes({ autoplay: !!value }),
            }),
            el(RangeControl, {
              label: __('Intervalo (ms)', 'sage'),
              min: 2500,
              max: 20000,
              step: 100,
              value: Number(attributes.interval || 6500),
              onChange: (value) => setAttributes({ interval: Number(value || 6500) }),
            }),
            el(ToggleControl, {
              label: __('Mostrar controles', 'sage'),
              checked: !!attributes.show_controls,
              onChange: (value) => setAttributes({ show_controls: !!value }),
            }),
          ),
        ),
        el(
          'div',
          blockProps,
          el('p', { className: 'm-0 text-xs font-semibold uppercase tracking-wide text-zinc-500' }, __('Carrusel de secciones ecommerce', 'sage')),
          el('strong', { className: 'block mt-2 text-sm' }, attributes.title || __('Carrusel de secciones', 'sage')),
          el('span', { className: 'block mt-1 text-xs text-zinc-500' }, attributes.sections || 'categories,brands,promos'),
        ),
      );
    },
    save: () => null,
  });

  registerBlockType('sage/home-hero', {
    apiVersion: 3,
    title: __('Home: Hero', 'sage'),
    description: __('Seccion hero ecommerce del tema.', 'sage'),
    category: 'widgets',
    icon: 'slides',
    edit: createSectionBlockEdit({
      title: __('Hero principal', 'sage'),
      description: __('Mueve este bloque para cambiar el orden del hero en el Home.', 'sage'),
    }),
    save: () => null,
  });

  registerBlockType('sage/home-best-sellers', {
    apiVersion: 3,
    title: __('Home: Mas Vendidos', 'sage'),
    description: __('Seccion de productos mas vendidos.', 'sage'),
    category: 'widgets',
    icon: 'cart',
    edit: createSectionBlockEdit({
      title: __('Productos mas vendidos', 'sage'),
      description: __('Mueve este bloque para reordenar la seccion en el Home.', 'sage'),
    }),
    save: () => null,
  });

  registerBlockType('sage/home-top-rated', {
    apiVersion: 3,
    title: __('Home: Mejor Valorados', 'sage'),
    description: __('Seccion de productos top rated.', 'sage'),
    category: 'widgets',
    icon: 'star-filled',
    edit: createSectionBlockEdit({
      title: __('Productos mejor valorados', 'sage'),
      description: __('Mueve este bloque para reordenar la seccion en el Home.', 'sage'),
    }),
    save: () => null,
  });

  registerBlockType('sage/home-newsletter', {
    apiVersion: 3,
    title: __('Home: Newsletter', 'sage'),
    description: __('Seccion de newsletter del home.', 'sage'),
    category: 'widgets',
    icon: 'email',
    edit: createSectionBlockEdit({
      title: __('Newsletter', 'sage'),
      description: __('Mueve este bloque para reordenar la newsletter en el Home.', 'sage'),
    }),
    save: () => null,
  });

  registerBlockType('sage/home-blog', {
    apiVersion: 3,
    title: __('Home: Blog', 'sage'),
    description: __('Seccion de entradas recientes del blog.', 'sage'),
    category: 'widgets',
    icon: 'admin-post',
    edit: createSectionBlockEdit({
      title: __('Contenido reciente', 'sage'),
      description: __('Mueve este bloque para reordenar la seccion de blog en el Home.', 'sage'),
    }),
    save: () => null,
  });

  registerBlockType('sage/category-card', {
    apiVersion: 3,
    title: __('Tarjeta de categoria', 'sage'),
    parent: ['sage/featured-categories'],
    category: 'widgets',
    icon: 'index-card',
    attributes: {
      name: { type: 'string', default: '' },
      url: { type: 'string', default: '' },
      image_url: { type: 'string', default: '' },
      badge: { type: 'string', default: '' },
    },
    edit: createCategoryCardEdit,
    save: () => null,
  });

  registerBlockType('sage/brand-card', {
    apiVersion: 3,
    title: __('Tarjeta de marca', 'sage'),
    parent: ['sage/featured-brands'],
    category: 'widgets',
    icon: 'tag',
    attributes: {
      name: { type: 'string', default: '' },
      url: { type: 'string', default: '' },
      image_url: { type: 'string', default: '' },
      logo_url: { type: 'string', default: '' },
      badge: { type: 'string', default: '' },
    },
    edit: createBrandCardEdit,
    save: () => null,
  });

  registerBlockType('sage/promo-card', {
    apiVersion: 3,
    title: __('Tarjeta promocional', 'sage'),
    parent: ['sage/featured-promos'],
    category: 'widgets',
    icon: 'megaphone',
    attributes: {
      eyebrow: { type: 'string', default: '' },
      title: { type: 'string', default: '' },
      description: { type: 'string', default: '' },
      cta_label: { type: 'string', default: '' },
      cta_url: { type: 'string', default: '' },
      image_url: { type: 'string', default: '' },
      theme: { type: 'string', default: 'dark' },
    },
    edit: createPromoCardEdit,
    save: () => null,
  });
});
