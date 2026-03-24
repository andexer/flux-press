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

    /**
     * @param array<int,array<string,mixed>> $manualCards
     */
    public function mount(array $manualCards = [], ?string $sectionTitle = null, ?string $sectionSubtitle = null, int $limitOverride = 0): void
    {
        $this->manualCards = array_values(array_filter($manualCards, fn ($item) => is_array($item)));
        $this->sectionTitle = is_string($sectionTitle) ? trim($sectionTitle) : '';
        $this->sectionSubtitle = is_string($sectionSubtitle) ? trim($sectionSubtitle) : '';
        $this->limitOverride = max(0, (int) $limitOverride);
    }

    #[Computed]
    public function categories(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $settings = $service->settings();
        $limits = is_array($settings['limits'] ?? null) ? $settings['limits'] : [];

        $limit = $this->limitOverride > 0
            ? min(24, $this->limitOverride)
            : max(1, (int) ($limits['categories'] ?? 8));

        return $service->featuredCategories(
            $limit,
            $this->manualCards
        );
    }

    #[Computed]
    public function canManageCatalog(): bool
    {
        return is_user_logged_in() && (
            current_user_can('manage_product_terms')
            || current_user_can('edit_products')
            || current_user_can('manage_woocommerce')
        );
    }
}; ?>

@php
    $resolvedTitle = $sectionTitle !== '' ? $sectionTitle : __('Categorias destacadas', 'flux-press');
    $resolvedSubtitle = $sectionSubtitle !== ''
        ? $sectionSubtitle
        : __('Explora las mejores tendencias del momento', 'flux-press');
@endphp

<section class="py-10 sm:py-12 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
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
            @if(function_exists('wc_get_page_permalink'))
                <flux:button href="{{ wc_get_page_permalink('shop') }}" wire:navigate variant="ghost" icon="arrow-right" class="max-sm:hidden shrink-0">
                    {{ __('Ver todo el catalogo', 'flux-press') }}
                </flux:button>
            @endif
        </div>

        @if(empty($this->categories))
            <flux:callout color="zinc" icon="squares-2x2">
                <flux:callout.heading>{{ __('No hay categorias de producto disponibles.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Crea categorias en WooCommerce o agrega tarjetas manuales desde Gutenberg.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        @else
            <div
                x-data="{
                    scrollCategories(direction) {
                        const track = this.$refs.track;
                        if (!track) {
                            return;
                        }

                        const item = track.querySelector('[data-category-item]');
                        const cardWidth = item ? item.getBoundingClientRect().width : 200;
                        track.scrollBy({ left: direction * (cardWidth + 12), behavior: 'smooth' });
                    }
                }"
                class="relative"
            >
                <button
                    type="button"
                    x-on:click="scrollCategories(-1)"
                    aria-label="{{ esc_attr__('Desplazar categorias a la izquierda', 'flux-press') }}"
                    class="hidden md:inline-flex absolute left-0 top-1/2 -translate-y-1/2 z-10 size-10 items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-700 bg-white/90 dark:bg-zinc-900/90 text-zinc-600 dark:text-zinc-300 hover:border-accent-400 dark:hover:border-accent-500 hover:text-accent-600 dark:hover:text-accent-400 transition-colors backdrop-blur"
                >
                    <flux:icon.chevron-left class="size-5" />
                </button>

                <div
                    x-ref="track"
                    class="flex gap-3 sm:gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory md:mx-12 px-0.5 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    @foreach($this->categories as $category)
                        @php
                            $categoryName = (string) ($category['name'] ?? '');
                            $categoryImage = (string) ($category['image'] ?? '');
                            $categoryBadge = trim((string) ($category['badge'] ?? ''));
                            if ($categoryBadge === '' && (int) ($category['count'] ?? 0) > 0) {
                                $categoryBadge = sprintf(
                                    _n('%d producto', '%d productos', (int) $category['count'], 'flux-press'),
                                    (int) $category['count']
                                );
                            }
                        @endphp

                        <article
                            data-category-item
                            class="group relative shrink-0 min-w-[165px] sm:min-w-[190px] lg:min-w-[210px] aspect-[4/4.7] overflow-hidden rounded-3xl border border-zinc-200/70 dark:border-zinc-700/80 bg-zinc-100 dark:bg-zinc-900 snap-start"
                        >
                            <a href="{{ $category['url'] }}" wire:navigate class="absolute inset-0">
                                @if($categoryImage !== '')
                                    <img src="{{ $categoryImage }}" alt="{{ $categoryName }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy" />
                                @else
                                    <div class="absolute inset-0 bg-linear-to-br from-zinc-700 via-zinc-800 to-zinc-900"></div>
                                @endif
                            </a>

                            <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/25 to-black/20 group-hover:from-black/65 transition-colors"></div>

                            @if($categoryBadge !== '')
                                <span class="absolute left-3 top-3 inline-flex max-w-[85%] items-center rounded-full bg-white/88 dark:bg-zinc-900/80 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-200 backdrop-blur">
                                    {{ $categoryBadge }}
                                </span>
                            @endif

                            <div class="absolute inset-x-3 bottom-3">
                                <a
                                    href="{{ $category['url'] }}"
                                    wire:navigate
                                    class="inline-flex w-full items-center justify-center rounded-2xl border border-white/30 bg-white/12 px-3 py-2 text-xs sm:text-[13px] font-black uppercase tracking-[0.18em] text-white backdrop-blur-sm transition-colors group-hover:bg-white/20"
                                >
                                    {{ $categoryName }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <button
                    type="button"
                    x-on:click="scrollCategories(1)"
                    aria-label="{{ esc_attr__('Desplazar categorias a la derecha', 'flux-press') }}"
                    class="hidden md:inline-flex absolute right-0 top-1/2 -translate-y-1/2 z-10 size-10 items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-700 bg-white/90 dark:bg-zinc-900/90 text-zinc-600 dark:text-zinc-300 hover:border-accent-400 dark:hover:border-accent-500 hover:text-accent-600 dark:hover:text-accent-400 transition-colors backdrop-blur"
                >
                    <flux:icon.chevron-right class="size-5" />
                </button>
            </div>

            @if($this->canManageCatalog)
                <p class="mt-4 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Tip: puedes editar imagenes de categorias en Productos > Categorias para mejorar esta seccion.', 'flux-press') }}
                </p>
            @endif
        @endif
    </div>
</section>
