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
    public function promos(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $limit = $this->limitOverride > 0 ? min(6, $this->limitOverride) : 2;

        return $service->featuredPromos($limit, $this->manualCards);
    }
}; ?>

@php
    $resolvedTitle = $sectionTitle !== '' ? $sectionTitle : __('Promociones destacadas', 'flux-press');
    $resolvedSubtitle = $sectionSubtitle !== ''
        ? $sectionSubtitle
        : __('Ofertas y lanzamientos en una vista mas visual', 'flux-press');
@endphp

<section class="py-10 sm:py-12 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-5 sm:mb-6">
            <flux:heading size="2xl" class="!font-black tracking-tight text-zinc-900 dark:text-white uppercase">
                {{ $resolvedTitle }}
            </flux:heading>
            <flux:text class="mt-1 text-sm font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 uppercase">
                {{ $resolvedSubtitle }}
            </flux:text>
        </div>

        @if(empty($this->promos))
            <flux:callout color="zinc" icon="tag">
                <flux:callout.heading>{{ __('No hay promociones activas en este momento.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Agrega tarjetas manuales desde Gutenberg o configura ofertas en WooCommerce.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        @else
            <div class="grid gap-4 lg:gap-6 md:grid-cols-2">
                @foreach($this->promos as $promo)
                    @php
                        $theme = (string) ($promo['theme'] ?? 'dark');
                        $isLight = $theme === 'light';
                        $ctaLabel = (string) ($promo['cta_label'] ?? __('Ver mas', 'flux-press'));
                        $ctaUrl = (string) ($promo['cta_url'] ?? '#');
                    @endphp
                    <article class="group relative overflow-hidden rounded-[2rem] border border-zinc-200/70 dark:border-zinc-700 min-h-[320px] sm:min-h-[360px]">
                        @if(($promo['image_url'] ?? '') !== '')
                            <img src="{{ $promo['image_url'] }}" alt="{{ $promo['title'] ?? '' }}" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy" />
                        @else
                            <div class="absolute inset-0 bg-linear-to-br from-zinc-700 to-zinc-900"></div>
                        @endif

                        <div class="absolute inset-0 {{ $isLight ? 'bg-linear-to-br from-zinc-100/45 via-zinc-300/35 to-zinc-900/60' : 'bg-linear-to-br from-zinc-950/70 via-zinc-900/50 to-zinc-950/80' }}"></div>

                        <div class="relative h-full p-5 sm:p-7 lg:p-8 flex items-end">
                            <div class="w-full max-w-xl rounded-[1.6rem] border border-white/30 bg-white/12 px-5 py-5 sm:px-7 sm:py-6 backdrop-blur-md shadow-xl">
                                @if(($promo['eyebrow'] ?? '') !== '')
                                    <span class="inline-flex rounded-full border border-white/30 bg-black/20 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-white mb-3">
                                        {{ $promo['eyebrow'] }}
                                    </span>
                                @endif

                                <h3 class="text-white text-3xl sm:text-4xl font-black uppercase tracking-tight leading-[0.95]">
                                    {{ $promo['title'] ?? '' }}
                                </h3>

                                @if(($promo['description'] ?? '') !== '')
                                    <p class="mt-3 text-sm sm:text-base text-white/85 max-w-md">
                                        {{ $promo['description'] }}
                                    </p>
                                @endif

                                <div class="mt-5">
                                    <a
                                        href="{{ $ctaUrl }}"
                                        wire:navigate
                                        class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-black uppercase tracking-[0.12em] transition-colors {{ $isLight ? 'bg-accent-600 hover:bg-accent-700 text-white' : 'bg-zinc-900/70 hover:bg-zinc-900 text-white border border-white/30' }}"
                                    >
                                        {{ $ctaLabel }}
                                        <flux:icon.arrow-right class="size-4" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
