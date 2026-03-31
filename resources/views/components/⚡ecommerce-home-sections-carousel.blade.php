<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    /** @var array<int,string> */
    public array $sections = ['categories', 'brands', 'promos'];
    public bool $autoplay = true;
    public int $intervalMs = 6500;
    public bool $showControls = true;
    public string $title = '';
    public string $subtitle = '';

    /**
     * @param array<int,string>|string $sections
     */
    public function mount($sections = 'categories,brands,promos', bool $autoplay = true, int $interval = 6500, bool $showControls = true, ?string $title = null, ?string $subtitle = null): void
    {
        $this->sections = $this->sanitizeSections($sections);
        $this->autoplay = (bool) $autoplay;
        $this->intervalMs = max(2500, min(20000, (int) $interval));
        $this->showControls = (bool) $showControls;
        $this->title = is_string($title) ? trim($title) : '';
        $this->subtitle = is_string($subtitle) ? trim($subtitle) : '';
    }

    /**
     * @param array<int,string>|string $sections
     * @return array<int,string>
     */
    protected function sanitizeSections($sections): array
    {
        $allowed = ['categories', 'brands', 'promos'];
        $raw = is_array($sections) ? $sections : explode(',', (string) $sections);
        $result = [];

        foreach ($raw as $value) {
            $key = sanitize_key((string) $value);
            if ($key === '' || ! in_array($key, $allowed, true) || in_array($key, $result, true)) {
                continue;
            }
            $result[] = $key;
        }

        return ! empty($result) ? $result : $allowed;
    }

    /**
     * @return array<int,array{id:string,label:string}>
     */
    #[Computed]
    public function slides(): array
    {
        $labels = [
            'categories' => __('Categorias destacadas', 'sage'),
            'brands'     => __('Marcas destacadas', 'sage'),
            'promos'     => __('Promociones destacadas', 'sage'),
        ];

        $slides = [];
        foreach ($this->sections as $id) {
            $slides[] = [
                'id' => $id,
                'label' => $labels[$id] ?? ucfirst($id),
            ];
        }

        return $slides;
    }
}; ?>

@php
    $slides = $this->slides;
    $resolvedTitle = $title !== '' ? $title : __('Carrusel de secciones', 'sage');
    $resolvedSubtitle = $subtitle !== '' ? $subtitle : __('Activa, mueve y reagrupa categorias, marcas y promos desde Gutenberg o shortcode.', 'sage');
@endphp

@if(! empty($slides))
    <section
        class="py-10 sm:py-12 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800"
        x-data="{
            active: 0,
            total: {{ count($slides) }},
            autoplay: {{ $autoplay ? 'true' : 'false' }},
            interval: {{ $intervalMs }},
            timer: null,
            init() { if (this.autoplay && this.total > 1) { this.start(); } },
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
            next() { this.active = (this.active + 1) % this.total; },
            prev() { this.active = (this.active - 1 + this.total) % this.total; },
            goTo(index) { this.active = index; }
        }"
        x-on:mouseenter="stop()"
        x-on:mouseleave="if (autoplay) start()"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-5 sm:mb-6 flex items-end justify-between gap-4">
                <div class="min-w-0">
                    <flux:heading size="2xl" class="!font-black tracking-tight text-zinc-900 dark:text-white uppercase">
                        {{ $resolvedTitle }}
                    </flux:heading>
                    <flux:text class="mt-1 text-sm font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">
                        {{ $resolvedSubtitle }}
                    </flux:text>
                </div>

                @if(count($slides) > 1 && $showControls)
                    <div class="hidden sm:flex items-center gap-2">
                        <button
                            type="button"
                            x-on:click="prev()"
                            class="inline-flex size-10 items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-300 hover:border-accent-400 hover:text-accent-600 transition-colors"
                            aria-label="{{ esc_attr__('Slide anterior', 'sage') }}"
                        >
                            <flux:icon.chevron-left class="size-5" />
                        </button>
                        <button
                            type="button"
                            x-on:click="next()"
                            class="inline-flex size-10 items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-300 hover:border-accent-400 hover:text-accent-600 transition-colors"
                            aria-label="{{ esc_attr__('Siguiente slide', 'sage') }}"
                        >
                            <flux:icon.chevron-right class="size-5" />
                        </button>
                    </div>
                @endif
            </div>

            <div class="relative overflow-hidden rounded-[2rem] border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-900/50 shadow-sm">
                @foreach($slides as $index => $slide)
                    <article
                        x-show="active === {{ $index }}"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        x-cloak
                    >
                        <div class="border-b border-zinc-200/80 dark:border-zinc-800 px-4 sm:px-6 py-3">
                            <flux:text class="text-[11px] font-bold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">
                                {{ $slide['label'] }}
                            </flux:text>
                        </div>

                        <div class="min-h-[20rem] sm:min-h-[22rem]">
                            @if($slide['id'] === 'categories')
                                <livewire:ecommerce-home-categories :embedded="true" :key="'home-sections-carousel-categories-'.$index" />
                            @elseif($slide['id'] === 'brands')
                                <livewire:ecommerce-home-brands :embedded="true" :key="'home-sections-carousel-brands-'.$index" />
                            @elseif($slide['id'] === 'promos')
                                <livewire:ecommerce-home-promos :embedded="true" :key="'home-sections-carousel-promos-'.$index" />
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if(count($slides) > 1 && $showControls)
                <div class="mt-4 flex items-center justify-center gap-2">
                    @foreach($slides as $index => $slide)
                        <button
                            type="button"
                            x-on:click="goTo({{ $index }})"
                            class="h-2.5 rounded-full transition-all"
                            :class="active === {{ $index }} ? 'w-8 bg-accent-600' : 'w-2.5 bg-zinc-300 dark:bg-zinc-700'"
                            aria-label="{{ esc_attr(sprintf(__('Ir a %s', 'sage'), $slide['label'])) }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
