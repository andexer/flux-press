<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    /** @var array<int,array<string,mixed>> */
    public array $manualCards = [];
    public string $sectionTitle = '';
    public string $sectionSubtitle = '';
    public int $limitOverride = 0;
    public bool $embedded = false;

    /**
     * @param array<int,array<string,mixed>> $manualCards
     */
    public function mount(array $manualCards = [], ?string $sectionTitle = null, ?string $sectionSubtitle = null, int $limitOverride = 0, bool $embedded = false): void
    {
        $this->manualCards = array_values(array_filter($manualCards, fn ($item) => is_array($item)));
        $this->sectionTitle = is_string($sectionTitle) ? trim($sectionTitle) : '';
        $this->sectionSubtitle = is_string($sectionSubtitle) ? trim($sectionSubtitle) : '';
        $this->limitOverride = max(0, (int) $limitOverride);
        $this->embedded = (bool) $embedded;
    }

    #[Computed]
    public function brands(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $settings = $service->settings();
        $limits = is_array($settings['limits'] ?? null) ? $settings['limits'] : [];

        $limit = $this->limitOverride > 0
            ? min(24, $this->limitOverride)
            : max(1, (int) ($limits['brands'] ?? 8));

        return $service->featuredBrands(
            $limit,
            $this->manualCards
        );
    }
}; ?>

@php
    $myAccountUrl = function_exists('wc_get_page_permalink')
        ? (string) wc_get_page_permalink('myaccount')
        : (string) home_url('/');
    $resolvedTitle = $sectionTitle !== '' ? $sectionTitle : __('Tus marcas favoritas', 'flux-press');
    $resolvedSubtitle = $sectionSubtitle !== ''
        ? $sectionSubtitle
        : __('Inicia sesion para obtener beneficios exclusivos', 'flux-press');
    $sectionClasses = $embedded
        ? 'py-4 sm:py-5 bg-transparent border-0'
        : 'py-10 sm:py-12 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800';
    $containerClasses = $embedded
        ? 'px-4 sm:px-5 lg:px-6'
        : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8';
@endphp

<section class="{{ $sectionClasses }}">
    <div class="{{ $containerClasses }}">
        <div class="mb-5 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <flux:heading size="2xl" class="!font-black tracking-tight text-zinc-900 dark:text-white uppercase">
                    {{ $resolvedTitle }}
                </flux:heading>
                <flux:text class="mt-1 text-sm font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">
                    {{ $resolvedSubtitle }}
                </flux:text>
            </div>

            <flux:button href="{{ esc_url($myAccountUrl) }}" wire:navigate icon="arrow-right" class="shrink-0 max-sm:w-full">
                {{ __('Ver tiendas oficiales', 'flux-press') }}
            </flux:button>
        </div>

        @if(empty($this->brands))
            <flux:callout color="zinc" icon="building-storefront">
                <flux:callout.heading>{{ __('No hay marcas para mostrar.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Agrega marcas en WooCommerce o crea tarjetas manuales desde Gutenberg.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        @else
            <div
                x-data="{
                    scrollBrands(direction) {
                        const track = this.$refs.track;
                        if (!track) {
                            return;
                        }

                        const item = track.querySelector('[data-brand-item]');
                        const cardWidth = item ? item.getBoundingClientRect().width : 240;
                        track.scrollBy({ left: direction * (cardWidth + 12), behavior: 'smooth' });
                    }
                }"
                class="relative"
            >
                <button
                    type="button"
                    x-on:click="scrollBrands(-1)"
                    aria-label="{{ esc_attr__('Desplazar marcas a la izquierda', 'flux-press') }}"
                    class="hidden md:inline-flex absolute left-0 top-1/2 -translate-y-1/2 z-10 size-10 items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-700 bg-white/90 dark:bg-zinc-900/90 text-zinc-600 dark:text-zinc-300 hover:border-accent-400 dark:hover:border-accent-500 hover:text-accent-600 dark:hover:text-accent-400 transition-colors backdrop-blur"
                >
                    <flux:icon.chevron-left class="size-5" />
                </button>

                <div
                    x-ref="track"
                    class="flex gap-3 sm:gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory md:mx-12 px-0.5 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    @foreach($this->brands as $brand)
                        @php
                            $brandName = (string) ($brand['name'] ?? '');
                            $brandImage = (string) ($brand['image'] ?? '');
                            $brandLogo = (string) ($brand['logo'] ?? '');
                            $brandCount = (int) ($brand['count'] ?? 0);
                            $brandBadge = trim((string) ($brand['badge'] ?? __('Marca afiliada', 'flux-press')));
                            $brandInitials = strtoupper(function_exists('mb_substr') ? (string) mb_substr($brandName, 0, 2) : substr($brandName, 0, 2));
                        @endphp

                        <a
                            href="{{ $brand['url'] }}"
                            wire:navigate
                            data-brand-item
                            class="group relative block shrink-0 min-w-[150px] sm:min-w-[185px] lg:min-w-[205px] aspect-[1.45/1] overflow-hidden rounded-3xl border border-zinc-200/70 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900 snap-start shadow-sm hover:shadow-lg transition-shadow"
                        >
                            @if($brandImage !== '')
                                <img src="{{ $brandImage }}" alt="{{ $brandName }}" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
                            @else
                                <div class="absolute inset-0 bg-linear-to-br from-zinc-800 via-zinc-700 to-zinc-900 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-700"></div>
                                <span class="absolute top-3 right-3 text-2xl font-black text-white/35 tracking-widest">{{ $brandInitials }}</span>
                            @endif

                            <div class="absolute inset-0 bg-linear-to-r from-black/65 via-black/30 to-black/50"></div>

                            <div class="absolute left-3 top-3 inline-flex max-w-[85%] items-center rounded-full bg-white/90 text-zinc-800 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider">
                                {{ $brandBadge }}
                            </div>

                            <div class="absolute inset-0 flex items-center justify-center p-6">
                                @if($brandLogo !== '')
                                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="max-h-full max-w-full object-contain drop-shadow-[0_6px_12px_rgba(0,0,0,0.45)]" loading="lazy" />
                                @else
                                    <span class="text-white text-lg font-black uppercase tracking-[0.2em] drop-shadow-md">{{ $brandName }}</span>
                                @endif
                            </div>

                            <div class="absolute inset-x-3 bottom-3">
                                <div class="inline-flex w-full items-center justify-between rounded-xl bg-black/35 px-3 py-1.5 backdrop-blur-sm">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-white line-clamp-1">
                                        {{ $brandName }}
                                    </p>
                                    @if($brandCount > 0)
                                        <span class="text-[10px] font-semibold text-white/80">
                                            {{ $brandCount }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <button
                    type="button"
                    x-on:click="scrollBrands(1)"
                    aria-label="{{ esc_attr__('Desplazar marcas a la derecha', 'flux-press') }}"
                    class="hidden md:inline-flex absolute right-0 top-1/2 -translate-y-1/2 z-10 size-10 items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-700 bg-white/90 dark:bg-zinc-900/90 text-zinc-600 dark:text-zinc-300 hover:border-accent-400 dark:hover:border-accent-500 hover:text-accent-600 dark:hover:text-accent-400 transition-colors backdrop-blur"
                >
                    <flux:icon.chevron-right class="size-5" />
                </button>
            </div>
        @endif
    </div>
</section>
