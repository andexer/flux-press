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

@php
    $myAccountUrl = function_exists('wc_get_page_permalink')
        ? (string) wc_get_page_permalink('myaccount')
        : (string) home_url('/');
@endphp

<section class="py-10 sm:py-12 bg-white dark:bg-zinc-950 border-y border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-5 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs sm:text-sm font-semibold text-zinc-700 dark:text-zinc-200 underline decoration-zinc-300 dark:decoration-zinc-700 underline-offset-4">
                    {{ __('Inicia sesion para obtener recompensas y beneficios.', 'flux-press') }}
                </p>
                <flux:heading size="xl" class="mt-2 !font-black tracking-tight">{{ __('Marcas destacadas y afiliadas', 'flux-press') }}</flux:heading>
            </div>

            <flux:button href="{{ esc_url($myAccountUrl) }}" wire:navigate icon="arrow-right" class="shrink-0">
                {{ __('Ir a mi cuenta', 'flux-press') }}
            </flux:button>
        </div>

        @if(empty($this->brands))
            <flux:callout color="zinc" icon="building-storefront">
                <flux:callout.heading>{{ __('No hay marcas para mostrar.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Agrega terminos de marcas en taxonomias compatibles para poblar esta seccion automaticamente.', 'flux-press') }}</flux:callout.text>
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
                    class="flex gap-3 sm:gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory md:mx-12 px-0.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    @foreach($this->brands as $brand)
                        @php
                            $brandName = (string) ($brand['name'] ?? '');
                            $brandImage = (string) ($brand['image'] ?? '');
                            $brandCount = (int) ($brand['count'] ?? 0);
                            $brandInitials = strtoupper(function_exists('mb_substr') ? (string) mb_substr($brandName, 0, 2) : substr($brandName, 0, 2));
                        @endphp

                        <a
                            href="{{ $brand['url'] }}"
                            wire:navigate
                            data-brand-item
                            class="group relative block shrink-0 min-w-[200px] sm:min-w-[230px] lg:min-w-[250px] aspect-[2.4/1] overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900 snap-start"
                        >
                            @if($brandImage !== '')
                                <img src="{{ $brandImage }}" alt="{{ $brandName }}" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
                            @else
                                <div class="absolute inset-0 bg-linear-to-br from-zinc-800 via-zinc-700 to-zinc-900 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-700"></div>
                                <span class="absolute top-3 right-3 text-2xl font-black text-white/35 tracking-widest">{{ $brandInitials }}</span>
                            @endif

                            <div class="absolute inset-0 bg-linear-to-r from-black/65 via-black/30 to-black/50"></div>

                            <div class="absolute left-3 top-3 inline-flex items-center rounded-full bg-white/90 text-zinc-800 px-2.5 py-1 text-[11px] font-semibold">
                                {{ sprintf(_n('%d producto', '%d productos', $brandCount, 'flux-press'), $brandCount) }}
                            </div>

                            <div class="absolute inset-x-3 bottom-3">
                                <p class="text-white font-black uppercase tracking-wider leading-tight line-clamp-1">
                                    {{ $brandName }}
                                </p>
                                <p class="text-[11px] text-white/80 uppercase tracking-widest mt-1">
                                    {{ __('Marca afiliada', 'flux-press') }}
                                </p>
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
