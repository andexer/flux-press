@php
    defined('ABSPATH') || exit;

    do_action('woocommerce_before_edit_account_form');

    $currentUserId = get_current_user_id();
    $ordersCount = function_exists('wc_get_customer_order_count') ? (int) wc_get_customer_order_count($currentUserId) : 0;
    $registeredAtRaw = (string) get_the_author_meta('user_registered', $currentUserId);
    $registeredAtTs = $registeredAtRaw !== '' ? strtotime($registeredAtRaw) : false;
    $registeredAt = $registeredAtTs ? wp_date(get_option('date_format'), $registeredAtTs) : __('Not available', 'flux-press');
    $editAddressUrl = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('edit-address') : '';
    $ordersUrl = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('orders') : '';
    $downloadsUrl = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('downloads') : '';
@endphp

<section class="flux-account-edit">
    <header class="flux-account-edit-hero">
        <div class="flux-account-edit-hero__media">
            <livewire:profile-photo />
        </div>

        <div class="flux-account-edit-hero__content">
            <p class="flux-account-edit-hero__kicker">{{ __('Social Profile', 'flux-press') }}</p>
            <h1 class="flux-account-edit-hero__title">{{ esc_html($user->display_name) }}</h1>
            <p class="flux-account-edit-hero__subtitle">{{ esc_html($user->user_email) }}</p>
            <div class="flux-account-edit-hero__chips">
                <span>{{ sprintf(_n('%d order', '%d orders', $ordersCount, 'flux-press'), $ordersCount) }}</span>
                <span>{{ __('Member since', 'flux-press') }} {{ esc_html($registeredAt) }}</span>
            </div>
        </div>
    </header>

    <form class="woocommerce-EditAccountForm edit-account flux-edit-account-form" action="" method="post" @php do_action('woocommerce_edit_account_form_tag'); @endphp>
        @php do_action('woocommerce_edit_account_form_start'); @endphp

        <div class="flux-edit-account-grid">
            <aside class="flux-edit-account-aside">
                <section class="flux-edit-card flux-edit-card--profile">
                    <p class="flux-edit-card__kicker">{{ __('Profile', 'flux-press') }}</p>
                    <h2 class="flux-edit-card__title">{{ esc_html($user->display_name) }}</h2>
                    <p class="flux-edit-card__text">{{ esc_html($user->user_email) }}</p>

                    <dl class="flux-edit-meta-grid">
                        <div>
                            <dt>{{ __('Username', 'flux-press') }}</dt>
                            <dd>{{ esc_html($user->user_login) }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Orders', 'flux-press') }}</dt>
                            <dd>{{ esc_html((string) $ordersCount) }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Member', 'flux-press') }}</dt>
                            <dd>{{ esc_html($registeredAt) }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Status', 'flux-press') }}</dt>
                            <dd>{{ __('Active', 'flux-press') }}</dd>
                        </div>
                    </dl>

                    <div class="flux-edit-shortcuts">
                        @if($ordersUrl !== '')
                            <a href="{{ esc_url($ordersUrl) }}" class="flux-edit-shortcut-link">{{ __('View orders', 'flux-press') }}</a>
                        @endif
                        @if($editAddressUrl !== '')
                            <a href="{{ esc_url($editAddressUrl) }}" class="flux-edit-shortcut-link">{{ __('Edit addresses', 'flux-press') }}</a>
                        @endif
                        @if($downloadsUrl !== '')
                            <a href="{{ esc_url($downloadsUrl) }}" class="flux-edit-shortcut-link">{{ __('My downloads', 'flux-press') }}</a>
                        @endif
                    </div>
                </section>
            </aside>

            <div class="flux-edit-account-main">
                <section class="flux-edit-card">
                    <div class="flux-edit-card__header">
                        <flux:icon.user class="size-5 text-accent-600 dark:text-accent-400" />
                        <div>
                            <h3>{{ __('Public profile', 'flux-press') }}</h3>
                            <p>{{ __('Name shown in your account and reviews.', 'flux-press') }}</p>
                        </div>
                    </div>

                    <div class="flux-edit-row-grid">
                        <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                            <label for="account_first_name">{{ esc_html__('First name', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span></label>
                            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="{{ esc_attr($user->first_name) }}" aria-required="true" />
                        </p>

                        <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                            <label for="account_last_name">{{ esc_html__('Last name', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span></label>
                            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="{{ esc_attr($user->last_name) }}" aria-required="true" />
                        </p>
                    </div>

                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="account_display_name">{{ esc_html__('Display name', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span></label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" aria-describedby="account_display_name_description" value="{{ esc_attr($user->display_name) }}" aria-required="true" />
                        <span id="account_display_name_description"><em>{{ esc_html__('This will be how your name will be displayed in the account section and in reviews', 'woocommerce') }}</em></span>
                    </p>
                </section>

                <section class="flux-edit-card">
                    <div class="flux-edit-card__header">
                        <flux:icon.envelope class="size-5 text-accent-600 dark:text-accent-400" />
                        <div>
                            <h3>{{ __('Contact', 'flux-press') }}</h3>
                            <p>{{ __('Manage your email and extra account fields.', 'flux-press') }}</p>
                        </div>
                    </div>

                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="account_email">{{ esc_html__('Email address', 'woocommerce') }}&nbsp;<span class="required" aria-hidden="true">*</span></label>
                        <input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="{{ esc_attr($user->user_email) }}" aria-required="true" />
                    </p>

                    @php
                        /**
                         * Hook where additional fields should be rendered.
                         *
                         * @since 8.7.0
                         */
                        do_action('woocommerce_edit_account_form_fields');
                    @endphp
                </section>

                <section class="flux-edit-card">
                    <div class="flux-edit-card__header">
                        <flux:icon.shield-check class="size-5 text-accent-600 dark:text-accent-400" />
                        <div>
                            <h3>{{ __('Security', 'flux-press') }}</h3>
                            <p>{{ __('Leave password fields empty if you do not want to change it.', 'flux-press') }}</p>
                        </div>
                    </div>

                    <fieldset class="flux-edit-password-fieldset">
                        <legend>{{ esc_html__('Password change', 'woocommerce') }}</legend>

                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="password_current">{{ esc_html__('Current password (leave blank to leave unchanged)', 'woocommerce') }}</label>
                            <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="current-password" />
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="password_1">{{ esc_html__('New password (leave blank to leave unchanged)', 'woocommerce') }}</label>
                            <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="new-password" />
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="password_2">{{ esc_html__('Confirm new password', 'woocommerce') }}</label>
                            <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="new-password" />
                        </p>
                    </fieldset>
                </section>
            </div>
        </div>

        @php
            /**
             * My Account edit account form.
             *
             * @since 2.6.0
             */
            do_action('woocommerce_edit_account_form');
        @endphp

        <div class="flux-edit-account-submit">
            @php wp_nonce_field('save_account_details', 'save-account-details-nonce'); @endphp
            <button type="submit" class="woocommerce-Button button{{ esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') }}" name="save_account_details" value="{{ esc_attr__('Save changes', 'woocommerce') }}">{{ esc_html__('Save changes', 'woocommerce') }}</button>
            <input type="hidden" name="action" value="save_account_details" />
        </div>

        @php do_action('woocommerce_edit_account_form_end'); @endphp
    </form>
</section>

@php do_action('woocommerce_after_edit_account_form'); @endphp
