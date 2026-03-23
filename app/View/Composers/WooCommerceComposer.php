<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

/**
 * Centralized WooCommerce data composer.
 *
 * Injects common WC data into woocommerce.* views and layouts,
 * eliminating scattered class_exists('WooCommerce') checks.
 */
class WooCommerceComposer extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'woocommerce.*',
        'layouts.app',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        $isActive = class_exists('WooCommerce');

        return [
            'isWooCommerceActive' => $isActive,
            'cartCount'           => $isActive && WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
            'showCartIcon'        => $isActive ? (bool) get_theme_mod('woocommerce_show_cart_icon', config('theme-interface.woocommerce.show_cart_icon', true)) : false,
            'currencySymbol'      => $isActive ? get_woocommerce_currency_symbol() : '',
            'accountEndpoints'    => $isActive ? wc_get_account_menu_items() : [],
            'accountIcons'        => $isActive ? apply_filters('woocommerce_account_menu_icons', []) : [],
        ];
    }
}
