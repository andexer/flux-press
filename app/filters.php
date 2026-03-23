<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'flux-press'));
});

/**
 * Override the core search block to use our custom Flux UI search form.
 *
 * @param  string $block_content
 * @param  array  $block
 * @return string
 */
add_filter('render_block_core/search', function ($block_content, $block) {
    return get_search_form(false);
}, 10, 2);

/**
 * Force classic shortcodes for WooCommerce Cart and Checkout blocks
 * to preserve the custom Flux UI Tailwind styling.
 *
 * @param  string $content
 * @return string
 */
add_filter('the_content', function ($content) {
    if (class_exists('WooCommerce') && in_the_loop() && is_main_query()) {
        if (is_cart() && has_block('woocommerce/cart', $content)) {
            return '[woocommerce_cart]';
        }
        if (is_checkout() && has_block('woocommerce/checkout', $content)) {
            return '[woocommerce_checkout]';
        }
    }
    return $content;
}, 5);
/**
 * Dynamic Icon Map for WooCommerce Account Endpoints.
 * This prevents hardcoding icons in the Blade templates.
 */
add_filter('woocommerce_account_menu_icons', function ($icons) {
    return array_merge($icons, [
        'dashboard'       => 'squares-2x2',
        'orders'          => 'shopping-bag',
        'downloads'       => 'arrow-down-tray',
        'edit-address'    => 'map-pin',
        'edit-account'    => 'user-circle',
        'payment-methods' => 'credit-card',
        'customer-logout' => 'arrow-right-start-on-rectangle',
    ]);
});
