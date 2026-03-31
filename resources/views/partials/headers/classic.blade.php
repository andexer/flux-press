@php
    $isWooCommerce = class_exists('WooCommerce');
    $isAccountSidebarContext = $isWooCommerce && is_account_page() && is_user_logged_in();
    $megaMenuOptions = isset($megaMenuConfig) && is_array($megaMenuConfig) ? $megaMenuConfig : [];
    $megaMenuEnabled = (bool) ($megaMenuOptions['enabled'] ?? true);

    $topMenuItems = [];
    $childMenuItemsByParent = [];

    if (isset($menuItems) && is_array($menuItems)) {
        foreach ($menuItems as $item) {
            if (! is_object($item)) {
                continue;
            }

            $itemId = (string) ($item->ID ?? '');
            if ($itemId === '') {
                continue;
            }

            $parentId = (string) ($item->menu_item_parent ?? '0');
            if ($parentId === '' || $parentId === '0') {
                $topMenuItems[] = $item;
                continue;
            }

            $childMenuItemsByParent[$parentId][] = $item;
        }
    }

    $highlightLinks = [];
    if (isset($highlightMenuItems) && is_array($highlightMenuItems)) {
        foreach ($highlightMenuItems as $item) {
            if (is_object($item) && ! empty($item->title) && ! empty($item->url)) {
                $highlightLinks[] = $item;
            }
        }
    }

    if (empty($highlightLinks)) {
        $highlightLinks = array_slice($topMenuItems, 0, 6);
    }

    if (empty($highlightLinks)) {
        $highlightLinks = [
            (object) ['title' => __('Categories', 'sage'), 'url' => home_url('/shop/')],
            (object) ['title' => __('New Arrivals', 'sage'), 'url' => home_url('/shop/?orderby=date')],
            (object) ['title' => __('Clearance', 'sage'), 'url' => home_url('/shop/?on_sale=1')],
            (object) ['title' => __('Top Sellers', 'sage'), 'url' => home_url('/shop/?orderby=popularity')],
        ];
    }

    $utilityLeftLinks = [];
    if (isset($utilityLeftMenuItems) && is_array($utilityLeftMenuItems)) {
        foreach ($utilityLeftMenuItems as $item) {
            if (is_object($item) && ! empty($item->title) && ! empty($item->url)) {
                $utilityLeftLinks[] = ['title' => $item->title, 'url' => $item->url];
            }
        }
    }

    if (empty($utilityLeftLinks)) {
        $utilityLeftLinks = [
            ['title' => __('Daily Deals', 'sage'), 'url' => home_url('/shop/?on_sale=1')],
            ['title' => __('Gift Cards', 'sage'), 'url' => home_url('/shop/')],
            ['title' => __('Sell on Store', 'sage'), 'url' => home_url('/vender/')],
        ];
    }

    $utilityRightLinks = [];
    if (isset($utilityRightMenuItems) && is_array($utilityRightMenuItems)) {
        foreach ($utilityRightMenuItems as $item) {
            if (is_object($item) && ! empty($item->title) && ! empty($item->url)) {
                $utilityRightLinks[] = ['title' => $item->title, 'url' => $item->url];
            }
        }
    }

    if (empty($utilityRightLinks)) {
        $utilityRightLinks = [
            ['title' => __('Help Center', 'sage'), 'url' => home_url('/ayuda/')],
            ['title' => __('Track Order', 'sage'), 'url' => home_url('/mi-cuenta/orders/')],
            ['title' => __('EN / USD', 'sage'), 'url' => '#'],
        ];
    }

    $isLoggedIn = is_user_logged_in();
    $shopUrl = $isWooCommerce ? wc_get_page_permalink('shop') : home_url('/');
    $accountUrl = $isWooCommerce ? wc_get_account_endpoint_url('dashboard') : admin_url('profile.php');
    $loginUrl = $isWooCommerce ? wc_get_account_endpoint_url('dashboard') : wp_login_url();

    $actionMenuItemsResolved = [];
    if (isset($actionMenuItems) && is_array($actionMenuItems)) {
        foreach ($actionMenuItems as $item) {
            if (is_object($item) && ! empty($item->title) && ! empty($item->url)) {
                $actionMenuItemsResolved[] = $item;
            }
        }
    }

    $hasActionMenuItems = ! empty($actionMenuItemsResolved);
@endphp

<flux:header
    @class([
        'flux-header-shell flux-header-mobile-compact',
        'sticky top-0' => $sticky ?? false,
        'relative' => ! ($sticky ?? false),
    ])
