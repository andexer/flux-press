<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public bool $canAccess = false;
    public bool $autoOpen = false;

    /** @var array<int,string> */
    public array $sectionOrder = [];

    /** @var array<string,bool> */
    public array $sectionVisibility = [];

    public string $contentMode = 'hybrid';
    public string $statusMessage = '';
    public int $lastSavedAt = 0;

    public function mount(): void
    {
        $this->canAccess = is_user_logged_in() && current_user_can('edit_theme_options');

        if (! $this->canAccess) {
            return;
        }

        $settings = app(HomeEcommerceDataService::class)->settings();
        $resolvedOrder = is_array($settings['section_order'] ?? null) ? $settings['section_order'] : [];
        $this->sectionOrder = $this->sanitizeOrder($resolvedOrder);

        $show = is_array($settings['show'] ?? null) ? $settings['show'] : [];
        foreach (HomeEcommerceDataService::SECTION_KEYS as $section) {
            $this->sectionVisibility[$section] = (bool) ($show[$section] ?? true);
        }

        $resolvedMode = (string) ($settings['content_mode'] ?? 'hybrid');
        $this->contentMode = in_array($resolvedMode, $this->allowedContentModes(), true) ? $resolvedMode : 'hybrid';
        $this->autoOpen = is_admin() || (isset($_GET['flux_builder']) && (string) $_GET['flux_builder'] !== '0');
    }

    #[Computed]
    public function sectionCards(): array
    {
        $meta = $this->sectionMeta();
        $service = app(HomeEcommerceDataService::class);
        $cards = [];

        foreach ($this->sectionOrder as $section) {
            if (! isset($meta[$section])) {
                continue;
            }

            $cards[] = [
                'key'         => $section,
                'label'       => $meta[$section]['label'],
                'description' => $meta[$section]['description'],
                'enabled'     => (bool) ($this->sectionVisibility[$section] ?? false),
                'available'   => $service->isSectionAvailable($section),
            ];
        }

        return $cards;
    }

    /**
     * @param array<int,string> $ordered
     */
    public function reorderSections(array $ordered): void
    {
        if (! $this->canAccess) {
            return;
        }

        $nextOrder = $this->sanitizeOrder($ordered);
        if ($nextOrder === $this->sectionOrder) {
            return;
        }

        $this->sectionOrder = $nextOrder;
        set_theme_mod('home_ecommerce_section_order', implode(',', $this->sectionOrder));
        logger()->info('flux_home_builder_reorder', [
            'order' => $this->sectionOrder,
            'user' => get_current_user_id(),
        ]);
        $this->markSaved(__('Orden de secciones actualizado.', 'flux-press'), true);
    }

    public function toggleSection(string $section): void
    {
        if (! $this->canAccess) {
            return;
        }

        $section = sanitize_key($section);
        if (! in_array($section, HomeEcommerceDataService::SECTION_KEYS, true)) {
            return;
        }

        if (! app(HomeEcommerceDataService::class)->isSectionAvailable($section)) {
            return;
        }

        $enabled = ! (bool) ($this->sectionVisibility[$section] ?? false);
        $this->sectionVisibility[$section] = $enabled;
        set_theme_mod("home_ecommerce_show_{$section}", $enabled);
        logger()->info('flux_home_builder_toggle', [
            'section' => $section,
            'enabled' => $enabled,
            'user' => get_current_user_id(),
        ]);

        $this->markSaved(
            $enabled
                ? __('Seccion activada.', 'flux-press')
                : __('Seccion ocultada.', 'flux-press'),
            true
        );
    }

    public function setContentMode(string $mode): void
    {
        if (! $this->canAccess) {
            return;
        }

        $mode = sanitize_key($mode);
        if (! in_array($mode, $this->allowedContentModes(), true)) {
            return;
        }

        if ($this->contentMode === $mode) {
            return;
        }

        $this->contentMode = $mode;
        set_theme_mod('home_ecommerce_content_mode', $mode);
        logger()->info('flux_home_builder_mode', [
            'mode' => $mode,
            'user' => get_current_user_id(),
        ]);
        $this->markSaved(__('Modo de contenido actualizado.', 'flux-press'), true);
    }

    public function refreshHome(): void
    {
        if (! $this->canAccess) {
            return;
        }

        $this->markSaved(__('Vista actualizada.', 'flux-press'));
    }

    /**
     * @return array<int,string>
     */
    private function sanitizeOrder(array $ordered): array
    {
        $allowed = HomeEcommerceDataService::SECTION_KEYS;
        $sanitized = [];
        $seen = [];

        foreach ($ordered as $item) {
            $key = sanitize_key((string) $item);

            if (! in_array($key, $allowed, true) || isset($seen[$key])) {
                continue;
            }

            $sanitized[] = $key;
            $seen[$key] = true;
        }

        foreach ($allowed as $fallback) {
            if (isset($seen[$fallback])) {
                continue;
            }

            $sanitized[] = $fallback;
            $seen[$fallback] = true;
        }

        return $sanitized;
    }

    /**
     * @return array<int,string>
     */
    private function allowedContentModes(): array
    {
        return ['builder', 'hybrid', 'editor'];
    }

    /**
     * @return array<string,array{label:string,description:string}>
     */
    private function sectionMeta(): array
    {
        return [
            'hero' => [
                'label' => __('Hero principal', 'flux-press'),
                'description' => __('Slider principal y llamada inicial.', 'flux-press'),
            ],
            'categories' => [
                'label' => __('Categorias destacadas', 'flux-press'),
                'description' => __('Tarjetas compactas de categorias.', 'flux-press'),
            ],
            'best_sellers' => [
                'label' => __('Mas vendidos', 'flux-press'),
                'description' => __('Productos con mayor traccion.', 'flux-press'),
            ],
            'top_rated' => [
                'label' => __('Mejor valorados', 'flux-press'),
                'description' => __('Productos con mejor puntuacion.', 'flux-press'),
            ],
            'brands' => [
                'label' => __('Marcas destacadas', 'flux-press'),
                'description' => __('Carrusel de marcas afiliadas.', 'flux-press'),
            ],
            'promos' => [
                'label' => __('Promociones', 'flux-press'),
                'description' => __('Bloques de ofertas y campanas.', 'flux-press'),
            ],
            'newsletter' => [
                'label' => __('Newsletter', 'flux-press'),
                'description' => __('Captura de correo y CTA.', 'flux-press'),
            ],
            'blog' => [
                'label' => __('Blog', 'flux-press'),
                'description' => __('Entradas recientes del sitio.', 'flux-press'),
            ],
        ];
    }

    private function markSaved(string $message, bool $needsReload = false): void
    {
        $this->statusMessage = $message;
        $this->lastSavedAt = time();

        // Refresca componentes Livewire del home en caliente.
        $this->dispatch('flux-home-builder-refresh');
        $this->dispatch('flux-home-builder-refresh')->to('ecommerce-home-builder');
        $this->dispatch('fluxHomeBuilderRefresh');
        $this->dispatch('fluxHomeBuilderRefresh')->to('ecommerce-home-builder');
        $this->js("window.Livewire && window.Livewire.dispatch && window.Livewire.dispatch('flux-home-builder-refresh');");
        $this->js("window.Livewire && window.Livewire.dispatch && window.Livewire.dispatch('fluxHomeBuilderRefresh');");
        $this->js("window.dispatchEvent(new CustomEvent('flux-home-builder:refresh'));");

        if ($needsReload) {
            $this->js("window.dispatchEvent(new CustomEvent('flux-home-builder:reload'));");
            $this->js("window.setTimeout(() => window.location.reload(), 120);");
        }
    }
};
?>

