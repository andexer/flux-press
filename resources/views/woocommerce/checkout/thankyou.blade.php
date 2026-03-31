@php
    defined('ABSPATH') || exit;
@endphp

<section class="flux-wc-thankyou-page max-w-4xl mx-auto px-4 sm:px-0 py-2 sm:py-4">
    <div class="woocommerce-order rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 sm:p-8 shadow-sm">
        @if($order)
            @php do_action('woocommerce_before_thankyou', $order->get_id()); @endphp

            @if($order->has_status('failed'))
                <div class="rounded-2xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/20 p-5 mb-6">
                    <div class="flex items-start gap-3">
                        <flux:icon.x-circle class="size-6 text-red-500 mt-0.5" />
                        <div>
                            <flux:heading size="sm" class="text-red-700 dark:text-red-300 mb-1">
                                {{ __('We could not process your order', 'sage') }}
                            </flux:heading>
                            <flux:text class="text-red-700/90 dark:text-red-200 text-sm">
                                {{ __('The transaction was rejected by the bank or payment provider. You can try again.', 'sage') }}
                            </flux:text>
                        </div>
                    </div>
                </div>

                <div class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions flex flex-wrap gap-3">
                    <a href="{{ esc_url($order->get_checkout_payment_url()) }}" class="button pay">{{ esc_html__('Pay', 'woocommerce') }}</a>
                    @if(is_user_logged_in())
                        <a href="{{ esc_url(wc_get_page_permalink('myaccount')) }}" class="button pay">{{ esc_html__('My account', 'woocommerce') }}</a>
                    @endif
                </div>
            @else
                @php wc_get_template('checkout/order-received.php', ['order' => $order]); @endphp

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <p class="text-[11px] uppercase tracking-widest font-semibold text-zinc-500 dark:text-zinc-400">{{ esc_html__('Order number:', 'woocommerce') }}</p>
                        <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->get_order_number() }}</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <p class="text-[11px] uppercase tracking-widest font-semibold text-zinc-500 dark:text-zinc-400">{{ esc_html__('Date:', 'woocommerce') }}</p>
                        <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{!! wc_format_datetime($order->get_date_created()) !!}</p>
                    </div>

                    @if(is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email())
                        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4">
                            <p class="text-[11px] uppercase tracking-widest font-semibold text-zinc-500 dark:text-zinc-400">{{ esc_html__('Email:', 'woocommerce') }}</p>
                            <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->get_billing_email() }}</p>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-accent-300/60 dark:border-accent-500/50 bg-accent-50 dark:bg-accent-500/10 p-4">
                        <p class="text-[11px] uppercase tracking-widest font-semibold text-accent-700 dark:text-accent-400">{{ esc_html__('Total:', 'woocommerce') }}</p>
                        <p class="mt-1 font-bold text-accent-700 dark:text-accent-400">{!! $order->get_formatted_order_total() !!}</p>
                    </div>

                    @if($order->get_payment_method_title())
                        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 sm:col-span-2">
                            <p class="text-[11px] uppercase tracking-widest font-semibold text-zinc-500 dark:text-zinc-400">{{ esc_html__('Payment method:', 'woocommerce') }}</p>
                            <p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{!! wp_kses_post($order->get_payment_method_title()) !!}</p>
                        </div>
                    @endif
                </div>
            @endif

            @php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); @endphp
            @php do_action('woocommerce_thankyou', $order->get_id()); @endphp
        @else
            @php wc_get_template('checkout/order-received.php', ['order' => false]); @endphp
        @endif
    </div>
</section>
