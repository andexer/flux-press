@php
    defined('ABSPATH') || exit;

    global $product;

    $hasRenderableProduct = $product instanceof \WC_Product;
@endphp

@if($hasRenderableProduct)
@php
    $regularPrice = (float) $product->get_regular_price();
    $salePrice = (float) $product->get_sale_price();
    $discountLabel = '';
    if ($product->is_on_sale() && $regularPrice > 0 && $salePrice > 0 && $salePrice < $regularPrice) {
        $discountLabel = '-' . max(1, (int) round((($regularPrice - $salePrice) / $regularPrice) * 100)) . '%';
    } elseif ($product->is_on_sale()) {
        $discountLabel = __('Oferta', 'flux-press');
    }

    $reviewCount = (int) $product->get_review_count();
    $sku = (string) $product->get_sku();
    $stockLabel = $product->is_in_stock()
        ? ($product->backorders_allowed() ? __('Disponible bajo pedido', 'flux-press') : __('Disponible', 'flux-press'))
        : __('Agotado', 'flux-press');
    $stockClasses = $product->is_in_stock()
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-800'
        : 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-800';

    $terms = get_the_terms($product->get_id(), 'product_cat');
    $primaryCategory = is_array($terms) ? reset($terms) : null;
    $primaryCategoryUrl = ($primaryCategory instanceof \WP_Term)
        ? get_term_link($primaryCategory, 'product_cat')
        : null;
    if (is_wp_error($primaryCategoryUrl)) {
        $primaryCategoryUrl = null;
    }

    $summaryStats = array_values(array_filter([
        [
            'label' => __('Estado', 'flux-press'),
            'value' => $stockLabel,
        ],
        [
            'label' => __('SKU', 'flux-press'),
            'value' => $sku !== '' ? $sku : __('No disponible', 'flux-press'),
        ],
        [
            'label' => __('Resenas', 'flux-press'),
            'value' => $reviewCount > 0
                ? sprintf(
                    _n('%d resena', '%d resenas', $reviewCount, 'flux-press'),
                    $reviewCount
                )
                : __('Sin resenas', 'flux-press'),
        ],
    ], static fn ($item) => is_array($item) && ($item['value'] ?? '') !== ''));

    ob_start();
    do_action('woocommerce_before_single_product_summary');
    $beforeSingleSummaryHooks = trim((string) ob_get_clean());

    ob_start();
    do_action('woocommerce_single_product_summary');
    $singleSummaryHooks = trim((string) ob_get_clean());

    ob_start();
    do_action('woocommerce_after_single_product_summary');
    $afterSingleSummaryHooks = trim((string) ob_get_clean());

    ob_start();
    woocommerce_template_single_meta();
    $metaHtml = trim((string) ob_get_clean());

    ob_start();
    woocommerce_template_single_sharing();
    $sharingHtml = trim((string) ob_get_clean());

    $productTabs = apply_filters('woocommerce_product_tabs', []);

    ob_start();
    woocommerce_upsell_display();
    $upsellsHtml = trim((string) ob_get_clean());

    ob_start();
    woocommerce_output_related_products();
    $relatedHtml = trim((string) ob_get_clean());
@endphp

@php do_action('woocommerce_before_single_product'); @endphp

@if(post_password_required())
    {!! get_the_password_form() !!}
