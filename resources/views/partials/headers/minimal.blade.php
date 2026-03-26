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
            (object) ['title' => __('Hot Deals', 'flux-press'), 'url' => home_url('/shop/?on_sale=1')],
            (object) ['title' => __('Whats New', 'flux-press'), 'url' => home_url('/shop/?orderby=date')],
            (object) ['title' => __('Best Sellers', 'flux-press'), 'url' => home_url('/shop/?orderby=popularity')],
        ];
    }

    $isLoggedIn = is_user_logged_in();
    $shopUrl = $isWooCommerce ? wc_get_page_permalink('shop') : home_url('/');
    $accountUrl = $isWooCommerce ? wc_get_account_endpoint_url('dashboard') : admin_url('profile.php');
    $loginUrl = $isWooCommerce ? wc_get_account_endpoint_url('dashboard') : wp_login_url();
@endphp

<flux:header
    @class([
        'flux-header-shell flux-header-mobile-compact',
        'sticky top-0' => $sticky ?? false,
        'relative' => ! ($sticky ?? false),
    ])
>
    <div class="w-full">
    <div class="flux-header-main-wrap">
        <div class="flex w-full items-center justify-between gap-2 sm:gap-3">
            @if($isAccountSidebarContext)
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />
            @endif

            @if(! $isAccountSidebarContext && ! empty($topMenuItems))
                <flux:modal.trigger name="minimal-mobile-menu">
                    <flux:button variant="ghost" size="sm" icon="bars-3" class="lg:hidden" aria-label="{{ esc_attr__('Abrir menu', 'flux-press') }}" />
                </flux:modal.trigger>
            @endif

            <a href="{{ home_url('/') }}" wire:navigate class="inline-flex min-w-0 items-center gap-2 rounded-xl px-1.5 py-1 text-sm font-black tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-base">
                @if(! empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="{{ esc_attr($siteName ?? get_bloginfo('name')) }}" class="h-7 w-auto shrink-0">
                @else
                    <span class="size-2 rounded-full bg-accent-500"></span>
                @endif
                <span class="truncate max-[430px]:hidden">{{ $siteName ?? get_bloginfo('name') }}</span>
            </a>

            <div class="hidden min-w-0 flex-1 md:block">
                <livewire:global-search variant="market" :show-scope="true" />
            </div>

            <div class="flex items-center gap-1 sm:gap-2">
                @if($isWooCommerce)
                    <flux:button variant="ghost" size="sm" href="{{ $shopUrl }}" wire:navigate icon="building-storefront" class="hidden xl:inline-flex">
                        {{ __('Tienda', 'flux-press') }}
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
                            @endif
                            <flux:menu.separator />
                            <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" href="{{ wp_logout_url(home_url('/')) }}">
                                {{ __('Cerrar Sesion', 'flux-press') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                @else
                    <flux:button variant="primary" size="sm" href="{{ $loginUrl }}" icon="user">
                        {{ __('Acceder', 'flux-press') }}
                    </flux:button>
                @endif
            </div>
        </div>

        <div class="flux-header-mobile-search-row">
            <livewire:global-search variant="market" :show-scope="true" />
        </div>

        @if(! $isAccountSidebarContext)
            <div class="flux-header-divider mt-3 hidden lg:flex flex-wrap items-center justify-between gap-2 border-t pt-3">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach(array_slice($highlightLinks, 0, 6) as $item)
                        <a href="{{ $item->url }}" wire:navigate class="flux-header-chip">
                            {{ $item->title }}
                        </a>
                    @endforeach
                </div>

                @if(! $megaMenuEnabled && ! empty($topMenuItems))
                    <div class="hidden xl:flex items-center gap-1">
                        @foreach(array_slice($topMenuItems, 0, 4) as $item)
                            <a href="{{ $item->url }}" wire:navigate class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-semibold text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100">
                                {{ $item->title }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($megaMenuEnabled)
                <div class="flux-header-divider mt-3 hidden lg:flex border-t pt-3">
                    <livewire:mega-menu :items="$menuItems ?? []" :config="$megaMenuOptions" />
                </div>
            @endif
        @endif
    </div>
    </div>
</flux:header>

@if(! $isAccountSidebarContext && ! empty($topMenuItems))
    <flux:modal name="minimal-mobile-menu" variant="flyout" class="max-w-sm w-full !p-0">
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
                    @foreach(array_slice($highlightLinks, 0, 6) as $item)
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
                <div @class([
                    'grid gap-2',
                    'grid-cols-2' => $isWooCommerce,
                    'grid-cols-1' => ! $isWooCommerce,
                ])>
                    @if($isWooCommerce)
                        <flux:button href="{{ $shopUrl }}" wire:navigate variant="ghost" icon="building-storefront" class="justify-center">
                            {{ __('Tienda', 'flux-press') }}
                        </flux:button>
                    @endif
                    <flux:button href="{{ $isLoggedIn ? $accountUrl : $loginUrl }}" wire:navigate variant="primary" icon="user" class="justify-center">
                        {{ $isLoggedIn ? __('Cuenta', 'flux-press') : __('Acceder', 'flux-press') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
@endif
