@php
    $isAccountSidebarContext = class_exists('WooCommerce') && is_account_page() && is_user_logged_in();
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
@endphp

<flux:header class="sticky top-0 z-40 bg-zinc-50/90 dark:bg-zinc-900/85 backdrop-blur border-b border-zinc-200/80 dark:border-zinc-700/80 shadow-[0_1px_0_0_rgba(0,0,0,0.02)]">
    @if($isAccountSidebarContext)
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />
    @endif

    <flux:brand
        href="{{ home_url('/') }}"
        :name="$siteName ?? get_bloginfo('name')"
        :logo="$logoUrl ?? null"
        class="max-lg:hidden dark:hidden"
    />
    <flux:brand
        href="{{ home_url('/') }}"
        :name="$siteName ?? get_bloginfo('name')"
        :logo="$logoUrl ?? null"
        class="max-lg:hidden! hidden dark:flex"
    />

    @if(! $isAccountSidebarContext && ! empty($topMenuItems))
        <flux:modal.trigger name="classic-mobile-menu">
            <flux:button variant="ghost" size="sm" icon="bars-3-bottom-left" class="lg:hidden" aria-label="{{ esc_attr__('Abrir menu principal', 'flux-press') }}" />
        </flux:modal.trigger>
    @endif

    @if($megaMenuEnabled)
        <div class="max-lg:hidden flex-1 px-2">
            <livewire:mega-menu :items="$menuItems ?? []" :config="$megaMenuOptions" />
        </div>
    @else
        <flux:navbar class="-mb-px max-lg:hidden">
            @foreach($topMenuItems as $item)
                <flux:navbar.item href="{{ $item->url }}" :current="url()->current() === $item->url" wire:navigate>{{ $item->title }}</flux:navbar.item>
            @endforeach
        </flux:navbar>
    @endif

    <flux:spacer />

    <flux:navbar class="me-2 flex items-center gap-1 sm:gap-2">
        <div class="max-sm:hidden">
            <livewire:global-search />
        </div>
        @if(class_exists('WooCommerce'))
            <livewire:cart-icon />
        @endif
        @if(current_user_can('manage_options'))
            <livewire:theme-settings />
        @endif
    </flux:navbar>

    @php $isLoggedIn = is_user_logged_in(); @endphp

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
                <flux:menu.item icon="user-circle" href="{{ admin_url('profile.php') }}">{{ __('Admin', 'flux-press') }}</flux:menu.item>
                @if(class_exists('WooCommerce'))
                    <flux:menu.item icon="user" href="{{ wc_get_account_endpoint_url('dashboard') }}" wire:navigate>{{ __('Mi Cuenta', 'flux-press') }}</flux:menu.item>
                @endif
                <flux:menu.separator />
                <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" href="{{ wp_logout_url(home_url('/')) }}">
                    {{ __('Cerrar Sesion', 'flux-press') }}
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    @else
        @php
            $loginUrl = class_exists('WooCommerce') ? wc_get_account_endpoint_url('dashboard') : wp_login_url();
        @endphp
        <flux:button variant="primary" size="sm" href="{{ $loginUrl }}" icon="user">{{ __('Acceder', 'flux-press') }}</flux:button>
    @endif
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
                    <flux:button variant="ghost" icon="x-mark" size="sm" aria-label="{{ esc_attr__('Cerrar menu', 'flux-press') }}" />
                </flux:modal.close>
            </div>

            <div class="border-b border-zinc-200/70 dark:border-zinc-800/80 px-4 py-3">
                <livewire:global-search />
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
                    'grid-cols-2' => class_exists('WooCommerce'),
                    'grid-cols-1' => ! class_exists('WooCommerce'),
                ])>
                    @if(class_exists('WooCommerce'))
                        <flux:button href="{{ wc_get_page_permalink('shop') }}" wire:navigate variant="ghost" icon="building-storefront" class="justify-center">
                            {{ __('Tienda', 'flux-press') }}
                        </flux:button>
                    @endif
                    <flux:button href="{{ is_user_logged_in() && class_exists('WooCommerce') ? wc_get_account_endpoint_url('dashboard') : wp_login_url() }}" wire:navigate variant="primary" icon="user" class="justify-center">
                        {{ is_user_logged_in() ? __('Cuenta', 'flux-press') : __('Acceder', 'flux-press') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
@endif
