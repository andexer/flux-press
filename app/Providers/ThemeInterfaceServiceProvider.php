<?php

namespace App\Providers;

use App\Customizer\FluxPresetSelectorControl;
use App\Services\FluxThemePresetService;
use App\Services\HomeEcommerceDataService;
use App\Traits\SanitizesCustomizerValues;
use App\View\Composers\AppComposer;
use App\View\Composers\FooterComposer;
use App\View\Composers\HeaderComposer;
use App\View\Composers\HomeComposer;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use WP_Customize_Cropped_Image_Control;

class ThemeInterfaceServiceProvider extends ServiceProvider
{
    use SanitizesCustomizerValues;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/theme-interface.php',
            'theme-interface'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enlazar View Composers a los parciales de header, footer y layout global
        View::composer(['layouts.app', 'sections.header', 'sections.footer'], AppComposer::class);
        View::composer('sections.header', HeaderComposer::class);
        View::composer('sections.footer', FooterComposer::class);
        View::composer(['front-page', 'page-home'], HomeComposer::class);

        // Render de header/footer basado en hooks de WordPress.
        add_action('get_header', [$this, 'renderHeaderContent'], 1);
        add_action('get_footer', [$this, 'renderFooterContent'], 1);

        // Hooks adicionales para meta tags y scripts.
        add_action('wp_head', [$this, 'renderMetaTags'], 5);
        add_action('wp_footer', [$this, 'renderFooterScripts'], 5);

        // Registrar secciones del WordPress Customizer.
        add_action('customize_register', [$this, 'registerCustomizerSettings']);
        add_action('customize_controls_enqueue_scripts', [$this, 'enqueuePresetSelectorAssets']);

        // Registrar bloques/shortcodes del Home ecommerce.
        add_action('init', [$this, 'registerHomeEcommerceBlocksAndShortcodes']);

        // Visual Builder del Home ecommerce en barra/admin.
        add_action('admin_menu', [$this, 'registerFluxVisualBuilderAdminMenu']);
        add_action('admin_bar_menu', [$this, 'registerFluxVisualBuilderAdminBarMenu'], 90);
        add_action('admin_head', [$this, 'renderFluxVisualBuilderAdminHead']);
        add_action('admin_footer', [$this, 'renderFluxVisualBuilderAdminFooter']);
        add_action('template_redirect', [$this, 'ensureFluxBuilderAccess'], 1);
        add_filter('show_admin_bar', [$this, 'hideAdminBarInFluxPreview'], 20);