@if($canAccess)
    <div
        data-flux-home-builder-drawer
        x-data="fluxVisualBuilderPanel({ initialOpen: @js($autoOpen) })"
        x-on:flux-home-builder:open.window="openPanel()"
        x-on:flux-home-builder:close.window="closePanel()"
        x-on:flux-home-builder:toggle.window="togglePanel()"
        x-on:keydown.escape.window="closePanel()"
        x-cloak
        wire:key="home-visual-builder-drawer"
    >
        <div
            x-show="open"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[9998] bg-zinc-950/40 backdrop-blur-[2px]"
            x-on:click="closePanel()"
        ></div>

        <aside
            x-show="open"
            x-transition:enter="transition ease-out duration-220"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-180"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="fixed right-0 top-0 z-[9999] flex h-[100dvh] w-full max-w-[430px] flex-col border-l border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-950"
            role="dialog"
            aria-label="{{ esc_attr__('Flux Visual Builder', 'flux-press') }}"
        >
            <header class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <flux:heading size="sm" class="!font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                            {{ __('Flux Builder', 'flux-press') }}
                        </flux:heading>
                        <flux:subheading class="mt-1 text-xs">
                            {{ __('Editor visual del home en vivo', 'flux-press') }}
                        </flux:subheading>
                    </div>

                    <flux:button type="button" variant="ghost" size="sm" icon="x-mark" x-on:click="closePanel()">
                        {{ __('Cerrar', 'flux-press') }}
                    </flux:button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-4">
                @if ($statusMessage !== '')
                    <flux:callout color="zinc" icon="check-circle" class="mb-4">
                        <flux:callout.heading>{{ $statusMessage }}</flux:callout.heading>
                        @if ($lastSavedAt > 0)
                            <flux:callout.text>
                                {{ sprintf(__('Ultima actualizacion: %s', 'flux-press'), wp_date('H:i:s', $lastSavedAt)) }}
                            </flux:callout.text>
                        @endif
                    </flux:callout>
                @endif

                <flux:card class="mb-4 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
                    <flux:heading size="sm" class="!font-black uppercase tracking-wider text-zinc-600 dark:text-zinc-300">
                        {{ __('Modo de contenido', 'flux-press') }}
                    </flux:heading>

                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach (['builder' => __('Builder', 'flux-press'), 'hybrid' => __('Hybrid', 'flux-press'), 'editor' => __('Editor', 'flux-press')] as $mode => $label)
                            <flux:button
                                type="button"
                                size="sm"
                                wire:click="setContentMode('{{ $mode }}')"
                                variant="{{ $contentMode === $mode ? 'primary' : 'outline' }}"
                                class="justify-center"
                            >
                                {{ $label }}
                            </flux:button>
                        @endforeach
                    </div>
                </flux:card>

                <flux:card class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/70">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <flux:heading size="sm" class="!font-black uppercase tracking-wider text-zinc-600 dark:text-zinc-300">
                            {{ __('Secciones del Home', 'flux-press') }}
                        </flux:heading>
                        <span class="text-xs font-semibold text-zinc-500">{{ __('Arrastra y suelta', 'flux-press') }}</span>
                    </div>

                    <div class="space-y-2" x-data="fluxSectionSorter($wire)" x-ref="sectionList">
                        @foreach ($this->sectionCards as $card)
                            <div
                                class="rounded-xl border border-zinc-200 bg-zinc-50/60 px-3 py-2.5 transition dark:border-zinc-700 dark:bg-zinc-800/40"
                                data-section="{{ $card['key'] }}"
                                draggable="true"
                                x-on:dragstart="start($event)"
                                x-on:dragover.prevent
                                x-on:drop.prevent="drop($event)"
                                x-on:dragend="end()"
                                :class="draggingKey === '{{ $card['key'] }}' ? 'opacity-70 ring-2 ring-accent-500/60' : ''"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $card['label'] }}</p>
                                            @if (! $card['available'])
                                                <span class="rounded-full border border-amber-300 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                                                    {{ __('Requiere WooCommerce', 'flux-press') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $card['description'] }}</p>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            wire:click="toggleSection('{{ $card['key'] }}')"
                                            variant="{{ $card['enabled'] ? 'primary' : 'ghost' }}"
                                            :disabled="! $card['available']"
                                        >
                                            {{ $card['enabled'] ? __('Activo', 'flux-press') : __('Oculto', 'flux-press') }}
                                        </flux:button>
                                        <button
                                            type="button"
                                            class="rounded-md p-1.5 text-zinc-500 hover:bg-zinc-200/70 hover:text-zinc-700 dark:hover:bg-zinc-700/60 dark:hover:text-zinc-200"
                                            title="{{ esc_attr__('Arrastrar para reordenar', 'flux-press') }}"
                                        >
                                            <flux:icon.bars-3 class="size-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            </div>

            <footer class="border-t border-zinc-200 p-4 dark:border-zinc-800">
                <div class="grid grid-cols-2 gap-2">
                    <flux:button type="button" variant="outline" icon="arrow-path" wire:click="refreshHome" class="justify-center">
                        {{ __('Refrescar', 'flux-press') }}
                    </flux:button>
                    <flux:button type="button" variant="primary" icon="check" x-on:click="closePanel()" class="justify-center">
                        {{ __('Listo', 'flux-press') }}
                    </flux:button>
                </div>
            </footer>
        </aside>
    </div>
@endif
