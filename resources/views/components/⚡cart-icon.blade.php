<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

new class extends Component
{
    /**
     * Whether WooCommerce is available.
     */
    public bool $wooCommerceActive = false;

    /**
     * Whether to show the cart icon (Customizer setting).
     */
    public bool $showCartIcon = true;

    public function mount(): void
    {
        $this->wooCommerceActive = class_exists('WooCommerce');
        $this->showCartIcon = (bool) get_theme_mod('woocommerce_show_cart_icon', true);
    }

    #[Computed]
    public function cartCount(): int
    {
        if (! $this->wooCommerceActive || ! function_exists('WC') || ! WC()->cart) {
            return 0;
        }

        return WC()->cart->get_cart_contents_count();
    }

    #[Computed]
    public function cartItems(): array
    {
        if (! $this->wooCommerceActive || ! function_exists('WC') || ! WC()->cart) {
            return [];
        }

        $items = [];
        foreach (WC()->cart->get_cart() as $cartItemKey => $cartItem) {
            $product = $cartItem['data'];

            if (! $product) {
                continue;
            }

            $items[] = [
                'key'       => $cartItemKey,
                'name'      => $product->get_name(),
                'quantity'  => $cartItem['quantity'],
                'price'     => wc_price($product->get_price() * $cartItem['quantity']),
                'image'     => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail'),
                'permalink' => $product->get_permalink(),
            ];
        }

        return $items;
    }

    #[Computed]
    public function cartTotal(): string
    {
        if (! $this->wooCommerceActive || ! function_exists('WC') || ! WC()->cart) {
            return '';
        }

        return WC()->cart->get_cart_total();
    }

    #[Computed]
    public function cartUrl(): string
    {
        if (! $this->wooCommerceActive || ! function_exists('wc_get_cart_url')) {
            return '#';
        }

        return wc_get_cart_url();
    }

    #[Computed]
    public function checkoutUrl(): string
    {
        if (! $this->wooCommerceActive || ! function_exists('wc_get_checkout_url')) {
            return '#';
        }

        return wc_get_checkout_url();
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(string $cartItemKey): void
    {
        if (! $this->wooCommerceActive || ! function_exists('WC') || ! WC()->cart) {
            return;
        }

        WC()->cart->remove_cart_item($cartItemKey);

        $this->updateCart();
    }

    /**
     * Update the quantity of an item in the cart.
     */
    public function updateQuantity(string $cartItemKey, int $quantity): void
    {
        if (! $this->wooCommerceActive || ! function_exists('WC') || ! WC()->cart) {
            return;
        }

        if ($quantity <= 0) {
            WC()->cart->remove_cart_item($cartItemKey);
        } else {
            WC()->cart->set_quantity($cartItemKey, $quantity);
        }

        $this->updateCart();
    }

    #[On('cart-updated')]
    public function updateCart(): void
    {
        // Clear computed caches to force Livewire to re-evaluate them on next render
        unset($this->cartCount, $this->cartItems, $this->cartTotal);
    }
}; ?>

<div wire:poll.5s>
    @if ($this->wooCommerceActive && $this->showCartIcon)
        {{-- Cart Icon Trigger --}}
        <flux:modal.trigger name="cart-flyout">
            <flux:button variant="ghost" size="sm" class="relative" aria-label="{{ __('Shopping Cart', 'flux-press') }}">
                <flux:icon.shopping-cart class="size-5" />
                @if ($this->cartCount > 0)
                    <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-[0.65rem] font-bold leading-none text-white bg-accent-600 dark:bg-accent-500 rounded-full">
                        {{ $this->cartCount }}
                    </span>
                @endif
            </flux:button>
        </flux:modal.trigger>

        {{-- Cart Flyout Modal --}}
        <flux:modal name="cart-flyout" flyout variant="floating" class="md:w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">{{ __('Your Cart', 'flux-press') }}</flux:heading>

                @if (count($this->cartItems) > 0)
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->cartItems as $item)
                            <div class="flex items-center gap-4 py-4" wire:key="cart-item-{{ $item['key'] }}">
                                {{-- Product Image --}}
                                <img
                                    src="{{ $item['image'] }}"
                                    alt="{{ esc_attr($item['name']) }}"
                                    class="w-16 h-16 rounded-lg object-cover flex-shrink-0"
                                />

                                {{-- Product Info --}}
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $item['permalink'] }}" wire:navigate class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:text-accent-600 dark:hover:text-accent-400 transition-colors truncate block">
                                        {{ $item['name'] }}
                                    </a>
                                    <div class="flex items-center justify-between gap-2 mt-2">
                                        {{-- Quantity Controller --}}
                                        <div class="flex items-center border border-zinc-200 dark:border-zinc-700 rounded-md overflow-hidden bg-white dark:bg-zinc-800">
                                            <button 
                                                wire:click="updateQuantity('{{ $item['key'] }}', {{ $item['quantity'] - 1 }})" 
                                                class="px-2 py-0.5 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                                                aria-label="{{ __('Decrease quantity', 'flux-press') }}"
                                            >
                                                &minus;
                                            </button>
                                            <span class="px-2 py-0.5 text-xs font-medium text-zinc-900 dark:text-zinc-100 min-w-[1.5rem] text-center">
                                                {{ $item['quantity'] }}
                                            </span>
                                            <button 
                                                wire:click="updateQuantity('{{ $item['key'] }}', {{ $item['quantity'] + 1 }})" 
                                                class="px-2 py-0.5 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                                                aria-label="{{ __('Increase quantity', 'flux-press') }}"
                                            >
                                                &plus;
                                            </button>
                                        </div>

                                        <span class="text-sm font-semibold text-accent-600 dark:text-accent-400">
                                            {!! $item['price'] !!}
                                        </span>
                                    </div>
                                </div>

                                {{-- Remove Button --}}
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="x-mark"
                                    wire:click="removeItem('{{ $item['key'] }}')"
                                    aria-label="{{ __('Remove item', 'flux-press') }}"
                                    class="flex-shrink-0 text-zinc-400 hover:text-red-500 dark:hover:text-red-400"
                                />
                            </div>
                        @endforeach
                    </div>

                    {{-- Cart Total --}}
                    <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Total', 'flux-press') }}</span>
                        <span class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{!! $this->cartTotal !!}</span>
                    </div>
                @else
                    {{-- Empty Cart --}}
                    <div class="text-center py-8">
                        <flux:icon.shopping-cart class="size-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-4" />
                        <flux:subheading>{{ __('Your cart is empty', 'flux-press') }}</flux:subheading>
                    </div>
                @endif
            </div>

            @if (count($this->cartItems) > 0)
                <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="filled" href="{{ $this->cartUrl }}" wire:navigate>
                        {{ __('View Cart', 'flux-press') }}
                    </flux:button>

                    <flux:button variant="primary" href="{{ $this->checkoutUrl }}" wire:navigate>
                        {{ __('Checkout', 'flux-press') }}
                    </flux:button>
                </div>
            @endif
        </flux:modal>
    @endif
</div>