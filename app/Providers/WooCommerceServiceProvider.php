<?php

namespace App\Providers;

use App\Traits\SanitizesCustomizerValues;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class WooCommerceServiceProvider extends ServiceProvider
{
    use SanitizesCustomizerValues;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Config is already merged by ThemeInterfaceServiceProvider.
    }

    /**
     * Bootstrap any application services.
     *
     * All WooCommerce theme support and integrations are conditional:
     * nothing runs unless WooCommerce is installed and active.
     */
    public function boot(): void
    {
        if (! $this->isWooCommerceActive()) {
            return;
        }

        $this->declareThemeSupport();
        $this->registerShopSidebar();
        $this->removeDefaultStyles();
        $this->registerHooks();
        $this->registerCustomizerSettings();
        $this->registerTemplateOverrides();
        $this->registerTemplateIncludeOverrides();
        $this->registerComposers();
    }

    /**
     * Check if WooCommerce is installed and active.
     */
    protected function isWooCommerceActive(): bool
    {
        return class_exists('WooCommerce');
    }

    /**
     * Declare WooCommerce theme support with optional features.
     */
    protected function declareThemeSupport(): void
    {
        add_action('after_setup_theme', function () {
            add_theme_support('woocommerce', [
                'thumbnail_image_width' => 300,
                'single_image_width'    => 600,
                'product_grid'          => [
                    'default_rows'    => 3,
                    'min_rows'        => 1,
                    'default_columns' => 4,
                    'min_columns'     => 1,
                    'max_columns'     => 6,
                ],
            ]);

            add_theme_support('wc-product-gallery-zoom');
            add_theme_support('wc-product-gallery-lightbox');
            add_theme_support('wc-product-gallery-slider');
        }, 20);
    }

    /**
     * Register WooCommerce-specific widget area.
     */
    protected function registerShopSidebar(): void
    {
        add_action('widgets_init', function () {
            register_sidebar([
                'name'          => __('Shop Sidebar', 'flux-press'),
                'id'            => 'sidebar-shop',
                'description'   => __('Widget area for WooCommerce shop pages.', 'flux-press'),
                'before_widget' => '<section class="widget %1$s %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h3>',
                'after_title'   => '</h3>',
            ]);
        });
    }

    /**
     * Remove all default WooCommerce styles.
     * The theme provides its own woocommerce.css via Vite.
     */
    protected function removeDefaultStyles(): void
    {
        add_filter('woocommerce_enqueue_styles', '__return_empty_array');
    }

    /**
     * Register all WooCommerce hooks & filters for professional integration.
     */
    protected function registerHooks(): void
    {
        // ── Related Products: 4 columns, 4 products ──
        add_filter('woocommerce_output_related_products_args', function ($args) {
            $args['posts_per_page'] = 4;
            $args['columns']        = 4;
            return $args;
        });

        // ── Upsells: 4 columns ──
        add_filter('woocommerce_upsell_display_args', function ($args) {
            $args['posts_per_page'] = 4;
            $args['columns']        = 4;
            return $args;
        });

        // ── Disable native WC breadcrumbs (theme uses Flux breadcrumbs) ──
        add_action('init', function () {
            remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        });

        // ── Cross-sells: 4 columns ──
        add_filter('woocommerce_cross_sells_columns', function () {
            return 4;
        });

        // ── AJAX cart fragments: update cart counter in header ──
        add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
            $count = WC()->cart->get_cart_contents_count();
            $fragments['.flux-cart-count'] = '<span class="flux-cart-count">' . esc_html($count) . '</span>';
            return $fragments;
        });

        $this->registerShopCatalogFiltering();
    }

    /**
     * Apply advanced shop archive filters from query args.
     */
    protected function registerShopCatalogFiltering(): void
    {
        add_action('pre_get_posts', function (\WP_Query $query) {
            if (is_admin() || ! $query->is_main_query()) {
                return;
            }

            if (! $this->isWooCommerceCatalogQuery($query)) {
                return;
            }

            $this->applyCatalogTaxonomyFilters($query);
            $this->applyCatalogMetaFilters($query);
        }, 20);
    }

    protected function isWooCommerceCatalogQuery(\WP_Query $query): bool
    {
        if ($query->is_post_type_archive('product')) {
            return true;
        }

        if ($query->is_tax(['product_cat', 'product_tag', 'product_brand'])) {
            return true;
        }

        $vendorTaxonomy = $this->resolveVendorTaxonomy();
        if ($vendorTaxonomy !== null && $query->is_tax($vendorTaxonomy)) {
            return true;
        }

        return false;
    }

    protected function applyCatalogTaxonomyFilters(\WP_Query $query): void
    {
        $selectedCategories = $this->sanitizeSlugArray($_GET['fp_cat'] ?? []);
        $selectedBrands = $this->sanitizeSlugArray($_GET['fp_brand'] ?? []);
        $selectedVendors = $this->sanitizeSlugArray($_GET['fp_vendor'] ?? []);
        $vendorTaxonomy = $this->resolveVendorTaxonomy();

        $taxQuery = $query->get('tax_query');
        if (! is_array($taxQuery)) {
            $taxQuery = [];
        }

        if (! empty($selectedCategories) && ! $query->is_tax('product_cat')) {
            $taxQuery[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $selectedCategories,
                'operator' => 'IN',
            ];
        }

        if (! empty($selectedBrands) && taxonomy_exists('product_brand') && ! $query->is_tax('product_brand')) {
            $taxQuery[] = [
                'taxonomy' => 'product_brand',
                'field'    => 'slug',
                'terms'    => $selectedBrands,
                'operator' => 'IN',
            ];
        }

        if (! empty($selectedVendors) && $vendorTaxonomy !== null && ! $query->is_tax($vendorTaxonomy)) {
            $taxQuery[] = [
                'taxonomy' => $vendorTaxonomy,
                'field'    => 'slug',
                'terms'    => $selectedVendors,
                'operator' => 'IN',
            ];
        }

        if (count($taxQuery) > 1 && ! isset($taxQuery['relation'])) {
            $taxQuery['relation'] = 'AND';
        }

        $query->set('tax_query', $taxQuery);
    }

    protected function applyCatalogMetaFilters(\WP_Query $query): void
    {
        $priceRange = isset($_GET['fp_price']) ? sanitize_text_field(wp_unslash((string) $_GET['fp_price'])) : '';
        [$rangeMin, $rangeMax] = $this->parsePriceRange($priceRange);

        $minPrice = isset($_GET['min_price']) ? (float) wp_unslash((string) $_GET['min_price']) : null;
        $maxPrice = isset($_GET['max_price']) ? (float) wp_unslash((string) $_GET['max_price']) : null;

        if ($rangeMin !== null) {
            $minPrice = $rangeMin;
        }

        if ($rangeMax !== null) {
            $maxPrice = $rangeMax;
        }

        $metaQuery = $query->get('meta_query');
        if (! is_array($metaQuery)) {
            $metaQuery = [];
        }

        if ($minPrice !== null || $maxPrice !== null) {
            $from = $minPrice !== null ? max(0, $minPrice) : 0;
            $to = $maxPrice !== null ? max($from, $maxPrice) : 99999999;

            $metaQuery[] = [
                'key'     => '_price',
                'value'   => [$from, $to],
                'type'    => 'DECIMAL(10,2)',
                'compare' => 'BETWEEN',
            ];
        }

        $rating = isset($_GET['fp_rating']) ? (int) $_GET['fp_rating'] : 0;
        if ($rating > 0) {
            $metaQuery[] = [
                'key'     => '_wc_average_rating',
                'value'   => max(1, min(5, $rating)),
                'type'    => 'DECIMAL(3,2)',
                'compare' => '>=',
            ];
        }

        if (count($metaQuery) > 1 && ! isset($metaQuery['relation'])) {
            $metaQuery['relation'] = 'AND';
        }
        $query->set('meta_query', $metaQuery);

        $onSaleOnly = isset($_GET['on_sale']) && (string) $_GET['on_sale'] === '1';
        if ($onSaleOnly && function_exists('wc_get_product_ids_on_sale')) {
            $saleIds = array_map('intval', (array) wc_get_product_ids_on_sale());
            $saleIds = array_values(array_filter($saleIds));

            if (empty($saleIds)) {
                $query->set('post__in', [0]);

                return;
            }

            $existingPostIn = $query->get('post__in');
            if (is_array($existingPostIn) && ! empty($existingPostIn)) {
                $existingPostIn = array_map('intval', $existingPostIn);
                $query->set('post__in', array_values(array_intersect($existingPostIn, $saleIds)));

                return;
            }

            $query->set('post__in', $saleIds);
        }
    }

    /**
     * @param mixed $raw
     * @return string[]
     */
    protected function sanitizeSlugArray($raw): array
    {
        $values = is_array($raw) ? $raw : [$raw];
        $result = [];

        foreach ($values as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $slug = sanitize_title(wp_unslash((string) $value));
            if ($slug === '' || in_array($slug, $result, true)) {
                continue;
            }

            $result[] = $slug;
        }

        return $result;
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    protected function parsePriceRange(string $raw): array
    {
        if (! preg_match('/^\s*(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)\s*$/', $raw, $matches)) {
            return [null, null];
        }

        $min = (float) $matches[1];
        $max = (float) $matches[2];

        if ($max <= $min) {
            return [null, null];
        }

        return [$min, $max];
    }

    protected function resolveVendorTaxonomy(): ?string
    {
        $configured = config('theme-interface.woocommerce.shop_filters.vendor_taxonomies', []);
        $candidates = is_array($configured) ? $configured : [];

        if (empty($candidates)) {
            $candidates = [
                'product_vendor',
                'wcpv_product_vendors',
                'yith_shop_vendor',
                'dc_vendor_shop',
            ];
        }

        foreach ($candidates as $taxonomy) {
            if (! is_string($taxonomy) || $taxonomy === '') {
                continue;
            }

            if (taxonomy_exists($taxonomy)) {
                return $taxonomy;
            }
        }

        return null;
    }

    /**
     * Register dynamic WooCommerce partial/template overrides from Blade views.
     *
     * Any WooCommerce template_name like "myaccount/navigation.php" will be
     * resolved to "resources/views/woocommerce/myaccount/navigation.blade.php".
     */
    protected function registerTemplateOverrides(): void
    {
        add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
            return $this->resolveWooCommerceTemplateBridge($template_name, $template);
        }, 10, 3);

        add_filter('wc_get_template_part', function ($template, $slug, $name) {
            $template_name = $name ? "{$slug}-{$name}.php" : "{$slug}.php";

            return $this->resolveWooCommerceTemplateBridge($template_name, $template);
        }, 10, 3);
    }

    /**
     * Register WordPress template_include overrides used by WooCommerce pages.
     */
    protected function registerTemplateIncludeOverrides(): void
    {
        add_filter('template_include', function ($template) {
            if (is_account_page() && $this->shouldUseAssignedMyAccountTemplate()) {
                return $this->findAccountTemplate() ?: $template;
            }

            if (! function_exists('is_woocommerce') || ! is_woocommerce()) {
                return $template;
            }

            $template_name = $this->extractTemplateNameFromPath($template);
            if (! $template_name) {
                return $template;
            }

            $candidate = $this->findWooCommerceBladeTemplate($template_name);

            return $candidate ?: $template;
        }, 98);
    }

    private function findAccountTemplate(): ?string
    {
        $path = get_theme_file_path('resources/views/my-account.blade.php');

        return file_exists($path) ? $path : null;
    }

    private function shouldUseAssignedMyAccountTemplate(): bool
    {
        $account_page_id = function_exists('wc_get_page_id') ? (int) wc_get_page_id('myaccount') : 0;
        if ($account_page_id <= 0) {
            return false;
        }

        $assigned_slug = (string) get_page_template_slug($account_page_id);

        return basename($assigned_slug) === 'my-account.blade.php';
    }

    /**
     * Register WooCommerce View Composers.
     */
    protected function registerComposers(): void
    {
        View::composer(
            ['woocommerce.*', 'layouts.app', 'my-account'],
            \App\View\Composers\WooCommerceComposer::class
        );
    }

    /**
     * Resolve a WooCommerce template_name to a Blade template path.
     *
     * Example:
     * - myaccount/navigation.php -> resources/views/woocommerce/myaccount/navigation.blade.php
     */
    private function findWooCommerceBladeTemplate(string $templateName): ?string
    {
        $relative = trim(str_replace('\\', '/', $templateName), '/');
        if ($relative === '' || ! str_ends_with($relative, '.php')) {
            return null;
        }

        $bladeRelative = preg_replace('/\.php$/', '.blade.php', $relative);
        if (! is_string($bladeRelative)) {
            return null;
        }

        $path = get_theme_file_path("resources/views/woocommerce/{$bladeRelative}");

        return file_exists($path) ? $path : null;
    }

    /**
     * Resolve the WooCommerce bridge template used to render Blade overrides.
     */
    private function getWooCommerceBladeBridgeTemplate(): ?string
    {
        $bridge = get_theme_file_path('app/WooCommerce/woocommerce-template-bridge.php');

        return file_exists($bridge) ? $bridge : null;
    }

    /**
     * Return template name relative to template roots (e.g. archive-product.php).
     */
    private function extractTemplateNameFromPath(string $templatePath): ?string
    {
        $basename = wp_basename($templatePath);

        return $basename !== '' && str_ends_with($basename, '.php') ? $basename : null;
    }

    /**
     * Return bridge template when a Blade override exists for the template name.
     */
    private function resolveWooCommerceTemplateBridge(string $templateName, string $fallback): string
    {
        if (! $this->findWooCommerceBladeTemplate($templateName)) {
            return $fallback;
        }

        $bridge = $this->getWooCommerceBladeBridgeTemplate();
        if (! $bridge) {
            return $fallback;
        }

        $GLOBALS['flux_press_wc_blade_template_name'] = $templateName;

        return $bridge;
    }

    /**
     * Register WooCommerce Customizer settings.
     */
    protected function registerCustomizerSettings(): void
    {
        add_action('customize_register', function (\WP_Customize_Manager $wp_customize) {
            $wp_customize->add_section('flux_woocommerce_section', [
                'title'       => __('Flux Press: WooCommerce', 'flux-press'),
                'description' => __('WooCommerce integration settings for the theme.', 'flux-press'),
                'priority'    => 32,
            ]);

            // Show cart icon in header
            $wp_customize->add_setting('woocommerce_show_cart_icon', [
                'default'           => config('theme-interface.woocommerce.show_cart_icon', true),
                'sanitize_callback' => [$this, 'sanitizeBoolean'],
                'transport'         => 'refresh',
            ]);

            $wp_customize->add_control('woocommerce_show_cart_icon', [
                'label'   => __('Show cart icon in header', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'checkbox',
            ]);

            // Show shop sidebar
            $wp_customize->add_setting('woocommerce_show_shop_sidebar', [
                'default'           => config('theme-interface.woocommerce.show_shop_sidebar', true),
                'sanitize_callback' => [$this, 'sanitizeBoolean'],
                'transport'         => 'refresh',
            ]);

            $wp_customize->add_control('woocommerce_show_shop_sidebar', [
                'label'   => __('Show sidebar on shop pages', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'checkbox',
            ]);

            // Shop banner controls
            $wp_customize->add_setting('woocommerce_shop_banner_enabled', [
                'default'           => config('theme-interface.woocommerce.shop_banner.enabled', true),
                'sanitize_callback' => [$this, 'sanitizeBoolean'],
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_banner_enabled', [
                'label'   => __('Mostrar banner de tienda', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'checkbox',
            ]);

            $wp_customize->add_setting('woocommerce_shop_banner_title', [
                'default'           => config('theme-interface.woocommerce.shop_banner.title', ''),
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_banner_title', [
                'label'   => __('Titulo del banner', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'text',
            ]);

            $wp_customize->add_setting('woocommerce_shop_banner_subtitle', [
                'default'           => config('theme-interface.woocommerce.shop_banner.subtitle', ''),
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_banner_subtitle', [
                'label'   => __('Subtitulo del banner', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'text',
            ]);

            $wp_customize->add_setting('woocommerce_shop_banner_content_html', [
                'default'           => config('theme-interface.woocommerce.shop_banner.content_html', ''),
                'sanitize_callback' => 'wp_kses_post',
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_banner_content_html', [
                'label'       => __('Contenido HTML del banner', 'flux-press'),
                'description' => __('Permite texto enriquecido basico.', 'flux-press'),
                'section'     => 'flux_woocommerce_section',
                'type'        => 'textarea',
            ]);

            $wp_customize->add_setting('woocommerce_shop_banner_image_url', [
                'default'           => config('theme-interface.woocommerce.shop_banner.image_url', ''),
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_banner_image_url', [
                'label'   => __('Imagen del banner (URL)', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'url',
            ]);

            $wp_customize->add_setting('woocommerce_shop_banner_primary_cta_label', [
                'default'           => config('theme-interface.woocommerce.shop_banner.primary_cta_label', ''),
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_banner_primary_cta_label', [
                'label'   => __('Texto boton principal', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'text',
            ]);

            $wp_customize->add_setting('woocommerce_shop_banner_primary_cta_url', [
                'default'           => config('theme-interface.woocommerce.shop_banner.primary_cta_url', ''),
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_banner_primary_cta_url', [
                'label'   => __('URL boton principal', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'url',
            ]);

            $wp_customize->add_setting('woocommerce_shop_banner_secondary_cta_label', [
                'default'           => config('theme-interface.woocommerce.shop_banner.secondary_cta_label', ''),
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_banner_secondary_cta_label', [
                'label'   => __('Texto boton secundario', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'text',
            ]);

            $wp_customize->add_setting('woocommerce_shop_banner_secondary_cta_url', [
                'default'           => config('theme-interface.woocommerce.shop_banner.secondary_cta_url', ''),
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_banner_secondary_cta_url', [
                'label'   => __('URL boton secundario', 'flux-press'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'url',
            ]);

            // Shop filters limits
            $wp_customize->add_setting('woocommerce_shop_filters_categories_limit', [
                'default'           => config('theme-interface.woocommerce.shop_filters.categories_limit', 12),
                'sanitize_callback' => fn ($value): int => $this->sanitizeBoundedInt($value, 1, 30, 12),
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_filters_categories_limit', [
                'label'       => __('Limite de categorias en filtros', 'flux-press'),
                'section'     => 'flux_woocommerce_section',
                'type'        => 'number',
                'input_attrs' => ['min' => 1, 'max' => 30, 'step' => 1],
            ]);

            $wp_customize->add_setting('woocommerce_shop_filters_brands_limit', [
                'default'           => config('theme-interface.woocommerce.shop_filters.brands_limit', 12),
                'sanitize_callback' => fn ($value): int => $this->sanitizeBoundedInt($value, 1, 30, 12),
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_filters_brands_limit', [
                'label'       => __('Limite de marcas en filtros', 'flux-press'),
                'section'     => 'flux_woocommerce_section',
                'type'        => 'number',
                'input_attrs' => ['min' => 1, 'max' => 30, 'step' => 1],
            ]);

            $wp_customize->add_setting('woocommerce_shop_filters_vendors_limit', [
                'default'           => config('theme-interface.woocommerce.shop_filters.vendors_limit', 12),
                'sanitize_callback' => fn ($value): int => $this->sanitizeBoundedInt($value, 1, 30, 12),
                'transport'         => 'refresh',
            ]);
            $wp_customize->add_control('woocommerce_shop_filters_vendors_limit', [
                'label'       => __('Limite de vendedores en filtros', 'flux-press'),
                'section'     => 'flux_woocommerce_section',
                'type'        => 'number',
                'input_attrs' => ['min' => 1, 'max' => 30, 'step' => 1],
            ]);
        });
    }

    /**
     * @param mixed $value
     */
    protected function sanitizeBoundedInt($value, int $min, int $max, int $fallback): int
    {
        $int = (int) $value;
        if ($int < $min || $int > $max) {
            return $fallback;
        }

        return $int;
    }
}
