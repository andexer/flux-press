@php
    $isWooCommerce = class_exists('WooCommerce');
    $isAccountSidebarContext = $isWooCommerce && is_account_page() && is_user_logged_in();
    $megaMenuOptions = isset($megaMenuConfig) && is_array($megaMenuConfig) ? $megaMenuConfig : [];
    $megaMenuEnabled = (bool) ($megaMenuOptions['enabled'] ?? true);

    $topMenuItems = [];
    if (isset($menuItems) && is_array($menuItems)) {
        foreach ($menuItems as $item) {
            if (is_object($item) && empty($item->menu_item_parent)) {
                $topMenuItems[] = $item;
            }
        }
    }

    $isLoggedIn = is_user_logged_in();
    $shopUrl = $isWooCommerce ? wc_get_page_permalink('shop') : home_url('/');
    $accountUrl = $isWooCommerce ? wc_get_account_endpoint_url('dashboard') : admin_url('profile.php');
    $loginUrl = $isWooCommerce ? wc_get_account_endpoint_url('dashboard') : wp_login_url();
    $ordersUrl = $isWooCommerce ? wc_get_account_endpoint_url('orders') : admin_url();
    $compareUrl = $isWooCommerce
        ? add_query_arg(['post_type' => 'product', 'orderby' => 'rating'], $shopUrl)
        : home_url('/');
    $wishlistUrl = home_url('/wishlist/');

    $primaryNavItems = array_slice($topMenuItems, 0, 4);

    $highlightMenuItemsResolved = [];
    if (isset($highlightMenuItems) && is_array($highlightMenuItems)) {
        foreach ($highlightMenuItems as $item) {
            if (is_object($item) && ! empty($item->title) && ! empty($item->url)) {
                $highlightMenuItemsResolved[] = $item;
            }
        }
    }

    if (empty($highlightMenuItemsResolved)) {
        $highlightMenuItemsResolved = array_slice($topMenuItems, 4, 7);
    }

    if (empty($highlightMenuItemsResolved)) {
        $highlightMenuItemsResolved = [
            (object) ['title' => __('Hot Deals', 'flux-press'), 'url' => add_query_arg(['post_type' => 'product', 'orderby' => 'popularity'], $shopUrl)],
            (object) ['title' => __('Whats New', 'flux-press'), 'url' => add_query_arg(['post_type' => 'product', 'orderby' => 'date'], $shopUrl)],
            (object) ['title' => __('Daily Deals', 'flux-press'), 'url' => add_query_arg(['post_type' => 'product', 'on_sale' => 1], $shopUrl)],
            (object) ['title' => __('Best Sellers', 'flux-press'), 'url' => add_query_arg(['post_type' => 'product', 'orderby' => 'popularity'], $shopUrl)],
            (object) ['title' => __('Clearance', 'flux-press'), 'url' => add_query_arg(['post_type' => 'product', 'on_sale' => 1], $shopUrl)],
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
            ['title' => __('Daily Deals', 'flux-press'), 'url' => add_query_arg(['post_type' => 'product', 'on_sale' => 1], $shopUrl)],
            ['title' => __('Find Stores', 'flux-press'), 'url' => home_url('/tiendas/')],
            ['title' => __('Gift Cards', 'flux-press'), 'url' => $shopUrl],
            ['title' => __('Sell on Store', 'flux-press'), 'url' => home_url('/vender/')],
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
            ['title' => __('Browsing History', 'flux-press'), 'url' => $ordersUrl],
            ['title' => __('Track Order', 'flux-press'), 'url' => $ordersUrl],
            ['title' => __('Help Center', 'flux-press'), 'url' => home_url('/ayuda/')],
            ['title' => __('EN/USD', 'flux-press'), 'url' => '#'],
        ];
    }
@endphp

<flux:header
    @class([
        'z-40 border-b border-zinc-200/80 bg-white/95 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/90',
        'sticky top-0' => $sticky ?? false,
        'relative' => ! ($sticky ?? false),
    ])
