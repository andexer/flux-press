<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function promoData(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $settings = $service->settings();
        $limits = is_array($settings['limits'] ?? null) ? $settings['limits'] : [];

        return $service->promoData(
            max(1, (int) ($limits['products'] ?? 6)),
            max(1, (int) ($limits['categories'] ?? 8))
        );
    }
}; ?>

<section class="py-12 sm:py-14 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 sm:mb-8">
            <flux:badge color="rose" class="mb-2 uppercase tracking-widest">{{ __('Promociones', 'flux-press') }}</flux:badge>
            <flux:heading size="3xl" class="!font-black tracking-tight">{{ __('Ofertas activas', 'flux-press') }}</flux:heading>
        </div>

        @if(empty($this->promoData['products']) && empty($this->promoData['categories']))
            <flux:callout color="zinc" icon="tag">
                <flux:callout.heading>{{ __('No hay ofertas activas en este momento.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Cuando existan productos en oferta, esta seccion se actualizara automaticamente.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        @else
            <div class="grid lg:grid-cols-12 gap-6">
                <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($this->promoData['products'] as $product)
                        <livewire:product-card :product-id="$product['id']" variant="compact" :key="'promo-'.$product['id'].'-'.$loop->index" />
                    @endforeach
                </div>

                <aside class="lg:col-span-4 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/60 p-4 sm:p-5">
                    <h3 class="text-sm font-black uppercase tracking-widest text-zinc-600 dark:text-zinc-300 mb-3">{{ __('Categorias en oferta', 'flux-press') }}</h3>
                    @if(empty($this->promoData['categories']))
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sin categorias promocionadas por ahora.', 'flux-press') }}</p>
                    @else
                        <div class="space-y-2">
                            @foreach($this->promoData['categories'] as $category)
                                <a href="{{ $category['url'] }}" wire:navigate class="flex items-center justify-between rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 hover:border-accent-400 dark:hover:border-accent-500 transition-colors">
                                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $category['name'] }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $category['count'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </aside>
            </div>
        @endif
    </div>
</section>
