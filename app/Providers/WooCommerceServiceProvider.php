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
        });
    }
}
