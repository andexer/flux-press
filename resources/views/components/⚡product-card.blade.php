<?php

use Illuminate\Support\Facades\Log;
use Livewire\Component;

new class extends Component {
	public int $productId;

	public string $variant = 'shop';

	public bool $justAdded = false;

	public function mount(int $productId, string $variant = 'shop'): void
	{
		$this->productId = $productId;
		$this->variant = in_array($variant, ['shop', 'compact'], true) ? $variant : 'shop';
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
			\Flux::toast(__('El carrito no esta disponible.', 'flux-press'), variant: 'danger');

			return;
		}

		try {
			$product = $this->product;

			if (!$product) {
				\Flux::toast(__('No pudimos cargar este producto.', 'flux-press'), variant: 'danger');

				return;
			}

			if (!$product->is_purchasable()) {
				\Flux::toast(__('Este producto no se puede comprar en este momento.', 'flux-press'), variant: 'danger');

				return;
			}

			if (!$product->is_in_stock()) {
				\Flux::toast(__('Este producto esta agotado.', 'flux-press'), variant: 'danger');

				return;
			}

			if ($product->is_type('variable') || $product->is_type('grouped') || $product->is_type('external')) {
				$this->redirect($product->get_permalink(), navigate: true);

				return;
			}

			$added = WC()->cart->add_to_cart($product->get_id(), 1);

			if (!$added) {
				\Flux::toast(__('No se pudo agregar al carrito.', 'flux-press'), variant: 'danger');

				return;
			}

			WC()->cart->calculate_totals();

			$this->justAdded = true;

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

	public function clearAddedState(): void
	{
		$this->justAdded = false;
	}
}; ?>

@if ($this->product)
	@php
		$product = $this->product;
		$isCompact = $variant === 'compact';
		$isShop = $variant === 'shop';
		$isActionProduct = $product->is_type('variable') || $product->is_type('grouped') || $product->is_type('external');
		$regularPrice = (float) $product->get_regular_price();
		$salePrice = (float) $product->get_sale_price();
		$discount = ($regularPrice > 0 && $salePrice > 0 && $salePrice < $regularPrice)
			? max(1, (int) round((($regularPrice - $salePrice) / $regularPrice) * 100))
			: 0;
		$cartUrl = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/carrito/');
		$averageRating = (float) $product->get_average_rating();
		$reviewCount = (int) $product->get_review_count();
		$totalSales = (int) $product->get_total_sales();

		$primaryCategoryName = __('Sin categoria', 'flux-press');
		$productTerms = get_the_terms($product->get_id(), 'product_cat');
		if (is_array($productTerms) && isset($productTerms[0]) && $productTerms[0] instanceof \WP_Term) {
			$primaryCategoryName = $productTerms[0]->name;
		}

		$isInCart = false;
		if (function_exists('WC') && WC()->cart) {
			$cartKey = WC()->cart->find_product_in_cart(WC()->cart->generate_cart_id($product->get_id()));
			$isInCart = is_string($cartKey) && $cartKey !== '';
		}

		$showAddedState = ($isInCart || $this->justAdded) && !$isActionProduct;
		$showSalesMeta = $totalSales > 0;
		$showRatingMeta = $averageRating > 0;
	@endphp

	<article @class([
		'flux-product-card group relative flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-[0_10px_30px_-22px_rgba(15,23,42,0.45)] transition-all duration-300 dark:border-zinc-800 dark:bg-zinc-900',
		'hover:-translate-y-0.5 hover:shadow-[0_22px_40px_-28px_rgba(15,23,42,0.55)]' => !$isCompact,
		'rounded-xl' => $isCompact,
	])>
		<div class="relative isolate p-2 pb-0">
			<a href="{{ $product->get_permalink() }}" wire:navigate @class([
				'block overflow-hidden rounded-[1rem] bg-zinc-100 dark:bg-zinc-800',
				'aspect-[5/6]' => $isShop,
				'aspect-[4/5]' => $isCompact,
			])>
				@if ($product->get_image_id())
					<img src="{{ wp_get_attachment_image_url($product->get_image_id(), $isCompact ? 'woocommerce_thumbnail' : 'large') }}"
						alt="{{ esc_attr($product->get_name()) }}" loading="lazy"
						class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.035]" />
				@else
					<img src="{{ wc_placeholder_img_src('woocommerce_thumbnail') }}" alt="{{ __('Placeholder', 'flux-press') }}"
						class="h-full w-full object-cover opacity-50" />
				@endif
			</a>

			<div class="pointer-events-none absolute inset-x-2 top-2 z-10 flex items-start justify-between gap-2">
				@if($product->is_on_sale() && $discount > 0)
					<span
						class="inline-flex items-center rounded-full bg-red-500 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.08em] text-white shadow-sm">
						-{{ $discount }}%
					</span>
				@else
					<span></span>
				@endif

				@if($product->is_in_stock())
					<span
						class="inline-flex items-center rounded-full bg-emerald-500/90 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.08em] text-white shadow-sm">
						{{ __('Stock', 'flux-press') }}
					</span>
				@endif
			</div>

			<div class="pointer-events-none absolute inset-x-2 bottom-2 z-10">
				<div
					class="inline-flex max-w-full items-center gap-2 rounded-full border border-white/55 bg-zinc-900/55 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-white backdrop-blur-md">
					<span class="truncate">{{ $primaryCategoryName }}</span>
					@if($showRatingMeta)
						<span class="h-1 w-1 rounded-full bg-white/70"></span>
						<span class="inline-flex items-center gap-1 whitespace-nowrap">
							<flux:icon.star class="size-3 text-amber-300" />
							{{ number_format_i18n($averageRating, 1) }}
						</span>
					@endif
				</div>
			</div>
		</div>

		<div @class([
			'flex flex-1 flex-col px-3 pb-3',
			'pt-2.5' => $isShop,
			'pt-3' => $isCompact,
		])>
			<h3>
				<a href="{{ $product->get_permalink() }}" wire:navigate @class([
					'line-clamp-2 font-semibold text-zinc-900 transition-colors hover:text-accent-700 dark:text-zinc-100 dark:hover:text-accent-300',
					'text-[13px] leading-snug' => $isShop,
					'text-sm leading-snug' => $isCompact,
				])>
					{{ $product->get_name() }}
				</a>
			</h3>

			@if($isShop)
				<div
					class="mt-2 flex min-h-5 flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-medium text-zinc-500 dark:text-zinc-400">
					@if($showSalesMeta)
						<span class="whitespace-nowrap">
							{{ sprintf(_n('%s vendido', '%s vendidos', $totalSales, 'flux-press'), number_format_i18n($totalSales)) }}
						</span>
					@endif

					@if($showRatingMeta && $reviewCount > 0)
						<span class="inline-flex items-center gap-1 whitespace-nowrap">
							<flux:icon.chat-bubble-left-ellipsis class="size-3.5" />
							{{ sprintf(_n('%s resena', '%s resenas', $reviewCount, 'flux-press'), number_format_i18n($reviewCount)) }}
						</span>
					@endif

					@if(!$showSalesMeta && !($showRatingMeta && $reviewCount > 0))
						<span class="whitespace-nowrap">{{ __('Nuevo', 'flux-press') }}</span>
					@endif
				</div>
			@endif

			<div class="mt-3 flex items-end justify-between gap-2">
				<div class="min-w-0 space-y-1">
					@if($product->is_on_sale() && $product->get_regular_price() !== '')
						<p class="text-[11px] leading-none text-zinc-400 line-through">
							{!! wc_price($product->get_regular_price()) !!}
						</p>
					@endif

					<p @class([
						'font-black leading-none text-accent-700 dark:text-accent-400',
						'text-sm' => $isCompact || $isShop,
					])>
						{!! $product->get_price_html() !!}
					</p>
				</div>
			</div>

			<div class="mt-auto grid gap-2 pt-3.5">
				@if ($product->is_in_stock())
					@if($isActionProduct)
						<flux:button variant="outline" href="{{ $product->get_permalink() }}" wire:navigate @class([
							'w-full justify-center rounded-xl',
							'h-8 text-[11px]' => $isShop,
							'h-9 text-xs' => $isCompact,
						])>
							{{ __('Ver producto', 'flux-press') }}
						</flux:button>
					@else
						@if($showAddedState)
							<div
								class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
								<flux:icon.check-circle class="size-4" />
								{{ __('Agregado al carrito', 'flux-press') }}
							</div>

							<div @class([
								'grid gap-2',
								'grid-cols-2' => $isShop,
								'grid-cols-1' => $isCompact,
							])>
								<flux:button wire:click="addToCart" variant="ghost" icon="plus" wire:loading.attr="disabled" @class([
									'justify-center rounded-xl border border-zinc-200 dark:border-zinc-700',
									'h-8 text-[11px]' => $isShop,
									'h-9 text-xs' => $isCompact,
								])>
									<span wire:loading.remove wire:target="addToCart">{{ __('Agregar 1 mas', 'flux-press') }}</span>
									<span wire:loading wire:target="addToCart">{{ __('Agregando...', 'flux-press') }}</span>
								</flux:button>

								<flux:button href="{{ $cartUrl }}" wire:navigate variant="outline" @class([
									'justify-center rounded-xl',
									'h-8 text-[11px]' => $isShop,
									'h-9 text-xs' => $isCompact,
								])>
									{{ __('Ver carrito', 'flux-press') }}
								</flux:button>
							</div>

							@if($this->justAdded)
								<button type="button" wire:click="clearAddedState"
									class="text-left text-[11px] font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
									{{ __('Seguir comprando', 'flux-press') }}
								</button>
							@endif
						@else
							<flux:button wire:click="addToCart" variant="primary" icon="shopping-bag" wire:loading.attr="disabled"
								@class([
									'w-full justify-center rounded-xl',
									'h-8 text-[11px]' => $isShop,
									'h-9 text-xs' => $isCompact,
								])>
								<span wire:loading.remove wire:target="addToCart">
									{{ $isCompact ? __('Agregar', 'flux-press') : __('Agregar al carrito', 'flux-press') }}
								</span>
								<span wire:loading wire:target="addToCart">{{ __('Agregando...', 'flux-press') }}</span>
							</flux:button>

							@if($isShop)
								<a href="{{ $product->get_permalink() }}" wire:navigate
									class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-[11px] font-semibold text-zinc-600 no-underline transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-300 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
									{{ __('Ver detalles', 'flux-press') }}
								</a>
							@endif
						@endif
					@endif
				@else
					<flux:button disabled variant="ghost"
						class="w-full justify-center rounded-xl border border-zinc-200 bg-zinc-100 h-8 text-[11px] text-zinc-500 cursor-not-allowed dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
						{{ __('Agotado', 'flux-press') }}
					</flux:button>
				@endif
			</div>
		</div>
	</article>
@endif