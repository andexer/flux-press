<?php

use App\Services\FluxThemePresetService;
use Livewire\Component;

new class extends Component
{
    public array $presets = [];

    public string $selectedPreset = '';

    public string $statusMessage = '';

    public bool $isApplying = false;

    public bool $showConfirmModal = false;

    public ?string $pendingPresetKey = null;

    private FluxThemePresetService $presetService;

    public function boot(): void
    {
        $this->presetService = new FluxThemePresetService;
    }

    public function mount(): void
    {
        $this->loadPresets();
        $this->detectCurrentPreset();
    }

    public function loadPresets(): void
    {
        $this->presets = $this->presetService->getAvailablePresets();
    }

    public function detectCurrentPreset(): void
    {
        $currentConfig = $this->presetService->exportCurrentConfig();
        $currentHash = md5(wp_json_encode($currentConfig));

        foreach ($this->presets as $key => $preset) {
            $presetData = $this->presetService->loadPreset($key);
            if ($presetData !== null) {
                $presetHash = md5(wp_json_encode($presetData));
                if ($currentHash === $presetHash) {
                    $this->selectedPreset = $key;

                    return;
                }
            }
        }

        $this->selectedPreset = 'custom';
    }

    public function confirmApplyPreset(string $key): void
    {
        $this->pendingPresetKey = $key;
        $this->showConfirmModal = true;
    }

    public function cancelApply(): void
    {
        $this->pendingPresetKey = null;
        $this->showConfirmModal = false;
    }

    public function applyPreset(string $key): void
    {
        if (! current_user_can('edit_theme_options')) {
            $this->statusMessage = __('No tienes permisos para aplicar presets.', 'flux-press');

            return;
        }

        $this->isApplying = true;
        $this->statusMessage = __('Aplicando preset...', 'flux-press');

        $result = $this->presetService->applyPreset($key);

        $this->isApplying = false;
        $this->showConfirmModal = false;
        $this->pendingPresetKey = null;

        if ($result['success']) {
            $this->statusMessage = $result['message'];
            $this->selectedPreset = $key;
            $this->dispatch('flux-preset-applied');
            $this->js('window.location.reload();');
        } else {
            $this->statusMessage = $result['message'];
        }
    }

    public function exportCurrentConfig(): void
    {
        if (! current_user_can('edit_theme_options')) {
            $this->statusMessage = __('No tienes permisos para exportar.', 'flux-press');

            return;
        }

        $config = $this->presetService->exportCurrentConfig();
        $json = wp_json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'flux-press-export-'.wp_date('Y-m-d-His').'.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Length: '.strlen($json));

        echo $json;
        exit;
    }

    public function getPresetCategoriesProperty(): array
    {
        $categories = [];
        foreach ($this->presets as $preset) {
            $cat = $preset['category'] ?? 'general';
            if (! isset($categories[$cat])) {
                $categories[$cat] = [
                    'key' => $cat,
                    'label' => $this->getCategoryLabel($cat),
                    'count' => 0,
                ];
            }
            $categories[$cat]['count']++;
        }

        return array_values($categories);
    }

    protected function getCategoryLabel(string $category): string
    {
        $labels = [
            'business' => __('Negocios', 'flux-press'),
            'ecommerce' => __('Ecommerce', 'flux-press'),
            'marketing' => __('Marketing', 'flux-press'),
            'news' => __('Noticias', 'flux-press'),
            'portfolio' => __('Portfolio', 'flux-press'),
            'dark' => __('Dark Mode', 'flux-press'),
            'general' => __('General', 'flux-press'),
        ];

        return $labels[$category] ?? ucfirst($category);
    }

    public function getAccentColorsProperty(): array
    {
        return [
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
    }

    public function getPresetColorProperty(): string
    {
        if (! isset($this->presets[$this->selectedPreset])) {
            return '#3b82f6';
        }

        $preset = $this->presets[$this->selectedPreset];

        return $this->getAccentColorsProperty()[$preset['key']] ?? '#3b82f6';
    }
};
?>

