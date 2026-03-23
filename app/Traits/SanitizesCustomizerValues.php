<?php

namespace App\Traits;

/**
 * Shared sanitization callbacks for WordPress Customizer settings.
 *
 * Used by ThemeInterfaceServiceProvider and WooCommerceServiceProvider
 * to avoid code duplication.
 */
trait SanitizesCustomizerValues
{
    /**
     * Sanitize a value as boolean.
     *
     * Uses filter_var with FILTER_VALIDATE_BOOLEAN for proper
     * handling of string values like '1', 'true', 'on', 'yes'.
     */
    public function sanitizeBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
