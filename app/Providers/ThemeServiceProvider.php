<?php
namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    public function boot(): void
    {
        parent::boot();
        
        // Limpiar cache cuando cambian opciones visuales del tema.
        add_action('updated_option', function ($option) {
            if (in_array($option, ['flux_theme_appearance', 'flux_theme_accent'], true)) {
                wp_cache_flush();
            }
        });
    }
}
