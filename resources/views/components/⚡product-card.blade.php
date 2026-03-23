<?php

use Livewire\Component;
use Illuminate\Support\Facades\Log;

new class extends Component
{
    public int $productId;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    public function getProductProperty()
    {
        if (!function_exists('wc_get_product')) {
            return null;
        }
        return wc_get_product($this->productId);
    }

    public function addToCart(): void
    {
        if (!function_exists('WC') || !WC()->cart) {
            return;
        }

        try {
            $product = $this->product;
            
            if (!$product || !$product->is_in_stock()) {
                \Flux::toast(__('This product is currently out of stock.', 'flux-press'), variant: 'danger');
                return;
            }

            // Redirect to product page if it's a variable product (requires option selection)
            if ($product->is_type('variable')) {
                $this->redirect($product->get_permalink());
                return;
            }

            WC()->cart->add_to_cart($this->productId, 1);
            
            // Tell other components (like cart-icon) to refresh
            $this->dispatch('cart-updated');
            
            \Flux::toast(sprintf(__('%s has been added to your cart.', 'flux-press'), $product->get_name()), variant: 'success');
        } catch (\Exception $e) {
            \Flux::toast(__('Unable to add product to cart.', 'flux-press'), variant: 'danger');
            Log::error('WooCommerce Add to Cart error: ' . $e->getMessage());
        }
    }
}; ?>

<flux:card as="li" class="h-full flex flex-col p-0 overflow-hidden transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group border border-zinc-200 dark:border-zinc-800 dark:bg-zinc-900 rounded-2xl">
    @if ($this->product)
        {{-- Product Image with Links --}}
        <a href="{{ $this->product->get_permalink() }}" wire:navigate class="block relative aspect-square bg-zinc-50 dark:bg-zinc-800 overflow-hidden">
            @if ($this->product->is_on_sale())
                <span class="absolute top-3 left-3 z-10 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                    {{ __('Sale!', 'flux-press') }}
                </span>
            @endif
            
            {{-- Image container to handle object-cover safely --}}
            <div class="w-full h-full relative group-hover:scale-105 transition-transform duration-500">
                @if ($this->product->get_image_id())
                    <img 
                        src="{{ wp_get_attachment_image_url($this->product->get_image_id(), 'woocommerce_thumbnail') }}" 
                        alt="{{ esc_attr($this->product->get_name()) }}"
                        loading="lazy"
                        class="w-full h-full object-cover"
                    />
                @else
                    <img
                        src="{{ wc_placeholder_img_src('woocommerce_thumbnail') }}"
                        alt="{{ __('Placeholder', 'flux-press') }}"
                        class="w-full h-full object-cover opacity-50"
                    />
                @endif
            </div>
            
            {{-- Hover overlay overlay optional depending on design preference --}}
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
        </a>

        {{-- Product Details --}}
        <div class="p-5 flex flex-col flex-1">
            <h3 class="mb-2">
                <a href="{{ $this->product->get_permalink() }}" wire:navigate class="text-base font-semibold text-zinc-900 dark:text-zinc-100 hover:text-accent-600 dark:hover:text-accent-400 transition-colors line-clamp-2 leading-tight">
                    {{ $this->product->get_name() }}
                </a>
            </h3>

            {{-- Price Display --}}
            <div class="text-xl font-bold text-accent-600 dark:text-accent-400 mb-6 mt-auto flex items-center gap-2">
                @if ($this->product->is_on_sale())
                    <span class="text-sm font-normal text-zinc-400 line-through">
                        {!! wc_price($this->product->get_regular_price()) !!}
                    </span>
                    <span>{!! wc_price($this->product->get_sale_price()) !!}</span>
                @else
                    {!! $this->product->get_price_html() !!}
                @endif
            </div>

            {{-- Add to Cart / Actions --}}
            @if ($this->product->is_in_stock())
                @if($this->product->is_type('variable'))
                    <flux:button variant="filled" href="{{ $this->product->get_permalink() }}" class="w-full justify-center" wire:navigate>
                        {{ __('Select options', 'flux-press') }}
                    </flux:button>
                @else
                    {{-- Reactive Add to cart button --}}
                    <flux:button wire:click="addToCart" variant="primary" class="w-full justify-center" icon="shopping-bag" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="addToCart">{{ __('Add to cart', 'flux-press') }}</span>
                        <span wire:loading wire:target="addToCart">{{ __('Adding...', 'flux-press') }}</span>
                    </flux:button>
                @endif
            @else
                <flux:button disabled variant="ghost" class="w-full justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 cursor-not-allowed border border-zinc-200 dark:border-zinc-700">
                    {{ __('Out of stock', 'flux-press') }}
                </flux:button>
            @endif
        </div>
    @endif
</flux:card>