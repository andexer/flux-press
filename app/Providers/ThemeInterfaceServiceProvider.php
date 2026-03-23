<?php

namespace App\Providers;

use App\Traits\SanitizesCustomizerValues;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ThemeInterfaceServiceProvider extends ServiceProvider
{
	use SanitizesCustomizerValues;

	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		$this->mergeConfigFrom(
			__DIR__ . '/../../config/theme-interface.php',
			'theme-interface'
		);
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		// Enlazar View Composers a los parciales de header, footer y layout global
		View::composer(['layouts.app', 'sections.header', 'sections.footer'], \App\View\Composers\AppComposer::class);
		View::composer('sections.header', \App\View\Composers\HeaderComposer::class);
		View::composer('sections.footer', \App\View\Composers\FooterComposer::class);
		View::composer(['front-page', 'page-home'], \App\View\Composers\HomeComposer::class);

		// Render de header/footer basado en hooks de WordPress.
		add_action('get_header', [$this, 'renderHeaderContent'], 1);
		add_action('get_footer', [$this, 'renderFooterContent'], 1);

		// Hooks adicionales para meta tags y scripts.
		add_action('wp_head', [$this, 'renderMetaTags'], 5);
		add_action('wp_footer', [$this, 'renderFooterScripts'], 5);

		// Registrar secciones del WordPress Customizer.
		add_action('customize_register', [$this, 'registerCustomizerSettings']);
	}

	/**
	 * Renderizar el header con Acorn cuando se dispara get_header.
	 */
	public function renderHeaderContent(): void
	{
		if (did_action('get_header') > 1) {
			return;
		}

		echo View::make('sections.header')->render();
	}

	/**
	 * Renderizar el footer con Acorn cuando se dispara get_footer.
	 */
	public function renderFooterContent(): void
	{
		if (did_action('get_footer') > 1) {
			return;
		}

		echo View::make('sections.footer')->render();
	}

	/**
	 * Meta tags dinamicos basados en el contexto.
	 */
	public function renderMetaTags(): void
	{
		if (! is_front_page()) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr(get_bloginfo('description')) . '">' . "\n";
	}

	/**
	 * Scripts dinamicos del footer (ej. analytics configurable).
	 */
	public function renderFooterScripts(): void
	{
		if (! get_theme_mod('analytics_enabled', false)) {
			return;
		}

		$analyticsId = (string) get_theme_mod('analytics_id');
		if ($analyticsId === '') {
			return;
		}

		echo "<!-- Global site tag (gtag.js) - Google Analytics -->\n";
		echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr($analyticsId) . "\"></script>\n";
		echo "<script>\n";
		echo "  window.dataLayer = window.dataLayer || [];\n";
		echo "  function gtag(){dataLayer.push(arguments);}\n";
		echo "  gtag('js', new Date());\n";
		echo "  gtag('config', '" . esc_js($analyticsId) . "');\n";
		echo "</script>\n";
	}

	/**
	 * Registrar las secciones y controles del Customizer para Header y Footer.
	 */
	public function registerCustomizerSettings(\WP_Customize_Manager $wp_customize): void
	{
		// ─── Sección: Header ───────────────────────────────────
		$wp_customize->add_section('flux_header_section', [
			'title'    => __('Flux Press: Header', 'flux-press'),
			'priority' => 30,
		]);

		$wp_customize->add_setting('header_style', [
			'default'           => config('theme-interface.header.default_style', 'classic'),
			'sanitize_callback' => [$this, 'sanitizeHeaderStyle'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_style', [
			'label'   => __('Estilo del Header', 'flux-press'),
			'section' => 'flux_header_section',
			'type'    => 'select',
			'choices' => $this->styleChoices('header'),
		]);

		$wp_customize->add_setting('header_sticky', [
			'default'           => config('theme-interface.header.sticky', false),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_sticky', [
			'label'   => __('Header Sticky (fijo al hacer scroll)', 'flux-press'),
			'section' => 'flux_header_section',
			'type'    => 'checkbox',
		]);

		$this->registerMegaMenuCustomizerSettings($wp_customize);

		// ─── Sección: Footer ───────────────────────────────────
		$wp_customize->add_section('flux_footer_section', [
			'title'    => __('Flux Press: Footer', 'flux-press'),
			'priority' => 31,
		]);

		$wp_customize->add_setting('footer_style', [
			'default'           => config('theme-interface.footer.default_style', 'corporate'),
			'sanitize_callback' => [$this, 'sanitizeFooterStyle'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('footer_style', [
			'label'   => __('Estilo del Footer', 'flux-press'),
			'section' => 'flux_footer_section',
			'type'    => 'select',
			'choices' => $this->styleChoices('footer'),
		]);

		$this->registerHomeCustomizerSettings($wp_customize);
	}

	/**
	 * Registrar ajustes del Mega Menu para Header.
	 */
	protected function registerMegaMenuCustomizerSettings(\WP_Customize_Manager $wp_customize): void
	{
		$wp_customize->add_section('flux_header_megamenu_section', [
			'title'       => __('Flux Press: Mega Menu', 'flux-press'),
			'description' => __('Configura el mega menu inteligente del header.', 'flux-press'),
			'priority'    => 31,
		]);

		$wp_customize->add_setting('header_enable_mega_menu', [
			'default'           => config('theme-interface.header.mega_menu.enabled', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_enable_mega_menu', [
			'label'   => __('Activar Mega Menu', 'flux-press'),
			'section' => 'flux_header_megamenu_section',
			'type'    => 'checkbox',
		]);

		$wp_customize->add_setting('header_megamenu_show_categories', [
			'default'           => config('theme-interface.header.mega_menu.show_categories', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_megamenu_show_categories', [
			'label'   => __('Mostrar categorias de producto', 'flux-press'),
			'section' => 'flux_header_megamenu_section',
			'type'    => 'checkbox',
		]);

		$wp_customize->add_setting('header_megamenu_show_top_rated', [
			'default'           => config('theme-interface.header.mega_menu.show_top_rated', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_megamenu_show_top_rated', [
			'label'   => __('Mostrar productos mejor valorados', 'flux-press'),
			'section' => 'flux_header_megamenu_section',
			'type'    => 'checkbox',
		]);

		$wp_customize->add_setting('header_megamenu_show_best_selling', [
			'default'           => config('theme-interface.header.mega_menu.show_best_selling', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_megamenu_show_best_selling', [
			'label'   => __('Mostrar productos mas vendidos', 'flux-press'),
			'section' => 'flux_header_megamenu_section',
			'type'    => 'checkbox',
		]);

		$wp_customize->add_setting('header_megamenu_show_pages', [
			'default'           => config('theme-interface.header.mega_menu.show_pages', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_megamenu_show_pages', [
			'label'   => __('Mostrar paginas de WordPress', 'flux-press'),
			'section' => 'flux_header_megamenu_section',
			'type'    => 'checkbox',
		]);

		$wp_customize->add_setting('header_megamenu_categories_limit', [
			'default'           => config('theme-interface.header.mega_menu.categories_limit', 6),
			'sanitize_callback' => [$this, 'sanitizeMegaMenuLimit'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_megamenu_categories_limit', [
			'label'       => __('Limite de categorias', 'flux-press'),
			'section'     => 'flux_header_megamenu_section',
			'type'        => 'number',
			'input_attrs' => [
				'min'  => 1,
				'max'  => 12,
				'step' => 1,
			],
		]);

		$wp_customize->add_setting('header_megamenu_top_rated_limit', [
			'default'           => config('theme-interface.header.mega_menu.top_rated_limit', 4),
			'sanitize_callback' => [$this, 'sanitizeMegaMenuLimit'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_megamenu_top_rated_limit', [
			'label'       => __('Limite de productos destacados', 'flux-press'),
			'section'     => 'flux_header_megamenu_section',
			'type'        => 'number',
			'input_attrs' => [
				'min'  => 1,
				'max'  => 12,
				'step' => 1,
			],
		]);

		$wp_customize->add_setting('header_megamenu_best_selling_limit', [
			'default'           => config('theme-interface.header.mega_menu.best_selling_limit', 4),
			'sanitize_callback' => [$this, 'sanitizeMegaMenuLimit'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_megamenu_best_selling_limit', [
			'label'       => __('Limite de productos mas vendidos', 'flux-press'),
			'section'     => 'flux_header_megamenu_section',
			'type'        => 'number',
			'input_attrs' => [
				'min'  => 1,
				'max'  => 12,
				'step' => 1,
			],
		]);

		$wp_customize->add_setting('header_megamenu_pages_limit', [
			'default'           => config('theme-interface.header.mega_menu.pages_limit', 6),
			'sanitize_callback' => [$this, 'sanitizeMegaMenuLimit'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_megamenu_pages_limit', [
			'label'       => __('Limite de paginas', 'flux-press'),
			'section'     => 'flux_header_megamenu_section',
			'type'        => 'number',
			'input_attrs' => [
				'min'  => 1,
				'max'  => 20,
				'step' => 1,
			],
		]);

		$wp_customize->add_setting('header_megamenu_featured_item_text', [
			'default'           => config('theme-interface.header.mega_menu.featured_item_text', __('Descubrir', 'flux-press')),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_megamenu_featured_item_text', [
			'label'       => __('Etiqueta de item destacado', 'flux-press'),
			'description' => __('Texto mostrado en el acceso principal del mega menu si no hay menu asignado.', 'flux-press'),
			'section'     => 'flux_header_megamenu_section',
			'type'        => 'text',
		]);
	}

	/**
	 * Registrar ajustes del Home Builder para landing dinamica.
	 */
	protected function registerHomeCustomizerSettings(\WP_Customize_Manager $wp_customize): void
	{
		$wp_customize->add_section('flux_home_section', [
			'title'       => __('Flux Press: Home Builder', 'flux-press'),
			'description' => __('Selecciona variante de home y activa/desactiva secciones.', 'flux-press'),
			'priority'    => 32,
		]);

		$wp_customize->add_setting('home_layout', [
			'default'           => config('theme-interface.home.default_layout', 'corporate'),
			'sanitize_callback' => [$this, 'sanitizeHomeLayout'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('home_layout', [
			'label'   => __('Layout del Home', 'flux-press'),
			'section' => 'flux_home_section',
			'type'    => 'select',
			'choices' => $this->homeLayoutChoices(),
		]);

		$wp_customize->add_setting('home_show_features', [
			'default'           => config('theme-interface.home.sections.show_features', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('home_show_features', [
			'label'   => __('Mostrar seccion Features', 'flux-press'),
			'section' => 'flux_home_section',
			'type'    => 'checkbox',
		]);

		$wp_customize->add_setting('home_show_stats', [
			'default'           => config('theme-interface.home.sections.show_stats', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('home_show_stats', [
			'label'   => __('Mostrar seccion Stats', 'flux-press'),
			'section' => 'flux_home_section',
			'type'    => 'checkbox',
		]);

		$wp_customize->add_setting('home_show_cta', [
			'default'           => config('theme-interface.home.sections.show_cta', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('home_show_cta', [
			'label'   => __('Mostrar seccion CTA', 'flux-press'),
			'section' => 'flux_home_section',
			'type'    => 'checkbox',
		]);

		$wp_customize->add_setting('home_show_posts', [
			'default'           => config('theme-interface.home.sections.show_posts', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('home_show_posts', [
			'label'   => __('Mostrar seccion de entradas recientes', 'flux-press'),
			'section' => 'flux_home_section',
			'type'    => 'checkbox',
		]);

		$wp_customize->add_setting('home_posts_limit', [
			'default'           => config('theme-interface.home.sections.posts_limit', 6),
			'sanitize_callback' => [$this, 'sanitizeHomePostsLimit'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('home_posts_limit', [
			'label'       => __('Cantidad de entradas en Home', 'flux-press'),
			'section'     => 'flux_home_section',
			'type'        => 'number',
			'input_attrs' => [
				'min'  => 1,
				'max'  => 12,
				'step' => 1,
			],
		]);

		$wp_customize->add_setting('home_show_widgets', [
			'default'           => config('theme-interface.home.sections.show_widgets', true),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('home_show_widgets', [
			'label'   => __('Mostrar area de widgets Home', 'flux-press'),
			'section' => 'flux_home_section',
			'type'    => 'checkbox',
		]);
	}

	/**
	 * Sanitizar limites numericos del Mega Menu.
	 */
	public function sanitizeMegaMenuLimit($value): int
	{
		$limit = absint($value);
		if ($limit < 1) {
			return 1;
		}

		return min($limit, 20);
	}

	/**
	 * Sanitizar limite de posts de Home.
	 */
	public function sanitizeHomePostsLimit($value): int
	{
		$limit = absint($value);
		if ($limit < 1) {
			return 1;
		}

		return min($limit, 12);
	}

	/**
	 * Sanitizar estilo de header contra estilos permitidos.
	 */
	public function sanitizeHeaderStyle($value): string
	{
		return $this->sanitizeStyleByType((string) $value, 'header');
	}

	/**
	 * Sanitizar estilo de footer contra estilos permitidos.
	 */
	public function sanitizeFooterStyle($value): string
	{
		return $this->sanitizeStyleByType((string) $value, 'footer');
	}

	/**
	 * Sanitizar variante de Home.
	 */
	public function sanitizeHomeLayout($value): string
	{
		$value = (string) $value;
		$allowed = array_keys($this->homeLayoutChoices());
		if (in_array($value, $allowed, true)) {
			return $value;
		}

		$default = (string) config('theme-interface.home.default_layout', 'corporate');
		if (in_array($default, $allowed, true)) {
			return $default;
		}

		return ! empty($allowed) ? (string) $allowed[0] : 'corporate';
	}

	/**
	 * Obtener choices del customizer desde config centralizada.
	 *
	 * @return array<string,string>
	 */
	protected function styleChoices(string $type): array
	{
		$styles = config("theme-interface.{$type}.styles", []);
		if (! is_array($styles) || empty($styles)) {
			return [];
		}

		$choices = [];
		foreach ($styles as $key => $label) {
			$key = (string) $key;
			if ($key === '') {
				continue;
			}

			$fallbackLabel = ucwords(str_replace('-', ' ', $key));
			$choices[$key] = is_string($label) && $label !== '' ? __($label, 'flux-press') : __($fallbackLabel, 'flux-press');
		}

		return $choices;
	}

	/**
	 * Obtener choices de layouts para Home.
	 *
	 * @return array<string,string>
	 */
	protected function homeLayoutChoices(): array
	{
		$layouts = config('theme-interface.home.layouts', []);
		if (! is_array($layouts) || empty($layouts)) {
			return [];
		}

		$choices = [];
		foreach ($layouts as $key => $label) {
			$key = (string) $key;
			if ($key === '') {
				continue;
			}

			$fallbackLabel = ucwords(str_replace('-', ' ', $key));
			$choices[$key] = is_string($label) && $label !== '' ? __($label, 'flux-press') : __($fallbackLabel, 'flux-press');
		}

		return $choices;
	}

	/**
	 * Sanitizar valor de estilo por tipo con fallback a default_style.
	 */
	protected function sanitizeStyleByType(string $value, string $type): string
	{
		$allowed = array_keys($this->styleChoices($type));
		if (in_array($value, $allowed, true)) {
			return $value;
		}

		$default = (string) config("theme-interface.{$type}.default_style", '');
		if ($default !== '' && in_array($default, $allowed, true)) {
			return $default;
		}

		return ! empty($allowed) ? (string) $allowed[0] : '';
	}
}
