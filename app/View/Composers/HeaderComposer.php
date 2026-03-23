<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class HeaderComposer extends Composer
{
	/**
	 * List of views served by this composer.
	 *
	 * @var string[]
	 */
	protected static $views = [
		'sections.header',
	];

	/**
	 * Data to be passed to view before rendering.
	 *
	 * @return array
	 */
	public function with()
	{
		return [
			'variant'   => $this->variant(),
			'sticky'    => $this->sticky(),
			'menuItems' => $this->menuItems(),
            'cta'       => $this->ctaData(),
		];
	}

    /**
     * Get dynamic CTA data from Customizer or fallback.
     */
    protected function ctaData(): array
    {
        return [
            'label' => get_theme_mod('header_cta_label', __('Empezar', 'sage')),
            'url'   => get_theme_mod('header_cta_url', '#'),
            'show'  => (bool) get_theme_mod('header_cta_show', true),
        ];
    }

	/**
	 * Obtener la variante activa del header desde el Customizer.
	 */
	protected function variant(): string
	{
		$allowed = ['classic', 'centered', 'minimal'];
		$variant = get_theme_mod('header_style', config('theme-interface.header.default_style', 'classic'));

		return in_array($variant, $allowed, true) ? $variant : 'classic';
	}

	/**
	 * Determinar si el header debe ser sticky.
	 */
	protected function sticky(): bool
	{
		return (bool) get_theme_mod('header_sticky', config('theme-interface.header.sticky', false));
	}

	/**
	 * Obtener los items del menú de navegación principal.
	 *
	 * @return array<object>
	 */
	protected function menuItems(): array
	{
		$locations = get_nav_menu_locations();

		if (empty($locations['primary_navigation'])) {
			return [];
		}

		$menu = wp_get_nav_menu_object($locations['primary_navigation']);

		if (! $menu) {
			return [];
		}

		return wp_get_nav_menu_items($menu->term_id) ?: [];
	}
}
