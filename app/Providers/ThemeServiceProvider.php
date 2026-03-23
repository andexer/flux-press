<?php
namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    public function register(): void
    {
        parent::register();
        $this->mergeConfigFrom(__DIR__ . '/../../config/theme.php', 'theme');
    }
    
    public function boot(): void
    {
        parent::boot();

        // Tema inmediato
        add_action('wp_head', [$this, 'applyThemeOnLoad'], 1);
        add_action('admin_head', [$this, 'applyThemeOnLoad'], 1);
        
        // Listener Livewire
        add_action('wp_footer', [$this, 'livewireThemeListener'], 1);
        
        // Limpiar cache en cambios
        add_action('updated_option', function($option) {
            if (in_array($option, ['flux_theme_appearance', 'flux_theme_accent'])) {
                wp_cache_flush();
            }
        });
    }

    public function applyThemeOnLoad(): void
    {
        $appearance = get_option('flux_theme_appearance', 'system');
        $this->applyAppearance($appearance);
    }
    
    public function livewireThemeListener(): void
    {
        ?>
        <script id="flux-theme-listener">
        document.addEventListener('livewire:init', () => {
            Livewire.hook('message.processed', ({ component }) => {
                if (component.name === 'theme-settings') {
                    const html = document.documentElement;
                    const appearance = localStorage.getItem('flux-appearance') || '<?= esc_js(get_option("flux_theme_appearance", "system")) ?>';
                    
                    html.classList.remove('dark', 'light');
                    if (appearance === 'dark') html.classList.add('dark');
                }
            });
        });
        </script>
        <?php
    }
    
    private function applyAppearance(string $mode): void
    {
        $html = '<script>document.documentElement.classList.remove("dark","light");</script>';
        
        if ($mode === 'dark') {
            $html .= '<script>document.documentElement.classList.add("dark")</script>';
        } elseif ($mode === 'system') {
            $html .= '<script>
                const mql = window.matchMedia("(prefers-color-scheme: dark)");
                if (mql.matches) document.documentElement.classList.add("dark");
                mql.addEventListener("change", e => {
                    document.documentElement.classList.toggle("dark", e.matches);
                });
            </script>';
        }
        
        echo $html;
    }
}