@else
    <article id="product-{{ $product->get_id() }}" @php wc_product_class('flux-single-product-shell space-y-8', $product); @endphp>
        <div class="flux-single-product-grid grid gap-6 xl:grid-cols-[minmax(0,1.03fr)_minmax(0,0.97fr)]">
            <div class="flux-single-product-media space-y-4 xl:sticky xl:top-24 xl:self-start">
                <flux:card class="flux-single-product-card flux-single-gallery-shell overflow-hidden rounded-[2rem] border border-zinc-200/70 bg-white/95 p-4 shadow-sm backdrop-blur dark:border-zinc-800/80 dark:bg-zinc-900/90 sm:p-5">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <flux:text class="text-[11px] uppercase tracking-[0.3em] text-zinc-500 dark:text-zinc-400">
                                {{ __('Galeria', 'flux-press') }}
                            </flux:text>
                            <flux:heading size="lg" class="mt-2 !font-black tracking-tight text-zinc-900 dark:text-zinc-100">
                                {{ __('Vista del producto', 'flux-press') }}
                            </flux:heading>
                        </div>

                        @if($discountLabel !== '')
                            <span class="inline-flex items-center rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white shadow-sm">
                                {{ $discountLabel }}
                            </span>
                        @endif
                    </div>

                    <livewire:product-gallery :product-id="$product->get_id()" :key="'single-gallery-'.$product->get_id()" />
                </flux:card>

                @if($beforeSingleSummaryHooks !== '')
                    <div class="flux-single-product-hook flux-single-product-hook-before space-y-4">
                        {!! $beforeSingleSummaryHooks !!}
                    </div>
                @endif
            </div>

            <div class="flux-single-product-main space-y-6">
                <div class="flux-single-product-top grid gap-6 lg:grid-cols-[minmax(0,0.92fr)_minmax(17rem,0.55fr)]">
                    <div class="flux-single-product-summary space-y-4">
                        <flux:card class="flux-single-product-card overflow-hidden rounded-[2rem] border border-zinc-200/70 bg-white/95 p-5 shadow-sm backdrop-blur dark:border-zinc-800/80 dark:bg-zinc-900/90 sm:p-6">
                            <div class="space-y-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($primaryCategory instanceof \WP_Term && is_string($primaryCategoryUrl) && $primaryCategoryUrl !== '')
                                        <a href="{{ $primaryCategoryUrl }}" wire:navigate class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.25em] text-zinc-600 transition-colors hover:border-accent-300 hover:text-accent-700 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-300 dark:hover:border-accent-700 dark:hover:text-accent-300">
                                            {{ $primaryCategory->name }}
                                        </a>
                                    @endif

                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $stockClasses }}">
                                        {{ $stockLabel }}
                                    </span>
                                </div>

                                <div class="space-y-4">
                                    @php woocommerce_template_single_title(); @endphp
                                    @php woocommerce_template_single_rating(); @endphp
                                    @php woocommerce_template_single_price(); @endphp
                                    @php woocommerce_template_single_excerpt(); @endphp
                                </div>

                                @if(! empty($summaryStats))
                                    <div class="flux-single-summary-stats grid gap-3 border-t border-zinc-200/80 pt-4 dark:border-zinc-800/80 sm:grid-cols-3">
                                        @foreach($summaryStats as $stat)
                                            <div class="flux-single-summary-stat rounded-2xl bg-zinc-50/75 p-3 dark:bg-zinc-800/55">
                                                <flux:text class="text-[11px] uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">
                                                    {{ $stat['label'] }}
                                                </flux:text>
                                                <flux:text class="mt-2 block text-sm font-semibold leading-snug text-zinc-800 dark:text-zinc-200">
                                                    {{ $stat['value'] }}
                                                </flux:text>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </flux:card>
                    </div>

                    <aside class="flux-single-product-purchase">
                        <div class="space-y-4 lg:sticky lg:top-24">
                            <flux:card class="flux-single-product-card flux-single-purchase-card overflow-hidden rounded-[1.5rem] border border-zinc-200/70 bg-white/95 p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900/95 sm:p-5">
                                <div class="space-y-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <flux:text class="text-[11px] uppercase tracking-[0.3em] text-zinc-500 dark:text-zinc-400">
                                                {{ __('Compra', 'flux-press') }}
                                            </flux:text>
                                            <div class="flux-single-purchase-price mt-3 text-3xl font-black leading-none text-zinc-900 dark:text-zinc-100">
                                                {!! $product->get_price_html() !!}
                                            </div>
                                            <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ __('Selecciona cantidad y agrega al carrito al instante.', 'flux-press') }}
                                            </flux:text>
                                        </div>

                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $stockClasses }}">
                                            {{ $stockLabel }}
                                        </span>
                                    </div>

                                    @if($discountLabel !== '')
                                        <div class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-800">
                                            {{ sprintf(__('Ahorra %s', 'flux-press'), $discountLabel) }}
                                        </div>
                                    @endif

                                    <flux:separator />

                                    <div class="flux-single-purchase-form">
                                        @php woocommerce_template_single_add_to_cart(); @endphp
                                    </div>

                                    @if($metaHtml !== '')
                                        <flux:separator />

                                        <div class="flux-single-purchase-meta">
                                            {!! $metaHtml !!}
                                        </div>
                                    @endif

                                    @if($sharingHtml !== '')
                                        <flux:separator />

                                        <div class="flux-single-purchase-sharing">
                                            {!! $sharingHtml !!}
                                        </div>
                                    @endif
                                </div>
                            </flux:card>
                        </div>
                    </aside>
                </div>

                @if($singleSummaryHooks !== '')
                    <div class="flux-single-product-hook flux-single-product-hook-summary space-y-4">
                        {!! $singleSummaryHooks !!}
                    </div>
                @endif

                @if(! empty($productTabs))
                    <div class="flux-single-product-section flux-single-product-tabs-shell">
                        @include('woocommerce.single-product.tabs.tabs', ['productTabs' => $productTabs])
                    </div>
                @endif

                @if($afterSingleSummaryHooks !== '')
                    <div class="flux-single-product-hook flux-single-product-hook-after space-y-4">
                        {!! $afterSingleSummaryHooks !!}
                    </div>
                @endif
            </div>
        </div>

        @if($upsellsHtml !== '' || $relatedHtml !== '')
            <div class="flux-single-product-lower space-y-8">
                @if($upsellsHtml !== '')
                    <div class="flux-single-product-section flux-single-product-upsells-shell">
                        {!! $upsellsHtml !!}
                    </div>
                @endif

                @if($relatedHtml !== '')
                    <div class="flux-single-product-section flux-single-product-related-shell">
                        {!! $relatedHtml !!}
                    </div>
                @endif
            </div>
        @endif
    </article>
@endif

@php do_action('woocommerce_after_single_product'); @endphp
@endif
