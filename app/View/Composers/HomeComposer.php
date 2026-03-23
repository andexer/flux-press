<?php

namespace App\View\Composers;

use App\Services\HomeEcommerceDataService;
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

	public function __construct(protected HomeEcommerceDataService $homeEcommerceDataService)
	{
	}

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
		$editorContent = $this->homeEditorContent();
		$contentMode = $this->homeEcommerceContentMode();

		return [
			'homeLayout'               => $layout,
			'homeShowFeatures'         => $this->homeSectionFlag('show_features', true),
			'homeShowStats'            => $this->homeSectionFlag('show_stats', true),
			'homeShowCta'              => $this->homeSectionFlag('show_cta', true),
			'homeShowPosts'            => $showPosts,
			'homeShowWidgets'          => $this->homeSectionFlag('show_widgets', true),
			'homePostsLimit'           => $postsLimit,
			'homePosts'                => $showPosts ? $this->homeEcommerceDataService->latestPostsData($postsLimit) : [],
			'homeEcommerceContentMode' => $contentMode,
			'homeEditorContent'        => $editorContent,
			'homeHasEditorContent'     => $editorContent !== '',
		];
	}

	protected function homeEcommerceContentMode(): string
	{
		$allowed = ['builder', 'hybrid', 'editor'];
		$default = (string) config('theme-interface.home.ecommerce.content_mode', 'hybrid');
		$value = (string) get_theme_mod('home_ecommerce_content_mode', $default);

		if (in_array($value, $allowed, true)) {
			return $value;
		}

		return in_array($default, $allowed, true) ? $default : 'hybrid';
	}

	protected function homeEditorContent(): string
	{
		$postId = (int) get_queried_object_id();
		if ($postId <= 0) {
			return '';
		}

		$post = get_post($postId);
		if (! $post instanceof \WP_Post) {
			return '';
		}

		$rawContent = trim((string) $post->post_content);
		if ($rawContent !== '') {
			return (string) apply_filters('the_content', $rawContent);
		}

		if (! class_exists('\\Elementor\\Plugin')) {
			return '';
		}

		$elementorData = get_post_meta($postId, '_elementor_data', true);
		if (! is_string($elementorData) || trim($elementorData) === '') {
			return '';
		}

		$plugin = \Elementor\Plugin::instance();
		if (! isset($plugin->frontend) || ! method_exists($plugin->frontend, 'get_builder_content_for_display')) {
			return '';
		}

		$rendered = $plugin->frontend->get_builder_content_for_display($postId, true);

		return is_string($rendered) ? $rendered : '';
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
}
