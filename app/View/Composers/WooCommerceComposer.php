<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

/**
 * Centralized WooCommerce data composer.
 *
 * Injects common WC data into woocommerce.* views and layouts.
 */
class WooCommerceComposer extends Composer
{
    private const CACHE_GROUP = 'flux_wc_composer';
    private const CACHE_TTL = 300;

    /**
     * @var string[]
     */
    protected static $views = [
        'woocommerce.*',
        'layouts.app',
    ];

    /**
     * @return array<string,mixed>
     */
    public function with(): array
    {
        $isActive = class_exists('WooCommerce');
        $data = [
            'isWooCommerceActive' => $isActive,
            'cartCount'           => $isActive && WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
            'showCartIcon'        => $isActive ? (bool) get_theme_mod('woocommerce_show_cart_icon', config('theme-interface.woocommerce.show_cart_icon', true)) : false,
            'currencySymbol'      => $isActive ? get_woocommerce_currency_symbol() : '',
            'accountEndpoints'    => $isActive ? wc_get_account_menu_items() : [],
            'accountIcons'        => $isActive ? apply_filters('woocommerce_account_menu_icons', []) : [],
        ];

        if ($isActive && $this->isShopContext()) {
            $data['shopBanner'] = $this->shopBannerData();
            $data['shopFilters'] = $this->shopFiltersData();
        }

        return $data;
    }

