<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FluxCustomizerServiceProvider extends ServiceProvider
{
	/**
	 * Allowed Tailwind color names for accent color validation.
	 */
	protected const ALLOWED_COLORS = [
		'slate', 'gray', 'zinc', 'neutral', 'stone',
		'red', 'orange', 'amber', 'yellow', 'lime',
		'green', 'emerald', 'teal', 'cyan', 'sky',
		'blue', 'indigo', 'violet', 'purple', 'fuchsia',
		'pink', 'rose',
	];

	public function boot(): void
	{
		// Inyectamos en el footer con prioridad máxima para ganar la cascada CSS
		add_action('wp_footer', [$this, 'injectDynamicThemeVars'], 9999);
		add_filter('language_attributes', [$this, 'injectAppearanceClass'], 20);
	}

	public function injectAppearanceClass(string $attrs): string
	{
		$appearance = (string) get_option('flux_theme_appearance', 'light');
		$appearance = in_array($appearance, ['light', 'dark'], true) ? $appearance : 'light';

		if (str_contains($attrs, 'class=')) {
			return preg_replace(
				'/class=["\']([^"\']*)["\']/',
				'class="$1 flux-appearance-' . esc_attr($appearance) . '"',
				$attrs
			);
		}

		return $attrs . ' class="flux-appearance-' . esc_attr($appearance) . '"';
	}

	public function injectDynamicThemeVars(): void
	{
		$accent = get_option('flux_theme_accent', 'sky');
		$isHex = (bool) preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $accent);
		$shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

		// Validate non-hex color names against allowlist to prevent CSS injection
		if (! $isHex && ! in_array($accent, self::ALLOWED_COLORS, true)) {
			$accent = 'sky';
		}

		echo '<style id="flux-dynamic-vars-overrides">';
		echo ':root, [data-flux-appearance] {';

		if ($isHex) {
			$safeHex = esc_attr($accent);
			foreach ($shades as $shade) {
				echo "--color-accent-{$shade}: {$safeHex} !important;";
			}
			echo "--color-accent: {$safeHex} !important;";
			echo "--color-accent-content: {$safeHex} !important;";
		} else {
			foreach ($shades as $shade) {
				echo "--color-accent-{$shade}: var(--color-{$accent}-{$shade}) !important;";
			}
			echo "--color-accent: var(--color-accent-600) !important;";
			echo "--color-accent-content: var(--color-accent-600) !important;";
		}
		echo '--color-accent-foreground: #ffffff !important;';
		echo '}';

		// Forzar colores en modo oscuro
		echo '.dark, [data-flux-appearance="dark"] {';
		if (!$isHex) {
			echo "--color-accent: var(--color-{$accent}-400) !important;";
			echo "--color-accent-content: var(--color-{$accent}-400) !important;";
		}
		echo '}';
		echo '</style>';
	}
}
