<?php

namespace App\Customizer;

use App\Services\FluxThemePresetService;
use WP_Customize_Control;

class FluxPresetSelectorControl extends WP_Customize_Control
{
    public $type = 'flux_preset_selector';

    public function render_content(): void
    {
        $presetService = new FluxThemePresetService;
        $presets = $presetService->getAvailablePresets();
        $currentConfig = $presetService->exportCurrentConfig();
        $currentHash = md5(wp_json_encode($currentConfig));

        $activePreset = 'custom';
        foreach ($presets as $key => $preset) {
            $presetData = $presetService->loadPreset($key);
            if ($presetData !== null) {
                $presetHash = md5(wp_json_encode($presetData));
                if ($currentHash === $presetHash) {
                    $activePreset = $key;
                    break;
                }
            }
        }

        $accentColors = [
            'sky' => '#0ea5e9',
            'blue' => '#3b82f6',
            'indigo' => '#6366f1',
            'violet' => '#8b5cf6',
            'purple' => '#a855f7',
            'fuchsia' => '#d946ef',
            'pink' => '#ec4899',
            'rose' => '#f43f5e',
            'red' => '#ef4444',
            'orange' => '#f97316',
            'amber' => '#f59e0b',
            'green' => '#22c55e',
            'emerald' => '#10b981',
            'teal' => '#14b8a6',
            'cyan' => '#06b6d4',
        ];

        $categoryLabels = [
            'business' => __('Negocios', 'sage'),
            'ecommerce' => __('Ecommerce', 'sage'),
            'marketing' => __('Marketing', 'sage'),
            'news' => __('Noticias', 'sage'),
            'portfolio' => __('Portfolio', 'sage'),
            'dark' => __('Dark Mode', 'sage'),
            'gaming' => __('Gaming', 'sage'),
            'social' => __('Social', 'sage'),
            'general' => __('General', 'sage'),
        ];
        ?>
        <div class="flux-preset-selector-control">
            <p class="description" style="margin-bottom: 1rem;">
                <?php esc_html_e('Aplica una plantilla predefinida para configurar rápidamente el tema completo. Se guardará un backup de tu configuración actual.', 'sage'); ?>
            </p>

            <div class="flux-preset-grid">
                <?php foreach ($presets as $key => $preset) { ?>
                    <?php $presetColor = $accentColors[$key] ?? '#3b82f6'; ?>
                    <div class="flux-preset-card <?php echo $activePreset === $key ? 'active' : ''; ?>"
                         data-preset-key="<?php echo esc_attr($key); ?>"
                         style="--preset-color: <?php echo esc_attr($presetColor); ?>">
                        <div class="flux-preset-thumbnail">
                            <div class="flux-preset-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                                </svg>
                                <span class="flux-preset-category"><?php echo esc_html($categoryLabels[$preset['category'] ?? 'general'] ?? ucfirst($preset['category'] ?? 'general')); ?></span>
                            </div>
                        </div>
                        <div class="flux-preset-info">
                            <h4><?php echo esc_html($preset['name']); ?></h4>
                            <p><?php echo esc_html($preset['description']); ?></p>
                        </div>
                        <?php if ($activePreset === $key) { ?>
                            <div class="flux-preset-active-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                </svg>
                                <?php esc_html_e('Activo', 'sage'); ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if ($activePreset === 'custom') { ?>
                    <div class="flux-preset-card active custom">
                        <div class="flux-preset-thumbnail">
                            <div class="flux-preset-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flux-preset-info">
                            <h4><?php esc_html_e('Configuración Personal', 'sage'); ?></h4>
                            <p><?php esc_html_e('Has personalizado la configuración del tema.', 'sage'); ?></p>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="flux-preset-actions" style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" class="button" id="flux-preset-export">
                    <?php esc_html_e('Exportar Configuración', 'sage'); ?>
                </button>
            </div>

            <div id="flux-preset-message" class="notice notice-success" style="display: none; margin-top: 1rem;"></div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var $body = $('body');
            
            $('.flux-preset-card[data-preset-key]').on('click', function() {
                var presetKey = $(this).data('preset-key');
                var presetName = $(this).find('h4').text();
                
                if (confirm('<?php esc_html_e('¿Aplicar esta plantilla?', 'sage'); ?>\n\n' + 
                           '<?php printf(esc_attr__('¿Estás seguro de que quieres aplicar la plantilla "%s"?', 'sage'), ''); ?>'.replace('%s', presetName) +
                           '\n\n<?php esc_html_e('Se guardará un backup de tu configuración actual.', 'sage'); ?>')) {
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'flux_apply_preset',
                            preset_key: presetKey,
                            nonce: '<?php echo wp_create_nonce('flux_preset_nonce'); ?>'
                        },
                        beforeSend: function() {
                            $('#flux-preset-message')
                                .removeClass('notice-success notice-error')
                                .addClass('notice-warning')
                                .html('<span class="spinner is-active"></span> <?php esc_html_e('Aplicando plantilla...', 'sage'); ?>')
                                .show();
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#flux-preset-message')
                                    .removeClass('notice-warning')
                                    .addClass('notice-success')
                                    .html('<p>' + response.data + '</p>')
                                    .show();
                                
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                $('#flux-preset-message')
                                    .removeClass('notice-warning')
                                    .addClass('notice-error')
                                    .html('<p>' + response.data + '</p>')
                                    .show();
                            }
                        },
                        error: function() {
                            $('#flux-preset-message')
                                .removeClass('notice-warning')
                                .addClass('notice-error')
                                .html('<p><?php esc_html_e('Error al aplicar la plantilla.', 'sage'); ?></p>')
                                .show();
                        }
                    });
                }
            });

            $('#flux-preset-export').on('click', function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'flux_export_config',
                        nonce: '<?php echo wp_create_nonce('flux_preset_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var blob = new Blob([response.data], { type: 'application/json' });
                            var url = URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'sage-config-<?php echo date('Y-m-d-His'); ?>.json';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
}