>
    <div class="w-full">
        @if(! $isAccountSidebarContext)
            <div class="flux-header-topbar hidden xl:block">
                <div class="flex w-full items-center justify-between gap-4 py-2 text-xs">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                        @foreach($utilityLeftLinks as $link)
                            <a href="{{ $link['url'] }}" wire:navigate class="font-semibold text-zinc-600 transition-colors hover:text-accent-700 dark:text-zinc-300 dark:hover:text-accent-300">
                                {{ $link['title'] }}
                            </a>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                        @foreach($utilityRightLinks as $link)
                            <a href="{{ $link['url'] }}" wire:navigate class="font-semibold text-zinc-600 transition-colors hover:text-accent-700 dark:text-zinc-300 dark:hover:text-accent-300">
                                {{ $link['title'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="flux-header-main-wrap">
            <div class="flex items-center justify-between gap-2 sm:gap-3 lg:gap-4">
                <div class="flex min-w-0 items-center gap-2 lg:gap-3">
                    @if($isAccountSidebarContext)
                        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />
                    @endif

                    @if(! $isAccountSidebarContext && ! empty($topMenuItems))
                        <flux:modal.trigger name="classic-mobile-menu">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="bars-3-bottom-left"
                                class="lg:hidden"
                                aria-label="{{ esc_attr__('Open main menu', 'sage') }}"
                            />
                        </flux:modal.trigger>
                    @endif

                    <a href="{{ home_url('/') }}" wire:navigate class="inline-flex min-w-0 items-center gap-2 rounded-xl px-1 py-1 text-sm font-black tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-base">
                        @if(! empty($logoUrl))
                            <img src="{{ $logoUrl }}" alt="{{ esc_attr($siteName ?? get_bloginfo('name')) }}" class="h-7 w-auto shrink-0">
                        @else
                            <span class="size-2 rounded-full bg-accent-500"></span>
                        @endif
                        <span class="truncate max-[430px]:hidden">{{ $siteName ?? get_bloginfo('name') }}</span>
                    </a>

                    @if(! $isAccountSidebarContext && ! $megaMenuEnabled && ! empty($topMenuItems))
                        <nav class="hidden 2xl:flex items-center gap-1">
                            @foreach(array_slice($topMenuItems, 0, 6) as $item)
                                <a href="{{ $item->url }}" wire:navigate class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:hover:text-zinc-50">
                                    {{ $item->title }}
                                </a>
                            @endforeach
                        </nav>
                    @endif
                </div>

                <div class="hidden min-w-0 flex-1 md:block">
                    <livewire:global-search variant="market" :show-scope="true" />
                </div>

                <div class="flex items-center gap-1 sm:gap-2">
                    @if($hasActionMenuItems)
                        @include('partials.headers.action-links', [
                            'items' => $actionMenuItemsResolved,
                            'containerClass' => 'hidden xl:flex items-center gap-1',
                            'linkClass' => 'inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:hover:text-zinc-50',
                        ])
                    @elseif($isWooCommerce)
                        <flux:button variant="ghost" size="sm" href="{{ $shopUrl }}" wire:navigate icon="building-storefront" class="hidden xl:inline-flex">
                            {{ __('Shop', 'sage') }}
                        </flux:button>
                    @endif

                    @if($isWooCommerce)
                        <livewire:cart-icon />
                    @endif

                    @if(current_user_can('manage_options'))
                        <livewire:theme-settings />
                    @endif

                    @if($isLoggedIn)
                        @php
                            $currentUser = wp_get_current_user();
                            $customPhotoId = (int) get_user_meta($currentUser->ID, 'flux_profile_photo_id', true);
                            $avatarUrl = $customPhotoId
                                ? wp_get_attachment_image_url($customPhotoId, 'thumbnail')
                                : null;
                            $avatarUrl = $avatarUrl ?: get_avatar_url($currentUser->ID, ['size' => 64]);
                        @endphp
                        <flux:dropdown position="bottom" align="end">
                            <flux:profile avatar="{{ $avatarUrl }}" name="{{ $currentUser->display_name }}" />

                            <flux:menu class="w-64">
                                <flux:menu.item icon="user-circle" href="{{ admin_url('profile.php') }}">{{ __('Profile', 'sage') }}</flux:menu.item>
                                @if($isWooCommerce)
                                    <flux:menu.item icon="user" href="{{ $accountUrl }}" wire:navigate>{{ __('My Account', 'sage') }}</flux:menu.item>
                                @endif
                                <flux:menu.separator />
                                <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" href="{{ wp_logout_url(home_url('/')) }}">
                                    {{ __('Sign Out', 'sage') }}
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    @else
                        <flux:button variant="primary" size="sm" href="{{ $loginUrl }}" icon="user">{{ __('Sign In', 'sage') }}</flux:button>
                    @endif
                </div>
            </div>

            <div class="flux-header-mobile-search-row">
                <livewire:global-search variant="market" :show-scope="true" />
            </div>

            @if(! $isAccountSidebarContext)
                <div class="flux-header-divider mt-3 hidden lg:flex flex-wrap items-center justify-between gap-2 border-t pt-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach(array_slice($highlightLinks, 0, 7) as $item)
                            <a
                                href="{{ $item->url }}"
                                wire:navigate
                                class="flux-header-chip"
                            >
                                <span class="size-1.5 rounded-full bg-accent-500"></span>
                                <span>{{ $item->title }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="hidden xl:flex items-center gap-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                        @foreach(array_slice($utilityRightLinks, 0, 3) as $link)
                            <a href="{{ $link['url'] }}" wire:navigate class="transition-colors hover:text-accent-600 dark:hover:text-accent-300">{{ $link['title'] }}</a>
                        @endforeach
                    </div>
                </div>

                @if($megaMenuEnabled)
                    <div class="flux-header-divider mt-3 hidden lg:flex border-t pt-3">
                        <livewire:mega-menu :items="$megaMenuItems ?? []" :config="$megaMenuOptions" />
                    </div>
                @endif
            @endif
        </div>
    </div>
</flux:header>

@if(! $isAccountSidebarContext && ! empty($topMenuItems))
    <flux:modal name="classic-mobile-menu" variant="flyout" class="max-w-sm w-full !p-0">
        <div class="flex h-full flex-col bg-white dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-3 border-b border-zinc-200 dark:border-zinc-800 px-4 py-4">
                <a href="{{ home_url('/') }}" wire:navigate class="inline-flex min-w-0 items-center gap-2 text-sm font-black tracking-tight text-zinc-900 dark:text-zinc-100">
                    @if(! empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="{{ esc_attr($siteName ?? get_bloginfo('name')) }}" class="h-7 w-auto shrink-0">
                    @endif
                    <span class="truncate">{{ $siteName ?? get_bloginfo('name') }}</span>
                </a>

                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" size="sm" aria-label="{{ esc_attr__('Close menu', 'sage') }}" />
                </flux:modal.close>
            </div>

            <div class="border-b border-zinc-200/70 dark:border-zinc-800/80 px-4 py-3">
                <livewire:global-search variant="market" :show-scope="true" />
            </div>

            <div class="border-b border-zinc-200/70 dark:border-zinc-800/80 p-3">
                <div class="grid grid-cols-2 gap-2">
                    @foreach(array_slice($highlightLinks, 0, 6) as $item)
                        <a href="{{ $item->url }}" wire:navigate class="inline-flex items-center justify-center rounded-xl bg-zinc-100 px-2 py-2 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            {{ $item->title }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-3 space-y-3">
                @foreach($topMenuItems as $item)
                    @php
                        $itemId = (string) ($item->ID ?? '');
                        $children = $childMenuItemsByParent[$itemId] ?? [];
                    @endphp

                    <div class="rounded-xl border border-zinc-200/80 dark:border-zinc-700/80 bg-zinc-50/70 dark:bg-zinc-800/30 p-2">
                        <a
                            href="{{ $item->url }}"
                            wire:navigate
                            class="flex items-center justify-between rounded-lg px-2.5 py-2 text-sm font-semibold text-zinc-900 transition-colors hover:bg-white dark:text-zinc-100 dark:hover:bg-zinc-900"
                        >
                            <span>{{ $item->title }}</span>
                            <flux:icon.arrow-up-right class="size-4 text-zinc-400" />
                        </a>

                        @if(! empty($children))
                            <div class="mt-1 space-y-1 px-1 pb-1">
                                @foreach($children as $child)
                                    <a
                                        href="{{ $child->url }}"
                                        wire:navigate
                                        class="block rounded-lg px-2 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-white hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-zinc-100"
                                    >
                                        {{ $child->title }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-auto border-t border-zinc-200 dark:border-zinc-800 px-4 py-4">
                @if($hasActionMenuItems)
                    @include('partials.headers.action-links', [
                        'items' => $actionMenuItemsResolved,
                        'containerClass' => 'grid gap-2',
                        'linkClass' => 'inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-semibold text-zinc-700 transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700',
                    ])

                    <div class="mt-2">
                        <flux:button href="{{ $isLoggedIn ? $accountUrl : $loginUrl }}" wire:navigate variant="primary" icon="user" class="w-full justify-center">
                            {{ $isLoggedIn ? __('Account', 'sage') : __('Sign In', 'sage') }}
                        </flux:button>
                    </div>
                @else
                    <div @class([
                        'grid gap-2',
                        'grid-cols-2' => $isWooCommerce,
                        'grid-cols-1' => ! $isWooCommerce,
                    ])>
                        @if($isWooCommerce)
                            <flux:button href="{{ $shopUrl }}" wire:navigate variant="ghost" icon="building-storefront" class="justify-center">
                                {{ __('Shop', 'sage') }}
                            </flux:button>
                        @endif
                        <flux:button href="{{ $isLoggedIn ? $accountUrl : $loginUrl }}" wire:navigate variant="primary" icon="user" class="justify-center">
                            {{ $isLoggedIn ? __('Account', 'sage') : __('Sign In', 'sage') }}
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>
    </flux:modal>
@endif
