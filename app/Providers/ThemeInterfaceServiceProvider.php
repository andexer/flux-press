<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ThemeInterfaceServiceProvider extends ServiceProvider
{
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

		// Registrar secciones del WordPress Customizer
		add_action('customize_register', [$this, 'registerCustomizerSettings']);
	}

	/**
	 * Registrar las secciones y controles del Customizer para Header y Footer.
	 */
	public function registerCustomizerSettings(\WP_Customize_Manager $wp_customize): void
	{
		// ─── Sección: Header ───────────────────────────────────
		$wp_customize->add_section('flux_header_section', [
			'title'    => __('Flux Press: Header', 'sage'),
			'priority' => 30,
		]);

		$wp_customize->add_setting('header_style', [
			'default'           => config('theme-interface.header.default_style', 'classic'),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_style', [
			'label'   => __('Estilo del Header', 'sage'),
			'section' => 'flux_header_section',
			'type'    => 'select',
			'choices' => [
				'classic'  => __('Classic — Logo izquierda, menú centro, CTA derecha', 'sage'),
				'centered' => __('Centered — Logo y menú centrados', 'sage'),
				'minimal'  => __('Minimal — Logo + menú off-canvas', 'sage'),
			],
		]);

		$wp_customize->add_setting('header_sticky', [
			'default'           => config('theme-interface.header.sticky', false),
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('header_sticky', [
			'label'   => __('Header Sticky (fijo al hacer scroll)', 'sage'),
			'section' => 'flux_header_section',
			'type'    => 'checkbox',
		]);

		// ─── Sección: Footer ───────────────────────────────────
		$wp_customize->add_section('flux_footer_section', [
			'title'    => __('Flux Press: Footer', 'sage'),
			'priority' => 31,
		]);

		$wp_customize->add_setting('footer_style', [
			'default'           => config('theme-interface.footer.default_style', 'corporate'),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		]);

		$wp_customize->add_control('footer_style', [
			'label'   => __('Estilo del Footer', 'sage'),
			'section' => 'flux_footer_section',
			'type'    => 'select',
			'choices' => [
				'corporate' => __('Corporate — Multi-columna con widgets', 'sage'),
				'clean'     => __('Clean — Centrado minimalista', 'sage'),
				'saas'      => __('SaaS — Fat Footer con CTA', 'sage'),
			],
		]);
	}

	/**
	 * Sanitize callback para valores booleanos del Customizer.
	 */
	public function sanitizeBoolean($value): bool
	{
		return (bool) $value;
	}
}
