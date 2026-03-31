{{-- Footer Variant: Corporate --}}
@php
    $homeUrl = (string) home_url('/');
    $blogPageId = (int) get_option('page_for_posts');
    $blogUrl = $blogPageId > 0 ? (string) get_permalink($blogPageId) : $homeUrl;
    $shopUrl = function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('shop') : $homeUrl;

    $quickItems = [];
    foreach ((array) ($quickLinks ?? []) as $item) {
        if (! is_object($item) || ! isset($item->url, $item->title)) {
            continue;
        }

        $quickItems[] = [
            'title' => (string) $item->title,
            'url'   => (string) $item->url,
        ];
    }

    if (empty($quickItems)) {
        $quickItems = [
            ['title' => __('Home', 'sage'), 'url' => $homeUrl],
            ['title' => __('Shop', 'sage'), 'url' => $shopUrl],
            ['title' => __('Blog', 'sage'), 'url' => $blogUrl],
        ];
    }

    $resourceItems = [];
    foreach ((array) ($resourcesMenu ?? []) as $item) {
        if (! is_object($item) || ! isset($item->url, $item->title)) {
            continue;
        }

        $resourceItems[] = [
            'title' => (string) $item->title,
            'url'   => (string) $item->url,
        ];
    }

    if (empty($resourceItems)) {
        $resourceItems = array_slice($quickItems, 0, 3);
    }

    $socialItems = array_values(array_filter(
        (array) ($socialLinks ?? []),
        static fn ($social): bool => is_array($social) && ! empty($social['url'])
    ));

    $newsletterAction = (string) apply_filters('flux_footer_newsletter_action', $homeUrl);
    $newsletterMethod = strtolower((string) apply_filters('flux_footer_newsletter_method', 'post'));
    if (! in_array($newsletterMethod, ['post', 'get'], true)) {
        $newsletterMethod = 'post';
    }

    $privacyUrl = function_exists('get_privacy_policy_url') ? (string) get_privacy_policy_url() : '';
    if ($privacyUrl === '') {
        $privacyUrl = (string) apply_filters('flux_footer_privacy_url', $homeUrl);
    }

    $termsUrl = (string) apply_filters('flux_footer_terms_url', (string) home_url('/terms'));
    $cookiesUrl = (string) apply_filters('flux_footer_cookies_url', (string) home_url('/cookies'));

    $legalItems = array_values(array_filter([
        ['label' => __('Privacy', 'sage'), 'url' => $privacyUrl],
        ['label' => __('Terms', 'sage'), 'url' => $termsUrl],
        ['label' => __('Cookies', 'sage'), 'url' => $cookiesUrl],
    ], static fn (array $item): bool => (string) ($item['url'] ?? '') !== ''));
@endphp