        // AJAX handlers para presets del tema.
        add_action('wp_ajax_flux_apply_preset', [$this, 'ajaxApplyPreset']);
        add_action('wp_ajax_flux_export_config', [$this, 'ajaxExportConfig']);
        add_action('wp_ajax_flux_restore_backup', [$this, 'ajaxRestoreBackup']);
    }

    /**
     * Registrar menu del Visual Builder en el admin de WordPress.
     */
    public function registerFluxVisualBuilderAdminMenu(): void
    {
        add_menu_page(
            __('Flux Visual Builder', 'flux-press'),
            __('Flux Builder', 'flux-press'),
            'edit_theme_options',
            'flux-visual-builder',
            [$this, 'renderFluxVisualBuilderAdminPage'],
            'dashicons-screenoptions',
            61
        );
    }

    /**
     * Renderizar pantalla admin del Visual Builder.
     */
    public function renderFluxVisualBuilderAdminPage(): void
    {
        if (! current_user_can('edit_theme_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta pantalla.', 'flux-press'));
        }

        echo View::make('admin.flux-visual-builder')->render();
    }

    /**
     * Agregar acceso rapido al Visual Builder en la admin bar.
     */
    public function registerFluxVisualBuilderAdminBarMenu(\WP_Admin_Bar $adminBar): void
    {
        if (! is_user_logged_in() || ! current_user_can('edit_theme_options')) {
            return;
        }

        $targetUrl = add_query_arg('flux_builder', '1', home_url('/'));

        $adminBar->add_node([
            'id' => 'flux-visual-builder-live',
            'title' => __('Flux Builder', 'flux-press'),
            'href' => $targetUrl,
            'meta' => [
                'class' => 'flux-visual-builder-live',
                'title' => __('Abrir Flux Visual Builder', 'flux-press'),
            ],
        ]);
    }

    /**
     * Cargar estilos/scripts del Visual Builder en su pantalla admin.
     */
    public function renderFluxVisualBuilderAdminHead(): void
    {
        if (! $this->isFluxVisualBuilderAdminPage()) {
            return;
        }

        echo Vite::withEntryPoints([
            'resources/css/app.css',
            'resources/js/app.js',
        ])->toHtml();

        echo View::make('admin.partials.flux-visual-builder-head')->render();
    }

    /**
     * Cargar scripts de Livewire/Flux al final de la pantalla admin del builder.
     */
    public function renderFluxVisualBuilderAdminFooter(): void
    {
        if (! $this->isFluxVisualBuilderAdminPage()) {
            return;
        }

        echo View::make('admin.partials.flux-visual-builder-footer')->render();
    }

    /**
     * Forzar autenticacion/capacidad al entrar en modo flux_builder frontend.
     */
    public function ensureFluxBuilderAccess(): void
    {
        if (is_admin()) {
            return;
        }

        $isFluxPreview = isset($_GET['flux_builder']) && (string) $_GET['flux_builder'] !== '0';
        if (! $isFluxPreview) {
            return;
        }

        if (is_user_logged_in() && current_user_can('edit_theme_options')) {
            return;
        }

        $targetUrl = add_query_arg('flux_builder', '1', home_url('/'));

        if (! is_user_logged_in()) {
            wp_safe_redirect(wp_login_url($targetUrl));
            exit;
        }

        wp_die(
            esc_html__('No tienes permisos para usar Flux Visual Builder.', 'flux-press'),
            esc_html__('Acceso denegado', 'flux-press'),
            ['response' => 403]
        );
    }

    /**
     * Ocultar admin bar en preview frontend del Visual Builder.
     */
    public function hideAdminBarInFluxPreview(bool $show): bool
    {
        if (is_admin()) {
            return $show;
        }

        $isFluxPreview = isset($_GET['flux_builder']) && (string) $_GET['flux_builder'] === '1';

        if ($isFluxPreview && current_user_can('edit_theme_options')) {
            return false;
        }

        return $show;
    }

    /**
     * Determinar si estamos en la pagina admin del Visual Builder.
     */
    protected function isFluxVisualBuilderAdminPage(): bool
    {
        if (! is_admin()) {
            return false;
        }

        $page = sanitize_key((string) ($_GET['page'] ?? ''));

        return $page === 'flux-visual-builder';
    }

    /**
     * Renderizar el header con Acorn cuando se dispara get_header.
     */
    public function renderHeaderContent(): void
    {
        if (did_action('get_header') > 1) {
            return;
        }

        echo View::make('sections.header')->render();
    }

    /**
     * Renderizar el footer con Acorn cuando se dispara get_footer.
     */
    public function renderFooterContent(): void
    {
        if (did_action('get_footer') > 1) {
            return;
        }

        echo View::make('sections.footer')->render();
    }

    /**
     * Meta tags dinamicos basados en el contexto.
     */
    public function renderMetaTags(): void
    {
        if (! is_front_page()) {
            return;
        }

        echo '<meta name="description" content="'.esc_attr(get_bloginfo('description')).'">'."\n";
    }

    /**
     * Scripts dinamicos del footer (ej. analytics configurable).
     */
    public function renderFooterScripts(): void
    {
        if (! get_theme_mod('analytics_enabled', false)) {
            return;
        }

        $analyticsId = (string) get_theme_mod('analytics_id');
        if ($analyticsId === '') {
            return;
        }

        echo "<!-- Global site tag (gtag.js) - Google Analytics -->\n";
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id='.esc_attr($analyticsId)."\"></script>\n";
        echo "<script>\n";
        echo "  window.dataLayer = window.dataLayer || [];\n";
        echo "  function gtag(){dataLayer.push(arguments);}\n";
        echo "  gtag('js', new Date());\n";
        echo "  gtag('config', '".esc_js($analyticsId)."');\n";
        echo "</script>\n";
    }

    /**
     * Enqueue assets para el selector de presets en el Customizer.
     */
    public function enqueuePresetSelectorAssets(): void
    {
        $presetCss = get_theme_file_uri('resources/css/preset-selector.css');
        if (file_exists(get_theme_file_path('resources/css/preset-selector.css'))) {
            wp_enqueue_style(
                'flux-preset-selector',
                $presetCss,
                [],
                wp_get_theme()->get('Version')
            );
        }
    }

    /**
     * Registrar las secciones y controles del Customizer para Header y Footer.
     */
    public function registerCustomizerSettings(\WP_Customize_Manager $wp_customize): void
    {
        // ─── Sección: Presets del Tema ─────────────────────────
        $this->registerThemePresetsCustomizerSettings($wp_customize);

        // ─── Sección: Header ───────────────────────────────────
        $wp_customize->add_section('flux_header_section', [
            'title' => __('Flux Press: Header', 'flux-press'),
            'description' => __('Edita titulos y URLs desde Apariencia > Menus usando: Primary Navigation, Header Highlights Navigation y Header Utility (Left/Right).', 'flux-press'),
            'priority' => 30,
        ]);

        $wp_customize->add_setting('header_style', [
            'default' => config('theme-interface.header.default_style', 'classic'),
            'sanitize_callback' => [$this, 'sanitizeHeaderStyle'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_style', [
            'label' => __('Estilo del Header', 'flux-press'),
            'section' => 'flux_header_section',
            'type' => 'select',
            'choices' => $this->styleChoices('header'),
        ]);

        $wp_customize->add_setting('header_sticky', [
            'default' => config('theme-interface.header.sticky', false),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_sticky', [
            'label' => __('Header Sticky (fijo al hacer scroll)', 'flux-press'),
            'section' => 'flux_header_section',
            'type' => 'checkbox',
        ]);

        $this->registerMegaMenuCustomizerSettings($wp_customize);

        // ─── Sección: Footer ───────────────────────────────────
        $wp_customize->add_section('flux_footer_section', [
            'title' => __('Flux Press: Footer', 'flux-press'),
            'priority' => 31,
        ]);

        $wp_customize->add_setting('footer_style', [
            'default' => config('theme-interface.footer.default_style', 'corporate'),
            'sanitize_callback' => [$this, 'sanitizeFooterStyle'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('footer_style', [
            'label' => __('Estilo del Footer', 'flux-press'),
            'section' => 'flux_footer_section',
            'type' => 'select',
            'choices' => $this->styleChoices('footer'),
        ]);

        $this->registerHomeCustomizerSettings($wp_customize);
    }

    /**
     * Registrar ajustes del Mega Menu para Header.
     */
    protected function registerMegaMenuCustomizerSettings(\WP_Customize_Manager $wp_customize): void
    {
        $wp_customize->add_section('flux_header_megamenu_section', [
            'title' => __('Flux Press: Mega Menu', 'flux-press'),
            'description' => __('Configura el mega menu inteligente del header.', 'flux-press'),
            'priority' => 31,
        ]);

        $wp_customize->add_setting('header_enable_mega_menu', [
            'default' => config('theme-interface.header.mega_menu.enabled', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_enable_mega_menu', [
            'label' => __('Activar Mega Menu', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('header_megamenu_show_categories', [
            'default' => config('theme-interface.header.mega_menu.show_categories', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_megamenu_show_categories', [
            'label' => __('Mostrar categorias de producto', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('header_megamenu_show_top_rated', [
            'default' => config('theme-interface.header.mega_menu.show_top_rated', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_megamenu_show_top_rated', [
            'label' => __('Mostrar productos mejor valorados', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('header_megamenu_show_best_selling', [
            'default' => config('theme-interface.header.mega_menu.show_best_selling', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_megamenu_show_best_selling', [
            'label' => __('Mostrar productos mas vendidos', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('header_megamenu_show_pages', [
            'default' => config('theme-interface.header.mega_menu.show_pages', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_megamenu_show_pages', [
            'label' => __('Mostrar paginas de WordPress', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('header_megamenu_categories_limit', [
            'default' => config('theme-interface.header.mega_menu.categories_limit', 6),
            'sanitize_callback' => [$this, 'sanitizeMegaMenuLimit'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_megamenu_categories_limit', [
            'label' => __('Limite de categorias', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'number',
            'input_attrs' => [
                'min' => 1,
                'max' => 12,
                'step' => 1,
            ],
        ]);

        $wp_customize->add_setting('header_megamenu_top_rated_limit', [
            'default' => config('theme-interface.header.mega_menu.top_rated_limit', 4),
            'sanitize_callback' => [$this, 'sanitizeMegaMenuLimit'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_megamenu_top_rated_limit', [
            'label' => __('Limite de productos destacados', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'number',
            'input_attrs' => [
                'min' => 1,
                'max' => 12,
                'step' => 1,
            ],
        ]);

        $wp_customize->add_setting('header_megamenu_best_selling_limit', [
            'default' => config('theme-interface.header.mega_menu.best_selling_limit', 4),
            'sanitize_callback' => [$this, 'sanitizeMegaMenuLimit'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_megamenu_best_selling_limit', [
            'label' => __('Limite de productos mas vendidos', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'number',
            'input_attrs' => [
                'min' => 1,
                'max' => 12,
                'step' => 1,
            ],
        ]);

        $wp_customize->add_setting('header_megamenu_pages_limit', [
            'default' => config('theme-interface.header.mega_menu.pages_limit', 6),
            'sanitize_callback' => [$this, 'sanitizeMegaMenuLimit'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_megamenu_pages_limit', [
            'label' => __('Limite de paginas', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'number',
            'input_attrs' => [
                'min' => 1,
                'max' => 20,
                'step' => 1,
            ],
        ]);

        $wp_customize->add_setting('header_megamenu_featured_item_text', [
            'default' => config('theme-interface.header.mega_menu.featured_item_text', __('Descubrir', 'flux-press')),
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('header_megamenu_featured_item_text', [
            'label' => __('Etiqueta de item destacado', 'flux-press'),
            'description' => __('Texto mostrado en el acceso principal del mega menu si no hay menu asignado.', 'flux-press'),
            'section' => 'flux_header_megamenu_section',
            'type' => 'text',
        ]);
    }

    /**
     * Registrar ajustes del Home Builder para landing dinamica.
     */
    protected function registerHomeCustomizerSettings(\WP_Customize_Manager $wp_customize): void
    {
        $wp_customize->add_section('flux_home_section', [
            'title' => __('Flux Press: Home Builder', 'flux-press'),
            'description' => __('Selecciona variante de home y activa/desactiva secciones.', 'flux-press'),
            'priority' => 32,
        ]);

        $wp_customize->add_setting('home_layout', [
            'default' => config('theme-interface.home.default_layout', 'corporate'),
            'sanitize_callback' => [$this, 'sanitizeHomeLayout'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('home_layout', [
            'label' => __('Layout del Home', 'flux-press'),
            'section' => 'flux_home_section',
            'type' => 'select',
            'choices' => $this->homeLayoutChoices(),
        ]);

        $wp_customize->add_setting('home_show_features', [
            'default' => config('theme-interface.home.sections.show_features', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('home_show_features', [
            'label' => __('Mostrar seccion Features', 'flux-press'),
            'section' => 'flux_home_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('home_show_stats', [
            'default' => config('theme-interface.home.sections.show_stats', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('home_show_stats', [
            'label' => __('Mostrar seccion Stats', 'flux-press'),
            'section' => 'flux_home_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('home_show_cta', [
            'default' => config('theme-interface.home.sections.show_cta', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('home_show_cta', [
            'label' => __('Mostrar seccion CTA', 'flux-press'),
            'section' => 'flux_home_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('home_show_posts', [
            'default' => config('theme-interface.home.sections.show_posts', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('home_show_posts', [
            'label' => __('Mostrar seccion de entradas recientes', 'flux-press'),
            'section' => 'flux_home_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('home_posts_limit', [
            'default' => config('theme-interface.home.sections.posts_limit', 6),
            'sanitize_callback' => [$this, 'sanitizeHomePostsLimit'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('home_posts_limit', [
            'label' => __('Cantidad de entradas en Home', 'flux-press'),
            'section' => 'flux_home_section',
            'type' => 'number',
            'input_attrs' => [
                'min' => 1,
                'max' => 12,
                'step' => 1,
            ],
        ]);

        $wp_customize->add_setting('home_show_widgets', [
            'default' => config('theme-interface.home.sections.show_widgets', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('home_show_widgets', [
            'label' => __('Mostrar area de widgets Home', 'flux-press'),
            'section' => 'flux_home_section',
            'type' => 'checkbox',
        ]);

        $this->registerHomeEcommerceCustomizerSettings($wp_customize);
    }

    /**
     * Registrar sección de Presets del Tema.
     */
    protected function registerThemePresetsCustomizerSettings(\WP_Customize_Manager $wp_customize): void
    {
        $wp_customize->add_section('flux_presets_section', [
            'title' => __('Flux Press: Plantillas', 'flux-press'),
            'description' => __('Aplica una plantilla predefinida para configurar rápidamente el tema completo.', 'flux-press'),
            'priority' => 20,
        ]);

        $wp_customize->add_setting('flux_preset_selector', [
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage',
        ]);

        $wp_customize->add_control(new FluxPresetSelectorControl($wp_customize, 'flux_preset_selector', [
            'section' => 'flux_presets_section',
        ]));
    }

    /**
     * AJAX: Aplicar un preset al tema.
     */
    public function ajaxApplyPreset(): void
    {
        check_ajax_referer('flux_preset_nonce', 'nonce');

        if (! current_user_can('edit_theme_options')) {
            wp_send_json_error(__('No tienes permisos para realizar esta acción.', 'flux-press'));

            return;
        }

        $presetKey = sanitize_key($_POST['preset_key'] ?? '');

        if (empty($presetKey)) {
            wp_send_json_error(__('Preset no especificado.', 'flux-press'));

            return;
        }

        $presetService = new FluxThemePresetService;
        $result = $presetService->applyPreset($presetKey);

        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX: Exportar configuración actual.
     */
    public function ajaxExportConfig(): void
    {
        check_ajax_referer('flux_preset_nonce', 'nonce');

        if (! current_user_can('edit_theme_options')) {
            wp_send_json_error(__('No tienes permisos para realizar esta acción.', 'flux-press'));

            return;
        }

        $presetService = new FluxThemePresetService;
        $config = $presetService->exportCurrentConfig();
        $json = wp_json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        wp_send_json_success($json);
    }

    /**
     * AJAX: Restaurar desde backup.
     */
    public function ajaxRestoreBackup(): void
    {
        check_ajax_referer('flux_preset_nonce', 'nonce');

        if (! current_user_can('edit_theme_options')) {
            wp_send_json_error(__('No tienes permisos para realizar esta acción.', 'flux-press'));

            return;
        }

        $index = absint($_POST['backup_index'] ?? -1);

        if ($index < 0) {
            wp_send_json_error(__('Índice de backup inválido.', 'flux-press'));

            return;
        }

        $presetService = new FluxThemePresetService;
        $result = $presetService->restoreBackup($index);

        if ($result) {
            wp_send_json_success(__('Backup restaurado correctamente.', 'flux-press'));
        } else {
            wp_send_json_error(__('No se pudo restaurar el backup.', 'flux-press'));
        }
    }

    /**
     * Registrar ajustes del Home Ecommerce Builder.
     */
    protected function registerHomeEcommerceCustomizerSettings(\WP_Customize_Manager $wp_customize): void
    {
        $wp_customize->add_section('flux_home_ecommerce_section', [
            'title' => __('Flux Press: Home Ecommerce', 'flux-press'),
            'description' => __('Configura orden, visibilidad y contenido dinamico del Home ecommerce.', 'flux-press'),
            'priority' => 33,
        ]);

        $wp_customize->add_setting('home_ecommerce_section_order', [
            'default' => config('theme-interface.home.ecommerce.section_order', implode(',', HomeEcommerceDataService::SECTION_KEYS)),
            'sanitize_callback' => [$this, 'sanitizeHomeEcommerceSectionOrder'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('home_ecommerce_section_order', [
            'label' => __('Orden de secciones (CSV)', 'flux-press'),
            'description' => __('Usa: hero,categories,best_sellers,top_rated,brands,promos,newsletter,blog', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'text',
        ]);

        $wp_customize->add_setting('home_ecommerce_content_mode', [
            'default' => config('theme-interface.home.ecommerce.content_mode', 'hybrid'),
            'sanitize_callback' => [$this, 'sanitizeHomeEcommerceContentMode'],
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('home_ecommerce_content_mode', [
            'label' => __('Modo de contenido Home Ecommerce', 'flux-press'),
            'description' => __('Builder: solo secciones del tema. Hibrido: bloques del editor + builder. Editor: solo Gutenberg/Elementor.', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'select',
            'choices' => [
                'builder' => __('Builder del tema', 'flux-press'),
                'hybrid' => __('Hibrido (editor + builder)', 'flux-press'),
                'editor' => __('Solo editor de bloques/Elementor', 'flux-press'),
            ],
        ]);

        $sectionLabels = [
            'hero' => __('Mostrar Hero dinamico', 'flux-press'),
            'categories' => __('Mostrar categorias', 'flux-press'),
            'best_sellers' => __('Mostrar mas vendidos', 'flux-press'),
            'top_rated' => __('Mostrar mejor valorados', 'flux-press'),
            'brands' => __('Mostrar marcas', 'flux-press'),
            'promos' => __('Mostrar promociones', 'flux-press'),
            'newsletter' => __('Mostrar newsletter', 'flux-press'),
            'blog' => __('Mostrar blog', 'flux-press'),
        ];

        foreach ($sectionLabels as $sectionKey => $label) {
            $settingKey = "home_ecommerce_show_{$sectionKey}";
            $wp_customize->add_setting($settingKey, [
                'default' => config("theme-interface.home.ecommerce.sections.show_{$sectionKey}", true),
                'sanitize_callback' => [$this, 'sanitizeBoolean'],
                'transport' => 'refresh',
            ]);

            $wp_customize->add_control($settingKey, [
                'label' => $label,
                'section' => 'flux_home_ecommerce_section',
                'type' => 'checkbox',
            ]);
        }

        $wp_customize->add_setting('home_ecommerce_hero_limit', [
            'default' => config('theme-interface.home.ecommerce.limits.hero', 3),
            'sanitize_callback' => fn ($value): int => $this->sanitizeNumericRange($value, 1, 8, 3),
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_hero_limit', [
            'label' => __('Cantidad de productos Hero', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'number',
            'input_attrs' => ['min' => 1, 'max' => 8, 'step' => 1],
        ]);

        $wp_customize->add_setting('home_ecommerce_categories_limit', [
            'default' => config('theme-interface.home.ecommerce.limits.categories', 8),
            'sanitize_callback' => fn ($value): int => $this->sanitizeNumericRange($value, 1, 18, 8),
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_categories_limit', [
            'label' => __('Cantidad de categorias', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'number',
            'input_attrs' => ['min' => 1, 'max' => 18, 'step' => 1],
        ]);

        $wp_customize->add_setting('home_ecommerce_products_limit', [
            'default' => config('theme-interface.home.ecommerce.limits.products', 8),
            'sanitize_callback' => fn ($value): int => $this->sanitizeNumericRange($value, 1, 24, 8),
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_products_limit', [
            'label' => __('Cantidad de productos por grid', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'number',
            'input_attrs' => ['min' => 1, 'max' => 24, 'step' => 1],
        ]);

        $wp_customize->add_setting('home_ecommerce_brands_limit', [
            'default' => config('theme-interface.home.ecommerce.limits.brands', 8),
            'sanitize_callback' => fn ($value): int => $this->sanitizeNumericRange($value, 1, 18, 8),
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_brands_limit', [
            'label' => __('Cantidad de marcas', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'number',
            'input_attrs' => ['min' => 1, 'max' => 18, 'step' => 1],
        ]);

        $wp_customize->add_setting('home_ecommerce_blog_limit', [
            'default' => config('theme-interface.home.ecommerce.limits.blog', 6),
            'sanitize_callback' => fn ($value): int => $this->sanitizeNumericRange($value, 1, 12, 6),
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_blog_limit', [
            'label' => __('Cantidad de entradas blog', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'number',
            'input_attrs' => ['min' => 1, 'max' => 12, 'step' => 1],
        ]);

        $wp_customize->add_setting('home_ecommerce_hero_autoplay', [
            'default' => config('theme-interface.home.ecommerce.hero.autoplay', true),
            'sanitize_callback' => [$this, 'sanitizeBoolean'],
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_hero_autoplay', [
            'label' => __('Autoplay del carrusel Hero', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('home_ecommerce_hero_interval_ms', [
            'default' => config('theme-interface.home.ecommerce.hero.interval_ms', 6500),
            'sanitize_callback' => fn ($value): int => $this->sanitizeNumericRange($value, 2500, 20000, 6500),
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_hero_interval_ms', [
            'label' => __('Intervalo del carrusel (ms)', 'flux-press'),
            'description' => __('Rango recomendado: 3500 - 9000', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'number',
            'input_attrs' => ['min' => 2500, 'max' => 20000, 'step' => 100],
        ]);

        $this->registerHomeEcommerceHeroVisualSlidesCustomizerSettings($wp_customize);

        $wp_customize->add_setting('home_ecommerce_hero_slides_json', [
            'default' => config('theme-interface.home.ecommerce.hero.slides_json', '[]'),
            'sanitize_callback' => [$this, 'sanitizeHomeEcommerceHeroSlidesJson'],
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_hero_slides_json', [
            'label' => __('Slides del Hero (JSON)', 'flux-press'),
            'description' => __('Campos por slide: title, subtitle, content_html, image_url, badge, primary_label, primary_url, secondary_label, secondary_url. Solo se usa si no hay Slides Visuales activos.', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'textarea',
        ]);

        $wp_customize->add_setting('home_ecommerce_newsletter_title', [
            'default' => config('theme-interface.home.ecommerce.newsletter.title', 'Recibe novedades en tu correo'),
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_newsletter_title', [
            'label' => __('Titulo newsletter', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'text',
        ]);

        $wp_customize->add_setting('home_ecommerce_newsletter_text', [
            'default' => config('theme-interface.home.ecommerce.newsletter.text', 'Configura este bloque desde el personalizador y capta suscriptores de forma continua.'),
            'sanitize_callback' => 'sanitize_textarea_field',
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_newsletter_text', [
            'label' => __('Texto newsletter', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'textarea',
        ]);

        $wp_customize->add_setting('home_ecommerce_newsletter_button_label', [
            'default' => config('theme-interface.home.ecommerce.newsletter.button_label', 'Suscribirme'),
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_newsletter_button_label', [
            'label' => __('Etiqueta boton newsletter', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'text',
        ]);

        $wp_customize->add_setting('home_ecommerce_newsletter_button_url', [
            'default' => config('theme-interface.home.ecommerce.newsletter.button_url', '#'),
            'sanitize_callback' => 'esc_url_raw',
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_newsletter_button_url', [
            'label' => __('URL boton newsletter', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'url',
        ]);

        $wp_customize->add_setting('home_ecommerce_featured_categories_json', [
            'default' => config('theme-interface.home.ecommerce.featured_categories_json', '[]'),
            'sanitize_callback' => [$this, 'sanitizeHomeEcommerceFeaturedCategoriesJson'],
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_featured_categories_json', [
            'label' => __('Categorias destacadas (JSON)', 'flux-press'),
            'description' => __('Campos por card: name, url, image_url, badge', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'textarea',
        ]);

        $wp_customize->add_setting('home_ecommerce_featured_brands_json', [
            'default' => config('theme-interface.home.ecommerce.featured_brands_json', '[]'),
            'sanitize_callback' => [$this, 'sanitizeHomeEcommerceFeaturedBrandsJson'],
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_featured_brands_json', [
            'label' => __('Marcas destacadas (JSON)', 'flux-press'),
            'description' => __('Campos por card: name, url, image_url, logo_url, badge', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'textarea',
        ]);

        $wp_customize->add_setting('home_ecommerce_featured_promos_json', [
            'default' => config('theme-interface.home.ecommerce.featured_promos_json', '[]'),
            'sanitize_callback' => [$this, 'sanitizeHomeEcommerceFeaturedPromosJson'],
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control('home_ecommerce_featured_promos_json', [
            'label' => __('Promociones destacadas (JSON)', 'flux-press'),
            'description' => __('Campos por card: eyebrow, title, description, cta_label, cta_url, image_url, theme', 'flux-press'),
            'section' => 'flux_home_ecommerce_section',
            'type' => 'textarea',
        ]);
    }

    protected function registerHomeEcommerceHeroVisualSlidesCustomizerSettings(\WP_Customize_Manager $wp_customize): void
    {
        $section = 'flux_home_ecommerce_section';
        $slides = 6;

        for ($index = 1; $index <= $slides; $index++) {
            $prefix = "home_ecommerce_hero_slide_{$index}";
            $basePriority = 900 + ($index * 20);

            $wp_customize->add_setting("{$prefix}_enabled", [
                'default' => false,
                'sanitize_callback' => [$this, 'sanitizeBoolean'],
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control("{$prefix}_enabled", [
                'label' => sprintf(__('Slide visual %d activo', 'flux-press'), $index),
                'section' => $section,
                'type' => 'checkbox',
                'priority' => $basePriority,
            ]);

            $wp_customize->add_setting("{$prefix}_badge", [
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control("{$prefix}_badge", [
                'label' => sprintf(__('Slide visual %d badge', 'flux-press'), $index),
                'section' => $section,
                'type' => 'text',
                'priority' => $basePriority + 1,
            ]);

            $wp_customize->add_setting("{$prefix}_title", [
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control("{$prefix}_title", [
                'label' => sprintf(__('Slide visual %d titulo', 'flux-press'), $index),
                'section' => $section,
                'type' => 'text',
                'priority' => $basePriority + 2,
            ]);

            $wp_customize->add_setting("{$prefix}_subtitle", [
                'default' => '',
                'sanitize_callback' => 'sanitize_textarea_field',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control("{$prefix}_subtitle", [
                'label' => sprintf(__('Slide visual %d subtitulo', 'flux-press'), $index),
                'section' => $section,
                'type' => 'textarea',
                'priority' => $basePriority + 3,
            ]);

            $wp_customize->add_setting("{$prefix}_image_id", [
                'default' => 0,
                'sanitize_callback' => 'absint',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control(new WP_Customize_Cropped_Image_Control($wp_customize, "{$prefix}_image_id", [
                'label' => sprintf(__('Slide visual %d imagen (con recorte)', 'flux-press'), $index),
                'description' => __('Sube imagen personalizada y ajusta el recorte sin salir del personalizador.', 'flux-press'),
                'section' => $section,
                'priority' => $basePriority + 4,
                'width' => 1920,
                'height' => 760,
                'flex_width' => true,
                'flex_height' => true,
            ]));

            $wp_customize->add_setting("{$prefix}_primary_label", [
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control("{$prefix}_primary_label", [
                'label' => sprintf(__('Slide visual %d boton principal', 'flux-press'), $index),
                'section' => $section,
                'type' => 'text',
                'priority' => $basePriority + 5,
            ]);

            $wp_customize->add_setting("{$prefix}_primary_url", [
                'default' => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control("{$prefix}_primary_url", [
                'label' => sprintf(__('Slide visual %d URL principal', 'flux-press'), $index),
                'section' => $section,
                'type' => 'url',
                'priority' => $basePriority + 6,
            ]);

            $wp_customize->add_setting("{$prefix}_secondary_label", [
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control("{$prefix}_secondary_label", [
                'label' => sprintf(__('Slide visual %d boton secundario', 'flux-press'), $index),
                'section' => $section,
                'type' => 'text',
                'priority' => $basePriority + 7,
            ]);

            $wp_customize->add_setting("{$prefix}_secondary_url", [
                'default' => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control("{$prefix}_secondary_url", [
                'label' => sprintf(__('Slide visual %d URL secundaria', 'flux-press'), $index),
                'section' => $section,
                'type' => 'url',
                'priority' => $basePriority + 8,
            ]);
        }
    }

    /**
     * Sanitizar limites numericos del Mega Menu.
     */
    public function sanitizeMegaMenuLimit($value): int
    {
        $limit = absint($value);
        if ($limit < 1) {
            return 1;
        }

        return min($limit, 20);
    }

    /**
     * Sanitizar limite de posts de Home.
     */
    public function sanitizeHomePostsLimit($value): int
    {
        $limit = absint($value);
        if ($limit < 1) {
            return 1;
        }

        return min($limit, 12);
    }

    /**
     * Sanitizar orden de secciones ecommerce en formato CSV.
     */
    public function sanitizeHomeEcommerceSectionOrder($value): string
    {
        $raw = strtolower((string) $value);
        $parts = array_map('trim', explode(',', $raw));
        $allowed = HomeEcommerceDataService::SECTION_KEYS;
        $resolved = [];

        foreach ($parts as $part) {
            if ($part === '' || ! in_array($part, $allowed, true) || in_array($part, $resolved, true)) {
                continue;
            }

            $resolved[] = $part;
        }

        foreach ($allowed as $fallback) {
            if (! in_array($fallback, $resolved, true)) {
                $resolved[] = $fallback;
            }
        }

        return implode(',', $resolved);
    }

    /**
     * Sanitizar modo de contenido para home ecommerce.
     *
     * @param  mixed  $value
     */
    public function sanitizeHomeEcommerceContentMode($value): string
    {
        $mode = sanitize_key((string) $value);
        $allowed = ['builder', 'hybrid', 'editor'];

        if (in_array($mode, $allowed, true)) {
            return $mode;
        }

        $default = (string) config('theme-interface.home.ecommerce.content_mode', 'hybrid');

        return in_array($default, $allowed, true) ? $default : 'hybrid';
    }

    /**
     * Sanitizar slides JSON del hero ecommerce.
     *
     * @param  mixed  $value
     */
    public function sanitizeHomeEcommerceHeroSlidesJson($value): string
    {
        $decoded = json_decode((string) $value, true);
        if (! is_array($decoded)) {
            return '[]';
        }

        $sanitized = [];
        foreach ($decoded as $slide) {
            if (! is_array($slide)) {
                continue;
            }

            $row = [
                'title' => sanitize_text_field((string) ($slide['title'] ?? '')),
                'subtitle' => sanitize_text_field((string) ($slide['subtitle'] ?? '')),
                'content_html' => wp_kses_post((string) ($slide['content_html'] ?? '')),
                'image_url' => esc_url_raw((string) ($slide['image_url'] ?? '')),
                'image_id' => absint($slide['image_id'] ?? 0),
                'badge' => sanitize_text_field((string) ($slide['badge'] ?? '')),
                'primary_label' => sanitize_text_field((string) ($slide['primary_label'] ?? '')),
                'primary_url' => esc_url_raw((string) ($slide['primary_url'] ?? '')),
                'secondary_label' => sanitize_text_field((string) ($slide['secondary_label'] ?? '')),
                'secondary_url' => esc_url_raw((string) ($slide['secondary_url'] ?? '')),
            ];

            if (
                $row['title'] === ''
                && $row['subtitle'] === ''
                && $row['content_html'] === ''
                && $row['image_url'] === ''
                && ((int) $row['image_id']) <= 0
            ) {
                continue;
            }

            $sanitized[] = $row;
        }

        $encoded = wp_json_encode($sanitized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '[]';
    }

    /**
     * @param  mixed  $value
     */
    public function sanitizeHomeEcommerceFeaturedCategoriesJson($value): string
    {
        return $this->sanitizeHomeEcommerceCardsJson((string) $value, [
            'name' => 'text',
            'url' => 'url',
            'image_url' => 'url_or_path',
            'badge' => 'text',
        ], ['name']);
    }

    /**
     * @param  mixed  $value
     */
    public function sanitizeHomeEcommerceFeaturedBrandsJson($value): string
    {
        return $this->sanitizeHomeEcommerceCardsJson((string) $value, [
            'name' => 'text',
            'url' => 'url',
            'image_url' => 'url_or_path',
            'logo_url' => 'url_or_path',
            'badge' => 'text',
        ], ['name']);
    }

    /**
     * @param  mixed  $value
     */
    public function sanitizeHomeEcommerceFeaturedPromosJson($value): string
    {
        return $this->sanitizeHomeEcommerceCardsJson((string) $value, [
            'eyebrow' => 'text',
            'title' => 'text',
            'description' => 'textarea',
            'cta_label' => 'text',
            'cta_url' => 'url',
            'image_url' => 'url_or_path',
            'theme' => 'theme',
        ], ['title']);
    }

    /**
     * @param  array<string,string>  $schema
     * @param  string[]  $required
     */
    protected function sanitizeHomeEcommerceCardsJson(string $value, array $schema, array $required): string
    {
        $decoded = json_decode($value, true);
        if (! is_array($decoded)) {
            return '[]';
        }

        $sanitized = [];
        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }

            $row = [];
            foreach ($schema as $field => $type) {
                $raw = (string) ($item[$field] ?? '');
                switch ($type) {
                    case 'url':
                        $row[$field] = esc_url_raw($raw);
                        break;
                    case 'textarea':
                        $row[$field] = sanitize_textarea_field($raw);
                        break;
                    case 'theme':
                        $theme = sanitize_key($raw);
                        $row[$field] = in_array($theme, ['dark', 'light', 'accent'], true) ? $theme : 'dark';
                        break;
                    case 'url_or_path':
                        if (filter_var($raw, FILTER_VALIDATE_URL)) {
                            $row[$field] = esc_url_raw($raw);
                        } else {
                            $row[$field] = ltrim(sanitize_text_field($raw), '/');
                        }
                        break;
                    default:
                        $row[$field] = sanitize_text_field($raw);
                        break;
                }
            }

            $skip = false;
            foreach ($required as $requiredField) {
                if (trim((string) ($row[$requiredField] ?? '')) === '') {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }

            $sanitized[] = $row;
        }

        $encoded = wp_json_encode($sanitized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '[]';
    }

    public function registerHomeEcommerceBlocksAndShortcodes(): void
    {
        $this->registerHomeEcommerceBlocks();
        $this->registerHomeEcommerceShortcodes();
    }

    protected function registerHomeEcommerceBlocks(): void
    {
        if (! function_exists('register_block_type')) {
            return;
        }

        register_block_type('flux-press/featured-categories', [
            'render_callback' => [$this, 'renderFeaturedCategoriesBlock'],
            'attributes' => [
                'title' => ['type' => 'string', 'default' => __('Categorias destacadas', 'flux-press')],
                'subtitle' => ['type' => 'string', 'default' => __('Explora las mejores tendencias del momento', 'flux-press')],
                'limit' => ['type' => 'number', 'default' => 8],
            ],
        ]);

        register_block_type('flux-press/featured-brands', [
            'render_callback' => [$this, 'renderFeaturedBrandsBlock'],
            'attributes' => [
                'title' => ['type' => 'string', 'default' => __('Tus marcas favoritas', 'flux-press')],
                'subtitle' => ['type' => 'string', 'default' => __('Inicia sesion para obtener beneficios exclusivos', 'flux-press')],
                'limit' => ['type' => 'number', 'default' => 8],
            ],
        ]);

        register_block_type('flux-press/featured-promos', [
            'render_callback' => [$this, 'renderFeaturedPromosBlock'],
            'attributes' => [
                'title' => ['type' => 'string', 'default' => __('Promociones destacadas', 'flux-press')],
                'subtitle' => ['type' => 'string', 'default' => __('Ofertas y lanzamientos en una vista mas visual', 'flux-press')],
                'limit' => ['type' => 'number', 'default' => 2],
            ],
        ]);

        register_block_type('flux-press/home-sections-carousel', [
            'render_callback' => [$this, 'renderHomeSectionsCarouselBlock'],
            'attributes' => [
                'title' => ['type' => 'string', 'default' => __('Carrusel de secciones', 'flux-press')],
                'subtitle' => ['type' => 'string', 'default' => __('Mueve, activa y reagrupa secciones ecommerce', 'flux-press')],
                'sections' => ['type' => 'string', 'default' => 'categories,brands,promos'],
                'autoplay' => ['type' => 'boolean', 'default' => true],
                'interval' => ['type' => 'number', 'default' => 6500],
                'show_controls' => ['type' => 'boolean', 'default' => true],
            ],
        ]);

        register_block_type('flux-press/home-hero', [
            'render_callback' => [$this, 'renderHomeHeroBlock'],
        ]);

        register_block_type('flux-press/home-best-sellers', [
            'render_callback' => [$this, 'renderHomeBestSellersBlock'],
        ]);

        register_block_type('flux-press/home-top-rated', [
            'render_callback' => [$this, 'renderHomeTopRatedBlock'],
        ]);

        register_block_type('flux-press/home-newsletter', [
            'render_callback' => [$this, 'renderHomeNewsletterBlock'],
        ]);

        register_block_type('flux-press/home-blog', [
            'render_callback' => [$this, 'renderHomeBlogBlock'],
        ]);

        register_block_type('flux-press/category-card', [
            'render_callback' => static fn (): string => '',
            'attributes' => [
                'name' => ['type' => 'string', 'default' => ''],
                'url' => ['type' => 'string', 'default' => ''],
                'image_url' => ['type' => 'string', 'default' => ''],
                'badge' => ['type' => 'string', 'default' => ''],
            ],
        ]);

        register_block_type('flux-press/brand-card', [
            'render_callback' => static fn (): string => '',
            'attributes' => [
                'name' => ['type' => 'string', 'default' => ''],
                'url' => ['type' => 'string', 'default' => ''],
                'image_url' => ['type' => 'string', 'default' => ''],
                'logo_url' => ['type' => 'string', 'default' => ''],
                'badge' => ['type' => 'string', 'default' => ''],
            ],
        ]);

        register_block_type('flux-press/promo-card', [
            'render_callback' => static fn (): string => '',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => ''],
                'title' => ['type' => 'string', 'default' => ''],
                'description' => ['type' => 'string', 'default' => ''],
                'cta_label' => ['type' => 'string', 'default' => ''],
                'cta_url' => ['type' => 'string', 'default' => ''],
                'image_url' => ['type' => 'string', 'default' => ''],
                'theme' => ['type' => 'string', 'default' => 'dark'],
            ],
        ]);
    }

    protected function registerHomeEcommerceShortcodes(): void
    {
        add_shortcode('flux_featured_categories', function ($atts): string {
            $atts = shortcode_atts([
                'title' => __('Categorias destacadas', 'flux-press'),
                'subtitle' => __('Explora las mejores tendencias del momento', 'flux-press'),
                'limit' => 8,
                'cards_json' => '',
            ], (array) $atts, 'flux_featured_categories');

            return $this->renderLivewireSection('ecommerce-home-categories', [
                'manualCards' => $this->extractManualCardsFromJson((string) $atts['cards_json']),
                'sectionTitle' => sanitize_text_field((string) $atts['title']),
                'sectionSubtitle' => sanitize_text_field((string) $atts['subtitle']),
                'limitOverride' => max(1, min(24, (int) $atts['limit'])),
            ], 'shortcode-featured-categories');
        });

        add_shortcode('flux_featured_brands', function ($atts): string {
            $atts = shortcode_atts([
                'title' => __('Tus marcas favoritas', 'flux-press'),
                'subtitle' => __('Inicia sesion para obtener beneficios exclusivos', 'flux-press'),
                'limit' => 8,
                'cards_json' => '',
            ], (array) $atts, 'flux_featured_brands');

            return $this->renderLivewireSection('ecommerce-home-brands', [
                'manualCards' => $this->extractManualCardsFromJson((string) $atts['cards_json']),
                'sectionTitle' => sanitize_text_field((string) $atts['title']),
                'sectionSubtitle' => sanitize_text_field((string) $atts['subtitle']),
                'limitOverride' => max(1, min(24, (int) $atts['limit'])),
            ], 'shortcode-featured-brands');
        });

        add_shortcode('flux_featured_promos', function ($atts): string {
            $atts = shortcode_atts([
                'title' => __('Promociones destacadas', 'flux-press'),
                'subtitle' => __('Ofertas y lanzamientos en una vista mas visual', 'flux-press'),
                'limit' => 2,
                'cards_json' => '',
            ], (array) $atts, 'flux_featured_promos');

            return $this->renderLivewireSection('ecommerce-home-promos', [
                'manualCards' => $this->extractManualCardsFromJson((string) $atts['cards_json']),
                'sectionTitle' => sanitize_text_field((string) $atts['title']),
                'sectionSubtitle' => sanitize_text_field((string) $atts['subtitle']),
                'limitOverride' => max(1, min(6, (int) $atts['limit'])),
            ], 'shortcode-featured-promos');
        });

        add_shortcode('flux_home_sections_carousel', function ($atts): string {
            $atts = shortcode_atts([
                'title' => __('Carrusel de secciones', 'flux-press'),
                'subtitle' => __('Mueve, activa y reagrupa secciones ecommerce', 'flux-press'),
                'sections' => 'categories,brands,promos',
                'autoplay' => '1',
                'interval' => 6500,
                'show_controls' => '1',
            ], (array) $atts, 'flux_home_sections_carousel');

            return $this->renderLivewireSection('ecommerce-home-sections-carousel', [
                'title' => sanitize_text_field((string) $atts['title']),
                'subtitle' => sanitize_text_field((string) $atts['subtitle']),
                'sections' => $this->sanitizeEcommerceSectionList((string) $atts['sections']),
                'autoplay' => $this->toShortcodeBool($atts['autoplay'], true),
                'interval' => max(2500, min(20000, (int) $atts['interval'])),
                'showControls' => $this->toShortcodeBool($atts['show_controls'], true),
            ], 'shortcode-home-sections-carousel');
        });

        add_shortcode('flux_home_hero', function (): string {
            return $this->renderLivewireSection('ecommerce-home-hero', [], 'shortcode-home-hero');
        });

        add_shortcode('flux_home_best_sellers', function (): string {
            return $this->renderLivewireSection('ecommerce-home-best-sellers', [], 'shortcode-home-best-sellers');
        });

        add_shortcode('flux_home_top_rated', function (): string {
            return $this->renderLivewireSection('ecommerce-home-top-rated', [], 'shortcode-home-top-rated');
        });

        add_shortcode('flux_home_newsletter', function (): string {
            return $this->renderLivewireSection('ecommerce-home-newsletter', [], 'shortcode-home-newsletter');
        });

        add_shortcode('flux_home_blog', function (): string {
            return $this->renderLivewireSection('ecommerce-home-blog', [], 'shortcode-home-blog');
        });
    }

    /**
     * @param  array<string,mixed>  $attributes
     * @param  mixed  $block
     */
    public function renderFeaturedCategoriesBlock(array $attributes = [], string $content = '', $block = null): string
    {
        $manualCards = $this->extractManualCardsFromBlock($block, 'flux-press/category-card');
        $title = sanitize_text_field((string) ($attributes['title'] ?? __('Categorias destacadas', 'flux-press')));
        $subtitle = sanitize_text_field((string) ($attributes['subtitle'] ?? __('Explora las mejores tendencias del momento', 'flux-press')));
        $limit = max(1, min(24, (int) ($attributes['limit'] ?? 8)));

        return $this->renderLivewireSection('ecommerce-home-categories', [
            'manualCards' => $manualCards,
            'sectionTitle' => $title,
            'sectionSubtitle' => $subtitle,
            'limitOverride' => $limit,
        ], 'block-featured-categories');
    }

    /**
     * @param  array<string,mixed>  $attributes
     * @param  mixed  $block
     */
    public function renderFeaturedBrandsBlock(array $attributes = [], string $content = '', $block = null): string
    {
        $manualCards = $this->extractManualCardsFromBlock($block, 'flux-press/brand-card');
        $title = sanitize_text_field((string) ($attributes['title'] ?? __('Tus marcas favoritas', 'flux-press')));
        $subtitle = sanitize_text_field((string) ($attributes['subtitle'] ?? __('Inicia sesion para obtener beneficios exclusivos', 'flux-press')));
        $limit = max(1, min(24, (int) ($attributes['limit'] ?? 8)));

        return $this->renderLivewireSection('ecommerce-home-brands', [
            'manualCards' => $manualCards,
            'sectionTitle' => $title,
            'sectionSubtitle' => $subtitle,
            'limitOverride' => $limit,
        ], 'block-featured-brands');
    }

    /**
     * @param  array<string,mixed>  $attributes
     * @param  mixed  $block
     */
    public function renderFeaturedPromosBlock(array $attributes = [], string $content = '', $block = null): string
    {
        $manualCards = $this->extractManualCardsFromBlock($block, 'flux-press/promo-card');
        $title = sanitize_text_field((string) ($attributes['title'] ?? __('Promociones destacadas', 'flux-press')));
        $subtitle = sanitize_text_field((string) ($attributes['subtitle'] ?? __('Ofertas y lanzamientos en una vista mas visual', 'flux-press')));
        $limit = max(1, min(6, (int) ($attributes['limit'] ?? 2)));

        return $this->renderLivewireSection('ecommerce-home-promos', [
            'manualCards' => $manualCards,
            'sectionTitle' => $title,
            'sectionSubtitle' => $subtitle,
            'limitOverride' => $limit,
        ], 'block-featured-promos');
    }

    /**
     * @param  array<string,mixed>  $attributes
     */
    public function renderHomeSectionsCarouselBlock(array $attributes = []): string
    {
        $title = sanitize_text_field((string) ($attributes['title'] ?? __('Carrusel de secciones', 'flux-press')));
        $subtitle = sanitize_text_field((string) ($attributes['subtitle'] ?? __('Mueve, activa y reagrupa secciones ecommerce', 'flux-press')));
        $sections = $this->sanitizeEcommerceSectionList((string) ($attributes['sections'] ?? 'categories,brands,promos'));
        $autoplay = (bool) ($attributes['autoplay'] ?? true);
        $interval = max(2500, min(20000, (int) ($attributes['interval'] ?? 6500)));
        $showControls = (bool) ($attributes['show_controls'] ?? true);

        return $this->renderLivewireSection('ecommerce-home-sections-carousel', [
            'title' => $title,
            'subtitle' => $subtitle,
            'sections' => $sections,
            'autoplay' => $autoplay,
            'interval' => $interval,
            'showControls' => $showControls,
        ], 'block-home-sections-carousel');
    }

    public function renderHomeHeroBlock(): string
    {
        return $this->renderLivewireSection('ecommerce-home-hero', [], 'block-home-hero');
    }

    public function renderHomeBestSellersBlock(): string
    {
        return $this->renderLivewireSection('ecommerce-home-best-sellers', [], 'block-home-best-sellers');
    }

    public function renderHomeTopRatedBlock(): string
    {
        return $this->renderLivewireSection('ecommerce-home-top-rated', [], 'block-home-top-rated');
    }

    public function renderHomeNewsletterBlock(): string
    {
        return $this->renderLivewireSection('ecommerce-home-newsletter', [], 'block-home-newsletter');
    }

    public function renderHomeBlogBlock(): string
    {
        return $this->renderLivewireSection('ecommerce-home-blog', [], 'block-home-blog');
    }

    /**
     * @param  mixed  $block
     * @return array<int,array<string,mixed>>
     */
    protected function extractManualCardsFromBlock($block, string $cardBlockName): array
    {
        if (! ($block instanceof \WP_Block)) {
            return [];
        }

        $parsed = $block->parsed_block ?? null;
        if (! is_array($parsed)) {
            return [];
        }

        $rows = [];
        $walk = function (array $innerBlocks) use (&$walk, &$rows, $cardBlockName): void {
            foreach ($innerBlocks as $innerBlock) {
                if (! is_array($innerBlock)) {
                    continue;
                }

                if ((string) ($innerBlock['blockName'] ?? '') === $cardBlockName) {
                    $attrs = $innerBlock['attrs'] ?? [];
                    if (is_array($attrs)) {
                        $rows[] = $attrs;
                    }
                }

                $children = $innerBlock['innerBlocks'] ?? [];
                if (is_array($children) && ! empty($children)) {
                    $walk($children);
                }
            }
        };

        $inner = $parsed['innerBlocks'] ?? [];
        if (is_array($inner) && ! empty($inner)) {
            $walk($inner);
        }

        return $rows;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    protected function extractManualCardsFromJson(string $json): array
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, fn ($item) => is_array($item)));
    }

    /**
     * @return array<int,string>
     */
    protected function sanitizeEcommerceSectionList(string $value): array
    {
        $allowed = ['categories', 'brands', 'promos'];
        $sections = array_map(
            static fn ($item) => sanitize_key((string) $item),
            explode(',', $value)
        );

        $sections = array_values(array_filter($sections, static fn ($item) => in_array($item, $allowed, true)));
        $sections = array_values(array_unique($sections));

        return ! empty($sections) ? $sections : $allowed;
    }

    /**
     * @param  mixed  $value
     */
    protected function toShortcodeBool($value, bool $default = false): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return $default;
        }

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $default;
    }

    /**
     * @param  array<string,mixed>  $params
     */
    protected function renderLivewireSection(string $componentName, array $params, string $keyPrefix): string
    {
        try {
            $key = $keyPrefix.'-'.wp_generate_uuid4();
            $instance = Livewire::mount($componentName, $params, $key);

            if (is_string($instance)) {
                return $instance;
            }

            if (is_object($instance) && method_exists($instance, 'html')) {
                return (string) $instance->html();
            }

            if (is_object($instance) && method_exists($instance, 'toHtml')) {
                return (string) $instance->toHtml();
            }

            return is_scalar($instance) ? (string) $instance : '';
        } catch (\Throwable $exception) {
            logger()->error('flux_livewire_section_render_failed', [
                'component' => $componentName,
                'message' => $exception->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * Sanitizar estilo de header contra estilos permitidos.
     */
    public function sanitizeHeaderStyle($value): string
    {
        return $this->sanitizeStyleByType((string) $value, 'header');
    }

    /**
     * Sanitizar estilo de footer contra estilos permitidos.
     */
    public function sanitizeFooterStyle($value): string
    {
        return $this->sanitizeStyleByType((string) $value, 'footer');
    }

    /**
     * Sanitizar variante de Home.
     */
    public function sanitizeHomeLayout($value): string
    {
        $value = (string) $value;
        $allowed = array_keys($this->homeLayoutChoices());
        if (in_array($value, $allowed, true)) {
            return $value;
        }

        $default = (string) config('theme-interface.home.default_layout', 'corporate');
        if (in_array($default, $allowed, true)) {
            return $default;
        }

        return ! empty($allowed) ? (string) $allowed[0] : 'corporate';
    }

    /**
     * Obtener choices del customizer desde config centralizada.
     *
     * @return array<string,string>
     */
    protected function styleChoices(string $type): array
    {
        $styles = config("theme-interface.{$type}.styles", []);
        if (! is_array($styles) || empty($styles)) {
            return [];
        }

        $choices = [];
        foreach ($styles as $key => $label) {
            $key = (string) $key;
            if ($key === '') {
                continue;
            }

            $fallbackLabel = ucwords(str_replace('-', ' ', $key));
            $choices[$key] = is_string($label) && $label !== '' ? __($label, 'flux-press') : __($fallbackLabel, 'flux-press');
        }

        return $choices;
    }

    /**
     * Obtener choices de layouts para Home.
     *
     * @return array<string,string>
     */
    protected function homeLayoutChoices(): array
    {
        $layouts = config('theme-interface.home.layouts', []);
        if (! is_array($layouts) || empty($layouts)) {
            return [];
        }

        $choices = [];
        foreach ($layouts as $key => $label) {
            $key = (string) $key;
            if ($key === '') {
                continue;
            }

            $fallbackLabel = ucwords(str_replace('-', ' ', $key));
            $choices[$key] = is_string($label) && $label !== '' ? __($label, 'flux-press') : __($fallbackLabel, 'flux-press');
        }

        return $choices;
    }

    /**
     * Sanitizar valor de estilo por tipo con fallback a default_style.
     */
    protected function sanitizeStyleByType(string $value, string $type): string
    {
        $allowed = array_keys($this->styleChoices($type));
        if (in_array($value, $allowed, true)) {
            return $value;
        }

        $default = (string) config("theme-interface.{$type}.default_style", '');
        if ($default !== '' && in_array($default, $allowed, true)) {
            return $default;
        }

        return ! empty($allowed) ? (string) $allowed[0] : '';
    }

    /**
     * Sanitizar entero en rango para settings numericos.
     *
     * @param  mixed  $value
     */
    protected function sanitizeNumericRange($value, int $min, int $max, int $fallback): int
    {
        $int = absint($value);
        if ($int < $min || $int > $max) {
            return $fallback;
        }

        return $int;
    }
}
