<?php

namespace App\View\Composers;

use App\Services\MenuService;
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
			'menuItems' => MenuService::items('primary_navigation'),
            'megaMenuItems' => MenuService::itemsWithFallback('header_mega_navigation', 'primary_navigation'),
            'actionMenuItems' => MenuService::topLevelItems('header_actions_navigation'),
            'highlightMenuItems' => MenuService::topLevelItems('header_highlights_navigation'),
            'utilityLeftMenuItems' => MenuService::topLevelItems('header_utility_left_navigation'),
            'utilityRightMenuItems' => MenuService::topLevelItems('header_utility_right_navigation'),
            'cta'       => $this->ctaData(),
            'megaMenuConfig' => $this->megaMenuConfig(),
            'renderedByHook' => did_action('get_header') > 0,
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
		$styles = config('theme-interface.header.styles', []);
		$allowed = is_array($styles) ? array_keys($styles) : [];
		$default = (string) config('theme-interface.header.default_style', 'classic');
		$variant = (string) get_theme_mod('header_style', $default);

		if (in_array($variant, $allowed, true)) {
			return $variant;
		}

		if (in_array($default, $allowed, true)) {
			return $default;
		}

		return ! empty($allowed) ? (string) $allowed[0] : 'classic';
	}

	/**
	 * Determinar si el header debe ser sticky.
	 */
	protected function sticky(): bool
	{
		return (bool) get_theme_mod('header_sticky', config('theme-interface.header.sticky', false));
	}

    /**
     * Mega menu runtime configuration from Customizer.
     */
    protected function megaMenuConfig(): array
    {
        return [
            'enabled'            => (bool) get_theme_mod('header_enable_mega_menu', config('theme-interface.header.mega_menu.enabled', true)),
            'show_categories'    => (bool) get_theme_mod('header_megamenu_show_categories', config('theme-interface.header.mega_menu.show_categories', true)),
            'show_top_rated'     => (bool) get_theme_mod('header_megamenu_show_top_rated', config('theme-interface.header.mega_menu.show_top_rated', true)),
            'show_best_selling'  => (bool) get_theme_mod('header_megamenu_show_best_selling', config('theme-interface.header.mega_menu.show_best_selling', true)),
            'show_pages'         => (bool) get_theme_mod('header_megamenu_show_pages', config('theme-interface.header.mega_menu.show_pages', true)),
            'categories_limit'   => max(1, min(20, (int) get_theme_mod('header_megamenu_categories_limit', config('theme-interface.header.mega_menu.categories_limit', 6)))),
            'top_rated_limit'    => max(1, min(20, (int) get_theme_mod('header_megamenu_top_rated_limit', config('theme-interface.header.mega_menu.top_rated_limit', 4)))),
            'best_selling_limit' => max(1, min(20, (int) get_theme_mod('header_megamenu_best_selling_limit', config('theme-interface.header.mega_menu.best_selling_limit', 4)))),
            'pages_limit'        => max(1, min(20, (int) get_theme_mod('header_megamenu_pages_limit', config('theme-interface.header.mega_menu.pages_limit', 6)))),
            'featured_item_text' => (string) get_theme_mod('header_megamenu_featured_item_text', config('theme-interface.header.mega_menu.featured_item_text', __('Descubrir', 'sage'))),
        ];
    }
}
