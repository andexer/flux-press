<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public bool $canAccess = false;

    /** @var array<int,string> */
    public array $sectionOrder = [];

    /** @var array<string,bool> */
    public array $sectionVisibility = [];

    public string $contentMode = 'hybrid';
    public string $previewUrl = '';
    public string $statusMessage = '';
    public int $lastSavedAt = 0;

    public function mount(): void
    {
        $this->canAccess = current_user_can('edit_theme_options');

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
        $this->previewUrl = $this->buildPreviewUrl(true);
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
        $this->markSaved(__('Orden de secciones actualizado.', 'flux-press'));
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

        $enabled = ! (bool) ($this->sectionVisibility[$section] ?? false);
        $this->sectionVisibility[$section] = $enabled;
        set_theme_mod("home_ecommerce_show_{$section}", $enabled);

        $this->markSaved(
            $enabled
                ? __('Seccion activada.', 'flux-press')
                : __('Seccion ocultada.', 'flux-press')
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
        $this->markSaved(__('Modo de contenido actualizado.', 'flux-press'));
    }

    public function refreshPreview(): void
    {
        if (! $this->canAccess) {
            return;
        }

        $this->previewUrl = $this->buildPreviewUrl(true);
        $this->dispatchPreviewRefresh();
        $this->statusMessage = __('Preview recargado.', 'flux-press');
        $this->lastSavedAt = time();
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

    private function markSaved(string $message): void
    {
        $this->statusMessage = $message;
        $this->lastSavedAt = time();
        $this->previewUrl = $this->buildPreviewUrl(true);
        $this->dispatchPreviewRefresh();
    }

    private function dispatchPreviewRefresh(): void
    {
        $payload = wp_json_encode(
            ['url' => $this->previewUrl],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if (! is_string($payload) || $payload === '') {
            return;
        }

        $this->js("window.dispatchEvent(new CustomEvent('flux-home-builder-preview', { detail: {$payload} }));");
    }

    private function buildPreviewUrl(bool $cacheBust = false): string
    {
        $baseUrl = (string) home_url('/');
        $showOnFront = (string) get_option('show_on_front', 'posts');
        $frontPageId = (int) get_option('page_on_front', 0);

        if ($showOnFront === 'page' && $frontPageId > 0) {
            $frontPageUrl = get_permalink($frontPageId);
            if (is_string($frontPageUrl) && $frontPageUrl !== '') {
                $baseUrl = $frontPageUrl;
            }
        }

        $args = ['flux_visual_builder_preview' => '1'];
        if ($cacheBust) {
            $args['flux_preview_tick'] = (string) microtime(true);
        }

        return esc_url_raw((string) add_query_arg($args, $baseUrl));
    }
};
?>

<div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm md:p-5"
    x-data="fluxVisualBuilderPanel(@js($previewUrl))"
    x-on:flux-home-builder-preview.window="onPreviewRefresh($event)"
    wire:key="home-visual-builder-admin">
    @if (! $canAccess)
        <flux:callout color="amber" icon="shield-exclamation">
            <flux:callout.heading>{{ __('No tienes permisos para editar el builder visual.', 'flux-press') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Se requiere el permiso edit_theme_options.', 'flux-press') }}</flux:callout.text>
        </flux:callout>
    @else
        <div class="grid gap-4 xl:grid-cols-[430px_minmax(0,1fr)]">
            <aside class="space-y-4">
                <flux:card class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 shadow-sm">
                    <flux:heading size="lg" class="!font-black tracking-tight">
                        {{ __('Panel Visual del Home', 'flux-press') }}
                    </flux:heading>
                    <flux:subheading class="mt-1">
                        {{ __('Configura, reordena y aplica cambios en vivo desde la barra de administracion.', 'flux-press') }}
                    </flux:subheading>
                </flux:card>

                @if ($statusMessage !== '')
                    <flux:callout color="zinc" icon="check-circle">
                        <flux:callout.heading>{{ $statusMessage }}</flux:callout.heading>
                        @if ($lastSavedAt > 0)
                            <flux:callout.text>
                                {{ sprintf(__('Ultima actualizacion: %s', 'flux-press'), wp_date('H:i:s', $lastSavedAt)) }}
                            </flux:callout.text>
                        @endif
                    </flux:callout>
                @endif

                <flux:card class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <flux:heading size="sm" class="!font-black uppercase tracking-wider text-zinc-600">
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

                <flux:card class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <flux:heading size="sm" class="!font-black uppercase tracking-wider text-zinc-600">
                            {{ __('Secciones del Home', 'flux-press') }}
                        </flux:heading>
                        <span class="text-xs font-semibold text-zinc-500">
                            {{ __('Arrastra y suelta', 'flux-press') }}
                        </span>
                    </div>

                    <div class="space-y-2"
                        x-data="fluxSectionSorter($wire)"
                        x-ref="sectionList">
                        @foreach ($this->sectionCards as $card)
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 px-3 py-2.5 transition"
                                data-section="{{ $card['key'] }}"
                                draggable="true"
                                x-on:dragstart="start($event)"
                                x-on:dragover.prevent
                                x-on:drop.prevent="drop($event)"
                                x-on:dragend="end()"
                                :class="draggingKey === '{{ $card['key'] }}' ? 'opacity-70 ring-2 ring-accent-500/60' : ''">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-bold text-zinc-900">{{ $card['label'] }}</p>

                                            @if (! $card['available'])
                                                <span class="rounded-full border border-amber-300 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                                                    {{ __('Requiere WooCommerce', 'flux-press') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-xs text-zinc-500">{{ $card['description'] }}</p>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            wire:click="toggleSection('{{ $card['key'] }}')"
                                            variant="{{ $card['enabled'] ? 'primary' : 'ghost' }}"
                                        >
                                            {{ $card['enabled'] ? __('Activo', 'flux-press') : __('Oculto', 'flux-press') }}
                                        </flux:button>
                                        <button type="button" class="rounded-md p-1.5 text-zinc-500 hover:bg-zinc-200/70 hover:text-zinc-700" title="{{ esc_attr__('Arrastrar para reordenar', 'flux-press') }}">
                                            <flux:icon.bars-3 class="size-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            </aside>

            <section class="space-y-3">
                <div class="flex items-center justify-between gap-2 rounded-2xl border border-zinc-200 bg-white px-3 py-2 shadow-sm">
                    <div>
                        <p class="text-sm font-black text-zinc-900">{{ __('Preview en vivo', 'flux-press') }}</p>
                        <p class="text-xs text-zinc-500">{{ __('Los cambios se guardan y se aplican automaticamente.', 'flux-press') }}</p>
                    </div>
                    <flux:button type="button" size="sm" variant="outline" icon="arrow-path" wire:click="refreshPreview">
                        {{ __('Refrescar', 'flux-press') }}
                    </flux:button>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100 shadow-sm">
                    <iframe
                        title="{{ esc_attr__('Preview Home Ecommerce', 'flux-press') }}"
                        x-bind:src="previewUrl"
                        class="block h-[72vh] min-h-[560px] w-full bg-white"
                        loading="lazy"
                    ></iframe>
                </div>
            </section>
        </div>
    @endif
</div>

<script>
    if (!window.fluxVisualBuilderPanel) {
        window.fluxVisualBuilderPanel = function(initialPreviewUrl) {
            return {
                previewUrl: initialPreviewUrl,
                onPreviewRefresh(event) {
                    const nextUrl = event?.detail?.url;
                    if (typeof nextUrl === 'string' && nextUrl.length > 0) {
                        this.previewUrl = nextUrl;
                    }
                },
            };
        };
    }

    if (!window.fluxSectionSorter) {
        window.fluxSectionSorter = function(wire) {
            return {
                draggingKey: null,
                start(event) {
                    this.draggingKey = event.currentTarget?.dataset?.section ?? null;
                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                    }
                },
                drop(event) {
                    const targetKey = event.currentTarget?.dataset?.section ?? null;
                    if (!this.draggingKey || !targetKey || this.draggingKey === targetKey) {
                        return;
                    }

                    const currentOrder = Array.from(this.$refs.sectionList.querySelectorAll('[data-section]'))
                        .map((element) => element.dataset.section)
                        .filter((key) => typeof key === 'string' && key.length > 0);

                    const fromIndex = currentOrder.indexOf(this.draggingKey);
                    const toIndex = currentOrder.indexOf(targetKey);

                    if (fromIndex < 0 || toIndex < 0) {
                        this.draggingKey = null;
                        return;
                    }

                    const nextOrder = [...currentOrder];
                    const [moved] = nextOrder.splice(fromIndex, 1);
                    nextOrder.splice(toIndex, 0, moved);
                    wire.reorderSections(nextOrder);
                    this.draggingKey = null;
                },
                end() {
                    this.draggingKey = null;
                },
            };
        };
    }
</script>
