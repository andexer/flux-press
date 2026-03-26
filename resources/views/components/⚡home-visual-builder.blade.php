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

    /** @var array<int,array<string,mixed>> */
    public array $heroSlides = [];

    public int $activeHeroSlide = 0;
    public int $heroEditorStep = 1;
    public bool $heroAutoplay = true;
    public int $heroIntervalMs = 6500;

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

        $this->loadHeroBuilderState($settings);
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
     * @return array<int,array<string,mixed>>
     */
    #[Computed]
    public function heroSlideCards(): array
    {
        $cards = [];

        foreach ($this->heroSlides as $index => $slide) {
            $cards[] = [
                'index' => $index,
                'key' => (string) ($slide['key'] ?? 'slide-' . $index),
                'title' => trim((string) ($slide['title'] ?? '')) !== ''
                    ? trim((string) $slide['title'])
                    : sprintf(__('Slide %d sin titulo', 'flux-press'), $index + 1),
                'badge' => trim((string) ($slide['badge'] ?? '')),
                'image_url' => trim((string) ($slide['image_url'] ?? '')),
                'summary' => trim((string) ($slide['subtitle'] ?? '')),
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

    public function goToHeroStep(int $step): void
    {
        $this->heroEditorStep = max(1, min(3, $step));
    }

    public function nextHeroStep(): void
    {
        $this->heroEditorStep = min(3, $this->heroEditorStep + 1);
    }

    public function previousHeroStep(): void
    {
        $this->heroEditorStep = max(1, $this->heroEditorStep - 1);
    }

    public function selectHeroSlide(int $index): void
    {
        if (! isset($this->heroSlides[$index])) {
            return;
        }

        $this->activeHeroSlide = $index;
        $this->heroEditorStep = 2;
    }

    public function addHeroSlide(): void
    {
        if (! $this->canAccess || count($this->heroSlides) >= $this->heroSlideSlotCount()) {
            return;
        }

        $this->heroSlides[] = $this->blankHeroSlide();
        $this->activeHeroSlide = max(0, count($this->heroSlides) - 1);
        $this->heroEditorStep = 2;
        $this->persistHeroSettings(__('Nuevo slide agregado.', 'flux-press'));
    }

    public function duplicateHeroSlide(int $index): void
    {
        if (! $this->canAccess || ! isset($this->heroSlides[$index]) || count($this->heroSlides) >= $this->heroSlideSlotCount()) {
            return;
        }

        $duplicate = $this->sanitizeEditorSlide($this->heroSlides[$index]);
        $duplicate['key'] = $this->generateHeroSlideKey();

        array_splice($this->heroSlides, $index + 1, 0, [$duplicate]);
        $this->heroSlides = array_values($this->heroSlides);
        $this->activeHeroSlide = min($index + 1, count($this->heroSlides) - 1);
        $this->heroEditorStep = 2;
        $this->persistHeroSettings(__('Slide duplicado.', 'flux-press'));
    }

    public function removeHeroSlide(int $index): void
    {
        if (! $this->canAccess || ! isset($this->heroSlides[$index])) {
            return;
        }

        unset($this->heroSlides[$index]);
        $this->heroSlides = array_values($this->heroSlides);
        $this->activeHeroSlide = empty($this->heroSlides)
            ? 0
            : min($this->activeHeroSlide, count($this->heroSlides) - 1);

        if (empty($this->heroSlides)) {
            $this->heroEditorStep = 1;
        }

        $this->persistHeroSettings(__('Slide eliminado.', 'flux-press'));
    }

    /**
     * @param array<int,string> $orderedKeys
     */
    public function reorderHeroSlides(array $orderedKeys): void
    {
        if (! $this->canAccess || count($this->heroSlides) < 2) {
            return;
        }

        $slidesByKey = [];
        foreach ($this->heroSlides as $slide) {
            $key = (string) ($slide['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $slidesByKey[$key] = $slide;
        }

        $nextSlides = [];
        foreach ($orderedKeys as $orderedKey) {
            $orderedKey = (string) $orderedKey;
            if (! isset($slidesByKey[$orderedKey])) {
                continue;
            }

            $nextSlides[] = $slidesByKey[$orderedKey];
            unset($slidesByKey[$orderedKey]);
        }

        foreach ($slidesByKey as $remainingSlide) {
            $nextSlides[] = $remainingSlide;
        }

        if (empty($nextSlides)) {
            return;
        }

        $activeKey = (string) ($this->heroSlides[$this->activeHeroSlide]['key'] ?? '');
        $this->heroSlides = array_values($nextSlides);

        foreach ($this->heroSlides as $index => $slide) {
            if ((string) ($slide['key'] ?? '') === $activeKey) {
                $this->activeHeroSlide = $index;
                break;
            }
        }

        $this->persistHeroSettings(__('Orden del hero actualizado.', 'flux-press'));
    }

    public function saveHeroSettings(): void
    {
        if (! $this->canAccess) {
            return;
        }

        $this->persistHeroSettings(__('Hero principal actualizado.', 'flux-press'));
    }

    public function refreshHome(): void
    {
        if (! $this->canAccess) {
            return;
        }

        $this->markSaved(__('Vista actualizada.', 'flux-press'));
    }

    /**
     * @param array<int,string> $ordered
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

    /**
     * @param array<string,mixed> $settings
     */
    private function loadHeroBuilderState(array $settings): void
    {
        $hero = is_array($settings['hero'] ?? null) ? $settings['hero'] : [];
        $heroLimit = max(1, min($this->heroSlideSlotCount(), (int) (($settings['limits']['hero'] ?? $this->heroSlideSlotCount()))));

        $this->heroAutoplay = (bool) ($hero['autoplay'] ?? true);
        $this->heroIntervalMs = max(2500, min(20000, (int) ($hero['interval_ms'] ?? 6500)));

        $sourceSlides = [];
        if (! empty($hero['visual_slides']) && is_array($hero['visual_slides'])) {
            $sourceSlides = $hero['visual_slides'];
        } elseif (($hero['slides_json'] ?? '') !== '') {
            $decoded = json_decode((string) $hero['slides_json'], true);
            if (is_array($decoded)) {
                $sourceSlides = $decoded;
            }
        }

        if (empty($sourceSlides)) {
            $sourceSlides = app(HomeEcommerceDataService::class)->heroSlides($heroLimit);
        }

        $this->heroSlides = $this->normalizeSlidesForEditor($sourceSlides);
        $this->activeHeroSlide = empty($this->heroSlides) ? 0 : min($this->activeHeroSlide, count($this->heroSlides) - 1);
    }

    /**
     * @param array<int,array<string,mixed>> $slides
     * @return array<int,array<string,mixed>>
     */
    private function normalizeSlidesForEditor(array $slides): array
    {
        $normalized = [];

        foreach ($slides as $slide) {
            if (! is_array($slide)) {
                continue;
            }

            $normalized[] = $this->sanitizeEditorSlide($slide);

            if (count($normalized) >= $this->heroSlideSlotCount()) {
                break;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string,mixed> $slide
     * @return array<string,mixed>
     */
    private function sanitizeEditorSlide(array $slide): array
    {
        $key = trim((string) ($slide['key'] ?? ''));

        return [
            'key' => $key !== '' ? $key : $this->generateHeroSlideKey(),
            'badge' => sanitize_text_field((string) ($slide['badge'] ?? '')),
            'title' => sanitize_text_field((string) ($slide['title'] ?? '')),
            'subtitle' => sanitize_textarea_field((string) ($slide['subtitle'] ?? '')),
            'content_html' => wp_kses_post((string) ($slide['content_html'] ?? '')),
            'image_url' => esc_url_raw((string) ($slide['image_url'] ?? '')),
            'primary_label' => sanitize_text_field((string) ($slide['primary_label'] ?? '')),
            'primary_url' => esc_url_raw((string) ($slide['primary_url'] ?? '')),
            'secondary_label' => sanitize_text_field((string) ($slide['secondary_label'] ?? '')),
            'secondary_url' => esc_url_raw((string) ($slide['secondary_url'] ?? '')),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function blankHeroSlide(): array
    {
        return [
            'key' => $this->generateHeroSlideKey(),
            'badge' => '',
            'title' => '',
            'subtitle' => '',
            'content_html' => '',
            'image_url' => '',
            'primary_label' => '',
            'primary_url' => '',
            'secondary_label' => '',
            'secondary_url' => '',
        ];
    }

    private function generateHeroSlideKey(): string
    {
        return 'hero-' . wp_generate_uuid4();
    }

    private function heroSlideSlotCount(): int
    {
        return 6;
    }

    private function persistHeroSettings(string $message): void
    {
        $slidesForStorage = [];

        foreach ($this->heroSlides as $slide) {
            $normalized = $this->sanitizeEditorSlide(is_array($slide) ? $slide : []);

            if (
                $normalized['title'] === ''
                && $normalized['subtitle'] === ''
                && $normalized['content_html'] === ''
                && $normalized['image_url'] === ''
            ) {
                continue;
            }

            $slidesForStorage[] = $normalized;

            if (count($slidesForStorage) >= $this->heroSlideSlotCount()) {
                break;
            }
        }

        foreach (range(1, $this->heroSlideSlotCount()) as $slot) {
            $prefix = "home_ecommerce_hero_slide_{$slot}";
            $slide = $slidesForStorage[$slot - 1] ?? null;

            if (is_array($slide)) {
                set_theme_mod("{$prefix}_enabled", true);
                set_theme_mod("{$prefix}_badge", $slide['badge']);
                set_theme_mod("{$prefix}_title", $slide['title']);
                set_theme_mod("{$prefix}_subtitle", $slide['subtitle']);
                set_theme_mod("{$prefix}_content_html", $slide['content_html']);
                set_theme_mod("{$prefix}_image_url", $slide['image_url']);
                set_theme_mod("{$prefix}_image_id", 0);
                set_theme_mod("{$prefix}_primary_label", $slide['primary_label']);
                set_theme_mod("{$prefix}_primary_url", $slide['primary_url']);
                set_theme_mod("{$prefix}_secondary_label", $slide['secondary_label']);
                set_theme_mod("{$prefix}_secondary_url", $slide['secondary_url']);
                continue;
            }

            set_theme_mod("{$prefix}_enabled", false);
            set_theme_mod("{$prefix}_badge", '');
            set_theme_mod("{$prefix}_title", '');
            set_theme_mod("{$prefix}_subtitle", '');
            set_theme_mod("{$prefix}_content_html", '');
            set_theme_mod("{$prefix}_image_url", '');
            set_theme_mod("{$prefix}_image_id", 0);
            set_theme_mod("{$prefix}_primary_label", '');
            set_theme_mod("{$prefix}_primary_url", '');
            set_theme_mod("{$prefix}_secondary_label", '');
            set_theme_mod("{$prefix}_secondary_url", '');
        }

        set_theme_mod('home_ecommerce_hero_autoplay', (bool) $this->heroAutoplay);
        set_theme_mod('home_ecommerce_hero_interval_ms', max(2500, min(20000, (int) $this->heroIntervalMs)));
        set_theme_mod(
            'home_ecommerce_hero_slides_json',
            wp_json_encode(array_map(function (array $slide): array {
                unset($slide['key']);
                return $slide;
            }, $slidesForStorage)) ?: '[]'
        );

        $this->heroSlides = $this->normalizeSlidesForEditor($slidesForStorage);
        $this->activeHeroSlide = empty($this->heroSlides) ? 0 : min($this->activeHeroSlide, count($this->heroSlides) - 1);

        $this->markSaved($message);
    }

    private function markSaved(string $message, bool $needsReload = false): void
    {
        $this->statusMessage = $message;
        $this->lastSavedAt = time();

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
        <button
            type="button"
            x-show="!open"
            x-transition.opacity.duration.200ms
            class="fixed bottom-4 right-4 z-[9997] inline-flex items-center gap-2 rounded-full bg-zinc-950 px-4 py-3 text-sm font-semibold text-white shadow-xl ring-1 ring-white/10 transition hover:bg-zinc-900 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100"
            x-on:click="openPanel()"
        >
            <flux:icon.adjustments-horizontal class="size-4" />
            {{ __('Editar home', 'flux-press') }}
        </button>

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
            class="fixed right-0 top-0 z-[9999] flex h-[100dvh] w-full max-w-[460px] flex-col border-l border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-950"
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
                            {{ __('Organiza el home y construye el hero paso a paso.', 'flux-press') }}
                        </flux:subheading>
                    </div>

                    <flux:button type="button" variant="ghost" size="sm" icon="x-mark" x-on:click="closePanel()">
                        {{ __('Cerrar', 'flux-press') }}
                    </flux:button>
                </div>
            </header>

            <div class="flex-1 space-y-4 overflow-y-auto p-4">
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

                <flux:callout color="accent" icon="sparkles">
                    <flux:callout.heading>{{ __('Guia rapida', 'flux-press') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('1. Ordena secciones. 2. Agrega y ordena slides. 3. Edita el contenido del hero y aplicalo al home.', 'flux-press') }}</flux:callout.text>
                </flux:callout>

                <flux:card class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
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

                <flux:card class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/70">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <flux:heading size="sm" class="!font-black uppercase tracking-wider text-zinc-600 dark:text-zinc-300">
                                {{ __('Hero principal', 'flux-press') }}
                            </flux:heading>
                            <flux:subheading class="mt-1 text-xs">
                                {{ __('Crea el carrusel con un flujo guiado y amigable.', 'flux-press') }}
                            </flux:subheading>
                        </div>

                        <span class="rounded-full bg-accent-500/10 px-2.5 py-1 text-xs font-bold text-accent-700 dark:text-accent-300">
                            {{ sprintf(_n('%d slide', '%d slides', count($this->heroSlideCards), 'flux-press'), count($this->heroSlideCards)) }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2">
                        @foreach ([1 => __('1. Slides', 'flux-press'), 2 => __('2. Contenido', 'flux-press'), 3 => __('3. Comportamiento', 'flux-press')] as $step => $label)
                            <flux:button
                                type="button"
                                size="sm"
                                wire:click="goToHeroStep({{ $step }})"
                                variant="{{ $heroEditorStep === $step ? 'primary' : 'outline' }}"
                                class="justify-center text-[11px]"
                            >
                                {{ $label }}
                            </flux:button>
                        @endforeach
                    </div>

                    @if ($heroEditorStep === 1)
                        <div class="mt-4 space-y-3">
                            <flux:callout color="zinc" icon="queue-list">
                                <flux:callout.heading>{{ __('Paso 1: arma la secuencia', 'flux-press') }}</flux:callout.heading>
                                <flux:callout.text>{{ __('Agrega slides, ordénalos con drag and drop y elige cuál editar. Todo lo que hagas aquí alimenta el carrusel principal.', 'flux-press') }}</flux:callout.text>
                            </flux:callout>

                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Maximo 6 slides visuales para mantener buena experiencia en desktop y mobile.', 'flux-press') }}</p>
                                <flux:button type="button" size="sm" variant="primary" icon="plus" wire:click="addHeroSlide" :disabled="count($this->heroSlideCards) >= 6">
                                    {{ __('Agregar', 'flux-press') }}
                                </flux:button>
                            </div>

                            @if (empty($this->heroSlideCards))
                                <div class="rounded-2xl border border-dashed border-zinc-300 px-4 py-6 text-center dark:border-zinc-700">
                                    <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ __('No hay slides listos para el hero.', 'flux-press') }}</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Empieza con un slide y luego ajustamos textos, botones e imagen.', 'flux-press') }}</p>
                                </div>
                            @else
                                <div class="space-y-2" x-data="fluxSlideSorter($wire)" x-ref="slideList">
                                    @foreach ($this->heroSlideCards as $slide)
                                        <div
                                            class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3 transition dark:border-zinc-700 dark:bg-zinc-800/50"
                                            data-slide-key="{{ $slide['key'] }}"
                                            draggable="true"
                                            x-on:dragstart="start($event)"
                                            x-on:dragover.prevent
                                            x-on:drop.prevent="drop($event)"
                                            x-on:dragend="end()"
                                            :class="draggingKey === '{{ $slide['key'] }}' ? 'opacity-75 ring-2 ring-accent-500/60' : ''"
                                        >
                                            <div class="flex items-start gap-3">
                                                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-zinc-200 text-xs font-black text-zinc-600 dark:bg-zinc-700 dark:text-zinc-200">
                                                    @if ($slide['image_url'] !== '')
                                                        <img src="{{ $slide['image_url'] }}" alt="{{ $slide['title'] }}" class="h-full w-full object-cover" />
                                                    @else
                                                        {{ sprintf('%02d', $slide['index'] + 1) }}
                                                    @endif
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <p class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $slide['title'] }}</p>
                                                        @if ($slide['badge'] !== '')
                                                            <span class="rounded-full bg-accent-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.18em] text-accent-700 dark:text-accent-300">
                                                                {{ $slide['badge'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="mt-1 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">{{ $slide['summary'] !== '' ? $slide['summary'] : __('Sin subtitulo todavia.', 'flux-press') }}</p>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <flux:button type="button" size="sm" variant="{{ $activeHeroSlide === $slide['index'] ? 'primary' : 'outline' }}" wire:click="selectHeroSlide({{ $slide['index'] }})">
                                                        {{ $activeHeroSlide === $slide['index'] ? __('Editando', 'flux-press') : __('Editar', 'flux-press') }}
                                                    </flux:button>
                                                    <flux:button type="button" size="sm" variant="ghost" icon="document-duplicate" wire:click="duplicateHeroSlide({{ $slide['index'] }})" :disabled="count($this->heroSlideCards) >= 6">
                                                        {{ __('Duplicar', 'flux-press') }}
                                                    </flux:button>
                                                </div>

                                                <div class="flex items-center gap-1">
                                                    <flux:button type="button" size="sm" variant="ghost" icon="trash" wire:click="removeHeroSlide({{ $slide['index'] }})">
                                                        {{ __('Quitar', 'flux-press') }}
                                                    </flux:button>
                                                    <button type="button" class="rounded-md p-1.5 text-zinc-500 hover:bg-zinc-200/70 hover:text-zinc-700 dark:hover:bg-zinc-700/60 dark:hover:text-zinc-200" title="{{ esc_attr__('Arrastrar slide', 'flux-press') }}">
                                                        <flux:icon.bars-3 class="size-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @elseif ($heroEditorStep === 2)
                        <div class="mt-4 space-y-3">
                            <flux:callout color="zinc" icon="pencil-square">
                                <flux:callout.heading>{{ __('Paso 2: edita el slide activo', 'flux-press') }}</flux:callout.heading>
                                <flux:callout.text>{{ __('Trabaja slide por slide. Primero elige uno, luego define mensaje, botones e imagen.', 'flux-press') }}</flux:callout.text>
                            </flux:callout>

                            @if (empty($this->heroSlides) || ! isset($this->heroSlides[$activeHeroSlide]))
                                <div class="rounded-2xl border border-dashed border-zinc-300 px-4 py-6 text-center dark:border-zinc-700">
                                    <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ __('Aun no hay un slide seleccionado.', 'flux-press') }}</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Agrega uno en el paso anterior y seguimos.', 'flux-press') }}</p>
                                </div>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($this->heroSlideCards as $slide)
                                        <button
                                            type="button"
                                            wire:click="selectHeroSlide({{ $slide['index'] }})"
                                            class="{{ $activeHeroSlide === $slide['index'] ? 'border-accent-400 bg-accent-500/10 text-accent-700 dark:text-accent-300' : 'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }} rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors"
                                        >
                                            {{ sprintf(__('Slide %d', 'flux-press'), $slide['index'] + 1) }}
                                        </button>
                                    @endforeach
                                </div>

                                <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-950 dark:border-zinc-800">
                                    <div class="relative min-h-[180px] p-5">
                                        @if (($heroSlides[$activeHeroSlide]['image_url'] ?? '') !== '')
                                            <img src="{{ $heroSlides[$activeHeroSlide]['image_url'] }}" alt="{{ $heroSlides[$activeHeroSlide]['title'] ?? __('Preview', 'flux-press') }}" class="absolute inset-0 h-full w-full object-cover opacity-45" />
                                        @endif
                                        <div class="absolute inset-0 bg-gradient-to-r from-zinc-950 via-zinc-950/85 to-zinc-900/35"></div>
                                        <div class="relative z-10 max-w-[80%]">
                                            @if (($heroSlides[$activeHeroSlide]['badge'] ?? '') !== '')
                                                <span class="inline-flex rounded-full bg-accent-500/15 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-accent-200">{{ $heroSlides[$activeHeroSlide]['badge'] }}</span>
                                            @endif
                                            <p class="mt-3 text-xl font-black text-white">{{ ($heroSlides[$activeHeroSlide]['title'] ?? '') !== '' ? $heroSlides[$activeHeroSlide]['title'] : __('Titulo del slide', 'flux-press') }}</p>
                                            <p class="mt-2 text-sm text-zinc-200">{{ ($heroSlides[$activeHeroSlide]['subtitle'] ?? '') !== '' ? $heroSlides[$activeHeroSlide]['subtitle'] : __('Agrega un subtitulo corto y claro para esta portada.', 'flux-press') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-3">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Badge', 'flux-press') }}</label>
                                        <input type="text" wire:model.blur="heroSlides.{{ $activeHeroSlide }}.badge" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-hidden focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="{{ esc_attr__('Ej: Mas vendido', 'flux-press') }}">
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Titulo principal', 'flux-press') }}</label>
                                        <input type="text" wire:model.blur="heroSlides.{{ $activeHeroSlide }}.title" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-hidden focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="{{ esc_attr__('Ej: Coleccion que marca tendencia', 'flux-press') }}">
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Subtitulo', 'flux-press') }}</label>
                                        <textarea wire:model.blur="heroSlides.{{ $activeHeroSlide }}.subtitle" rows="3" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-hidden focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="{{ esc_attr__('Explica rapido que ofreces en este slide.', 'flux-press') }}"></textarea>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('URL de imagen', 'flux-press') }}</label>
                                        <input type="url" wire:model.blur="heroSlides.{{ $activeHeroSlide }}.image_url" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-hidden focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="https://">
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Boton principal', 'flux-press') }}</label>
                                            <input type="text" wire:model.blur="heroSlides.{{ $activeHeroSlide }}.primary_label" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-hidden focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="{{ esc_attr__('Ver producto', 'flux-press') }}">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('URL principal', 'flux-press') }}</label>
                                            <input type="url" wire:model.blur="heroSlides.{{ $activeHeroSlide }}.primary_url" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-hidden focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="https://">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Boton secundario', 'flux-press') }}</label>
                                            <input type="text" wire:model.blur="heroSlides.{{ $activeHeroSlide }}.secondary_label" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-hidden focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="{{ esc_attr__('Explorar', 'flux-press') }}">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('URL secundaria', 'flux-press') }}</label>
                                            <input type="url" wire:model.blur="heroSlides.{{ $activeHeroSlide }}.secondary_url" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-hidden focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="https://">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mt-4 space-y-3">
                            <flux:callout color="zinc" icon="play">
                                <flux:callout.heading>{{ __('Paso 3: comportamiento del carrusel', 'flux-press') }}</flux:callout.heading>
                                <flux:callout.text>{{ __('Define si avanza solo y cada cuántos milisegundos cambia de slide. Usa valores suaves para que se sienta didactico y cómodo.', 'flux-press') }}</flux:callout.text>
                            </flux:callout>

                            <label class="flex items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/70 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Autoplay', 'flux-press') }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Activa el avance automatico del hero.', 'flux-press') }}</p>
                                </div>
                                <input type="checkbox" wire:model.live="heroAutoplay" class="h-5 w-5 rounded border-zinc-300 text-accent-600 focus:ring-accent-500 dark:border-zinc-600">
                            </label>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Intervalo en milisegundos', 'flux-press') }}</label>
                                <input type="number" min="2500" max="20000" step="100" wire:model.blur="heroIntervalMs" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-hidden focus:border-accent-400 focus:ring-4 focus:ring-accent-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Recomendado: entre 5000 y 7000 para lectura cómoda.', 'flux-press') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                        <flux:button type="button" variant="outline" icon="chevron-left" wire:click="previousHeroStep" :disabled="$heroEditorStep === 1">
                            {{ __('Anterior', 'flux-press') }}
                        </flux:button>

                        <div class="flex items-center gap-2">
                            <flux:button type="button" variant="outline" icon="arrow-path" wire:click="refreshHome">
                                {{ __('Vista', 'flux-press') }}
                            </flux:button>
                            @if ($heroEditorStep < 3)
                                <flux:button type="button" variant="ghost" icon="chevron-right" wire:click="nextHeroStep">
                                    {{ __('Siguiente', 'flux-press') }}
                                </flux:button>
                            @endif
                            <flux:button type="button" variant="primary" icon="check" wire:click="saveHeroSettings">
                                {{ __('Aplicar hero', 'flux-press') }}
                            </flux:button>
                        </div>
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
