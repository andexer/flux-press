@php
    defined('ABSPATH') || exit;

    do_action('woocommerce_before_checkout_form', $checkout);

    if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
        echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
        return;
    }
@endphp

<section class="flux-wc-checkout-page max-w-7xl mx-auto px-4 sm:px-0 py-4 sm:py-6 lg:py-8">
    <div class="mb-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <flux:heading size="xl" level="1" class="font-black tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ __('Complete Purchase', 'sage') }}
                </flux:heading>
                <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                    {{ __('Complete your details, confirm your order and pay securely.', 'sage') }}
                </flux:text>
            </div>

            <ol class="grid grid-cols-3 gap-2 sm:gap-3 min-w-0 w-full lg:w-auto">
                <li class="rounded-xl border border-accent-300/60 bg-accent-50 dark:bg-accent-500/10 px-3 py-2 text-xs font-semibold text-accent-700 dark:text-accent-400 text-center">{{ __('Details', 'sage') }}</li>
                <li class="rounded-xl border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300 text-center">{{ __('Review', 'sage') }}</li>
                <li class="rounded-xl border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300 text-center">{{ __('Payment', 'sage') }}</li>
            </ol>
        </div>
    </div>

    <form
        name="checkout"
        method="post"
        class="checkout woocommerce-checkout flux-wc-checkout-form"
        action="{{ esc_url(wc_get_checkout_url()) }}"
        enctype="multipart/form-data"
        aria-label="{{ esc_attr__('Checkout', 'woocommerce') }}"
    >
        @if($checkout->get_checkout_fields())
            @php do_action('woocommerce_checkout_before_customer_details'); @endphp

            <div class="col2-set" id="customer_details">
                <div class="col-1 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 sm:p-7 shadow-sm">
                    <div class="mb-5 flex items-center gap-2">
                        <flux:icon.user-circle class="size-5 text-accent-600 dark:text-accent-400" />
                        <flux:heading size="sm" class="uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Billing Information', 'sage') }}</flux:heading>
                    </div>
                    @php do_action('woocommerce_checkout_billing'); @endphp
                </div>

                <div class="col-2 mt-2 sm:mt-3 lg:mt-0 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 sm:p-7 shadow-sm">
                    <div class="mb-5 flex items-center gap-2">
                        <flux:icon.map-pin class="size-5 text-accent-600 dark:text-accent-400" />
                        <flux:heading size="sm" class="uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Shipping & Delivery', 'sage') }}</flux:heading>
                    </div>
                    @php do_action('woocommerce_checkout_shipping'); @endphp
                </div>
            </div>

            @php do_action('woocommerce_checkout_after_customer_details'); @endphp
        @endif

        @php do_action('woocommerce_checkout_before_order_review_heading'); @endphp

        <div class="mt-6 sm:mt-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 sm:p-7 shadow-sm">
            <div class="mb-5 flex items-center gap-2">
                <flux:icon.clipboard-document-check class="size-5 text-accent-600 dark:text-accent-400" />
                <h3 id="order_review_heading">{{ esc_html__('Your order', 'woocommerce') }}</h3>
            </div>

            @php do_action('woocommerce_checkout_before_order_review'); @endphp

            <div id="order_review" class="woocommerce-checkout-review-order">
                @php do_action('woocommerce_checkout_order_review'); @endphp
            </div>

            @php do_action('woocommerce_checkout_after_order_review'); @endphp
        </div>
    </form>
</section>

@php do_action('woocommerce_after_checkout_form', $checkout); @endphp
