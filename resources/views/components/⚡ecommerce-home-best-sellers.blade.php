<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function products(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $settings = $service->settings();
        $limits = is_array($settings['limits'] ?? null) ? $settings['limits'] : [];

        return $service->bestSellingProducts(max(1, (int) ($limits['products'] ?? 8)));
    }
}; ?>

<section class="py-12 sm:py-14 bg-zinc-50 dark:bg-zinc-950/80 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 sm:mb-8 flex items-end justify-between gap-4">
            <div>
                <flux:badge color="amber" class="mb-2 uppercase tracking-widest">{{ __('Solo para ti', 'flux-press') }}</flux:badge>
                <flux:heading size="3xl" class="!font-black tracking-tight">{{ __('Productos mas vendidos', 'flux-press') }}</flux:heading>
            </div>
        </div>

        @if(empty($this->products))
            <flux:callout color="zinc" icon="shopping-cart">
                <flux:callout.heading>{{ __('Aun no hay productos para mostrar.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('El sistema usara automaticamente ventas, popularidad y luego productos recientes como fallback.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        @else
            <div x-data="{ scrollLeft() { this.$refs.track.scrollBy({ left: -320, behavior: 'smooth' }) }, scrollRight() { this.$refs.track.scrollBy({ left: 320, behavior: 'smooth' }) } }">
                <div class="mb-4 flex items-center justify-end gap-2">
                    <button type="button" x-on:click="scrollLeft()" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-200 hover:border-accent-500 transition-colors" aria-label="{{ __('Anterior', 'flux-press') }}">
                        <flux:icon.chevron-left class="size-4" />
                    </button>
                    <button type="button" x-on:click="scrollRight()" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-200 hover:border-accent-500 transition-colors" aria-label="{{ __('Siguiente', 'flux-press') }}">
                        <flux:icon.chevron-right class="size-4" />
                    </button>
                </div>

                <div x-ref="track" class="flex gap-4 sm:gap-5 overflow-x-auto snap-x snap-mandatory pb-2">
                    @foreach($this->products as $product)
                        <div class="snap-start min-w-[200px] sm:min-w-[220px] max-w-[240px]">
                            <livewire:product-card :product-id="$product['id']" variant="compact" :key="'best-seller-'.$product['id'].'-'.$loop->index" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
