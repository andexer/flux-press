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
			'classic'       => 'Classic — Header inteligente',
			'centered'      => 'Centered — Logo y menu centrados',
			'minimal'       => 'Minimal — Drawer simple',
			'extra-minimal' => 'Extra Minimal — Maximo enfoque',
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
			'ecommerce' => 'Ecommerce — Hero + Features + Stats + CTA',
		],
		'sections'       => [
			'show_features' => true,
			'show_stats'    => true,
			'show_cta'      => true,
			'show_posts'    => true,
			'show_widgets'  => true,
			'posts_limit'   => 6,
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
	],

];
