<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Header Configuration
	|--------------------------------------------------------------------------
	|
	| Valores por defecto para el header. Estos valores se usan como fallback
	| cuando no se ha configurado nada desde el WordPress Customizer.
	|
	*/

	'header' => [
		'default_style' => 'classic',
		'styles'        => [
			'classic'       => 'Marketplace Split — Mega menu + busqueda amplia',
			'centered'      => 'Marketplace Pro — Utilidades + accesos destacados',
			'minimal'       => 'Commerce Compact — Header limpio y rapido',
			'extra-minimal' => 'Spotlight Search — Busqueda protagonista',
		],
		'sticky'        => false,
		'mega_menu'     => [
			'enabled'            => true,
			'show_categories'    => true,
			'show_top_rated'     => true,
			'show_best_selling'  => true,
			'show_pages'         => true,
			'categories_limit'   => 6,
			'top_rated_limit'    => 4,
			'best_selling_limit' => 4,
			'pages_limit'        => 6,
			'featured_item_text' => 'Descubrir',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Home Configuration
	|--------------------------------------------------------------------------
	|
	| Configuracion para la landing/home dinamica. Permite seleccionar
	| variantes y habilitar/deshabilitar secciones desde el Customizer.
	|
	*/

	'home' => [
		'default_layout' => 'corporate',
		'layouts'        => [
			'corporate' => 'Corporate — Hero + Features + Stats + CTA',
			'marketing' => 'Marketing — Hero + Features + CTA',
			'news'      => 'News — Hero + Posts + CTA',
			'profile'   => 'Profile — Hero + Features + CTA',
			'ecommerce' => 'Ecommerce — Home Builder dinamico',
		],
		'sections'       => [
			'show_features' => true,
			'show_stats'    => true,
			'show_cta'      => true,
			'show_posts'    => true,
			'show_widgets'  => true,
			'posts_limit'   => 6,
		],
		'ecommerce'      => [
			'content_mode' => 'hybrid',
			'section_order' => 'hero,categories,best_sellers,top_rated,brands,promos,newsletter,blog',
			'sections'      => [
				'show_hero'         => true,
				'show_categories'   => true,
				'show_best_sellers' => true,
				'show_top_rated'    => true,
				'show_brands'       => true,
				'show_promos'       => true,
				'show_newsletter'   => true,
				'show_blog'         => true,
			],
			'limits'        => [
				'hero'       => 3,
				'categories' => 8,
				'products'   => 8,
				'brands'     => 8,
				'blog'       => 6,
			],
			'hero'          => [
				'autoplay'    => true,
				'interval_ms' => 6500,
				'slides_json' => '[]',
			],
			'newsletter'    => [
				'title'        => 'Recibe novedades en tu correo',
				'text'         => 'Configura este bloque desde el personalizador y capta suscriptores de forma continua.',
				'button_label' => 'Suscribirme',
				'button_url'   => '#',
			],
			'featured_categories_json' => '[]',
			'featured_brands_json'     => '[]',
			'featured_promos_json'     => '[]',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Footer Configuration
	|--------------------------------------------------------------------------
	|
	| Valores por defecto para el footer. Se leen desde el Customizer,
	| con este archivo como fallback.
	|
	*/

	'footer' => [
		'default_style' => 'corporate',
		'styles'        => [
			'corporate'     => 'Corporate — Completo',
			'clean'         => 'Clean — Centrado',
			'saas'          => 'SaaS — Fat footer',
			'extra-minimal' => 'Extra Minimal — Solo lo esencial',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| WooCommerce Configuration
	|--------------------------------------------------------------------------
	|
	| Valores por defecto para la integracion con WooCommerce.
	| Estos valores se usan como fallback cuando no se ha configurado
	| nada desde el WordPress Customizer. Solo aplican si WooCommerce
	| esta instalado y activo.
	|
	*/

	'woocommerce' => [
		'show_cart_icon'    => true,
		'show_shop_sidebar' => true,
		'shop_banner'       => [
			'enabled'            => true,
			'title'              => 'Descubre productos para tu estilo',
			'subtitle'           => 'Filtra por precio, categorias, marcas y mas para encontrar justo lo que necesitas.',
			'content_html'       => '<p>Usa los filtros avanzados para comparar opciones y comprar con mejor criterio.</p>',
			'image_url'          => '',
			'primary_cta_label'  => 'Ver mas vendidos',
			'primary_cta_url'    => '/?s=&post_type=product&orderby=popularity',
			'secondary_cta_label'=> 'Ver ofertas',
			'secondary_cta_url'  => '/?s=&post_type=product&on_sale=1',
		],
		'shop_filters'      => [
			'categories_limit' => 12,
			'brands_limit'     => 12,
			'vendors_limit'    => 12,
			'attributes_limit' => 8,
			'vendor_taxonomies'=> [
				'product_vendor',
				'wcpv_product_vendors',
				'yith_shop_vendor',
				'dc_vendor_shop',
			],
			'price_ranges'     => [
				['label' => '0 - 25', 'min' => 0, 'max' => 25],
				['label' => '25 - 50', 'min' => 25, 'max' => 50],
				['label' => '50 - 100', 'min' => 50, 'max' => 100],
				['label' => '100 - 250', 'min' => 100, 'max' => 250],
				['label' => '250 - 500', 'min' => 250, 'max' => 500],
			],
		],
	],

];
