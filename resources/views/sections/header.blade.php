<flux:header class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    @if(class_exists('WooCommerce') && is_account_page() && is_user_logged_in())
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

    <flux:navbar class="-mb-px max-lg:hidden">
        @isset($menuItems)
            @if(is_array($menuItems))
                @foreach ($menuItems as $item)
                    @if(is_object($item) && empty($item->menu_item_parent))
                        <flux:navbar.item href="{{ $item->url }}" :current="url()->current() === $item->url" wire:navigate>{{ $item->title }}</flux:navbar.item>
                    @endif
                @endforeach
            @endif
        @endisset
    </flux:navbar>

    <flux:spacer />

    <flux:navbar class="me-4 flex items-center gap-2">
        <livewire:global-search />
        @if(class_exists('WooCommerce'))
            <livewire:cart-icon />
        @endif
        {{-- Appearance Toggle - Restricted to Admin --}}
        @if(current_user_can('manage_options'))
            <livewire:theme-settings />
        @endif
    </flux:navbar>

    @php
    $is_logged_in = is_user_logged_in();
    @endphp

    @if($is_logged_in)
        @php
            $current_user = wp_get_current_user();
            $avatar_url = get_avatar_url($current_user->ID, ['size' => 64]);
        @endphp
        <flux:dropdown position="top" align="start">
            <flux:profile avatar="{{ $avatar_url }}" name="{{ $current_user->display_name }}" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.item icon="user-circle" href="{{ admin_url('profile.php') }}">{{ __('Admin', 'sage') }}</flux:menu.item>
                    @if(class_exists('WooCommerce'))
                        <flux:menu.item icon="user" href="{{ wc_get_account_endpoint_url('dashboard') }}" wire:navigate>{{ __('Mi Cuenta', 'sage') }}</flux:menu.item>
                    @endif
                </flux:menu.radio.group>

                <flux:menu.separator />
                
                <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" href="{{ wp_logout_url(home_url('/')) }}">
                    {{ __('Cerrar Sesión', 'sage') }}
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    @else
        <flux:button variant="primary" size="sm" href="{{ wc_get_account_endpoint_url('dashboard') }}" icon="user" wire:navigate>{{ __('Acceder', 'sage') }}</flux:button>
    @endif
</flux:header>
