<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class HomeComposer extends Composer
{
	/**
	 * Views served by this composer.
	 *
	 * @var string[]
	 */
	protected static $views = [
		'front-page',
		'page-home',
	];

	/**
	 * Data to be passed to home views.
	 *
	 * @return array<string,mixed>
	 */
	public function with(): array
	{
		$layout = $this->homeLayout();
		$postsLimit = $this->homePostsLimit();
		$showPosts = $this->homeSectionFlag('show_posts', true);

		return [
			'homeLayout'       => $layout,
			'homeDemoVariant'  => $this->demoVariantForLayout($layout),
			'homeShowFeatures' => $this->homeSectionFlag('show_features', true),
			'homeShowStats'    => $this->homeSectionFlag('show_stats', true),
			'homeShowCta'      => $this->homeSectionFlag('show_cta', true),
			'homeShowPosts'    => $showPosts,
			'homeShowWidgets'  => $this->homeSectionFlag('show_widgets', true),
			'homePostsLimit'   => $postsLimit,
			'homePosts'        => $showPosts ? $this->latestPosts($postsLimit) : [],
		];
	}

	/**
	 * Resolve active home layout.
	 */
	protected function homeLayout(): string
	{
		$layouts = config('theme-interface.home.layouts', []);
		$allowed = is_array($layouts) ? array_keys($layouts) : [];
		$default = (string) config('theme-interface.home.default_layout', 'corporate');
		$value = (string) get_theme_mod('home_layout', $default);

		if (in_array($value, $allowed, true)) {
			return $value;
		}

		if (in_array($default, $allowed, true)) {
			return $default;
		}

		return ! empty($allowed) ? (string) $allowed[0] : 'corporate';
	}

	/**
	 * Resolve home section boolean from Customizer.
	 */
	protected function homeSectionFlag(string $key, bool $fallback): bool
	{
		$default = config("theme-interface.home.sections.{$key}", $fallback);

		return (bool) get_theme_mod("home_{$key}", $default);
	}

	/**
	 * Resolve posts limit for home posts section.
	 */
	protected function homePostsLimit(): int
	{
		$default = (int) config('theme-interface.home.sections.posts_limit', 6);
		$value = (int) get_theme_mod('home_posts_limit', $default);

		return max(1, min(12, $value));
	}

	/**
	 * Convert layout key to existing demo key for section components.
	 */
	protected function demoVariantForLayout(string $layout): string
	{
		$map = [
			'corporate' => 'corporate',
			'marketing' => 'marketing',
			'news'      => 'news',
			'profile'   => 'profile',
			'ecommerce' => 'ecommerce',
		];

		return $map[$layout] ?? 'corporate';
	}

	/**
	 * Retrieve latest published posts for home section.
	 *
	 * @return \WP_Post[]
	 */
	protected function latestPosts(int $limit): array
	{
		$posts = get_posts([
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'numberposts'         => $limit,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
		]);

		return is_array($posts) ? $posts : [];
	}
}
