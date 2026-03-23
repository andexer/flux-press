<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function brands(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $settings = $service->settings();
        $limits = is_array($settings['limits'] ?? null) ? $settings['limits'] : [];

        return $service->productBrands(max(1, (int) ($limits['brands'] ?? 8)));
    }
}; ?>

<section class="py-12 sm:py-14 bg-zinc-50 dark:bg-zinc-950/80 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 sm:mb-8">
            <flux:badge color="violet" class="mb-2 uppercase tracking-widest">{{ __('Marcas', 'flux-press') }}</flux:badge>
            <flux:heading size="3xl" class="!font-black tracking-tight">{{ __('Marcas destacadas', 'flux-press') }}</flux:heading>
        </div>

        @if(empty($this->brands))
            <flux:callout color="zinc" icon="building-storefront">
                <flux:callout.heading>{{ __('No hay marcas para mostrar.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Si usas taxonomy product_brand, esta seccion se poblara automaticamente.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach($this->brands as $brand)
                    <a href="{{ $brand['url'] }}" wire:navigate class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 flex items-center gap-3 hover:border-accent-400 dark:hover:border-accent-500 transition-colors">
                        <div class="size-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 overflow-hidden shrink-0">
                            @if($brand['image'] !== '')
                                <img src="{{ $brand['image'] }}" alt="{{ $brand['name'] }}" class="h-full w-full object-cover" loading="lazy" />
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <flux:icon.tag class="size-4 text-zinc-400" />
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="line-clamp-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $brand['name'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ sprintf(_n('%d producto', '%d productos', $brand['count'], 'flux-press'), $brand['count']) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
