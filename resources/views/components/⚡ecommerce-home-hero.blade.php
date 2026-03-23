<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function slides(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $settings = $service->settings();
        $limits = is_array($settings['limits'] ?? null) ? $settings['limits'] : [];
        $limit = max(1, (int) ($limits['hero'] ?? 3));

        return $service->heroSlides($limit);
    }

    #[Computed]
    public function heroConfig(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $settings = $service->settings();
        $hero = is_array($settings['hero'] ?? null) ? $settings['hero'] : [];

        return [
            'autoplay' => (bool) ($hero['autoplay'] ?? true),
            'interval_ms' => max(2500, min(20000, (int) ($hero['interval_ms'] ?? 6500))),
        ];
    }
}; ?>

<section class="relative overflow-hidden border-b border-zinc-200 dark:border-zinc-800 bg-gradient-to-br from-zinc-50 via-white to-sky-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900 py-10 sm:py-14 lg:py-16">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-12 -right-10 size-64 rounded-full bg-sky-200/40 dark:bg-sky-500/10 blur-3xl"></div>
        <div class="absolute -bottom-12 -left-10 size-64 rounded-full bg-emerald-200/40 dark:bg-emerald-500/10 blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(empty($this->slides))
            <flux:callout color="zinc" icon="shopping-bag">
                <flux:callout.heading>{{ __('No hay contenido para el carrusel.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Configura slides JSON en el Customizer o publica productos para usar fallback dinamico.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        @else
            <div
                x-data="{
                    index: 0,
                    total: {{ count($this->slides) }},
                    autoplay: {{ $this->heroConfig['autoplay'] ? 'true' : 'false' }},
                    interval: {{ (int) $this->heroConfig['interval_ms'] }},
                    timer: null,
                    init() {
                        if (this.autoplay && this.total > 1) {
                            this.start();
                        }
                    },
                    start() {
                        this.stop();
                        this.timer = setInterval(() => this.next(), this.interval);
                    },
                    stop() {
                        if (this.timer) {
                            clearInterval(this.timer);
                            this.timer = null;
                        }
                    },
                    next() {
                        this.index = (this.index + 1) % this.total;
                    },
                    prev() {
                        this.index = (this.index - 1 + this.total) % this.total;
                    },
                    goTo(value) {
                        this.index = value;
                        if (this.autoplay) {
                            this.start();
                        }
                    },
                }"
                x-on:mouseenter="stop()"
                x-on:mouseleave="if (autoplay && total > 1) start()"
                class="relative"
            >
                <div class="relative min-h-[420px] sm:min-h-[460px]">
                    @foreach($this->slides as $idx => $slide)
                        <article
                            x-show="index === {{ $idx }}"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute inset-0 rounded-3xl border border-zinc-200 dark:border-zinc-700 bg-white/90 dark:bg-zinc-900/90 overflow-hidden shadow-xl"
                        >
                            <div class="grid lg:grid-cols-12 h-full">
                                <div class="lg:col-span-7 p-6 sm:p-8 lg:p-10 flex flex-col justify-center gap-5">
                                    @if(($slide['badge'] ?? '') !== '')
                                        <flux:badge color="sky" class="w-max uppercase tracking-widest">{{ $slide['badge'] }}</flux:badge>
                                    @endif

                                    @if(($slide['title'] ?? '') !== '')
                                        <flux:heading size="5xl" class="!font-black tracking-tight leading-tight text-zinc-900 dark:text-zinc-100">
                                            {{ $slide['title'] }}
                                        </flux:heading>
                                    @endif

                                    @if(($slide['subtitle'] ?? '') !== '')
                                        <flux:text class="text-zinc-600 dark:text-zinc-300 text-base sm:text-lg">
                                            {{ $slide['subtitle'] }}
                                        </flux:text>
                                    @endif

                                    @if(($slide['content_html'] ?? '') !== '')
                                        <div class="prose prose-zinc dark:prose-invert max-w-none text-sm sm:text-base">
                                            {!! $slide['content_html'] !!}
                                        </div>
                                    @endif

                                    <div class="flex flex-wrap gap-3 pt-1">
                                        @if(($slide['primary_label'] ?? '') !== '' && ($slide['primary_url'] ?? '') !== '')
                                            <flux:button href="{{ $slide['primary_url'] }}" wire:navigate icon="arrow-up-right" variant="primary">
                                                {{ $slide['primary_label'] }}
                                            </flux:button>
                                        @endif

                                        @if(($slide['secondary_label'] ?? '') !== '' && ($slide['secondary_url'] ?? '') !== '')
                                            <flux:button href="{{ $slide['secondary_url'] }}" wire:navigate icon="arrow-right" variant="outline">
                                                {{ $slide['secondary_label'] }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>

                                <div class="lg:col-span-5 relative bg-zinc-100 dark:bg-zinc-800 min-h-[220px] sm:min-h-[260px] lg:min-h-full">
                                    @if(($slide['image_url'] ?? '') !== '')
                                        <img
                                            src="{{ $slide['image_url'] }}"
                                            alt="{{ $slide['title'] ?? __('Slide', 'flux-press') }}"
                                            class="absolute inset-0 h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <flux:icon.photo class="size-14 text-zinc-400" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if(count($this->slides) > 1)
                    <button
                        type="button"
                        x-on:click="prev()"
                        class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 z-20 inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-300 dark:border-zinc-700 bg-white/90 dark:bg-zinc-900/90 text-zinc-700 dark:text-zinc-200 hover:border-accent-500 transition-colors"
                        aria-label="{{ __('Anterior', 'flux-press') }}"
                    >
                        <flux:icon.chevron-left class="size-5" />
                    </button>
                    <button
                        type="button"
                        x-on:click="next()"
                        class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 z-20 inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-300 dark:border-zinc-700 bg-white/90 dark:bg-zinc-900/90 text-zinc-700 dark:text-zinc-200 hover:border-accent-500 transition-colors"
                        aria-label="{{ __('Siguiente', 'flux-press') }}"
                    >
                        <flux:icon.chevron-right class="size-5" />
                    </button>

                    <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                        @foreach($this->slides as $idx => $slide)
                            <button
                                type="button"
                                x-on:click="goTo({{ $idx }})"
                                class="h-2.5 rounded-full transition-all"
                                :class="index === {{ $idx }} ? 'w-8 bg-accent-600 dark:bg-accent-400' : 'w-2.5 bg-zinc-300 dark:bg-zinc-700'"
                                aria-label="{{ sprintf(__('Ir al slide %d', 'flux-press'), $idx + 1) }}"
                            ></button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
