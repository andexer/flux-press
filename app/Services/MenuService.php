<?php

namespace App\Services;

/**
 * Centralized service for WordPress navigation menu retrieval.
 *
 * Eliminates duplication of menu-fetching logic that was previously
 * spread across HeaderComposer, FooterComposer, and app.blade.php.
 */
class MenuService
{
    /**
     * Get menu items for a given registered menu location.
     *
     * @param  string  $location  Registered menu location identifier
     * @return array<object>  Array of WP_Post menu item objects
     */
    public static function items(string $location): array
    {
        $locations = get_nav_menu_locations();

        if (empty($locations[$location])) {
            return [];
        }

        $menu = wp_get_nav_menu_object($locations[$location]);

        if (! $menu) {
            return [];
        }

        return wp_get_nav_menu_items($menu->term_id) ?: [];
    }
}