    private function isShopContext(): bool
    {
        if (! function_exists('is_shop')) {
            return false;
        }

        if (is_shop() || is_product_category() || is_product_tag()) {
            return true;
        }

        if (taxonomy_exists('product_brand') && is_tax('product_brand')) {
            return true;
        }

        $vendorTaxonomy = $this->resolveVendorTaxonomy();
        if ($vendorTaxonomy !== null && is_tax($vendorTaxonomy)) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string,mixed>
     */
    private function shopBannerData(): array
    {
        $defaults = config('theme-interface.woocommerce.shop_banner', []);
        $shopUrl = function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('shop') : home_url('/');
        $saleUrl = add_query_arg('on_sale', '1', $shopUrl);

        $title = (string) get_theme_mod(
            'woocommerce_shop_banner_title',
            (string) ($defaults['title'] ?? '')
        );

        $subtitle = (string) get_theme_mod(
            'woocommerce_shop_banner_subtitle',
            (string) ($defaults['subtitle'] ?? '')
        );

        $contentHtml = wp_kses_post((string) get_theme_mod(
            'woocommerce_shop_banner_content_html',
            (string) ($defaults['content_html'] ?? '')
        ));

        $imageUrl = esc_url_raw((string) get_theme_mod(
            'woocommerce_shop_banner_image_url',
            (string) ($defaults['image_url'] ?? '')
        ));

        $primaryLabel = (string) get_theme_mod(
            'woocommerce_shop_banner_primary_cta_label',
            (string) ($defaults['primary_cta_label'] ?? '')
        );
        $primaryUrl = esc_url_raw((string) get_theme_mod(
            'woocommerce_shop_banner_primary_cta_url',
            (string) ($defaults['primary_cta_url'] ?? $shopUrl)
        ));

        $secondaryLabel = (string) get_theme_mod(
            'woocommerce_shop_banner_secondary_cta_label',
            (string) ($defaults['secondary_cta_label'] ?? '')
        );
        $secondaryUrl = esc_url_raw((string) get_theme_mod(
            'woocommerce_shop_banner_secondary_cta_url',
            (string) ($defaults['secondary_cta_url'] ?? $saleUrl)
        ));

        if ($primaryUrl === '') {
            $primaryUrl = $shopUrl;
        }

        if ($secondaryUrl === '') {
            $secondaryUrl = $saleUrl;
        }

        return [
            'enabled' => (bool) get_theme_mod(
                'woocommerce_shop_banner_enabled',
                (bool) ($defaults['enabled'] ?? true)
            ),
            'title'           => $title,
            'subtitle'        => $subtitle,
            'content_html'    => $contentHtml,
            'image_url'       => $imageUrl,
            'primary_label'   => $primaryLabel,
            'primary_url'     => $primaryUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url'   => $secondaryUrl,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function shopFiltersData(): array
    {
        $defaults = config('theme-interface.woocommerce.shop_filters', []);

        $categoryLimit = $this->boundedInt(
            get_theme_mod('woocommerce_shop_filters_categories_limit', $defaults['categories_limit'] ?? 12),
            1,
            30
        );
        $brandsLimit = $this->boundedInt(
            get_theme_mod('woocommerce_shop_filters_brands_limit', $defaults['brands_limit'] ?? 12),
            1,
            30
        );
        $vendorsLimit = $this->boundedInt(
            get_theme_mod('woocommerce_shop_filters_vendors_limit', $defaults['vendors_limit'] ?? 12),
            1,
            30
        );

        $priceRanges = $this->sanitizePriceRanges($defaults['price_ranges'] ?? []);
        $selectedPriceRange = sanitize_text_field((string) ($_GET['fp_price'] ?? ''));

        $selectedCategories = $this->sanitizeSlugArray($_GET['fp_cat'] ?? []);
        $selectedBrands = $this->sanitizeSlugArray($_GET['fp_brand'] ?? []);
        $selectedVendors = $this->sanitizeSlugArray($_GET['fp_vendor'] ?? []);
        $selectedRating = $this->boundedInt($_GET['fp_rating'] ?? 0, 0, 5);
        $selectedOnSale = isset($_GET['on_sale']) && (string) $_GET['on_sale'] === '1';

        $currentMinPrice = isset($_GET['min_price']) ? (float) wp_unslash((string) $_GET['min_price']) : null;
        $currentMaxPrice = isset($_GET['max_price']) ? (float) wp_unslash((string) $_GET['max_price']) : null;

        $activeFilters = $this->buildActiveFiltersSummary([
            'categories' => $selectedCategories,
            'brands'     => $selectedBrands,
            'vendors'    => $selectedVendors,
            'price'      => $selectedPriceRange,
            'rating'     => $selectedRating,
            'on_sale'    => $selectedOnSale,
        ]);

        $vendorTaxonomy = $this->resolveVendorTaxonomy();

        return [
            'form_action'         => $this->shopFormAction(),
            'price_ranges'        => $priceRanges,
            'categories'          => $this->termsForTaxonomy('product_cat', $categoryLimit),
            'brands'              => taxonomy_exists('product_brand') ? $this->termsForTaxonomy('product_brand', $brandsLimit) : [],
            'vendors'             => $vendorTaxonomy ? $this->termsForTaxonomy($vendorTaxonomy, $vendorsLimit) : [],
            'vendor_taxonomy'     => $vendorTaxonomy,
            'selected_categories' => $selectedCategories,
            'selected_brands'     => $selectedBrands,
            'selected_vendors'    => $selectedVendors,
            'selected_price'      => $selectedPriceRange,
            'selected_rating'     => $selectedRating,
            'selected_on_sale'    => $selectedOnSale,
            'min_price'           => $currentMinPrice,
            'max_price'           => $currentMaxPrice,
            'active_filters'      => $activeFilters,
            'active_count'        => count($activeFilters),
            'preserve'            => $this->preservedParams(),
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function termsForTaxonomy(string $taxonomy, int $limit): array
    {
        $limit = max(1, min(30, $limit));
        $cacheKey = "terms:{$taxonomy}:{$limit}";

        $data = $this->remember($cacheKey, function () use ($taxonomy, $limit) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => true,
                'number'     => $limit,
                'orderby'    => 'count',
                'order'      => 'DESC',
            ]);

            if (! is_array($terms) || is_wp_error($terms)) {
                return [];
            }

            $items = [];
            foreach ($terms as $term) {
                if (! $term instanceof \WP_Term) {
                    continue;
                }

                $url = get_term_link($term);
                if (is_wp_error($url)) {
                    continue;
                }

                $items[] = [
                    'id'    => (int) $term->term_id,
                    'name'  => (string) $term->name,
                    'slug'  => (string) $term->slug,
                    'count' => (int) $term->count,
                    'url'   => (string) $url,
                ];
            }

            return array_slice($items, 0, $limit);
        });

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<int|string,mixed> $rawRanges
     * @return array<int,array<string,mixed>>
     */
    private function sanitizePriceRanges(array $rawRanges): array
    {
        $ranges = [];
        foreach ($rawRanges as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = sanitize_text_field((string) ($row['label'] ?? ''));
            $min = is_numeric($row['min'] ?? null) ? (float) $row['min'] : null;
            $max = is_numeric($row['max'] ?? null) ? (float) $row['max'] : null;

            if ($label === '' || $min === null || $max === null || $min < 0 || $max <= $min) {
                continue;
            }

            $ranges[] = [
                'label' => $label,
                'min'   => $min,
                'max'   => $max,
                'key'   => $min . '-' . $max,
            ];
        }

        return $ranges;
    }

    /**
     * @param mixed $value
     * @return string[]
     */
    private function sanitizeSlugArray($value): array
    {
        $raw = is_array($value) ? $value : [$value];
        $items = [];

        foreach ($raw as $item) {
            if (! is_scalar($item)) {
                continue;
            }

            $slug = sanitize_title(wp_unslash((string) $item));
            if ($slug === '' || in_array($slug, $items, true)) {
                continue;
            }

            $items[] = $slug;
        }

        return $items;
    }

    /**
     * @param array<string,mixed> $current
     * @return string[]
     */
    private function buildActiveFiltersSummary(array $current): array
    {
        $result = [];

        foreach ((array) ($current['categories'] ?? []) as $slug) {
            $result[] = sprintf(__('Categoria: %s', 'sage'), (string) $slug);
        }

        foreach ((array) ($current['brands'] ?? []) as $slug) {
            $result[] = sprintf(__('Marca: %s', 'sage'), (string) $slug);
        }

        foreach ((array) ($current['vendors'] ?? []) as $slug) {
            $result[] = sprintf(__('Vendedor: %s', 'sage'), (string) $slug);
        }

        $price = (string) ($current['price'] ?? '');
        if ($price !== '') {
            $result[] = sprintf(__('Rango: %s', 'sage'), $price);
        }

        $rating = (int) ($current['rating'] ?? 0);
        if ($rating > 0) {
            $result[] = sprintf(__('Valoracion >= %d', 'sage'), $rating);
        }

        if ((bool) ($current['on_sale'] ?? false)) {
            $result[] = __('Solo ofertas', 'sage');
        }

        return $result;
    }

    /**
     * @return array<string,string>
     */
    private function preservedParams(): array
    {
        $keep = [];
        $allowed = ['orderby', 'paged', 'per_page'];

        foreach ($allowed as $key) {
            if (! isset($_GET[$key]) || ! is_scalar($_GET[$key])) {
                continue;
            }

            $keep[$key] = sanitize_text_field(wp_unslash((string) $_GET[$key]));
        }

        return $keep;
    }

    private function shopFormAction(): string
    {
        if (is_product_category() || is_product_tag() || is_tax()) {
            $queriedObject = get_queried_object();
            if ($queriedObject instanceof \WP_Term) {
                $url = get_term_link($queriedObject);
                if (! is_wp_error($url)) {
                    return (string) $url;
                }
            }
        }

        if (function_exists('wc_get_page_permalink')) {
            return (string) wc_get_page_permalink('shop');
        }

        return home_url('/');
    }

    private function resolveVendorTaxonomy(): ?string
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

        $transientKey = 'flux_wc_cmp_' . md5($key);
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
