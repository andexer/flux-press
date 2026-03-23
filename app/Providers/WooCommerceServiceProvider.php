<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class WooCommerceServiceProvider extends ServiceProvider
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
                'name'          => __('Shop Sidebar', 'sage'),
                'id'            => 'sidebar-shop',
                'description'   => __('Widget area for WooCommerce shop pages.', 'sage'),
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
        // Definitive method: return empty array to prevent WC from enqueuing any styles
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

        // ── Single product meta: clean output ──
        add_action('woocommerce_single_product_summary', function () {
            // Ensure SKU, categories, and tags are displayed
        }, 40);
    }

    /**
     * Register WooCommerce Customizer settings.
     */
    protected function registerCustomizerSettings(): void
    {
        add_action('customize_register', function (\WP_Customize_Manager $wp_customize) {
            $wp_customize->add_section('flux_woocommerce_section', [
                'title'       => __('Flux Press: WooCommerce', 'sage'),
                'description' => __('WooCommerce integration settings for the theme.', 'sage'),
                'priority'    => 32,
            ]);

            // Show cart icon in header
            $wp_customize->add_setting('woocommerce_show_cart_icon', [
                'default'           => config('theme-interface.woocommerce.show_cart_icon', true),
                'sanitize_callback' => [$this, 'sanitizeBoolean'],
                'transport'         => 'refresh',
            ]);

            $wp_customize->add_control('woocommerce_show_cart_icon', [
                'label'   => __('Show cart icon in header', 'sage'),
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
                'label'   => __('Show sidebar on shop pages', 'sage'),
                'section' => 'flux_woocommerce_section',
                'type'    => 'checkbox',
            ]);
        });
    }

    /**
     * Sanitize boolean values from Customizer.
     */
    public function sanitizeBoolean($value): bool
    {
        return (bool) $value;
    }
}
