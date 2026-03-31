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
			\Flux::toast(__('El carrito no esta disponible.', 'sage'), variant: 'danger');

			return;
		}

		try {
			$product = $this->product;

			if (!$product) {
				\Flux::toast(__('No pudimos cargar este producto.', 'sage'), variant: 'danger');

				return;
			}

			if (!$product->is_purchasable()) {
				\Flux::toast(__('Este producto no se puede comprar en este momento.', 'sage'), variant: 'danger');

				return;
			}

			if (!$product->is_in_stock()) {
				\Flux::toast(__('Este producto esta agotado.', 'sage'), variant: 'danger');

				return;
			}

			if ($product->is_type('variable') || $product->is_type('grouped') || $product->is_type('external')) {
				$this->redirect($product->get_permalink(), navigate: true);

				return;
			}

			$added = WC()->cart->add_to_cart($product->get_id(), 1);

			if (!$added) {
				\Flux::toast(__('No se pudo agregar al carrito.', 'sage'), variant: 'danger');

				return;
			}

			WC()->cart->calculate_totals();

			$this->justAdded = true;

			$this->dispatch('cart-updated');
			$this->dispatch('wc-cart-fragments-refresh');

			\Flux::toast(sprintf(__('%s fue agregado a tu carrito.', 'sage'), $product->get_name()), variant: 'success');
		} catch (\Throwable $e) {
			\Flux::toast(__('No se pudo agregar el producto al carrito.', 'sage'), variant: 'danger');
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
		$summarySource = (string) $product->get_short_description();
		if ($summarySource === '') {
			$summarySource = (string) get_post_field('post_excerpt', $product->get_id());
		}
		if ($summarySource === '') {
			$summarySource = (string) get_post_field('post_content', $product->get_id());
		}
		$productSummary = trim((string) wp_strip_all_tags($summarySource));
		$productSummary = html_entity_decode($productSummary, ENT_QUOTES | ENT_HTML5, get_bloginfo('charset') ?: 'UTF-8');
		$productSummary = trim((string) preg_replace('/\s+/u', ' ', $productSummary));
		$productSummary = $productSummary !== ''
			? wp_trim_words($productSummary, $isCompact ? 14 : 20, '...')
			: '';
		$showReviewMeta = $reviewCount > 0;
		$priceHtml = $product->get_price_html();
		if (($product->is_type('simple') || $product->is_type('variation')) && $product->get_price() !== '') {
			$priceHtml = wc_price((float) $product->get_price());
		}
		$hasPrice = trim((string) wp_strip_all_tags((string) $priceHtml)) !== '';

		$primaryCategoryName = __('Sin categoria', 'sage');
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
		$showRatingMeta = $averageRating > 0 || $showReviewMeta;
	@endphp

			<article @class([
				'flux-product-card group relative flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-[0_10px_30px_-22px_rgba(15,23,42,0.45)] transition-all duration-300 dark:border-zinc-800 dark:bg-zinc-900',
				'hover:shadow-[0_22px_40px_-28px_rgba(15,23,42,0.55)]' => !$isCompact,
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
						<img src="{{ wc_placeholder_img_src('woocommerce_thumbnail') }}" alt="{{ __('Placeholder', 'sage') }}"
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
							{{ __('Stock', 'sage') }}
						</span>
					@endif
				</div>

				@if($showAddedState)
					<div class="pointer-events-none absolute inset-x-2 top-2 z-20 flex justify-center">
						<span
							class="inline-flex items-center gap-1 rounded-full border border-emerald-200/80 bg-emerald-50/95 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.09em] text-emerald-700 shadow-sm dark:border-emerald-700/80 dark:bg-emerald-900/85 dark:text-emerald-200">
							<flux:icon.check-circle class="size-3.5" />
							{{ __('Agregado al carrito', 'sage') }}
						</span>
					</div>
				@endif

				<div class="pointer-events-none absolute inset-x-2 bottom-2 z-10">
					<div
						class="inline-flex max-w-full items-center gap-2 rounded-full border border-white/55 bg-zinc-900/55 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-white backdrop-blur-md">
						<span class="truncate">{{ $primaryCategoryName }}</span>
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

				@if($isShop && $productSummary !== '')
					<p class="mt-1.5 text-[11px] leading-relaxed text-zinc-500 dark:text-zinc-400 line-clamp-3">
						{{ $productSummary }}
					</p>
				@endif

				@if($hasPrice || ($product->is_on_sale() && $product->get_regular_price() !== ''))
					<div class="mt-2.5 flex items-end justify-between gap-2">
						<div class="min-w-0 space-y-1">
							@if($product->is_on_sale() && $product->get_regular_price() !== '')
								<p class="text-[11px] leading-none text-zinc-400 line-through">
									{!! wc_price($product->get_regular_price()) !!}
								</p>
							@endif

							@if($hasPrice)
								<p @class([
									'font-black leading-none text-accent-700 dark:text-accent-400',
									'text-sm' => $isCompact || $isShop,
								])>
									{!! $priceHtml !!}
								</p>
							@endif
						</div>
					</div>
				@endif

				@if($isShop)
					<div @class([
						'flex min-h-5 flex-wrap items-center gap-1.5 text-[10px] font-semibold text-zinc-500 dark:text-zinc-400',
						'mt-1.5' => !$hasPrice,
						'mt-2' => $hasPrice,
					])>
						@if($showSalesMeta)
							<span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-1 dark:bg-zinc-800/80">
								<flux:icon.shopping-bag class="size-3.5" />
								{{ sprintf(_n('%s vendido', '%s vendidos', $totalSales, 'sage'), number_format_i18n($totalSales)) }}
							</span>
						@endif

						@if($showRatingMeta)
							<span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-1 dark:bg-zinc-800/80">
								<flux:icon.star class="size-3.5 text-amber-400" />
								@if($averageRating > 0)
									{{ number_format_i18n($averageRating, 1) }}
								@else
									{{ __('Sin rating', 'sage') }}
								@endif
								@if($showReviewMeta)
									<span class="text-zinc-400 dark:text-zinc-500">({{ number_format_i18n($reviewCount) }})</span>
								@endif
							</span>
						@endif

						@if(!$showSalesMeta && !$showRatingMeta)
							<span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-1 dark:bg-zinc-800/80">
								{{ __('Nuevo', 'sage') }}
							</span>
						@endif
					</div>
				@endif

					<div @class([
						'grid gap-2',
						'mt-3 pt-2.5' => $isShop,
						'mt-3 pt-2.5' => $isCompact,
						'lg:absolute lg:inset-x-3 lg:bottom-3 lg:z-30 lg:mt-0 lg:rounded-2xl lg:border lg:border-white/55 lg:bg-white/70 lg:p-2.5 lg:pt-2.5 lg:ring-1 lg:ring-white/45 lg:shadow-[0_20px_36px_-24px_rgba(15,23,42,0.72)] lg:backdrop-blur-xl dark:lg:border-zinc-700/75 dark:lg:bg-zinc-900/70 dark:lg:ring-zinc-700/50' => $isShop,
						'lg:translate-y-2 lg:opacity-0 lg:pointer-events-none lg:transition-all lg:duration-200 lg:ease-out lg:group-hover:translate-y-0 lg:group-hover:opacity-100 lg:group-hover:pointer-events-auto lg:group-focus-within:translate-y-0 lg:group-focus-within:opacity-100 lg:group-focus-within:pointer-events-auto' => $isShop,
					])>
						@if ($product->is_in_stock())
							@if($isActionProduct)
								<flux:button variant="outline" href="{{ $product->get_permalink() }}" wire:navigate @class([
									'w-full min-w-0 justify-center rounded-xl font-semibold leading-none',
									'h-10 text-[11px]' => $isShop,
									'h-10 text-xs' => $isCompact,
								])>
									<span class="block min-w-0 truncate whitespace-nowrap">{{ __('Ver producto', 'sage') }}</span>
								</flux:button>
							@else
								@if($showAddedState)
									<div @class([
										'grid gap-2',
									'grid-cols-2' => $isShop,
									'grid-cols-1' => $isCompact,
								])>
										<flux:button wire:click="addToCart" variant="ghost" wire:loading.attr="disabled" @class([
											'w-full min-w-0 justify-center rounded-xl border border-zinc-200/80 bg-white/85 px-2 font-semibold leading-none dark:border-zinc-700 dark:bg-zinc-900/70',
											'h-10 text-[11px]' => $isShop,
											'h-10 text-xs' => $isCompact,
										])>
											<span class="block min-w-0 truncate whitespace-nowrap" wire:loading.remove wire:target="addToCart">
												{{ __('Agregar +1', 'sage') }}
											</span>
											<span class="block min-w-0 truncate whitespace-nowrap" wire:loading wire:target="addToCart">{{ __('Agregando...', 'sage') }}</span>
										</flux:button>

										<flux:button href="{{ $cartUrl }}" wire:navigate variant="outline" @class([
											'w-full min-w-0 justify-center rounded-xl font-semibold leading-none',
											'h-10 text-[11px]' => $isShop,
											'h-10 text-xs' => $isCompact,
										])>
											<span class="block min-w-0 truncate whitespace-nowrap">{{ __('Ver carrito', 'sage') }}</span>
										</flux:button>
									</div>

								@if($this->justAdded)
									<button type="button" wire:click="clearAddedState"
										class="text-left text-[10px] font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
										{{ __('Seguir comprando', 'sage') }}
									</button>
								@endif
							@else
									<div @class([
										'grid gap-2',
										'grid-cols-2' => $isShop,
										'grid-cols-1' => $isCompact,
									])>
										<flux:button wire:click="addToCart" variant="primary" wire:loading.attr="disabled"
											@class([
												'w-full min-w-0 justify-center rounded-xl font-semibold leading-none',
												'h-10 text-[11px]' => $isShop,
												'h-10 text-xs' => $isCompact,
											])>
											<span class="block min-w-0 truncate whitespace-nowrap" wire:loading.remove wire:target="addToCart">
												{{ __('Agregar', 'sage') }}
											</span>
											<span class="block min-w-0 truncate whitespace-nowrap" wire:loading wire:target="addToCart">{{ __('Agregando...', 'sage') }}</span>
										</flux:button>

										@if($isShop)
											<a href="{{ $product->get_permalink() }}" wire:navigate
												class="inline-flex h-10 w-full min-w-0 items-center justify-center rounded-xl border border-zinc-200/85 bg-zinc-50/85 px-2 text-[11px] font-semibold leading-none text-zinc-600 no-underline transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-300 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
												<span class="block min-w-0 truncate whitespace-nowrap">{{ __('Detalles', 'sage') }}</span>
											</a>
										@endif
									</div>
								@endif
							@endif
						@else
							<flux:button disabled variant="ghost"
								class="w-full min-w-0 justify-center rounded-xl border border-zinc-200 bg-zinc-100 h-10 text-[11px] font-semibold leading-none text-zinc-500 cursor-not-allowed dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
								<span class="block min-w-0 truncate whitespace-nowrap">{{ __('Agotado', 'sage') }}</span>
							</flux:button>
						@endif
				</div>
			</div>
		</article>
@endif
