<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class FooterComposer extends Composer
{
	/**
	 * List of views served by this composer.
	 *
	 * @var string[]
	 */
	protected static $views = [
		'sections.footer',
	];

	public function with()
	{
		return [
			'variant'       => $this->variant(),
			'siteName'      => get_bloginfo('name', 'display'),
			'footerWidgets' => is_active_sidebar('sidebar-footer'),
			'currentYear'   => date('Y'),
			'quickLinks'    => $this->menuItems('footer_navigation'),
			'resourcesMenu' => $this->menuItems('footer_navigation_2'),
            'socialLinks'   => $this->socialLinks(),
		];
	}

    /**
     * Retrieve dynamic social links.
     */
    protected function socialLinks(): array
    {
        // Esto puede venir de get_theme_mod() posteriormente.
        return [
            ['url' => get_theme_mod('social_website', '#'), 'icon' => 'globe-alt', 'label' => 'Website'],
            ['url' => get_theme_mod('social_email', '#'), 'icon' => 'envelope', 'label' => 'Email'],
        ];
    }

	/**
	 * Retrieve WP navigation items.
	 */
	protected function menuItems(string $location): array
	{
		$locations = get_nav_menu_locations();
		if (empty($locations[$location])) {
			return [];
		}
		$menu = wp_get_nav_menu_object($locations[$location]);
		if (! $menu) {
			return [];
		}
		return wp_get_nav_menu_items($menu->term_id) ?: [];
	}

	/**
	 * Obtener la variante activa del footer desde el Customizer.
	 */
	protected function variant(): string
	{
		$allowed = ['corporate', 'clean', 'saas'];
		$variant = get_theme_mod('footer_style', config('theme-interface.footer.default_style', 'corporate'));

		return in_array($variant, $allowed, true) ? $variant : 'corporate';
	}
}
