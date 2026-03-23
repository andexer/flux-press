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
        <flux:dropdown position="bottom" align="start" class="lg:hidden">
            <flux:button variant="ghost" size="sm" icon="bars-3-bottom-left" />
            <flux:menu class="w-72">
                @foreach($topMenuItems as $item)
                    <flux:menu.item href="{{ $item->url }}" wire:navigate>{{ $item->title }}</flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
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
