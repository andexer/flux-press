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
        $normalized = [];

        foreach (self::rawItems($location) as $item) {
            $menuItem = self::normalizeItem($item);

            if ($menuItem !== null) {
                $normalized[] = $menuItem;
            }
        }

        return $normalized;
    }

    /**
     * Get top-level items (parent = 0) for a location.
     *
     * @return array<object>
     */
    public static function topLevelItems(string $location): array
    {
        $items = self::items($location);

        return array_values(array_filter($items, static function (object $item): bool {
            $parent = (string) ($item->menu_item_parent ?? '0');

            return $parent === '' || $parent === '0';
        }));
    }

    /**
     * Get items from the primary location, fallback to another location when empty.
     *
     * @return array<object>
     */
    public static function itemsWithFallback(string $primaryLocation, string $fallbackLocation): array
    {
        $primaryItems = self::items($primaryLocation);

        if (! empty($primaryItems)) {
            return $primaryItems;
        }

        return self::items($fallbackLocation);
    }

    /**
     * Resolve raw menu items from a registered location.
     *
     * @return array<object>
     */
    protected static function rawItems(string $location): array
    {
        $locations = get_nav_menu_locations();

        if (empty($locations[$location])) {
            return [];
        }

        $menu = wp_get_nav_menu_object($locations[$location]);

        if (! $menu) {
            return [];
        }

        $items = wp_get_nav_menu_items($menu->term_id);

        return is_array($items) ? $items : [];
    }

    /**
     * Normalize menu item into a stable object shape used by views/components.
     */
    protected static function normalizeItem(object $item): ?object
    {
        $title = isset($item->title) ? wp_strip_all_tags((string) $item->title) : '';
        $url = isset($item->url) ? esc_url_raw((string) $item->url) : '';

        if ($title === '' || $url === '') {
            return null;
        }

        $id = (int) ($item->ID ?? $item->id ?? 0);
        $parent = (string) ($item->menu_item_parent ?? '0');

        $normalized = clone $item;
        $normalized->ID = $id;
        $normalized->id = $id;
        $normalized->title = $title;
        $normalized->url = $url;
        $normalized->menu_item_parent = $parent === '' ? '0' : $parent;

        return $normalized;
    }
}
