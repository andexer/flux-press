<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PerformanceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar servicios de caché o integraciones específicas de Redis/Memcached.
        // Ej: $this->app->singleton('redis', function ($app) { ... });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Definir reglas globales de performance (ej. deshabilitar emoji scripts, optimización de assets).
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
    }
}
