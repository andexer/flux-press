<?php

use Illuminate\Support\Facades\Log;
use Livewire\Component;

new class extends Component
{
    public int $productId;

    public string $variant = 'shop';

    public function mount(int $productId, string $variant = 'shop'): void
    {
        $this->productId = $productId;
        $this->variant = in_array($variant, ['shop', 'compact'], true) ? $variant : 'shop';
    }

    public function getProductProperty()
    {
        if (! function_exists('wc_get_product')) {
            return null;
        }

        return wc_get_product($this->productId);
    }

    public function addToCart(): void
    {
        if (! function_exists('WC') || ! WC()->cart) {
            \Flux::toast(__('El carrito no esta disponible.', 'flux-press'), variant: 'danger');

            return;
        }

        try {
            $product = $this->product;

            if (! $product) {
                \Flux::toast(__('No pudimos cargar este producto.', 'flux-press'), variant: 'danger');

                return;
            }

            if (! $product->is_purchasable()) {
                \Flux::toast(__('Este producto no se puede comprar en este momento.', 'flux-press'), variant: 'danger');

                return;
            }

            if (! $product->is_in_stock()) {
                \Flux::toast(__('Este producto esta agotado.', 'flux-press'), variant: 'danger');

                return;
            }

            if ($product->is_type('variable') || $product->is_type('grouped') || $product->is_type('external')) {
                $this->redirect($product->get_permalink(), navigate: true);

                return;
            }

            $added = WC()->cart->add_to_cart($product->get_id(), 1);

            if (! $added) {
                \Flux::toast(__('No se pudo agregar al carrito.', 'flux-press'), variant: 'danger');

                return;
            }

            WC()->cart->calculate_totals();

            $this->dispatch('cart-updated');
            $this->dispatch('wc-cart-fragments-refresh');

            \Flux::toast(sprintf(__('%s fue agregado a tu carrito.', 'flux-press'), $product->get_name()), variant: 'success');
        } catch (\Throwable $e) {
            \Flux::toast(__('No se pudo agregar el producto al carrito.', 'flux-press'), variant: 'danger');
            Log::error('WooCommerce add_to_cart error', [
                'product_id' => $this->productId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}; ?>

@if ($this->product)
    @php
        $isCompact = $variant === 'compact';
        $isShop = $variant === 'shop';
        $regularPrice = (float) $this->product->get_regular_price();
        $salePrice = (float) $this->product->get_sale_price();
        $discount = ($regularPrice > 0 && $salePrice > 0 && $salePrice < $regularPrice)
            ? max(1, (int) round((($regularPrice - $salePrice) / $regularPrice) * 100))
            : 0;
    @endphp

    <article @class([
        'group relative h-full overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 transition-all duration-300',
        'hover:-translate-y-0.5 hover:shadow-lg' => ! $isCompact,
        'p-2' => $isShop,
        'p-2.5' => $isCompact,
    ])>
        <div class="relative">
            <a href="{{ $this->product->get_permalink() }}" wire:navigate @class([
                'block overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800',
                'aspect-[5/6]' => $isShop,
                'aspect-[4/5]' => $isCompact,
            ])>
                @if ($this->product->get_image_id())
                    <img
                        src="{{ wp_get_attachment_image_url($this->product->get_image_id(), $isCompact ? 'woocommerce_thumbnail' : 'large') }}"
                        alt="{{ esc_attr($this->product->get_name()) }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                @else
                    <img
                        src="{{ wc_placeholder_img_src('woocommerce_thumbnail') }}"
                        alt="{{ __('Placeholder', 'flux-press') }}"
                        class="h-full w-full object-cover opacity-50"
                    />
                @endif
            </a>

            @if($this->product->is_on_sale() && $discount > 0)
                <span class="absolute left-2 top-2 inline-flex items-center rounded-full bg-red-500 px-2 py-0.5 text-[11px] font-bold text-white shadow-sm">
                    -{{ $discount }}%
                </span>
            @endif
        </div>

        <div @class([
            'flex flex-1 flex-col',
            'pt-2.5 px-0.5 pb-0.5' => $isShop,
            'pt-3 px-0.5 pb-0.5' => $isCompact,
            'p-3 sm:p-4' => ! $isCompact && ! $isShop,
        ])>
            <h3>
                <a
                    href="{{ $this->product->get_permalink() }}"
                    wire:navigate
                    @class([
                        'line-clamp-2 font-semibold text-zinc-900 dark:text-zinc-100 transition-colors hover:text-accent-600 dark:hover:text-accent-400',
                        'text-[13px] leading-snug' => $isShop || $isCompact,
                        'text-sm leading-tight' => ! $isCompact && ! $isShop,
                    ])
                >
                    {{ $this->product->get_name() }}
                </a>
            </h3>

            <div class="mt-2 flex items-end justify-between gap-2">
                <div class="min-w-0">
                    @if($this->product->is_on_sale() && $this->product->get_regular_price() !== '')
                        <p class="text-[11px] text-zinc-400 line-through leading-none">
                            {!! wc_price($this->product->get_regular_price()) !!}
                        </p>
                    @endif
                    <p @class([
                        'font-black text-accent-700 dark:text-accent-400 leading-none',
                        'text-sm' => $isCompact || $isShop,
                        'text-base' => ! $isCompact && ! $isShop,
                    ])>
                        {!! $this->product->get_price_html() !!}
                    </p>
                </div>

                @if(! $isCompact && ! $isShop && (float) $this->product->get_average_rating() > 0)
                    <span class="inline-flex items-center gap-1 rounded-full border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-2 py-1 text-[11px] font-semibold text-zinc-600 dark:text-zinc-300">
                        <flux:icon.star class="size-3 text-amber-500" />
                        {{ number_format((float) $this->product->get_average_rating(), 1) }}
                    </span>
                @endif
            </div>

            <div class="mt-3">
                @if ($this->product->is_in_stock())
                    @if($this->product->is_type('variable') || $this->product->is_type('grouped') || $this->product->is_type('external'))
                        <flux:button
                            variant="outline"
                            href="{{ $this->product->get_permalink() }}"
                            wire:navigate
                            @class([
                                'w-full justify-center',
                                'h-8 text-[11px]' => $isShop,
                                'h-9 text-xs' => $isCompact,
                            ])
                        >
                            {{ __('Ver producto', 'flux-press') }}
                        </flux:button>
                    @else
                        <flux:button
                            wire:click="addToCart"
                            variant="primary"
                            icon="shopping-bag"
                            wire:loading.attr="disabled"
                            @class([
                                'w-full justify-center',
                                'h-8 text-[11px]' => $isShop,
                                'h-9 text-xs' => $isCompact,
                            ])
                        >
                            <span wire:loading.remove wire:target="addToCart">
                                {{ $isCompact ? __('Agregar', 'flux-press') : __('Agregar al carrito', 'flux-press') }}
                            </span>
                            <span wire:loading wire:target="addToCart">{{ __('Agregando...', 'flux-press') }}</span>
                        </flux:button>
                    @endif
                @else
                    <flux:button disabled variant="ghost" class="w-full justify-center h-8 text-[11px] bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 cursor-not-allowed border border-zinc-200 dark:border-zinc-700">
                        {{ __('Agotado', 'flux-press') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </article>
@endif
