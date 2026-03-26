<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FluxThemePresetService
{
    protected string $presetsPath;

    protected string $schemaVersion = '1.0';

    public function __construct()
    {
        $this->presetsPath = get_theme_file_path('config/theme-presets');
    }

    public function getAvailablePresets(): array
    {
        $presets = [];

        if (! is_dir($this->presetsPath)) {
            return $presets;
        }

        $files = glob($this->presetsPath.'/*.json');

        foreach ($files as $file) {
            $key = basename($file, '.json');
            $preset = $this->loadPreset($key);

            if ($preset !== null) {
                $presets[$key] = [
                    'key' => $key,
                    'name' => $preset['name'] ?? $key,
                    'description' => $preset['description'] ?? '',
                    'thumbnail' => $preset['thumbnail'] ?? '',
                    'category' => $preset['category'] ?? 'general',
                    'version' => $preset['version'] ?? '1.0',
                ];
            }
        }

        return $presets;
    }

    public function loadPreset(string $key): ?array
    {
        $file = $this->presetsPath.'/'.$key.'.json';

        if (! file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        $preset = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('FluxThemePreset: Error decoding JSON', [
                'file' => $file,
                'error' => json_last_error_msg(),
            ]);

            return null;
        }

        if (! $this->validatePreset($preset)) {
            return null;
        }

        return $preset;
    }

    protected function validatePreset(array $preset): bool
    {
        $required = ['name', 'schema_version', 'settings'];

        foreach ($required as $field) {
            if (! isset($preset[$field])) {
                Log::warning('FluxThemePreset: Missing required field', ['field' => $field]);

                return false;
            }
        }

        if (! version_compare($preset['schema_version'], $this->schemaVersion, '<=')) {
            return false;
        }

        return true;
    }

    public function applyPreset(string $key): array
    {
        $preset = $this->loadPreset($key);

        if ($preset === null) {
            return [
                'success' => false,
                'message' => 'Preset no encontrado o inválido',
            ];
        }

        try {
            $this->createBackup();

            if (isset($preset['settings'])) {
                $this->applySettings($preset['settings']);
            }

            if (isset($preset['home_builder'])) {
                $this->applyHomeBuilderConfig($preset['home_builder']);
            }

            if (isset($preset['menus'])) {
                $this->setupMenus($preset['menus']);
            }

            if (isset($preset['pages'])) {
                $this->setupPages($preset['pages']);
            }

            if (isset($preset['widgets'])) {
                $this->applyWidgets($preset['widgets']);
            }

            do_action('flux_preset_applied', $key, $preset);

            return [
                'success' => true,
                'message' => sprintf('Preset "%s" aplicado correctamente', $preset['name']),
                'preset' => $preset['name'],
            ];
        } catch (\Exception $e) {
            Log::error('FluxThemePreset: Error applying preset', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al aplicar el preset: '.$e->getMessage(),
            ];
        }
    }

    protected function applySettings(array $settings): void
    {
        if (isset($settings['header'])) {
            if (isset($settings['header']['style'])) {
                set_theme_mod('header_style', $settings['header']['style']);
            }
            if (isset($settings['header']['sticky'])) {
                set_theme_mod('header_sticky', (bool) $settings['header']['sticky']);
            }
            if (isset($settings['header']['mega_menu'])) {
                $this->applyMegaMenuSettings($settings['header']['mega_menu']);
            }
        }

        if (isset($settings['footer'])) {
            if (isset($settings['footer']['style'])) {
                set_theme_mod('footer_style', $settings['footer']['style']);
            }
        }

        if (isset($settings['home'])) {
            if (isset($settings['home']['layout'])) {
                set_theme_mod('home_layout', $settings['home']['layout']);
            }
            if (isset($settings['home']['sections'])) {
                foreach ($settings['home']['sections'] as $section => $enabled) {
                    set_theme_mod("home_show_{$section}", (bool) $enabled);
                }
            }
        }

        if (isset($settings['colors'])) {
            if (isset($settings['colors']['accent'])) {
                update_option('flux_theme_accent', $settings['colors']['accent']);
            }
            if (isset($settings['colors']['appearance'])) {
                update_option('flux_theme_appearance', $settings['colors']['appearance']);
            }
        }

        if (isset($settings['woocommerce']) && class_exists('WooCommerce')) {
            $this->applyWooCommerceSettings($settings['woocommerce']);
        }
    }

    protected function applyMegaMenuSettings(array $settings): void
    {
        $fields = [
            'enabled' => 'header_enable_mega_menu',
            'show_categories' => 'header_megamenu_show_categories',
            'show_top_rated' => 'header_megamenu_show_top_rated',
            'show_best_selling' => 'header_megamenu_show_best_selling',
            'show_pages' => 'header_megamenu_show_pages',
            'categories_limit' => 'header_megamenu_categories_limit',
            'top_rated_limit' => 'header_megamenu_top_rated_limit',
            'best_selling_limit' => 'header_megamenu_best_selling_limit',
            'pages_limit' => 'header_megamenu_pages_limit',
            'featured_item_text' => 'header_megamenu_featured_item_text',
        ];

        foreach ($fields as $key => $themeMod) {
            if (isset($settings[$key])) {
                set_theme_mod($themeMod, $settings[$key]);
            }
        }
    }

    protected function applyHomeBuilderConfig(array $config): void
    {
        if (isset($config['content_mode'])) {
            set_theme_mod('home_ecommerce_content_mode', $config['content_mode']);
        }

        if (isset($config['section_order'])) {
            $order = is_array($config['section_order'])
                ? implode(',', $config['section_order'])
                : $config['section_order'];
            set_theme_mod('home_ecommerce_section_order', $order);
        }

        if (isset($config['sections'])) {
            foreach ($config['sections'] as $section => $enabled) {
                set_theme_mod("home_ecommerce_show_{$section}", (bool) $enabled);
            }
        }

        if (isset($config['limits'])) {
            foreach ($config['limits'] as $key => $value) {
                set_theme_mod("home_ecommerce_{$key}_limit", (int) $value);
            }
        }

        if (isset($config['hero'])) {
            foreach ($config['hero'] as $key => $value) {
                if ($key === 'slides_json') {
                    set_theme_mod("home_ecommerce_hero_{$key}", json_encode($value));
                } else {
                    set_theme_mod("home_ecommerce_hero_{$key}", $value);
                }
            }
        }
    }

    protected function applyWooCommerceSettings(array $settings): void
    {
        if (isset($settings['show_cart_icon'])) {
            set_theme_mod('woocommerce_show_cart_icon', (bool) $settings['show_cart_icon']);
        }
        if (isset($settings['show_shop_sidebar'])) {
            set_theme_mod('woocommerce_show_shop_sidebar', (bool) $settings['show_shop_sidebar']);
        }
    }

    protected function setupMenus(array $menuConfig): void
    {
        $menus = wp_get_nav_menus();
        $locations = [];

        foreach ($menuConfig as $location => $menuSlug) {
            foreach ($menus as $menu) {
                if ($menu->slug === $menuSlug) {
                    $locations[$location] = absint($menu->term_id);
                    break;
                }
            }
        }

        if (! empty($locations)) {
            set_theme_mod('nav_menu_locations', $locations);
        }
    }

    protected function setupPages(array $pageConfig): void
    {
        if (isset($pageConfig['front_page'])) {
            $home = get_posts([
                'name' => $pageConfig['front_page'],
                'post_type' => 'page',
                'post_status' => 'publish',
                'posts_per_page' => 1,
            ]);

            if (! empty($home)) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $home[0]->ID);
            }
        }

        if (isset($pageConfig['blog'])) {
            $blog = get_posts([
                'name' => $pageConfig['blog'],
                'post_type' => 'page',
                'post_status' => 'publish',
                'posts_per_page' => 1,
            ]);

            if (! empty($blog)) {
                update_option('page_for_posts', $blog[0]->ID);
            }
        }

        if (class_exists('WooCommerce')) {
            $wcPages = ['shop', 'cart', 'checkout', 'my_account'];
            foreach ($wcPages as $wcPage) {
                if (isset($pageConfig[$wcPage])) {
                    $page = get_posts([
                        'name' => $pageConfig[$wcPage],
                        'post_type' => 'page',
                        'post_status' => 'publish',
                        'posts_per_page' => 1,
                    ]);

                    if (! empty($page)) {
                        update_option("woocommerce_{$wcPage}_page_id", $page[0]->ID);
                    }
                }
            }
        }

        flush_rewrite_rules();
    }

    protected function applyWidgets(array $widgets): void
    {
        if (empty($widgets)) {
            return;
        }

        if (class_exists('Soo_Demo_Widgets_Importer')) {
            $importer = new \Soo_Demo_Widgets_Importer;
            $data = json_decode(json_encode($widgets));
            $importer->import($data);
        }
    }

    protected function createBackup(): void
    {
        $backup = [
            'timestamp' => time(),
            'theme_mods' => get_theme_mods(),
            'flux_options' => [
                'accent' => get_option('flux_theme_accent'),
                'appearance' => get_option('flux_theme_appearance'),
            ],
        ];

        $backups = get_option('flux_preset_backups', []);
        $backups[] = $backup;
        $backups = array_slice($backups, -5);

        update_option('flux_preset_backups', $backups);
    }

    public function getBackups(): array
    {
        return get_option('flux_preset_backups', []);
    }

    public function restoreBackup(int $index): bool
    {
        $backups = $this->getBackups();

        if (! isset($backups[$index])) {
            return false;
        }

        $backup = $backups[$index];

        if (isset($backup['theme_mods'])) {
            foreach ($backup['theme_mods'] as $key => $value) {
                if ($key !== 'nav_menu_locations') {
                    set_theme_mod($key, $value);
                }
            }
        }

        if (isset($backup['flux_options'])) {
            if (isset($backup['flux_options']['accent'])) {
                update_option('flux_theme_accent', $backup['flux_options']['accent']);
            }
            if (isset($backup['flux_options']['appearance'])) {
                update_option('flux_theme_appearance', $backup['flux_options']['appearance']);
            }
        }

        return true;
    }

    public function exportCurrentConfig(): array
    {
        return [
            'name' => 'Custom Export '.wp_date('Y-m-d H:i:s'),
            'schema_version' => $this->schemaVersion,
            'version' => wp_get_theme()->get('Version'),
            'settings' => [
                'header' => [
                    'style' => get_theme_mod('header_style', 'classic'),
                    'sticky' => get_theme_mod('header_sticky', false),
                    'mega_menu' => [
                        'enabled' => get_theme_mod('header_enable_mega_menu', true),
                        'show_categories' => get_theme_mod('header_megamenu_show_categories', true),
                        'show_top_rated' => get_theme_mod('header_megamenu_show_top_rated', true),
                        'show_best_selling' => get_theme_mod('header_megamenu_show_best_selling', true),
                        'show_pages' => get_theme_mod('header_megamenu_show_pages', true),
                        'categories_limit' => get_theme_mod('header_megamenu_categories_limit', 6),
                        'top_rated_limit' => get_theme_mod('header_megamenu_top_rated_limit', 4),
                        'best_selling_limit' => get_theme_mod('header_megamenu_best_selling_limit', 4),
                        'pages_limit' => get_theme_mod('header_megamenu_pages_limit', 6),
                        'featured_item_text' => get_theme_mod('header_megamenu_featured_item_text', 'Descubrir'),
                    ],
                ],
                'footer' => [
                    'style' => get_theme_mod('footer_style', 'corporate'),
                ],
                'home' => [
                    'layout' => get_theme_mod('home_layout', 'corporate'),
                    'sections' => [
                        'features' => get_theme_mod('home_show_features', true),
                        'stats' => get_theme_mod('home_show_stats', true),
                        'cta' => get_theme_mod('home_show_cta', true),
                        'posts' => get_theme_mod('home_show_posts', true),
                        'widgets' => get_theme_mod('home_show_widgets', true),
                    ],
                ],
                'colors' => [
                    'accent' => get_option('flux_theme_accent', 'sky'),
                    'appearance' => get_option('flux_theme_appearance', 'light'),
                ],
            ],
            'home_builder' => [
                'content_mode' => get_theme_mod('home_ecommerce_content_mode', 'hybrid'),
                'section_order' => explode(',', get_theme_mod('home_ecommerce_section_order', 'hero,categories,best_sellers,top_rated,brands,promos,newsletter,blog')),
                'sections' => [
                    'hero' => get_theme_mod('home_ecommerce_show_hero', true),
                    'categories' => get_theme_mod('home_ecommerce_show_categories', true),
                    'best_sellers' => get_theme_mod('home_ecommerce_show_best_sellers', true),
                    'top_rated' => get_theme_mod('home_ecommerce_show_top_rated', true),
                    'brands' => get_theme_mod('home_ecommerce_show_brands', true),
                    'promos' => get_theme_mod('home_ecommerce_show_promos', true),
                    'newsletter' => get_theme_mod('home_ecommerce_show_newsletter', true),
                    'blog' => get_theme_mod('home_ecommerce_show_blog', true),
                ],
            ],
        ];
    }

    public function downloadPresetAsJson(string $key): ?string
    {
        $preset = $this->loadPreset($key);

        if ($preset === null) {
            return null;
        }

        return wp_json_encode($preset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
