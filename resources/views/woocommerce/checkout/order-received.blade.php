@php
    defined('ABSPATH') || exit;

    $message = apply_filters(
        'woocommerce_thankyou_order_received_text',
        esc_html(__('Thank you. Your order has been received.', 'woocommerce')),
        $order
    );
@endphp

<div class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/20 p-5">
    <div class="flex items-start gap-3">
        <flux:icon.check-circle class="size-6 text-emerald-600 dark:text-emerald-400 mt-0.5" />
        <div>
            <flux:heading size="sm" class="text-emerald-800 dark:text-emerald-300 mb-1">
                {{ __('Order Confirmed', 'sage') }}
            </flux:heading>
            <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received !mb-0 !border-none !bg-transparent !p-0 text-sm text-emerald-800 dark:text-emerald-200">
                {!! $message !!}
            </p>
        </div>
    </div>
</div>
