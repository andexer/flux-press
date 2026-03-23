@php
    defined('ABSPATH') || exit;
    do_action('woocommerce_before_cart');

    $cartItemsCount = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $cartSubtotal = WC()->cart ? WC()->cart->get_cart_subtotal() : '';
@endphp

<section class="flux-wc-cart-page max-w-7xl mx-auto px-4 sm:px-0 py-4 sm:py-6 lg:py-8">
    <div class="mb-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl" level="1" class="font-black tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ __('Tu carrito', 'flux-press') }}
                </flux:heading>
                <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                    {{ __('Revisa tus productos antes de finalizar la compra.', 'flux-press') }}
                </flux:text>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <span class="inline-flex items-center rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-3 py-2 text-xs sm:text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                    {{ sprintf(_n('%d producto', '%d productos', $cartItemsCount, 'flux-press'), $cartItemsCount) }}
                </span>
                @if($cartSubtotal !== '')
                    <span class="inline-flex items-center rounded-xl bg-accent-600/10 text-accent-700 dark:text-accent-400 px-3 py-2 text-xs sm:text-sm font-semibold">
                        {!! wp_kses_post($cartSubtotal) !!}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_24rem] gap-6 lg:gap-8">
        <div class="min-w-0">
            <form class="woocommerce-cart-form" action="{{ esc_url(wc_get_cart_url()) }}" method="post">
                @php do_action('woocommerce_before_cart_table'); @endphp

                <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="product-remove"><span class="screen-reader-text">{{ esc_html__('Remove item', 'woocommerce') }}</span></th>
                            <th class="product-thumbnail"><span class="screen-reader-text">{{ esc_html__('Thumbnail image', 'woocommerce') }}</span></th>
                            <th scope="col" class="product-name">{{ esc_html__('Product', 'woocommerce') }}</th>
                            <th scope="col" class="product-price">{{ esc_html__('Price', 'woocommerce') }}</th>
                            <th scope="col" class="product-quantity">{{ esc_html__('Quantity', 'woocommerce') }}</th>
                            <th scope="col" class="product-subtotal">{{ esc_html__('Subtotal', 'woocommerce') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php do_action('woocommerce_before_cart_contents'); @endphp

                        @php
                            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                                $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);

                                if (! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                                    continue;
                                }

                                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                        @endphp
                            <tr class="woocommerce-cart-form__cart-item {{ esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)) }}">
                                <td class="product-remove">
                                    {!! apply_filters(
                                        'woocommerce_cart_item_remove_link',
                                        sprintf(
                                            '<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                                            esc_url(wc_get_cart_remove_url($cart_item_key)),
                                            esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
                                            esc_attr($product_id),
                                            esc_attr($_product->get_sku())
                                        ),
                                        $cart_item_key
                                    ) !!}
                                </td>

                                <td class="product-thumbnail">
                                    @php $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key); @endphp
                                    @if(! $product_permalink)
                                        {!! $thumbnail !!}
                                    @else
                                        <a href="{{ esc_url($product_permalink) }}">{!! $thumbnail !!}</a>
                                    @endif
                                </td>

                                <td scope="row" role="rowheader" class="product-name" data-title="{{ esc_attr__('Product', 'woocommerce') }}">
                                    @if(! $product_permalink)
                                        {!! wp_kses_post($product_name . '&nbsp;') !!}
                                    @else
                                        {!! wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key)) !!}
                                    @endif

                                    @php do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key); @endphp

                                    {!! wc_get_formatted_cart_item_data($cart_item) !!}

                                    @if($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity']))
                                        {!! wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id)) !!}
                                    @endif
                                </td>

                                <td class="product-price" data-title="{{ esc_attr__('Price', 'woocommerce') }}">
                                    {!! apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key) !!}
                                </td>

                                <td class="product-quantity" data-title="{{ esc_attr__('Quantity', 'woocommerce') }}">
                                    @php
                                        if ($_product->is_sold_individually()) {
                                            $min_quantity = 1;
                                            $max_quantity = 1;
                                        } else {
                                            $min_quantity = 0;
                                            $max_quantity = $_product->get_max_purchase_quantity();
                                        }

                                        $product_quantity = woocommerce_quantity_input(
                                            [
                                                'input_name'   => "cart[{$cart_item_key}][qty]",
                                                'input_value'  => $cart_item['quantity'],
                                                'max_value'    => $max_quantity,
                                                'min_value'    => $min_quantity,
                                                'product_name' => $product_name,
                                            ],
                                            $_product,
                                            false
                                        );
                                    @endphp

                                    @php
                                        $quantityMarkup = apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item);
                                    @endphp

                                    @if($_product->is_sold_individually())
                                        {!! $quantityMarkup !!}
                                    @else
                                        {!! sprintf(
                                            '<div class="flux-cart-qty-control" data-cart-item="%1$s">
                                                <button type="button" class="flux-cart-qty-btn flux-cart-qty-btn--minus" data-direction="minus" aria-label="%2$s">-</button>
                                                <div class="flux-cart-qty-field">%3$s</div>
                                                <button type="button" class="flux-cart-qty-btn flux-cart-qty-btn--plus" data-direction="plus" aria-label="%4$s">+</button>
                                            </div>',
                                            esc_attr($cart_item_key),
                                            esc_attr__('Decrease quantity', 'flux-press'),
                                            $quantityMarkup,
                                            esc_attr__('Increase quantity', 'flux-press')
                                        ) !!}
                                    @endif
                                </td>

                                <td class="product-subtotal" data-title="{{ esc_attr__('Subtotal', 'woocommerce') }}">
                                    {!! apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key) !!}
                                </td>
                            </tr>
                        @php
                            }
                        @endphp

                        @php do_action('woocommerce_cart_contents'); @endphp

                        <tr>
                            <td colspan="6" class="actions">
                                <div class="flux-cart-actions-row">
                                    @if(wc_coupons_enabled())
                                        <div class="coupon">
                                            <label for="coupon_code" class="screen-reader-text">{{ esc_html__('Coupon:', 'woocommerce') }}</label>
                                            <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="{{ esc_attr__('Coupon code', 'woocommerce') }}" />
                                            <button type="submit" class="button{{ esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') }}" name="apply_coupon" value="{{ esc_attr__('Apply coupon', 'woocommerce') }}">{{ esc_html__('Apply coupon', 'woocommerce') }}</button>
                                            @php do_action('woocommerce_cart_coupon'); @endphp
                                        </div>
                                    @endif

                                    <div class="flux-cart-update-wrap">
                                        <button type="submit" class="button{{ esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') }}" name="update_cart" value="{{ esc_attr__('Update cart', 'woocommerce') }}">{{ esc_html__('Update cart', 'woocommerce') }}</button>
                                    </div>
                                </div>

                                @php do_action('woocommerce_cart_actions'); @endphp

                                @php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); @endphp
                            </td>
                        </tr>

                        @php do_action('woocommerce_after_cart_contents'); @endphp
                    </tbody>
                </table>

                @php do_action('woocommerce_after_cart_table'); @endphp
            </form>
        </div>

        <aside class="flux-wc-cart-sidebar min-w-0 space-y-5">
            <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 sm:p-6 shadow-sm">
                <flux:heading size="sm" class="uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">
                    {{ __('Resumen de compra', 'flux-press') }}
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Confirma cantidades, aplica cupones y continua al checkout cuando este todo listo.', 'flux-press') }}
                </flux:text>
            </div>

            @php do_action('woocommerce_before_cart_collaterals'); @endphp
            <div class="cart-collaterals rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 sm:p-6 shadow-sm">
                @php do_action('woocommerce_cart_collaterals'); @endphp
            </div>
        </aside>
    </div>
</section>

@php do_action('woocommerce_after_cart'); @endphp
