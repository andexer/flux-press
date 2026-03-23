<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class LivewireServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 */
	public function register(): void
	{
		//
	}

	/**
	 * Bootstrap services.
	 */
	public function boot(): void
	{
		$themeUri = get_template_directory_uri();
		$themePath = get_template_directory();
		$this->publishLivewireAssets($themePath);
		$this->configureLivewireAssetUrl($themeUri);
		$this->publishFluxAssets($themePath);
		$this->overrideFluxScriptsDirective($themeUri);
	}

	/**
	 * Copy Livewire JS assets to the theme's public/vendor/livewire/ directory.
	 */
	private function publishLivewireAssets(string $themePath): void
	{
		$source = $themePath . '/vendor/livewire/livewire/dist';
		$dest = $themePath . '/public/vendor/livewire';

		if (! is_dir($source)) {
			return;
		}

		if (! is_dir($dest)) {
			@mkdir($dest, 0755, true);
		}
		$files = [
			'livewire.js',
			'livewire.min.js',
			'livewire.csp.js',
			'livewire.csp.min.js',
			'livewire.esm.js',
			'livewire.min.js.map',
			'livewire.csp.min.js.map',
			'livewire.esm.js.map',
			'livewire.csp.esm.js',
			'livewire.csp.esm.js.map',
			'manifest.json',
		];

		foreach ($files as $file) {
			$src = $source . '/' . $file;
			$dst = $dest . '/' . $file;

			if (file_exists($src) && (! file_exists($dst) || filemtime($src) > filemtime($dst))) {
				@copy($src, $dst);
			}
		}
	}

	/**
	 * Set Livewire's asset_url to point to the theme's public directory.
	 *
	 * Livewire's usePublishedAssetsIfAvailable() checks for published assets
	 * in public_path('vendor/livewire/') and when found, uses config('livewire.asset_url')
	 * as the full script URL (without filename). We set it to the theme's public URL
	 * pointing to the correct JS file.
	 */
	private function configureLivewireAssetUrl(string $themeUri): void
	{
		$debug = config('app.debug');
		$isCsp = config('livewire.csp_safe', false);

		if ($debug) {
			$file = $isCsp ? 'livewire.csp.js' : 'livewire.js';
		} else {
			$file = $isCsp ? 'livewire.csp.min.js' : 'livewire.min.js';
		}

		config()->set('livewire.asset_url', $themeUri . '/public/vendor/livewire/' . $file);
	}

	/**
	 * Copy Flux JS assets to the theme's public/flux/ directory.
	 */
	private function publishFluxAssets(string $themePath): void
	{
		$fluxDist = $themePath . '/vendor/livewire/flux/dist';
		$fluxPublic = $themePath . '/public/flux';

		if (! is_dir($fluxDist)) {
			return;
		}

		if (! is_dir($fluxPublic)) {
			@mkdir($fluxPublic, 0755, true);
		}
		$files = ['flux-lite.min.js', 'manifest.json'];
		// Also copy pro files if available
		$fluxProDist = $themePath . '/vendor/livewire/flux-pro/dist';
		if (is_dir($fluxProDist)) {
			$files = array_merge($files, ['flux.js', 'flux.min.js']);
			$fluxDist = $fluxProDist;
		}

		foreach ($files as $file) {
			$source = $fluxDist . '/' . $file;
			$dest = $fluxPublic . '/' . $file;

			if (! file_exists($source)) {
				$source = $themePath . '/vendor/livewire/flux/dist/' . $file;
			}

			if (file_exists($source) && (! file_exists($dest) || filemtime($source) > filemtime($dest))) {
				@copy($source, $dest);
			}
		}
	}
	/**
	 * Override @fluxScripts Blade directive to serve from theme's public URL.
	 */
	private function overrideFluxScriptsDirective(string $themeUri): void
	{
		Blade::directive('fluxScripts', function ($expression) {
			return <<<PHP
            <?php
                app('livewire')->forceAssetInjection();

                \$__fluxManifestPath = get_template_directory() . '/vendor/livewire/flux/dist/manifest.json';
                \$__fluxProManifestPath = get_template_directory() . '/vendor/livewire/flux-pro/dist/manifest.json';

                if (file_exists(\$__fluxProManifestPath)) {
                    \$__fluxManifest = json_decode(file_get_contents(\$__fluxProManifestPath), true);
                } else {
                    \$__fluxManifest = json_decode(file_get_contents(\$__fluxManifestPath), true);
                }

                \$__fluxVersionHash = \$__fluxManifest['/flux.js'] ?? '';
                \$__fluxBaseUrl = get_template_directory_uri() . '/public/flux';

                if (config('app.debug')) {
                    \$__fluxFile = file_exists(get_template_directory() . '/vendor/livewire/flux-pro/dist/flux.js')
                        ? 'flux.js'
                        : 'flux-lite.min.js';
                } else {
                    \$__fluxFile = file_exists(get_template_directory() . '/vendor/livewire/flux-pro/dist/flux.min.js')
                        ? 'flux.min.js'
                        : 'flux-lite.min.js';
                }

                echo '<script src="' . \$__fluxBaseUrl . '/' . \$__fluxFile . '?id=' . \$__fluxVersionHash . '" defer data-navigate-once></script>';
            ?>
            PHP;
		});
	}
}
