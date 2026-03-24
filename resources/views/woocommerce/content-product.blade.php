@php
    defined('ABSPATH') || exit;

    global $product;

    $isRenderableProduct = $product instanceof \WC_Product && $product->is_visible();
    static $fluxLoopCardInstance = 0;
    $fluxLoopCardInstance++;
    $loopIndex = function_exists('wc_get_loop_prop') ? (int) wc_get_loop_prop('loop', 0) : 0;
    $cardKey = 'wc-loop-product-' . ($product instanceof \WC_Product ? $product->get_id() : 'x') . '-' . $loopIndex . '-' . $fluxLoopCardInstance;
@endphp

@if($isRenderableProduct)
    <li @php wc_product_class('flux-loop-product-item', $product); @endphp>
        <div class="flux-product-card-host h-full">
            <livewire:product-card :product-id="$product->get_id()" :key="$cardKey" />
        </div>
    </li>
@endif
