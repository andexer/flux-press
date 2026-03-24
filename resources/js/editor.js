import domReady from '@wordpress/dom-ready';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { createElement, Fragment } from '@wordpress/element';
import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, TextControl, TextareaControl } from '@wordpress/components';

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
