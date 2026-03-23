<?php

namespace App\Services;

class HomeEcommerceDataService
{
    private const CACHE_GROUP = 'flux_home_ecommerce';
    private const CACHE_TTL = 300;

    /** @var string[] */
    public const SECTION_KEYS = [
        'hero',
        'categories',
        'best_sellers',
        'top_rated',
        'brands',
        'promos',
        'newsletter',
        'blog',
    ];

    /** @var string[] */
    private const WOO_SECTIONS = [
        'hero',
        'categories',
        'best_sellers',
        'top_rated',
        'brands',
        'promos',
    ];

    public function isWooCommerceActive(): bool
    {
        return class_exists('WooCommerce') && function_exists('wc_get_products');
    }

    /**
     * Runtime settings resolved from Customizer + config fallback.
     *
     * @return array<string,mixed>
     */
    public function settings(): array
    {
        $defaults = config('theme-interface.home.ecommerce', []);

        $defaultOrder = (string) ($defaults['section_order'] ?? implode(',', self::SECTION_KEYS));
        $resolvedOrder = $this->sanitizeSectionOrder((string) get_theme_mod('home_ecommerce_section_order', $defaultOrder));
        $contentModeDefault = (string) ($defaults['content_mode'] ?? 'hybrid');
        $contentMode = (string) get_theme_mod('home_ecommerce_content_mode', $contentModeDefault);
        $allowedContentModes = ['builder', 'hybrid', 'editor'];
        if (! in_array($contentMode, $allowedContentModes, true)) {
            $contentMode = in_array($contentModeDefault, $allowedContentModes, true) ? $contentModeDefault : 'hybrid';
        }

        $defaultSections = is_array($defaults['sections'] ?? null) ? $defaults['sections'] : [];
        $show = [];
        foreach (self::SECTION_KEYS as $section) {
            $defaultValue = (bool) ($defaultSections["show_{$section}"] ?? true);
            $show[$section] = (bool) get_theme_mod("home_ecommerce_show_{$section}", $defaultValue);
        }

        $limitDefaults = is_array($defaults['limits'] ?? null) ? $defaults['limits'] : [];

        $limits = [
            'hero'       => $this->boundedInt(get_theme_mod('home_ecommerce_hero_limit', $limitDefaults['hero'] ?? 3), 1, 8),
            'categories' => $this->boundedInt(get_theme_mod('home_ecommerce_categories_limit', $limitDefaults['categories'] ?? 8), 1, 18),
            'products'   => $this->boundedInt(get_theme_mod('home_ecommerce_products_limit', $limitDefaults['products'] ?? 8), 1, 24),
            'brands'     => $this->boundedInt(get_theme_mod('home_ecommerce_brands_limit', $limitDefaults['brands'] ?? 8), 1, 18),
            'blog'       => $this->boundedInt(get_theme_mod('home_ecommerce_blog_limit', $limitDefaults['blog'] ?? 6), 1, 12),
        ];

        $newsletterDefaults = is_array($defaults['newsletter'] ?? null) ? $defaults['newsletter'] : [];

        $newsletterTitle = (string) get_theme_mod(
            'home_ecommerce_newsletter_title',
            (string) ($newsletterDefaults['title'] ?? 'Recibe novedades en tu correo')
        );

        $newsletterText = (string) get_theme_mod(
            'home_ecommerce_newsletter_text',
            (string) ($newsletterDefaults['text'] ?? 'Configura este bloque desde el personalizador y capta suscriptores de forma continua.')
        );

        $newsletterButtonLabel = (string) get_theme_mod(
            'home_ecommerce_newsletter_button_label',
            (string) ($newsletterDefaults['button_label'] ?? 'Suscribirme')
        );

        $newsletterButtonUrlDefault = (string) ($newsletterDefaults['button_url'] ?? '#');
        $newsletterButtonUrl = esc_url_raw((string) get_theme_mod('home_ecommerce_newsletter_button_url', $newsletterButtonUrlDefault));
        if ($newsletterButtonUrl === '') {
            $newsletterButtonUrl = $newsletterButtonUrlDefault;
        }

        $heroDefaults = is_array($defaults['hero'] ?? null) ? $defaults['hero'] : [];
        $heroAutoplay = (bool) get_theme_mod('home_ecommerce_hero_autoplay', (bool) ($heroDefaults['autoplay'] ?? true));
        $heroInterval = $this->boundedInt(
            get_theme_mod('home_ecommerce_hero_interval_ms', $heroDefaults['interval_ms'] ?? 6500),
            2500,
            20000
        );
        $heroSlidesJson = (string) get_theme_mod(
            'home_ecommerce_hero_slides_json',
            (string) ($heroDefaults['slides_json'] ?? '[]')
        );

        return [
            'content_mode'  => $contentMode,
            'section_order' => $resolvedOrder,
            'show'          => $show,
            'limits'        => $limits,
            'hero'          => [
                'autoplay'    => $heroAutoplay,
                'interval_ms' => $heroInterval,
                'slides_json' => $heroSlidesJson,
            ],
            'newsletter'    => [
                'title'        => $newsletterTitle,
                'text'         => $newsletterText,
                'button_label' => $newsletterButtonLabel,
                'button_url'   => $newsletterButtonUrl,
            ],
        ];
    }

