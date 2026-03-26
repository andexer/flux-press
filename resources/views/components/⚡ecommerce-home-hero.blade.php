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

<section class="relative w-full overflow-hidden border-b border-zinc-200 dark:border-zinc-800 bg-zinc-950">
    @if(empty($this->slides))
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <flux:callout color="zinc" icon="shopping-bag">
                <flux:callout.heading>{{ __('No hay contenido para el carrusel.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Configura slides visuales o JSON en el Customizer para personalizar este hero.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        </div>
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
            class="relative isolate min-h-[72svh] sm:min-h-[620px]"
        >
            @foreach($this->slides as $idx => $slide)
                <article
                    x-show="index === {{ $idx }}"
                    x-transition:enter="transition ease-out duration-400"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-250"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0"
                >
                    <div class="absolute inset-0">
                        @if(($slide['image_url'] ?? '') !== '')
                            <img
                                src="{{ $slide['image_url'] }}"
                                alt="{{ $slide['title'] ?? __('Slide', 'flux-press') }}"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            />
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-teal-700 via-teal-900 to-zinc-950"></div>
                            <div class="absolute -left-16 top-8 size-64 rounded-full bg-amber-400/20 blur-3xl"></div>
                            <div class="absolute -right-16 bottom-8 size-64 rounded-full bg-cyan-300/20 blur-3xl"></div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-r from-zinc-950/90 via-zinc-950/65 to-zinc-950/20"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/75 via-zinc-950/30 to-transparent"></div>
                    </div>

                    <div class="relative flex h-full items-end">
                        <div class="w-full px-4 pb-10 pt-20 sm:px-8 sm:pb-12 lg:px-14 lg:pb-16">
                            <div class="max-w-3xl rounded-3xl border border-white/20 bg-zinc-950/60 p-6 shadow-2xl backdrop-blur-xl sm:p-8 lg:p-10">
                                @if(($slide['badge'] ?? '') !== '')
                                    <flux:badge color="amber" class="w-max uppercase tracking-[0.2em]">{{ $slide['badge'] }}</flux:badge>
                                @endif

                                @if(($slide['title'] ?? '') !== '')
                                    <flux:heading size="5xl" class="!mt-4 !font-black tracking-tight !leading-tight !text-white">
                                        {{ $slide['title'] }}
                                    </flux:heading>
                                @endif

                                @if(($slide['subtitle'] ?? '') !== '')
                                    <flux:text class="mt-4 text-base sm:text-lg text-zinc-100/90">
                                        {{ $slide['subtitle'] }}
                                    </flux:text>
                                @endif

                                @if(($slide['content_html'] ?? '') !== '')
                                    <div class="prose prose-invert mt-4 max-w-none text-sm sm:text-base">
                                        {!! $slide['content_html'] !!}
                                    </div>
                                @endif

                                <div class="mt-6 flex flex-wrap gap-3">
                                    @if(($slide['primary_label'] ?? '') !== '' && ($slide['primary_url'] ?? '') !== '')
                                        <flux:button href="{{ $slide['primary_url'] }}" wire:navigate icon="arrow-up-right" variant="primary" class="!bg-amber-500 hover:!bg-amber-600 !text-zinc-900 border-0">
                                            {{ $slide['primary_label'] }}
                                        </flux:button>
                                    @endif

                                    @if(($slide['secondary_label'] ?? '') !== '' && ($slide['secondary_url'] ?? '') !== '')
                                        <flux:button href="{{ $slide['secondary_url'] }}" wire:navigate icon="arrow-right" variant="outline" class="!border-white/50 !text-white hover:!bg-white/15">
                                            {{ $slide['secondary_label'] }}
                                        </flux:button>
                                    @endif
                                </div>

                                <div class="mt-6 flex flex-wrap items-center gap-4 text-xs font-semibold text-zinc-200/90 sm:text-sm">
                                    <span class="inline-flex items-center gap-2"><span class="size-1.5 rounded-full bg-emerald-400"></span>{{ __('Personalizable', 'flux-press') }}</span>
                                    <span class="inline-flex items-center gap-2"><span class="size-1.5 rounded-full bg-sky-400"></span>{{ __('Responsive desktop + mobile', 'flux-press') }}</span>
                                    <span class="inline-flex items-center gap-2"><span class="size-1.5 rounded-full bg-amber-400"></span>{{ __('Listo para conversion', 'flux-press') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach

            @if(count($this->slides) > 1)
                <button
                    type="button"
                    x-on:click="prev()"
                    class="absolute left-3 sm:left-5 top-1/2 -translate-y-1/2 z-20 inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/30 bg-zinc-950/40 text-white backdrop-blur transition hover:bg-zinc-900/70"
                    aria-label="{{ __('Anterior', 'flux-press') }}"
                >
                    <flux:icon.chevron-left class="size-5" />
                </button>
                <button
                    type="button"
                    x-on:click="next()"
                    class="absolute right-3 sm:right-5 top-1/2 -translate-y-1/2 z-20 inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/30 bg-zinc-950/40 text-white backdrop-blur transition hover:bg-zinc-900/70"
                    aria-label="{{ __('Siguiente', 'flux-press') }}"
                >
                    <flux:icon.chevron-right class="size-5" />
                </button>

                <div class="absolute bottom-4 left-1/2 z-20 -translate-x-1/2 flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                    @foreach($this->slides as $idx => $slide)
                        <button
                            type="button"
                            x-on:click="goTo({{ $idx }})"
                            class="h-2.5 rounded-full transition-all"
                            :class="index === {{ $idx }} ? 'w-10 bg-amber-400' : 'w-2.5 bg-white/50 hover:bg-white/80'"
                            aria-label="{{ sprintf(__('Ir al slide %d', 'flux-press'), $idx + 1) }}"
                        ></button>
                    @endforeach
                </div>

                <div class="absolute inset-x-0 bottom-14 z-20 hidden px-6 lg:block">
                    <div class="mx-auto flex max-w-5xl items-center gap-2 overflow-x-auto rounded-2xl border border-white/20 bg-zinc-950/45 p-2 backdrop-blur-xl">
                        @foreach($this->slides as $idx => $slide)
                            <button
                                type="button"
                                x-on:click="goTo({{ $idx }})"
                                class="group min-w-0 flex-1 rounded-xl px-3 py-2 text-left transition"
                                :class="index === {{ $idx }} ? 'bg-white/15' : 'hover:bg-white/10'"
                            >
                                <p class="truncate text-[11px] uppercase tracking-widest text-zinc-300">{{ sprintf(__('Slide %d', 'flux-press'), $idx + 1) }}</p>
                                <p class="mt-1 truncate text-sm font-semibold text-white/95">{{ $slide['title'] ?? __('Sin titulo', 'flux-press') }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</section>