<div>
    <div class="flux-preset-selector" x-data="{ showConfirm: false, pendingKey: null }">
        <?php if ($statusMessage) { ?>
            <div class="flux-preset-notice" x-data="{ show: true }" x-show="show" x-transition>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($statusMessage); ?></p>
                    <button type="button" class="notice-dismiss" @click="show = false">
                        <span class="screen-reader-text"><?php _e('Dismiss', 'flux-press'); ?></span>
                    </button>
                </div>
            </div>
        <?php } ?>

        <div class="flux-preset-header">
            <h3><?php esc_html_e('Plantillas del Tema', 'flux-press'); ?></h3>
            <p class="description">
                <?php esc_html_e('Aplica una plantilla predefinida para configurar rapidamente el tema completo.', 'flux-press'); ?>
            </p>
        </div>

        <div class="flux-preset-grid">
            <?php foreach ($this->presets as $key => $preset) { ?>
                <div 
                    class="flux-preset-card <?php echo $selectedPreset === $key ? 'active' : ''; ?>"
                    :class="{ 'active': selectedPreset === '<?php echo esc_js($key); ?>' }"
                    wire:click="confirmApplyPreset('<?php echo esc_js($key); ?>')"
                    style="--preset-color: <?php echo esc_attr($this->getAccentColorsProperty()[$key] ?? '#3b82f6'); ?>"
                >
                    <div class="flux-preset-thumbnail">
                        <?php if (! empty($preset['thumbnail'])) { ?>
                            <img src="<?php echo esc_url($preset['thumbnail']); ?>" alt="<?php echo esc_attr($preset['name']); ?>">
                        <?php } else { ?>
                            <div class="flux-preset-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                                </svg>
                                <span class="flux-preset-category"><?php echo esc_html($this->getCategoryLabel($preset['category'] ?? 'general')); ?></span>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="flux-preset-info">
                        <h4><?php echo esc_html($preset['name']); ?></h4>
                        <p><?php echo esc_html($preset['description']); ?></p>
                    </div>
                    <?php if ($selectedPreset === $key) { ?>
                        <div class="flux-preset-active-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                            </svg>
                            <?php esc_html_e('Activo', 'flux-press'); ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($selectedPreset === 'custom') { ?>
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
                        <h4><?php esc_html_e('Configuración Personal', 'flux-press'); ?></h4>
                        <p><?php esc_html_e('Has personalizado la configuración del tema.', 'flux-press'); ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="flux-preset-actions">
            <flux:button variant="outline" icon="arrow-down-tray" wire:click="exportCurrentConfig">
                <?php esc_html_e('Exportar Configuración', 'flux-press'); ?>
            </flux:button>
        </div>

        <?php if ($showConfirmModal && $pendingPresetKey) { ?>
            <div class="flux-preset-modal-overlay" x-data="{ show: true }" x-show="show" x-transition.opacity @click.self="show = false">
                <div class="flux-preset-modal" x-show="show" x-transition>
                    <h3><?php esc_html_e('¿Aplicar esta plantilla?', 'flux-press'); ?></h3>
                    <p>
                        <?php
                        printf(
                            esc_html__('¿Estás seguro de que quieres aplicar la plantilla "%s"? Se guardará un backup de tu configuración actual.', 'flux-press'),
                            esc_html($this->presets[$pendingPresetKey]['name'] ?? '')
                        );
            ?>
                    </p>
                    <p class="warning">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <?php esc_html_e('Esta acción puede sobrescribir algunos ajustes existentes.', 'flux-press'); ?>
                    </p>
                    <div class="flux-preset-modal-actions">
                        <flux:button variant="ghost" wire:click="cancelApply">
                            <?php esc_html_e('Cancelar', 'flux-press'); ?>
                        </flux:button>
                        <flux:button variant="primary" wire:click="applyPreset('<?php echo esc_js($pendingPresetKey); ?>')" :disabled="$isApplying">
                            <?php esc_html_e('Aplicar Plantilla', 'flux-press'); ?>
                        </flux:button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