    /**
     * Ordered section keys that must be rendered for ecommerce home.
     *
     * @return string[]
     */
    public function visibleSections(): array
    {
        $settings = $this->settings();
        $visible = [];

        foreach ($settings['section_order'] as $section) {
            if (! ($settings['show'][$section] ?? false)) {
                continue;
            }

            if (! $this->isSectionAvailable($section)) {
                continue;
            }

            $visible[] = $section;
        }

        return $visible;
    }

    public function isSectionAvailable(string $section): bool
    {
        if (in_array($section, self::WOO_SECTIONS, true) && ! $this->isWooCommerceActive()) {
            return false;
        }

        if ($section === 'brands' && ! taxonomy_exists('product_brand')) {
            return false;
        }

        return in_array($section, self::SECTION_KEYS, true);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function heroProducts(int $limit): array
    {
        return $this->bestSellingProducts(max(1, min(8, $limit)));
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function heroSlides(int $limit): array
    {
        $limit = max(1, min(12, $limit));
        $cacheKey = "hero-slides:{$limit}";

        $data = $this->remember($cacheKey, function () use ($limit) {
            $settings = $this->settings();
            $heroSettings = is_array($settings['hero'] ?? null) ? $settings['hero'] : [];
            $json = (string) ($heroSettings['slides_json'] ?? '[]');

            $customSlides = $this->normalizeHeroSlidesFromJson($json, $limit);
            if (! empty($customSlides)) {
                return $customSlides;
            }

            $products = $this->heroProducts($limit);
            $fallbackSlides = [];

            foreach ($products as $product) {
                if (! is_array($product)) {
                    continue;
                }

                $title = trim((string) ($product['name'] ?? ''));
                $url = esc_url_raw((string) ($product['url'] ?? ''));
                $image = esc_url_raw((string) ($product['image'] ?? ''));
                $description = trim((string) ($product['description'] ?? ''));
                $price = trim((string) ($product['price'] ?? ''));

                $contentParts = [];
                if ($description !== '') {
                    $contentParts[] = '<p>' . esc_html($description) . '</p>';
                }

                if ($price !== '') {
                    $contentParts[] = '<div class="font-black text-lg">' . wp_kses_post($price) . '</div>';
                }

                $fallbackSlides[] = [
                    'id'              => 'product-' . (string) ($product['id'] ?? uniqid('', false)),
                    'title'           => $title,
                    'subtitle'        => '',
                    'content_html'    => implode('', $contentParts),
                    'badge'           => __('Mas vendido', 'flux-press'),
                    'image_url'       => $image,
                    'primary_label'   => __('Ver producto', 'flux-press'),
                    'primary_url'     => $url,
                    'secondary_label' => function_exists('wc_get_page_permalink') ? __('Ir a tienda', 'flux-press') : '',
                    'secondary_url'   => function_exists('wc_get_page_permalink') ? esc_url_raw((string) wc_get_page_permalink('shop')) : '',
                    'source'          => 'product',
                ];
            }

            return array_slice($fallbackSlides, 0, $limit);
        });

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function productCategories(int $limit): array
    {
        if (! $this->isWooCommerceActive()) {
            return [];
        }

        $limit = max(1, min(24, $limit));
        $cacheKey = "categories:{$limit}";

        $data = $this->remember($cacheKey, function () use ($limit) {
            $terms = get_terms([
                'taxonomy'   => 'product_cat',
                'hide_empty' => true,
                'number'     => $limit,
                'parent'     => 0,
                'orderby'    => 'count',
                'order'      => 'DESC',
            ]);

            if (is_wp_error($terms)) {
                return [];
            }

            $termPool = is_array($terms) ? array_values(array_filter($terms, fn ($term) => $term instanceof \WP_Term)) : [];

            if (count($termPool) < $limit) {
                $excluded = array_map(fn (\WP_Term $term) => (int) $term->term_id, $termPool);
                $missing = $limit - count($termPool);

                $fallbackTerms = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'number'     => $missing,
                    'exclude'    => $excluded,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ]);

                if (is_array($fallbackTerms) && ! is_wp_error($fallbackTerms)) {
                    foreach ($fallbackTerms as $fallbackTerm) {
                        if (! $fallbackTerm instanceof \WP_Term) {
                            continue;
                        }

                        $termPool[] = $fallbackTerm;
                    }
                }
            }

            if (empty($termPool)) {
                return [];
            }

            $result = [];
            foreach ($termPool as $term) {
                $url = get_term_link($term);
                if (is_wp_error($url)) {
                    continue;
                }

                $thumbnailId = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
                $children = $this->childCategories((int) $term->term_id, 4);
                $featuredProduct = $this->featuredProductForCategory((int) $term->term_id);
                $featuredProductImage = (string) ($featuredProduct['image'] ?? '');
                $image = $thumbnailId > 0 ? (string) wp_get_attachment_image_url($thumbnailId, 'woocommerce_thumbnail') : '';
                if ($image === '' && $thumbnailId > 0) {
                    $image = (string) wp_get_attachment_image_url($thumbnailId, 'medium');
                }
                if ($image === '') {
                    $image = $featuredProductImage;
                }

                $result[] = [
                    'id'          => (int) $term->term_id,
                    'slug'        => (string) $term->slug,
                    'name'        => (string) $term->name,
                    'description' => wp_trim_words(wp_strip_all_tags((string) $term->description), 16, '...'),
                    'count'       => (int) $term->count,
                    'url'         => (string) $url,
                    'image'       => $image,
                    'has_image'   => $thumbnailId > 0,
                    'edit_url'    => $this->termEditUrl((int) $term->term_id, 'product_cat'),
                    'children'    => $children,
                    'featured_product' => $featuredProduct,
                ];
            }

            return array_slice($result, 0, $limit);
        });

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function bestSellingProducts(int $limit): array
    {
        if (! $this->isWooCommerceActive() || ! function_exists('wc_get_product')) {
            return [];
        }

        $limit = max(1, min(24, $limit));
        $cacheKey = "best-sellers:{$limit}";

        $data = $this->remember($cacheKey, function () use ($limit) {
            $seen = [];
            $items = [];

            $popular = wc_get_products([
                'limit'   => $limit,
                'status'  => 'publish',
                'orderby' => 'popularity',
                'order'   => 'DESC',
                'return'  => 'objects',
            ]);

            if (is_array($popular)) {
                foreach ($popular as $product) {
                    if (! $product instanceof \WC_Product) {
                        continue;
                    }

                    $id = (int) $product->get_id();
                    if ($id <= 0 || isset($seen[$id])) {
                        continue;
                    }

                    $seen[$id] = true;
                    $items[] = $this->mapProduct($product);
                }
            }

            if (count($items) < $limit) {
                $fallbackQuery = new \WP_Query([
                    'post_type'      => 'product',
                    'post_status'    => 'publish',
                    'posts_per_page' => max(12, $limit * 3),
                    'fields'         => 'ids',
                    'meta_key'       => 'total_sales',
                    'orderby'        => [
                        'meta_value_num' => 'DESC',
                        'date'           => 'DESC',
                    ],
                ]);

                if (is_array($fallbackQuery->posts)) {
                    foreach ($fallbackQuery->posts as $productId) {
                        $id = (int) $productId;
                        if ($id <= 0 || isset($seen[$id])) {
                            continue;
                        }

                        $product = wc_get_product($id);
                        if (! $product instanceof \WC_Product) {
                            continue;
                        }

                        $seen[$id] = true;
                        $items[] = $this->mapProduct($product);

                        if (count($items) >= $limit) {
                            break;
                        }
                    }
                }

                wp_reset_postdata();
            }

            if (count($items) < $limit) {
                $recent = wc_get_products([
                    'limit'   => $limit,
                    'status'  => 'publish',
                    'orderby' => 'date',
                    'order'   => 'DESC',
                    'return'  => 'objects',
                ]);

                if (is_array($recent)) {
                    foreach ($recent as $product) {
                        if (! $product instanceof \WC_Product) {
                            continue;
                        }

                        $id = (int) $product->get_id();
                        if ($id <= 0 || isset($seen[$id])) {
                            continue;
                        }

                        $seen[$id] = true;
                        $items[] = $this->mapProduct($product);

                        if (count($items) >= $limit) {
                            break;
                        }
                    }
                }
            }

            return array_slice($items, 0, $limit);
        });

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function topRatedProducts(int $limit): array
    {
        if (! $this->isWooCommerceActive()) {
            return [];
        }

        $limit = max(1, min(24, $limit));
        $cacheKey = "top-rated:{$limit}";

        $data = $this->remember($cacheKey, function () use ($limit) {
            $products = wc_get_products([
                'limit'   => $limit,
                'status'  => 'publish',
                'orderby' => 'rating',
                'order'   => 'DESC',
                'return'  => 'objects',
            ]);

            if (! is_array($products)) {
                return [];
            }

            $result = [];
            foreach ($products as $product) {
                if (! $product instanceof \WC_Product) {
                    continue;
                }

                $result[] = $this->mapProduct($product);
            }

            return array_slice($result, 0, $limit);
        });

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function productBrands(int $limit): array
    {
        if (! $this->isWooCommerceActive() || ! taxonomy_exists('product_brand')) {
            return [];
        }

        $limit = max(1, min(24, $limit));
        $cacheKey = "brands:{$limit}";

        $data = $this->remember($cacheKey, function () use ($limit) {
            $terms = get_terms([
                'taxonomy'   => 'product_brand',
                'hide_empty' => true,
                'number'     => $limit,
                'orderby'    => 'count',
                'order'      => 'DESC',
            ]);

            if (! is_array($terms) || is_wp_error($terms)) {
                return [];
            }

            $result = [];
            foreach ($terms as $term) {
                if (! $term instanceof \WP_Term) {
                    continue;
                }

                $url = get_term_link($term);
                if (is_wp_error($url)) {
                    continue;
                }

                $thumbnailId = (int) get_term_meta($term->term_id, 'thumbnail_id', true);

                $result[] = [
                    'id'    => (int) $term->term_id,
                    'name'  => (string) $term->name,
                    'url'   => (string) $url,
                    'count' => (int) $term->count,
                    'image' => $thumbnailId > 0 ? (string) wp_get_attachment_image_url($thumbnailId, 'medium') : '',
                ];
            }

            return array_slice($result, 0, $limit);
        });

        return is_array($data) ? $data : [];
    }

    /**
     * @return array{products: array<int,array<string,mixed>>, categories: array<int,array<string,mixed>>}
     */
    public function promoData(int $productLimit, int $categoryLimit): array
    {
        if (! $this->isWooCommerceActive() || ! function_exists('wc_get_product_ids_on_sale')) {
            return ['products' => [], 'categories' => []];
        }

        $productLimit = max(1, min(24, $productLimit));
        $categoryLimit = max(1, min(18, $categoryLimit));

        $cacheKey = "promos:{$productLimit}:{$categoryLimit}";

        $data = $this->remember($cacheKey, function () use ($productLimit, $categoryLimit) {
            $onSaleIds = array_map('intval', wc_get_product_ids_on_sale());
            $onSaleIds = array_values(array_filter($onSaleIds));

            if (empty($onSaleIds)) {
                return [
                    'products'   => [],
                    'categories' => [],
                ];
            }

            $products = wc_get_products([
                'include' => $onSaleIds,
                'limit'   => $productLimit,
                'status'  => 'publish',
                'orderby' => 'date',
                'order'   => 'DESC',
                'return'  => 'objects',
            ]);

            $productItems = [];
            if (is_array($products)) {
                foreach ($products as $product) {
                    if (! $product instanceof \WC_Product) {
                        continue;
                    }

                    $productItems[] = $this->mapProduct($product);
                }
            }

            $categoryItems = [];
            $terms = wp_get_object_terms($onSaleIds, 'product_cat', [
                'orderby' => 'count',
                'order'   => 'DESC',
            ]);

            if (is_array($terms) && ! is_wp_error($terms)) {
                $seen = [];

                foreach ($terms as $term) {
                    if (! $term instanceof \WP_Term) {
                        continue;
                    }

                    if (isset($seen[$term->term_id])) {
                        continue;
                    }

                    $url = get_term_link($term);
                    if (is_wp_error($url)) {
                        continue;
                    }

                    $seen[$term->term_id] = true;
                    $categoryItems[] = [
                        'id'    => (int) $term->term_id,
                        'name'  => (string) $term->name,
                        'url'   => (string) $url,
                        'count' => (int) $term->count,
                    ];

                    if (count($categoryItems) >= $categoryLimit) {
                        break;
                    }
                }
            }

            return [
                'products'   => array_slice($productItems, 0, $productLimit),
                'categories' => $categoryItems,
            ];
        });

        if (! is_array($data)) {
            return ['products' => [], 'categories' => []];
        }

        return [
            'products'   => is_array($data['products'] ?? null) ? $data['products'] : [],
            'categories' => is_array($data['categories'] ?? null) ? $data['categories'] : [],
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function latestPostsData(int $limit): array
    {
        $limit = max(1, min(12, $limit));
        $cacheKey = "blog-posts:{$limit}";

        $data = $this->remember($cacheKey, function () use ($limit) {
            $posts = get_posts([
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'numberposts'         => $limit,
                'orderby'             => 'date',
                'order'               => 'DESC',
                'ignore_sticky_posts' => true,
                'fields'              => 'ids',
            ]);

            if (! is_array($posts)) {
                return [];
            }

            $result = [];
            foreach ($posts as $postId) {
                $id = (int) $postId;
                if ($id <= 0) {
                    continue;
                }

                $result[] = [
                    'id'      => $id,
                    'title'   => get_the_title($id),
                    'url'     => (string) get_permalink($id),
                    'date'    => (string) get_the_date('', $id),
                    'excerpt' => wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($id)), 20, '...'),
                    'image'   => (string) get_the_post_thumbnail_url($id, 'large'),
                ];
            }

            return $result;
        });

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string,mixed>
     */
    private function mapProduct(\WC_Product $product): array
    {
        $productId = (int) $product->get_id();

        return [
            'id'          => $productId,
            'name'        => (string) $product->get_name(),
            'url'         => (string) $product->get_permalink(),
            'image'       => (string) get_the_post_thumbnail_url($productId, 'large'),
            'price'       => (string) $product->get_price_html(),
            'sales'       => (int) $product->get_total_sales(),
            'rating'      => (float) $product->get_average_rating(),
            'is_on_sale'  => (bool) $product->is_on_sale(),
            'description' => wp_trim_words(
                wp_strip_all_tags((string) ($product->get_short_description() ?: $product->get_description())),
                18,
                '...'
            ),
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function normalizeHeroSlidesFromJson(string $json, int $limit): array
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return [];
        }

        $slides = [];
        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }

            $slide = $this->sanitizeHeroSlide($row);
            if ($slide === null) {
                continue;
            }

            $slides[] = $slide;

            if (count($slides) >= $limit) {
                break;
            }
        }

        return $slides;
    }

    /**
     * @param array<string,mixed> $slide
     * @return array<string,mixed>|null
     */
    private function sanitizeHeroSlide(array $slide): ?array
    {
        $title = sanitize_text_field((string) ($slide['title'] ?? ''));
        $subtitle = sanitize_text_field((string) ($slide['subtitle'] ?? ''));
        $badge = sanitize_text_field((string) ($slide['badge'] ?? ''));
        $image = esc_url_raw((string) ($slide['image_url'] ?? ''));
        if ($image === '' && isset($slide['image_id'])) {
            $imageId = (int) $slide['image_id'];
            if ($imageId > 0) {
                $image = (string) wp_get_attachment_image_url($imageId, 'full');
            }
        }

        $contentHtml = wp_kses_post((string) ($slide['content_html'] ?? ''));

        $primaryLabel = sanitize_text_field(
            (string) (
                $slide['primary_label']
                ?? (is_array($slide['primary_button'] ?? null) ? ($slide['primary_button']['label'] ?? '') : '')
            )
        );
        $primaryUrl = esc_url_raw(
            (string) (
                $slide['primary_url']
                ?? (is_array($slide['primary_button'] ?? null) ? ($slide['primary_button']['url'] ?? '') : '')
            )
        );

        $secondaryLabel = sanitize_text_field(
            (string) (
                $slide['secondary_label']
                ?? (is_array($slide['secondary_button'] ?? null) ? ($slide['secondary_button']['label'] ?? '') : '')
            )
        );
        $secondaryUrl = esc_url_raw(
            (string) (
                $slide['secondary_url']
                ?? (is_array($slide['secondary_button'] ?? null) ? ($slide['secondary_button']['url'] ?? '') : '')
            )
        );

        if (
            $title === ''
            && $subtitle === ''
            && $contentHtml === ''
            && $image === ''
        ) {
            return null;
        }

        $hashSource = wp_json_encode([
            'title' => $title,
            'subtitle' => $subtitle,
            'image' => $image,
            'primary_url' => $primaryUrl,
            'secondary_url' => $secondaryUrl,
        ]);

        return [
            'id'              => 'custom-' . md5((string) $hashSource),
            'title'           => $title,
            'subtitle'        => $subtitle,
            'content_html'    => $contentHtml,
            'badge'           => $badge,
            'image_url'       => $image,
            'primary_label'   => $primaryLabel,
            'primary_url'     => $primaryUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url'   => $secondaryUrl,
            'source'          => 'custom',
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function childCategories(int $parentTermId, int $limit): array
    {
        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => $parentTermId,
            'number'     => max(1, min(10, $limit)),
            'orderby'    => 'count',
            'order'      => 'DESC',
        ]);

        if (! is_array($terms) || is_wp_error($terms)) {
            return [];
        }

        $result = [];
        foreach ($terms as $term) {
            if (! $term instanceof \WP_Term) {
                continue;
            }

            $url = get_term_link($term);
            if (is_wp_error($url)) {
                continue;
            }

            $result[] = [
                'id'    => (int) $term->term_id,
                'name'  => (string) $term->name,
                'slug'  => (string) $term->slug,
                'url'   => (string) $url,
                'count' => (int) $term->count,
            ];
        }

        return array_slice($result, 0, $limit);
    }

    /**
     * @return array<string,mixed>
     */
    private function featuredProductForCategory(int $termId): array
    {
        if (! function_exists('wc_get_product')) {
            return [
                'id'    => 0,
                'name'  => '',
                'url'   => '',
                'image' => '',
            ];
        }

        $productIds = get_posts([
            'post_type'              => 'product',
            'post_status'            => 'publish',
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'no_found_rows'          => true,
            'tax_query'              => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => [$termId],
                ],
            ],
            'meta_key'               => 'total_sales',
            'orderby'                => [
                'meta_value_num' => 'DESC',
                'date'           => 'DESC',
            ],
        ]);

        if (! is_array($productIds) || empty($productIds)) {
            return [
                'id'    => 0,
                'name'  => '',
                'url'   => '',
                'image' => '',
            ];
        }

        $id = (int) $productIds[0];
        if ($id <= 0) {
            return [
                'id'    => 0,
                'name'  => '',
                'url'   => '',
                'image' => '',
            ];
        }

        $product = wc_get_product($id);
        if (! $product instanceof \WC_Product) {
            return [
                'id'    => 0,
                'name'  => '',
                'url'   => '',
                'image' => '',
            ];
        }

        return [
            'id'    => $id,
            'name'  => (string) $product->get_name(),
            'url'   => (string) $product->get_permalink(),
            'image' => (string) get_the_post_thumbnail_url($id, 'medium'),
        ];
    }

    private function termEditUrl(int $termId, string $taxonomy): string
    {
        if ($termId <= 0 || $taxonomy === '' || ! is_user_logged_in()) {
            return '';
        }

        if (
            ! current_user_can('manage_product_terms')
            && ! current_user_can('edit_products')
            && ! current_user_can('manage_woocommerce')
        ) {
            return '';
        }

        return (string) add_query_arg([
            'taxonomy' => $taxonomy,
            'tag_ID' => $termId,
            'post_type' => 'product',
        ], admin_url('term.php'));
    }

    /**
     * @param mixed $value
     */
    private function boundedInt($value, int $min, int $max): int
    {
        $int = (int) $value;

        if ($int < $min) {
            return $min;
        }

        if ($int > $max) {
            return $max;
        }

        return $int;
    }

    /**
     * @return string[]
     */
    private function sanitizeSectionOrder(string $raw): array
    {
        $parts = array_map('trim', explode(',', strtolower($raw)));
        $allowed = array_fill_keys(self::SECTION_KEYS, true);
        $resolved = [];

        foreach ($parts as $part) {
            if ($part === '' || ! isset($allowed[$part]) || in_array($part, $resolved, true)) {
                continue;
            }

            $resolved[] = $part;
        }

        foreach (self::SECTION_KEYS as $fallback) {
            if (! in_array($fallback, $resolved, true)) {
                $resolved[] = $fallback;
            }
        }

        return $resolved;
    }

    /**
     * @return mixed
     */
    private function remember(string $key, callable $resolver)
    {
        $cacheKey = 'data:' . md5($key);

        $found = false;
        $cached = wp_cache_get($cacheKey, self::CACHE_GROUP, false, $found);
        if ($found) {
            return $cached;
        }

        $transientKey = 'flux_ecom_' . md5($key);
        $transient = get_transient($transientKey);
        if ($transient !== false) {
            wp_cache_set($cacheKey, $transient, self::CACHE_GROUP, self::CACHE_TTL);

            return $transient;
        }

        $value = $resolver();

        wp_cache_set($cacheKey, $value, self::CACHE_GROUP, self::CACHE_TTL);
        set_transient($transientKey, $value, self::CACHE_TTL);

        return $value;
    }
}
