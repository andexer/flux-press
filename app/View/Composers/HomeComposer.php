<?php

namespace App\View\Composers;

use App\Services\HomeEcommerceDataService;
use App\Services\HomeSectionBlocksService;
use Elementor\Plugin;
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

    public function __construct(protected HomeEcommerceDataService $homeEcommerceDataService) {}

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
        $editorEcommerceSections = $this->homeEcommerceEditorSections();

        return [
            'homeLayout' => $layout,
            'homeShowFeatures' => $this->homeSectionFlag('show_features', true),
            'homeShowStats' => $this->homeSectionFlag('show_stats', true),
            'homeShowCta' => $this->homeSectionFlag('show_cta', true),
            'homeShowPosts' => $showPosts,
            'homeShowWidgets' => $this->homeSectionFlag('show_widgets', true),
            'homePostsLimit' => $postsLimit,
            'homePosts' => $showPosts ? $this->homeEcommerceDataService->latestPostsData($postsLimit) : [],
            'homeEcommerceContentMode' => $contentMode,
            'homeEditorContent' => $editorContent,
            'homeHasEditorContent' => $editorContent !== '',
            'homeEcommerceEditorSections' => $editorEcommerceSections,
            'homeEditableContent' => $this->homeEditableContent(),
            'homeSectionsOrder' => $this->homeSectionsOrder(),
            'homeCustomSections' => $this->getCustomSections(),
        ];
    }

    /**
     * @return string[]
     */
    protected function homeEcommerceEditorSections(): array
    {
        $postId = $this->resolveHomePageId();
        if ($postId <= 0) {
            return [];
        }

        $post = get_post($postId);
        if (! $post instanceof \WP_Post) {
            return [];
        }

        $content = (string) $post->post_content;
        if (trim($content) === '') {
            return [];
        }

        $blockToSection = [
            'sage/featured-categories' => 'categories',
            'sage/featured-brands' => 'brands',
            'sage/featured-promos' => 'promos',
            'sage/home-hero' => 'hero',
            'sage/home-best-sellers' => 'best_sellers',
            'sage/home-top-rated' => 'top_rated',
            'sage/home-newsletter' => 'newsletter',
            'sage/home-blog' => 'blog',
        ];

        $found = [];

        $walkBlocks = function (array $blocks) use (&$walkBlocks, &$found, $blockToSection): void {
            foreach ($blocks as $block) {
                if (! is_array($block)) {
                    continue;
                }

                $name = (string) ($block['blockName'] ?? '');
                if ($name === 'sage/home-sections-carousel') {
                    foreach ($this->parseSectionsList((string) ($block['attrs']['sections'] ?? 'categories,brands,promos')) as $section) {
                        $found[$section] = true;
                    }
                } elseif ($name !== '' && isset($blockToSection[$name])) {
                    $found[$blockToSection[$name]] = true;
                }

                $innerBlocks = $block['innerBlocks'] ?? [];
                if (is_array($innerBlocks) && ! empty($innerBlocks)) {
                    $walkBlocks($innerBlocks);
                }
            }
        };

        $parsed = parse_blocks($content);
        if (is_array($parsed) && ! empty($parsed)) {
            $walkBlocks($parsed);
        }

        if (stripos($content, '[flux_featured_categories') !== false) {
            $found['categories'] = true;
        }
        if (stripos($content, '[flux_featured_brands') !== false) {
            $found['brands'] = true;
        }
        if (stripos($content, '[flux_featured_promos') !== false) {
            $found['promos'] = true;
        }
        if (stripos($content, '[flux_home_sections_carousel') !== false) {
            $found['categories'] = true;
            $found['brands'] = true;
            $found['promos'] = true;
        }
        if (stripos($content, '[flux_home_hero') !== false) {
            $found['hero'] = true;
        }
        if (stripos($content, '[flux_home_best_sellers') !== false) {
            $found['best_sellers'] = true;
        }
        if (stripos($content, '[flux_home_top_rated') !== false) {
            $found['top_rated'] = true;
        }
        if (stripos($content, '[flux_home_newsletter') !== false) {
            $found['newsletter'] = true;
        }
        if (stripos($content, '[flux_home_blog') !== false) {
            $found['blog'] = true;
        }

        return array_values(array_keys($found));
    }

    /**
     * @return string[]
     */
    protected function parseSectionsList(string $value): array
    {
        $allowed = ['categories', 'brands', 'promos'];
        $sections = array_map(
            static fn ($item) => sanitize_key((string) $item),
            explode(',', $value)
        );

        $sections = array_values(array_filter($sections, static fn ($item) => in_array($item, $allowed, true)));
        $sections = array_values(array_unique($sections));

        return ! empty($sections) ? $sections : $allowed;
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
        $postId = $this->resolveHomePageId();
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

        $plugin = Plugin::instance();
        if (! isset($plugin->frontend) || ! method_exists($plugin->frontend, 'get_builder_content_for_display')) {
            return '';
        }

        $rendered = $plugin->frontend->get_builder_content_for_display($postId, true);

        return is_string($rendered) ? $rendered : '';
    }

    protected function resolveHomePageId(): int
    {
        $postId = (int) get_queried_object_id();
        if ($postId > 0) {
            return $postId;
        }

        $frontPageId = (int) get_option('page_on_front');
        if ($frontPageId > 0) {
            return $frontPageId;
        }

        return 0;
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
     * Resolve sections order from Customizer.
     *
     * @return string[]
     */
    protected function homeSectionsOrder(): array
    {
        $default = config('theme-interface.home.sections.order', 'hero,features,stats,posts,cta,widgets');
        $value = (string) get_theme_mod('home_sections_order', $default);

        $parts = array_map('trim', explode(',', $value));
        $allowed = ['hero', 'features', 'stats', 'posts', 'cta', 'widgets'];
        $resolved = [];

        foreach ($parts as $part) {
            if ($part !== '' && in_array($part, $allowed, true) && ! in_array($part, $resolved, true)) {
                $resolved[] = $part;
            }
        }

        foreach ($allowed as $fallback) {
            if (! in_array($fallback, $resolved, true)) {
                $resolved[] = $fallback;
            }
        }

        return $resolved;
    }

    /**
     * Get custom sections from the blocks service.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function getCustomSections(): array
    {
        if (! class_exists('App\Services\HomeSectionBlocksService')) {
            return [];
        }

        $service = new HomeSectionBlocksService;

        return $service->getSections();
    }

    /**
     * Get editable content from Customizer for dynamic home sections.
     *
     * @return array<string,mixed>
     */
    protected function homeEditableContent(): array
    {
        $layout = $this->homeLayout();
        $defaults = $this->getLayoutDefaults($layout);

        return [
            'hero' => [
                'title' => $this->getContentOrDefault('home_hero_title', $defaults['hero']['title'] ?? ''),
                'subtitle' => $this->getContentOrDefault('home_hero_subtitle', $defaults['hero']['subtitle'] ?? ''),
                'badge' => $this->getContentOrDefault('home_hero_badge', $defaults['hero']['badge'] ?? ''),
                'badge_color' => get_theme_mod('home_hero_badge_color', $defaults['hero']['badge_color'] ?? 'sky'),
            ],
            'features' => [
                'title' => $this->getContentOrDefault('home_features_title', $defaults['features']['title'] ?? ''),
                'items' => [
                    [
                        'icon' => $this->getContentOrDefault('home_feature_1_icon', $defaults['features']['items'][0]['icon'] ?? 'briefcase'),
                        'title' => $this->getContentOrDefault('home_feature_1_title', $defaults['features']['items'][0]['title'] ?? ''),
                        'text' => $this->getContentOrDefault('home_feature_1_text', $defaults['features']['items'][0]['text'] ?? ''),
                    ],
                    [
                        'icon' => $this->getContentOrDefault('home_feature_2_icon', $defaults['features']['items'][1]['icon'] ?? 'shield-check'),
                        'title' => $this->getContentOrDefault('home_feature_2_title', $defaults['features']['items'][1]['title'] ?? ''),
                        'text' => $this->getContentOrDefault('home_feature_2_text', $defaults['features']['items'][1]['text'] ?? ''),
                    ],
                    [
                        'icon' => $this->getContentOrDefault('home_feature_3_icon', $defaults['features']['items'][2]['icon'] ?? 'chart-bar'),
                        'title' => $this->getContentOrDefault('home_feature_3_title', $defaults['features']['items'][2]['title'] ?? ''),
                        'text' => $this->getContentOrDefault('home_feature_3_text', $defaults['features']['items'][2]['text'] ?? ''),
                    ],
                ],
            ],
            'stats' => [
                'title' => $this->getContentOrDefault('home_stats_title', $defaults['stats']['title'] ?? ''),
                'items' => [
                    ['value' => $this->getContentOrDefault('home_stat_1_value', $defaults['stats']['items'][0]['value'] ?? ''), 'label' => $this->getContentOrDefault('home_stat_1_label', $defaults['stats']['items'][0]['label'] ?? '')],
                    ['value' => $this->getContentOrDefault('home_stat_2_value', $defaults['stats']['items'][1]['value'] ?? ''), 'label' => $this->getContentOrDefault('home_stat_2_label', $defaults['stats']['items'][1]['label'] ?? '')],
                    ['value' => $this->getContentOrDefault('home_stat_3_value', $defaults['stats']['items'][2]['value'] ?? ''), 'label' => $this->getContentOrDefault('home_stat_3_label', $defaults['stats']['items'][2]['label'] ?? '')],
                    ['value' => $this->getContentOrDefault('home_stat_4_value', $defaults['stats']['items'][3]['value'] ?? ''), 'label' => $this->getContentOrDefault('home_stat_4_label', $defaults['stats']['items'][3]['label'] ?? '')],
                ],
            ],
            'cta' => [
                'title' => $this->getContentOrDefault('home_cta_title', $defaults['cta']['title'] ?? ''),
                'description' => $this->getContentOrDefault('home_cta_description', $defaults['cta']['description'] ?? ''),
                'button_text' => $this->getContentOrDefault('home_cta_button_text', $defaults['cta']['button_text'] ?? ''),
                'button_url' => $this->getContentOrDefault('home_cta_button_url', $defaults['cta']['button_url'] ?? ''),
                'bg_color' => get_theme_mod('home_cta_bg_color', $defaults['cta']['bg_color'] ?? ''),
            ],
        ];
    }

    /**
     * Get content from Customizer or return default.
     */
    protected function getContentOrDefault(string $mod, string $default): string
    {
        $value = get_theme_mod($mod, '');

        return $value !== '' ? $value : $default;
    }

    /**
     * Get layout default content.
     *
     * @return array<string,array<string,mixed>>
     */
    protected function getLayoutDefaults(string $layout): array
    {
        $layouts = [
            'corporate' => [
                'hero' => [
                    'title' => __('Digital solutions for demanding teams', 'sage'),
                    'subtitle' => __('Structure, speed and premium experience for businesses that need measurable results.', 'sage'),
                    'badge' => __('Corporate', 'sage'),
                    'badge_color' => 'sky',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'briefcase', 'title' => __('Stable operation', 'sage'), 'text' => __('Architecture prepared for frictionless growth.', 'sage')],
                        ['icon' => 'shield-check', 'title' => __('Real security', 'sage'), 'text' => __('Good security practices at every layer of the site.', 'sage')],
                        ['icon' => 'chart-bar', 'title' => __('Clear KPIs', 'sage'), 'text' => __('Actionable metrics for data-driven decisions.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '99.9%', 'label' => __('Uptime', 'sage')],
                        ['value' => '42%', 'label' => __('Conversion improvement', 'sage')],
                        ['value' => '3x', 'label' => __('Delivery speed', 'sage')],
                        ['value' => '24/7', 'label' => __('Support', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Your home should no longer look improvised', 'sage'),
                    'description' => __('Activate or deactivate sections from the Customizer and adapt this landing to each objective.', 'sage'),
                    'button_text' => __('Customize Home', 'sage'),
                    'button_url' => admin_url('customize.php'),
                    'bg_color' => 'bg-slate-900',
                ],
            ],
            'marketing' => [
                'hero' => [
                    'title' => __('Conversion-focused landing', 'sage'),
                    'subtitle' => __('Structure oriented to campaigns, lead generation and sales with a clear narrative.', 'sage'),
                    'badge' => __('Marketing', 'sage'),
                    'badge_color' => 'lime',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'megaphone', 'title' => __('Messages that convert', 'sage'), 'text' => __('Content designed for commercial intent.', 'sage')],
                        ['icon' => 'cursor-arrow-rays', 'title' => __('Visible CTAs', 'sage'), 'text' => __('Highlighted and well-distributed actions.', 'sage')],
                        ['icon' => 'sparkles', 'title' => __('Memorable design', 'sage'), 'text' => __('Clear visual identity without overload.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '+31%', 'label' => __('Leads', 'sage')],
                        ['value' => '2.8x', 'label' => __('Average ROAS', 'sage')],
                        ['value' => '14d', 'label' => __('Campaign iteration', 'sage')],
                        ['value' => '87%', 'label' => __('Message retention', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Ready to boost your conversions?', 'sage'),
                    'description' => __('Customize every element of your landing page to match your campaign goals.', 'sage'),
                    'button_text' => __('Start Now', 'sage'),
                    'button_url' => home_url('/contacto'),
                    'bg_color' => 'bg-lime-600',
                ],
            ],
            'news' => [
                'hero' => [
                    'title' => __('Clear and ordered editorial cover', 'sage'),
                    'subtitle' => __('Prioritizes content, reading and navigation for media and digital publications.', 'sage'),
                    'badge' => __('News', 'sage'),
                    'badge_color' => 'orange',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'newspaper', 'title' => __('Smart cover', 'sage'), 'text' => __('Relevant content at top visual level.', 'sage')],
                        ['icon' => 'funnel', 'title' => __('Topic filtering', 'sage'), 'text' => __('Clear navigation by sections and tags.', 'sage')],
                        ['icon' => 'clock', 'title' => __('Constant updates', 'sage'), 'text' => __('Publish and organize information in real time.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '5m', 'label' => __('Avg reading time', 'sage')],
                        ['value' => '74%', 'label' => __('Returning users', 'sage')],
                        ['value' => '120+', 'label' => __('Weekly posts', 'sage')],
                        ['value' => '4.7/5', 'label' => __('Editorial rating', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Stay informed with quality journalism', 'sage'),
                    'description' => __('Subscribe to our newsletter and get the latest news delivered to your inbox.', 'sage'),
                    'button_text' => __('Subscribe', 'sage'),
                    'button_url' => home_url('/suscribirse'),
                    'bg_color' => 'bg-orange-600',
                ],
            ],
            'profile' => [
                'hero' => [
                    'title' => __('Personal presentation with professional focus', 'sage'),
                    'subtitle' => __('Ideal for personal brand, portfolio and high-value client acquisition.', 'sage'),
                    'badge' => __('Professional Profile', 'sage'),
                    'badge_color' => 'cyan',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'user-circle', 'title' => __('Personal brand', 'sage'), 'text' => __('Position your profile with clarity and personality.', 'sage')],
                        ['icon' => 'folder-open', 'title' => __('Portfolio', 'sage'), 'text' => __('Show cases and results in visual format.', 'sage')],
                        ['icon' => 'envelope', 'title' => __('Direct contact', 'sage'), 'text' => __('Reduce friction between visit and opportunity.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '12+', 'label' => __('Years of experience', 'sage')],
                        ['value' => '180+', 'label' => __('Completed projects', 'sage')],
                        ['value' => '95%', 'label' => __('Returning clients', 'sage')],
                        ['value' => '48h', 'label' => __('Response time', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Let\'s work together', 'sage'),
                    'description' => __('Contact me to discuss your project and how I can help you achieve your goals.', 'sage'),
                    'button_text' => __('Get in Touch', 'sage'),
                    'button_url' => home_url('/contacto'),
                    'bg_color' => 'bg-cyan-700',
                ],
            ],
            'saas' => [
                'hero' => [
                    'title' => __('Build faster with our SaaS platform', 'sage'),
                    'subtitle' => __('Scale your business with powerful tools designed for modern teams.', 'sage'),
                    'badge' => __('SaaS', 'sage'),
                    'badge_color' => 'violet',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'cloud', 'title' => __('Cloud Native', 'sage'), 'text' => __('Deploy anywhere with our cloud-first architecture.', 'sage')],
                        ['icon' => 'bolt', 'title' => __('Lightning Fast', 'sage'), 'text' => __('Optimized performance that scales with your users.', 'sage')],
                        ['icon' => 'lock-closed', 'title' => __('Enterprise Security', 'sage'), 'text' => __('Bank-grade security to protect your data.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '10K+', 'label' => __('Users', 'sage')],
                        ['value' => '99.9%', 'label' => __('Uptime', 'sage')],
                        ['value' => '50+', 'label' => __('Integrations', 'sage')],
                        ['value' => '24/7', 'label' => __('Support', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Start your free trial', 'sage'),
                    'description' => __('No credit card required. Start building today.', 'sage'),
                    'button_text' => __('Try Free', 'sage'),
                    'button_url' => home_url('/signup'),
                    'bg_color' => 'bg-violet-600',
                ],
            ],
            'startup' => [
                'hero' => [
                    'title' => __('Launch your startup idea', 'sage'),
                    'subtitle' => __('Everything you need to go from idea to market in record time.', 'sage'),
                    'badge' => __('Startup', 'sage'),
                    'badge_color' => 'emerald',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'rocket-launch', 'title' => __('Fast MVP', 'sage'), 'text' => __('Ship your minimum viable product in weeks.', 'sage')],
                        ['icon' => 'currency-dollar', 'title' => __('Affordable Pricing', 'sage'), 'text' => __('Plans that grow with your startup.', 'sage')],
                        ['icon' => 'users', 'title' => __('Community', 'sage'), 'text' => __('Join thousands of founders building together.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '$0', 'label' => __('Start for free', 'sage')],
                        ['value' => '5min', 'label' => __('Setup time', 'sage')],
                        ['value' => '100+', 'label' => __('Templates', 'sage')],
                        ['value' => '0%', 'label' => __('Hidden fees', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Ready to start?', 'sage'),
                    'description' => __('Join the waiting list and get early access to our platform.', 'sage'),
                    'button_text' => __('Join Waitlist', 'sage'),
                    'button_url' => home_url('/waitlist'),
                    'bg_color' => 'bg-emerald-600',
                ],
            ],
            'portfolio' => [
                'hero' => [
                    'title' => __('Showcase your work', 'sage'),
                    'subtitle' => __('Beautiful portfolio to attract clients and opportunities.', 'sage'),
                    'badge' => __('Portfolio', 'sage'),
                    'badge_color' => 'rose',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'photo', 'title' => __('Visual Stories', 'sage'), 'text' => __('Present your work with stunning visuals.', 'sage')],
                        ['icon' => 'paint-brush', 'title' => __('Customizable', 'sage'), 'text' => __('Make it uniquely yours with easy customizations.', 'sage')],
                        ['icon' => 'envelope', 'title' => __('Get Hired', 'sage'), 'text' => __('Make it easy for clients to contact you.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '100+', 'label' => __('Projects', 'sage')],
                        ['value' => '50+', 'label' => __('Clients', 'sage')],
                        ['value' => '5★', 'label' => __('Reviews', 'sage')],
                        ['value' => '24h', 'label' => __('Response', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Let\'s create something amazing', 'sage'),
                    'description' => __('Ready to start your next project? Get in touch today.', 'sage'),
                    'button_text' => __('Contact Me', 'sage'),
                    'button_url' => home_url('/contact'),
                    'bg_color' => 'bg-rose-600',
                ],
            ],
            'restaurant' => [
                'hero' => [
                    'title' => __('Exquisite dining experience', 'sage'),
                    'subtitle' => __('Join us for an unforgettable culinary journey with fresh, local ingredients.', 'sage'),
                    'badge' => __('Restaurant', 'sage'),
                    'badge_color' => 'orange',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'fire', 'title' => __('Fresh Ingredients', 'sage'), 'text' => __('Daily deliveries from local farms.', 'sage')],
                        ['icon' => 'users', 'title' => __('Chef\'s Table', 'sage'), 'text' => __('Intimate dining experience.', 'sage')],
                        ['icon' => 'musical-note', 'title' => __('Live Music', 'sage'), 'text' => __('Entertainment every weekend.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '15+', 'label' => __('Years', 'sage')],
                        ['value' => '4.9', 'label' => __('Rating', 'sage')],
                        ['value' => '50K+', 'label' => __('Guests', 'sage')],
                        ['value' => '3', 'label' => __('Michelin', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Reserve Your Table', 'sage'),
                    'description' => __('Book now for an exceptional dining experience.', 'sage'),
                    'button_text' => __('Book Now', 'sage'),
                    'button_url' => home_url('/reservations'),
                    'bg_color' => 'bg-orange-600',
                ],
            ],
            'medical' => [
                'hero' => [
                    'title' => __('Your health, our priority', 'sage'),
                    'subtitle' => __('Compassionate care with cutting-edge medical technology.', 'sage'),
                    'badge' => __('Medical', 'sage'),
                    'badge_color' => 'cyan',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'heart', 'title' => __('Cardiology', 'sage'), 'text' => __('Expert heart care specialists.', 'sage')],
                        ['icon' => 'eye', 'title' => __('Diagnostics', 'sage'), 'text' => __('State-of-the-art imaging center.', 'sage')],
                        ['icon' => 'clipboard', 'title' => __('24/7 Emergency', 'sage'), 'text' => __('Round-the-clock urgent care.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '50+', 'label' => __('Doctors', 'sage')],
                        ['value' => '24/7', 'label' => __('Availability', 'sage')],
                        ['value' => '98%', 'label' => __('Success', 'sage')],
                        ['value' => '15K+', 'label' => __('Patients', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Schedule an Appointment', 'sage'),
                    'description' => __('Book your visit today and experience quality care.', 'sage'),
                    'button_text' => __('Book Appointment', 'sage'),
                    'button_url' => home_url('/appointment'),
                    'bg_color' => 'bg-cyan-700',
                ],
            ],
            'education' => [
                'hero' => [
                    'title' => __('Empower your future', 'sage'),
                    'subtitle' => __('Quality education that transforms lives and careers.', 'sage'),
                    'badge' => __('Education', 'sage'),
                    'badge_color' => 'violet',
                ],
                'features' => [
                    'title' => '',
                    'items' => [
                        ['icon' => 'academic-cap', 'title' => __('Expert Faculty', 'sage'), 'text' => __('Learn from industry leaders.', 'sage')],
                        ['icon' => 'book-open', 'title' => __('Flexible Learning', 'sage'), 'text' => __('Online and in-person options.', 'sage')],
                        ['icon' => 'trophy', 'title' => __('Career Support', 'sage'), 'text' => __('Job placement assistance.', 'sage')],
                    ],
                ],
                'stats' => [
                    'title' => '',
                    'items' => [
                        ['value' => '10K+', 'label' => __('Graduates', 'sage')],
                        ['value' => '95%', 'label' => __('Employment', 'sage')],
                        ['value' => '50+', 'label' => __('Programs', 'sage')],
                        ['value' => '4.8', 'label' => __('Rating', 'sage')],
                    ],
                ],
                'cta' => [
                    'title' => __('Start Your Journey', 'sage'),
                    'description' => __('Enroll today and unlock your potential.', 'sage'),
                    'button_text' => __('Apply Now', 'sage'),
                    'button_url' => home_url('/apply'),
                    'bg_color' => 'bg-violet-600',
                ],
            ],
        ];

        return $layouts[$layout] ?? $layouts['corporate'];
    }
}
