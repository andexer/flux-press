<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function categories(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $settings = $service->settings();
        $limits = is_array($settings['limits'] ?? null) ? $settings['limits'] : [];

        return $service->productCategories(max(1, (int) ($limits['categories'] ?? 8)));
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

<section class="py-12 sm:py-14 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 sm:mb-8 flex items-end justify-between gap-4">
            <div>
                <flux:badge color="emerald" class="mb-2 uppercase tracking-widest">{{ __('Categorias', 'flux-press') }}</flux:badge>
                <flux:heading size="3xl" class="!font-black tracking-tight">{{ __('Explora por categoria', 'flux-press') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('Tarjetas mas compactas para navegar rapido entre colecciones y subcategorias.', 'flux-press') }}</flux:text>
            </div>
            @if(function_exists('wc_get_page_permalink'))
                <flux:button href="{{ wc_get_page_permalink('shop') }}" wire:navigate variant="ghost" icon="arrow-right" class="max-sm:hidden">
                    {{ __('Ver tienda', 'flux-press') }}
                </flux:button>
            @endif
        </div>

        @if(empty($this->categories))
                <flux:callout color="zinc" icon="squares-2x2">
                    <flux:callout.heading>{{ __('No hay categorias de producto disponibles.', 'flux-press') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('Crea categorias en WooCommerce para activar esta seccion.', 'flux-press') }}</flux:callout.text>
                </flux:callout>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 sm:gap-4">
                @foreach($this->categories as $category)
                    <article class="group rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/60 p-2.5 sm:p-3 hover:border-accent-400 dark:hover:border-accent-500 transition-all">
                        <a href="{{ $category['url'] }}" wire:navigate class="block aspect-[4/3] rounded-xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                            @if($category['image'] !== '')
                                <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <flux:icon.photo class="size-8 text-zinc-400" />
                                </div>
                            @endif
                        </a>
                        <div class="pt-2.5 px-0.5">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ $category['url'] }}" wire:navigate class="block text-xs sm:text-sm font-bold text-zinc-900 dark:text-zinc-100 line-clamp-2 hover:text-accent-700 dark:hover:text-accent-400 transition-colors">
                                    {{ $category['name'] }}
                                </a>
                                <span class="shrink-0 rounded-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 px-1.5 py-0.5 text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">
                                    {{ $category['count'] }}
                                </span>
                            </div>

                            @if(! empty($category['children']) && is_array($category['children']))
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach(array_slice($category['children'], 0, 2) as $child)
                                        <a
                                            href="{{ $child['url'] }}"
                                            wire:navigate
                                            class="inline-flex items-center rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 py-0.5 text-[10px] font-medium text-zinc-600 dark:text-zinc-300 hover:border-accent-500 hover:text-accent-700 dark:hover:text-accent-400 transition-colors"
                                        >
                                            {{ $child['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if($this->canManageCatalog && ($category['has_image'] ?? false) !== true && ($category['edit_url'] ?? '') !== '')
                                <a href="{{ $category['edit_url'] }}" class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 dark:text-amber-400 hover:underline">
                                    <flux:icon.photo class="size-3.5" />
                                    {{ __('Agregar imagen', 'flux-press') }}
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