>
    @if(! $isAccountSidebarContext)
        <div class="hidden lg:block border-b border-zinc-200/70 bg-zinc-50/85 dark:border-zinc-800/70 dark:bg-zinc-900/80">
            <div class="mx-auto flex max-w-[95rem] items-center justify-between gap-4 px-4 py-2 text-xs">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                    @foreach($utilityLeftLinks as $utilityLink)
                        <a href="{{ $utilityLink['url'] }}" wire:navigate class="font-semibold text-zinc-600 transition-colors hover:text-accent-700 dark:text-zinc-300 dark:hover:text-accent-300">
                            {{ $utilityLink['title'] }}
                        </a>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                    @foreach(array_slice($utilityRightLinks, 0, 3) as $utilityLink)
                        <a href="{{ $utilityLink['url'] }}" wire:navigate class="font-semibold text-zinc-600 transition-colors hover:text-accent-700 dark:text-zinc-300 dark:hover:text-accent-300">
                            {{ $utilityLink['title'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="mx-auto w-full max-w-[95rem] px-4 py-3">
        <div class="grid items-center gap-3 lg:grid-cols-[auto_minmax(0,1fr)_auto]">
            <div class="flex min-w-0 items-center gap-2 lg:gap-4">
                @if($isAccountSidebarContext)
                    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />
                @endif

                @if(! $isAccountSidebarContext && ! empty($topMenuItems))
                    <flux:modal.trigger name="centered-mobile-menu">
                        <flux:button variant="ghost" size="sm" icon="bars-3-bottom-left" class="lg:hidden" aria-label="{{ esc_attr__('Abrir menu', 'flux-press') }}" />
                    </flux:modal.trigger>
                @endif

                <a href="{{ home_url('/') }}" wire:navigate class="inline-flex min-w-0 items-center gap-2 rounded-xl px-2 py-1.5 text-base font-black tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-lg">
                    @if(! empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="{{ esc_attr($siteName ?? get_bloginfo('name')) }}" class="h-7 w-auto shrink-0">
                    @else
                        <span class="size-2 rounded-full bg-accent-500"></span>
                    @endif
                    <span class="truncate">{{ $siteName ?? get_bloginfo('name') }}</span>
                </a>

                @if(! $isAccountSidebarContext)
                    <nav class="hidden 2xl:flex items-center gap-1">
                        @foreach($primaryNavItems as $item)
                            <a href="{{ $item->url }}" wire:navigate class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:hover:text-zinc-50">
                                {{ $item->title }}
                            </a>
                        @endforeach
                    </nav>
                @endif
            </div>

            <div class="min-w-0 w-full">
                <livewire:global-search variant="market" :show-scope="true" />
            </div>

            <div class="flex items-center justify-end gap-1 sm:gap-2">
                @if($isWooCommerce)
                    <flux:button variant="ghost" size="sm" href="{{ $compareUrl }}" wire:navigate icon="arrows-right-left" class="hidden xl:inline-flex">
                        {{ __('Compare', 'flux-press') }}
                    </flux:button>
                    <flux:button variant="ghost" size="sm" href="{{ $wishlistUrl }}" wire:navigate icon="heart" class="hidden xl:inline-flex">
                        {{ __('Wishlist', 'flux-press') }}
                    </flux:button>
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
                            <flux:menu.item icon="user-circle" href="{{ admin_url('profile.php') }}">{{ __('Perfil', 'flux-press') }}</flux:menu.item>
                            @if($isWooCommerce)
                                <flux:menu.item icon="user" href="{{ $accountUrl }}" wire:navigate>{{ __('Mi Cuenta', 'flux-press') }}</flux:menu.item>
                                <flux:menu.item icon="truck" href="{{ $ordersUrl }}" wire:navigate>{{ __('Mis Ordenes', 'flux-press') }}</flux:menu.item>
                            @endif
                            <flux:menu.separator />
                            <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" href="{{ wp_logout_url(home_url('/')) }}">
                                {{ __('Cerrar Sesion', 'flux-press') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                @else
                    <flux:button variant="primary" size="sm" href="{{ $loginUrl }}" icon="user">
                        {{ __('Sign in', 'flux-press') }}
                    </flux:button>
                @endif
            </div>
        </div>

        @if(! $isAccountSidebarContext)
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-200/70 pt-3 dark:border-zinc-800/80">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach(array_slice($highlightMenuItemsResolved, 0, 7) as $item)
                        <a
                            href="{{ $item->url }}"
                            wire:navigate
                            class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-zinc-100/80 px-3 py-1.5 text-xs font-bold tracking-wide text-zinc-700 transition-colors hover:border-accent-400 hover:bg-accent-50 hover:text-accent-700 dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-200 dark:hover:border-accent-500 dark:hover:bg-accent-900/20 dark:hover:text-accent-300"
                        >
                            <span class="size-1.5 rounded-full bg-accent-500"></span>
                            <span>{{ $item->title }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="hidden xl:flex items-center gap-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                    @foreach(array_slice($utilityRightLinks, 0, 4) as $link)
                        <a href="{{ $link['url'] }}" wire:navigate class="transition-colors hover:text-accent-600 dark:hover:text-accent-300">{{ $link['title'] }}</a>
                    @endforeach
                </div>
            </div>

            @if($megaMenuEnabled)
                <div class="hidden lg:flex border-t border-zinc-200/70 pt-3 mt-3 dark:border-zinc-800/80">
                    <livewire:mega-menu :items="$menuItems ?? []" :config="$megaMenuOptions" />
                </div>
            @endif
        @endif
    </div>
</flux:header>

@if(! $isAccountSidebarContext && ! empty($topMenuItems))
    <flux:modal name="centered-mobile-menu" variant="flyout" class="max-w-sm w-full !p-0">
        <div class="flex h-full flex-col bg-white dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 px-4 py-4">
                <span class="text-sm font-black tracking-wide text-zinc-900 dark:text-zinc-100">{{ __('Navegacion', 'flux-press') }}</span>
                <flux:modal.close>
                    <flux:button variant="ghost" size="sm" icon="x-mark" />
                </flux:modal.close>
            </div>

            <div class="border-b border-zinc-200/70 dark:border-zinc-800/80 px-4 py-3">
                <livewire:global-search variant="market" :show-scope="true" />
            </div>

            <div class="border-b border-zinc-200/70 dark:border-zinc-800/80 p-3">
                <div class="grid grid-cols-2 gap-2">
                    @foreach(array_slice($highlightMenuItemsResolved, 0, 6) as $item)
                        <a href="{{ $item->url }}" wire:navigate class="inline-flex items-center justify-center rounded-xl bg-zinc-100 px-2 py-2 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            {{ $item->title }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-3">
                <flux:navlist>
                    @foreach($topMenuItems as $item)
                        <flux:navlist.item
                            href="{{ $item->url }}"
                            wire:navigate
                            icon="chevron-right"
                            class="!rounded-xl !px-3 !py-2.5 !text-base !font-semibold"
                        >
                            {{ $item->title }}
                        </flux:navlist.item>
                    @endforeach
                </flux:navlist>
            </div>

            <div class="mt-auto border-t border-zinc-200 dark:border-zinc-800 px-4 py-4">
                <div class="grid grid-cols-2 gap-2">
                    <flux:button href="{{ $isWooCommerce ? $shopUrl : home_url('/') }}" wire:navigate variant="ghost" icon="building-storefront" class="justify-center">
                        {{ __('Tienda', 'flux-press') }}
                    </flux:button>
                    <flux:button href="{{ $isLoggedIn ? $accountUrl : $loginUrl }}" wire:navigate variant="primary" icon="user" class="justify-center">
                        {{ $isLoggedIn ? __('Cuenta', 'flux-press') : __('Acceder', 'flux-press') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
@endif
