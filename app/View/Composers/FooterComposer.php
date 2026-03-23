<?php

namespace App\View\Composers;

use App\Services\MenuService;
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
			'quickLinks'    => MenuService::items('footer_navigation'),
			'resourcesMenu' => MenuService::items('footer_navigation_2'),
            'socialLinks'   => $this->socialLinks(),
		];
	}

    /**
     * Retrieve dynamic social links.
     */
    protected function socialLinks(): array
    {
        return [
            ['url' => get_theme_mod('social_website', '#'), 'icon' => 'globe-alt', 'label' => 'Website'],
            ['url' => get_theme_mod('social_email', '#'), 'icon' => 'envelope', 'label' => 'Email'],
        ];
    }

	/**
	 * Obtener la variante activa del footer desde el Customizer.
	 */
	protected function variant(): string
	{
		$styles = config('theme-interface.footer.styles', []);
		$allowed = is_array($styles) ? array_keys($styles) : [];
		$default = (string) config('theme-interface.footer.default_style', 'corporate');
		$variant = (string) get_theme_mod('footer_style', $default);

		if (in_array($variant, $allowed, true)) {
			return $variant;
		}

		if (in_array($default, $allowed, true)) {
			return $default;
		}

		return ! empty($allowed) ? (string) $allowed[0] : 'corporate';
	}
}
