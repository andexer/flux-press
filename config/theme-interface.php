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
		'default_style' => 'classic', // 'classic', 'centered', 'minimal'
		'sticky'        => false,
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
		'default_style' => 'corporate', // 'corporate', 'clean', 'saas'
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
