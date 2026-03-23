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

        return $service->topRatedProducts(max(1, (int) ($limits['products'] ?? 8)));
    }
}; ?>

<section class="py-12 sm:py-14 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 sm:mb-8">
            <flux:badge color="sky" class="mb-2 uppercase tracking-widest">{{ __('Calidad', 'flux-press') }}</flux:badge>
            <flux:heading size="3xl" class="!font-black tracking-tight">{{ __('Productos mejor valorados', 'flux-press') }}</flux:heading>
        </div>

        @if(empty($this->products))
            <flux:callout color="zinc" icon="star">
                <flux:callout.heading>{{ __('No hay valoraciones disponibles.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Cuando existan ratings en WooCommerce, esta seccion se llenara automaticamente.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach($this->products as $product)
                    <livewire:product-card :product-id="$product['id']" variant="compact" :key="'top-rated-'.$product['id'].'-'.$loop->index" />
                @endforeach
            </div>
        @endif
    </div>
</section>
