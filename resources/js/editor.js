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
        { title: __('Contenido', 'flux-press'), initialOpen: true },
        el(TextControl, {
          label: __('Titulo', 'flux-press'),
          value: attributes.title || '',
          onChange: (value) => setAttributes({ title: value }),
        }),
        el(TextControl, {
          label: __('Subtitulo', 'flux-press'),
          value: attributes.subtitle || '',
          onChange: (value) => setAttributes({ subtitle: value }),
        }),
        el(RangeControl, {
          label: __('Limite de tarjetas', 'flux-press'),
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
        { title: __('Tarjeta de categoria', 'flux-press'), initialOpen: true },
        el(TextControl, {
          label: __('Nombre', 'flux-press'),
          value: attributes.name || '',
          onChange: (value) => setAttributes({ name: value }),
        }),
        el(TextControl, {
          label: __('URL', 'flux-press'),
          value: attributes.url || '',
          onChange: (value) => setAttributes({ url: value }),
        }),
        el(TextControl, {
          label: __('Imagen (URL o ruta)', 'flux-press'),
          value: attributes.image_url || '',
          onChange: (value) => setAttributes({ image_url: value }),
        }),
        el(TextControl, {
          label: __('Badge', 'flux-press'),
          value: attributes.badge || '',
          onChange: (value) => setAttributes({ badge: value }),
        }),
      ),
    ),
    el(
      'div',
      blockProps,
      el('strong', { className: 'block text-sm' }, attributes.name || __('Categoria', 'flux-press')),
      el('span', { className: 'block text-xs text-zinc-500' }, attributes.badge || __('Tarjeta manual', 'flux-press')),
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
        { title: __('Tarjeta de marca', 'flux-press'), initialOpen: true },
        el(TextControl, {
          label: __('Nombre', 'flux-press'),
          value: attributes.name || '',
          onChange: (value) => setAttributes({ name: value }),
        }),
        el(TextControl, {
          label: __('URL', 'flux-press'),
          value: attributes.url || '',
          onChange: (value) => setAttributes({ url: value }),
        }),
        el(TextControl, {
          label: __('Imagen de fondo (URL o ruta)', 'flux-press'),
          value: attributes.image_url || '',
          onChange: (value) => setAttributes({ image_url: value }),
        }),
        el(TextControl, {
          label: __('Logo (URL o ruta)', 'flux-press'),
          value: attributes.logo_url || '',
          onChange: (value) => setAttributes({ logo_url: value }),
        }),
        el(TextControl, {
          label: __('Badge', 'flux-press'),
          value: attributes.badge || '',
          onChange: (value) => setAttributes({ badge: value }),
        }),
      ),
    ),
    el(
      'div',
      blockProps,
      el('strong', { className: 'block text-sm' }, attributes.name || __('Marca', 'flux-press')),
      el('span', { className: 'block text-xs text-zinc-500' }, attributes.badge || __('Tarjeta manual', 'flux-press')),
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
        { title: __('Tarjeta promocional', 'flux-press'), initialOpen: true },
        el(TextControl, {
          label: __('Etiqueta', 'flux-press'),
          value: attributes.eyebrow || '',
          onChange: (value) => setAttributes({ eyebrow: value }),
        }),
        el(TextControl, {
          label: __('Titulo', 'flux-press'),
          value: attributes.title || '',
          onChange: (value) => setAttributes({ title: value }),
        }),
        el(TextareaControl, {
          label: __('Descripcion', 'flux-press'),
          value: attributes.description || '',
          onChange: (value) => setAttributes({ description: value }),
        }),
        el(TextControl, {
          label: __('Boton CTA', 'flux-press'),
          value: attributes.cta_label || '',
          onChange: (value) => setAttributes({ cta_label: value }),
        }),
        el(TextControl, {
          label: __('URL CTA', 'flux-press'),
          value: attributes.cta_url || '',
          onChange: (value) => setAttributes({ cta_url: value }),
        }),
        el(TextControl, {
          label: __('Imagen (URL o ruta)', 'flux-press'),
          value: attributes.image_url || '',
          onChange: (value) => setAttributes({ image_url: value }),
        }),
        el(SelectControl, {
          label: __('Tema visual', 'flux-press'),
          value: attributes.theme || 'dark',
          options: [
            { label: __('Oscuro', 'flux-press'), value: 'dark' },
            { label: __('Claro', 'flux-press'), value: 'light' },
            { label: __('Acento', 'flux-press'), value: 'accent' },
          ],
          onChange: (value) => setAttributes({ theme: value }),
        }),
      ),
    ),
    el(
      'div',
      blockProps,
      el('strong', { className: 'block text-sm' }, attributes.title || __('Promocion', 'flux-press')),
      el('span', { className: 'block text-xs text-zinc-500' }, attributes.eyebrow || __('Tarjeta manual', 'flux-press')),
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
  registerBlockType('flux-press/featured-categories', {
    apiVersion: 3,
    title: __('Categorias destacadas', 'flux-press'),
    description: __('Carrusel compacto de categorias para home ecommerce.', 'flux-press'),
    category: 'widgets',
    icon: 'screenoptions',
    attributes: {
      title: { type: 'string', default: __('Categorias destacadas', 'flux-press') },
      subtitle: { type: 'string', default: __('Explora las mejores tendencias del momento', 'flux-press') },
      limit: { type: 'number', default: 8 },
    },
    edit: createParentEdit({
      description: __('Arrastra y ordena tarjetas de categoria.', 'flux-press'),
      allowedBlocks: ['flux-press/category-card'],
      template: [
        ['flux-press/category-card', { name: __('Tecnologia', 'flux-press') }],
        ['flux-press/category-card', { name: __('Hogar', 'flux-press') }],
      ],
      limitMin: 1,
      limitMax: 24,
    }),
    save: () => el(InnerBlocks.Content),
  });

  registerBlockType('flux-press/featured-brands', {
    apiVersion: 3,
    title: __('Marcas destacadas', 'flux-press'),
    description: __('Carrusel de marcas afiliadas para home ecommerce.', 'flux-press'),
    category: 'widgets',
    icon: 'tag',
    attributes: {
      title: { type: 'string', default: __('Tus marcas favoritas', 'flux-press') },
      subtitle: { type: 'string', default: __('Inicia sesion para obtener beneficios exclusivos', 'flux-press') },
      limit: { type: 'number', default: 8 },
    },
    edit: createParentEdit({
      description: __('Arrastra y ordena tarjetas de marca.', 'flux-press'),
      allowedBlocks: ['flux-press/brand-card'],
      template: [
        ['flux-press/brand-card', { name: 'Adidas' }],
        ['flux-press/brand-card', { name: 'Nike' }],
      ],
      limitMin: 1,
      limitMax: 24,
    }),
    save: () => el(InnerBlocks.Content),
  });

  registerBlockType('flux-press/featured-promos', {
    apiVersion: 3,
    title: __('Promociones destacadas', 'flux-press'),
    description: __('Bloque de promociones visuales para home ecommerce.', 'flux-press'),
    category: 'widgets',
    icon: 'megaphone',
    attributes: {
      title: { type: 'string', default: __('Promociones destacadas', 'flux-press') },
      subtitle: { type: 'string', default: __('Ofertas y lanzamientos en una vista mas visual', 'flux-press') },
      limit: { type: 'number', default: 2 },
    },
    edit: createParentEdit({
      description: __('Arrastra y ordena tarjetas promocionales.', 'flux-press'),
      allowedBlocks: ['flux-press/promo-card'],
      template: [
        ['flux-press/promo-card', { title: __('Novedades y lanzamientos', 'flux-press') }],
        ['flux-press/promo-card', { title: __('Ofertas relampago', 'flux-press') }],
      ],
      limitMin: 1,
      limitMax: 6,
    }),
    save: () => el(InnerBlocks.Content),
  });

  registerBlockType('flux-press/home-sections-carousel', {
    apiVersion: 3,
    title: __('Carrusel de secciones', 'flux-press'),
    description: __('Carrusel completo para reordenar categorias, marcas y promos en un solo bloque.', 'flux-press'),
    category: 'widgets',
    icon: 'images-alt2',
    attributes: {
      title: { type: 'string', default: __('Carrusel de secciones', 'flux-press') },
      subtitle: { type: 'string', default: __('Mueve, activa y reagrupa secciones ecommerce', 'flux-press') },
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
            { title: __('Carrusel', 'flux-press'), initialOpen: true },
            el(TextControl, {
              label: __('Titulo', 'flux-press'),
              value: attributes.title || '',
              onChange: (value) => setAttributes({ title: value }),
            }),
            el(TextControl, {
              label: __('Subtitulo', 'flux-press'),
              value: attributes.subtitle || '',
              onChange: (value) => setAttributes({ subtitle: value }),
            }),
            el(TextControl, {
              label: __('Secciones (coma separadas)', 'flux-press'),
              help: __('Usa: categories,brands,promos', 'flux-press'),
              value: attributes.sections || 'categories,brands,promos',
              onChange: (value) => setAttributes({ sections: value }),
            }),
            el(ToggleControl, {
              label: __('Autoplay', 'flux-press'),
              checked: !!attributes.autoplay,
              onChange: (value) => setAttributes({ autoplay: !!value }),
            }),
            el(RangeControl, {
              label: __('Intervalo (ms)', 'flux-press'),
              min: 2500,
              max: 20000,
              step: 100,
              value: Number(attributes.interval || 6500),
              onChange: (value) => setAttributes({ interval: Number(value || 6500) }),
            }),
            el(ToggleControl, {
              label: __('Mostrar controles', 'flux-press'),
              checked: !!attributes.show_controls,
              onChange: (value) => setAttributes({ show_controls: !!value }),
            }),
          ),
        ),
        el(
          'div',
          blockProps,
          el('p', { className: 'm-0 text-xs font-semibold uppercase tracking-wide text-zinc-500' }, __('Carrusel de secciones ecommerce', 'flux-press')),
          el('strong', { className: 'block mt-2 text-sm' }, attributes.title || __('Carrusel de secciones', 'flux-press')),
          el('span', { className: 'block mt-1 text-xs text-zinc-500' }, attributes.sections || 'categories,brands,promos'),
        ),
      );
    },
    save: () => null,
  });

  registerBlockType('flux-press/home-hero', {
    apiVersion: 3,
    title: __('Home: Hero', 'flux-press'),
    description: __('Seccion hero ecommerce del tema.', 'flux-press'),
    category: 'widgets',
    icon: 'slides',
    edit: createSectionBlockEdit({
      title: __('Hero principal', 'flux-press'),
      description: __('Mueve este bloque para cambiar el orden del hero en el Home.', 'flux-press'),
    }),
    save: () => null,
  });

  registerBlockType('flux-press/home-best-sellers', {
    apiVersion: 3,
    title: __('Home: Mas Vendidos', 'flux-press'),
    description: __('Seccion de productos mas vendidos.', 'flux-press'),
    category: 'widgets',
    icon: 'cart',
    edit: createSectionBlockEdit({
      title: __('Productos mas vendidos', 'flux-press'),
      description: __('Mueve este bloque para reordenar la seccion en el Home.', 'flux-press'),
    }),
    save: () => null,
  });

  registerBlockType('flux-press/home-top-rated', {
    apiVersion: 3,
    title: __('Home: Mejor Valorados', 'flux-press'),
    description: __('Seccion de productos top rated.', 'flux-press'),
    category: 'widgets',
    icon: 'star-filled',
    edit: createSectionBlockEdit({
      title: __('Productos mejor valorados', 'flux-press'),
      description: __('Mueve este bloque para reordenar la seccion en el Home.', 'flux-press'),
    }),
    save: () => null,
  });

  registerBlockType('flux-press/home-newsletter', {
    apiVersion: 3,
    title: __('Home: Newsletter', 'flux-press'),
    description: __('Seccion de newsletter del home.', 'flux-press'),
    category: 'widgets',
    icon: 'email',
    edit: createSectionBlockEdit({
      title: __('Newsletter', 'flux-press'),
      description: __('Mueve este bloque para reordenar la newsletter en el Home.', 'flux-press'),
    }),
    save: () => null,
  });

  registerBlockType('flux-press/home-blog', {
    apiVersion: 3,
    title: __('Home: Blog', 'flux-press'),
    description: __('Seccion de entradas recientes del blog.', 'flux-press'),
    category: 'widgets',
    icon: 'admin-post',
    edit: createSectionBlockEdit({
      title: __('Contenido reciente', 'flux-press'),
      description: __('Mueve este bloque para reordenar la seccion de blog en el Home.', 'flux-press'),
    }),
    save: () => null,
  });

  registerBlockType('flux-press/category-card', {
    apiVersion: 3,
    title: __('Tarjeta de categoria', 'flux-press'),
    parent: ['flux-press/featured-categories'],
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

  registerBlockType('flux-press/brand-card', {
    apiVersion: 3,
    title: __('Tarjeta de marca', 'flux-press'),
    parent: ['flux-press/featured-brands'],
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

  registerBlockType('flux-press/promo-card', {
    apiVersion: 3,
    title: __('Tarjeta promocional', 'flux-press'),
    parent: ['flux-press/featured-promos'],
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