<footer class="flux-footer-corporate relative bg-zinc-950 border-t border-zinc-800 overflow-hidden text-zinc-300">
    <div class="absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-accent-400/50 to-transparent"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[860px] h-[420px] bg-accent-500/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-14 lg:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-[1.3fr_1fr_1fr_1.2fr] gap-8 lg:gap-10">
            <div class="min-w-0">
                <div class="mb-4">
                    <flux:brand
                        href="{{ $homeUrl }}"
                        :name="$siteName ?? get_bloginfo('name')"
                        :logo="$logoUrl ?? null"
                        class="!text-white"
                    />
                </div>

                <flux:text class="text-zinc-400 max-w-sm leading-relaxed">
                    {{ __('Premium high-performance theme with Laravel, Livewire and Flux UI components.', 'sage') }}
                </flux:text>

                @if(! empty($socialItems))
                    <div class="mt-5 flex items-center flex-wrap gap-2">
                        @foreach($socialItems as $social)
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="{{ $social['icon'] ?? 'link' }}"
                                href="{{ $social['url'] }}"
                                aria-label="{{ esc_attr((string) ($social['label'] ?? __('Social network', 'sage'))) }}"
                                class="!text-zinc-400 hover:!text-white"
                            />
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="hidden lg:block min-w-0">
                <flux:heading size="sm" class="!text-white mb-4 uppercase tracking-wider">{{ __('Quick Links', 'sage') }}</flux:heading>
                <ul class="space-y-2.5">
                    @foreach($quickItems as $item)
                        <li>
                            <a href="{{ esc_url($item['url']) }}" class="text-sm text-zinc-400 hover:text-white transition-colors">
                                {{ $item['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="hidden lg:block min-w-0">
                <flux:heading size="sm" class="!text-white mb-4 uppercase tracking-wider">{{ __('Resources', 'sage') }}</flux:heading>
                <ul class="space-y-2.5">
                    @foreach($resourceItems as $item)
                        <li>
                            <a href="{{ esc_url($item['url']) }}" class="text-sm text-zinc-400 hover:text-white transition-colors">
                                {{ $item['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="hidden lg:block min-w-0">
                <flux:heading size="sm" class="!text-white mb-4 uppercase tracking-wider">{{ __('Newsletter', 'sage') }}</flux:heading>
                <flux:text class="text-zinc-400 mb-4 leading-relaxed">
                    {{ __('Subscribe to our newsletter for news and releases.', 'sage') }}
                </flux:text>

                <form action="{{ esc_url($newsletterAction) }}" method="{{ esc_attr($newsletterMethod) }}" class="flex gap-2">
                    <label for="footer-newsletter-email-desktop" class="sr-only">{{ __('Email', 'sage') }}</label>
                    <input
                        id="footer-newsletter-email-desktop"
                        name="email"
                        type="email"
                        required
                        placeholder="{{ esc_attr__('your@email.com', 'sage') }}"
                        class="min-w-0 flex-1 rounded-xl border border-zinc-700 bg-zinc-900/80 text-zinc-100 px-3 py-2.5 text-sm placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-accent-500/40"
                    />
                    <flux:button type="submit" icon="paper-airplane" aria-label="{{ esc_attr__('Subscribe', 'sage') }}" />
                </form>
            </div>

            <div class="lg:hidden col-span-full mt-1 border-t border-zinc-800/80 divide-y divide-zinc-800/80">
                <details class="group py-3" open>
                    <summary class="list-none cursor-pointer flex items-center justify-between gap-4 text-sm font-semibold text-zinc-100">
                        {{ __('Quick Links', 'sage') }}
                        <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform duration-200 group-open:rotate-180" />
                    </summary>
                    <ul class="mt-3 space-y-2">
                        @foreach($quickItems as $item)
                            <li>
                                <a href="{{ esc_url($item['url']) }}" class="text-sm text-zinc-400 hover:text-white transition-colors">
                                    {{ $item['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </details>

                <details class="group py-3">
                    <summary class="list-none cursor-pointer flex items-center justify-between gap-4 text-sm font-semibold text-zinc-100">
                        {{ __('Resources', 'sage') }}
                        <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform duration-200 group-open:rotate-180" />
                    </summary>
                    <ul class="mt-3 space-y-2">
                        @foreach($resourceItems as $item)
                            <li>
                                <a href="{{ esc_url($item['url']) }}" class="text-sm text-zinc-400 hover:text-white transition-colors">
                                    {{ $item['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </details>

                <details class="group py-3">
                    <summary class="list-none cursor-pointer flex items-center justify-between gap-4 text-sm font-semibold text-zinc-100">
                        {{ __('Newsletter', 'sage') }}
                        <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform duration-200 group-open:rotate-180" />
                    </summary>
                    <div class="mt-3">
                        <form action="{{ esc_url($newsletterAction) }}" method="{{ esc_attr($newsletterMethod) }}" class="flex gap-2">
                            <label for="footer-newsletter-email-mobile" class="sr-only">{{ __('Email', 'sage') }}</label>
                            <input
                                id="footer-newsletter-email-mobile"
                                name="email"
                                type="email"
                                required
                                placeholder="{{ esc_attr__('your@email.com', 'sage') }}"
                                class="min-w-0 flex-1 rounded-xl border border-zinc-700 bg-zinc-900/80 text-zinc-100 px-3 py-2.5 text-sm placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-accent-500/40"
                            />
                            <flux:button type="submit" icon="paper-airplane" aria-label="{{ esc_attr__('Subscribe', 'sage') }}" />
                        </form>
                    </div>
                </details>
            </div>
        </div>

        @if ($footerWidgets)
            <section class="mt-10 sm:mt-12 pt-8 border-t border-zinc-800/80">
                <flux:heading size="sm" class="!text-white uppercase tracking-wider">{{ __('More Content', 'sage') }}</flux:heading>
                <div class="flux-footer-widgets mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
                    @php(dynamic_sidebar('sidebar-footer'))
                </div>
            </section>
        @endif
    </div>

    <div class="border-t border-zinc-800/90 bg-black/35">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col md:flex-row items-center justify-between gap-3">
            <flux:text class="text-zinc-500 text-center md:text-left">
                &copy; {{ $currentYear }} <span class="text-zinc-200 font-medium">{!! $siteName !!}</span>. {{ __('All rights reserved.', 'sage') }}
            </flux:text>

            @if(! empty($legalItems))
                <nav aria-label="{{ esc_attr__('Legal', 'flux-press') }}" class="flex flex-wrap items-center justify-center gap-3 sm:gap-5">
                    @foreach($legalItems as $item)
                        <a href="{{ esc_url((string) $item['url']) }}" class="text-sm text-zinc-500 hover:text-zinc-200 transition-colors">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>
</footer>
