@php
    $isAccountSidebarContext = class_exists('WooCommerce') && is_account_page() && is_user_logged_in();
    $topMenuItems = [];

    if (isset($menuItems) && is_array($menuItems)) {
        foreach ($menuItems as $item) {
            if (is_object($item) && empty($item->menu_item_parent)) {
                $topMenuItems[] = $item;
            }
        }
    }
@endphp

<flux:header class="sticky top-0 z-40 bg-white/90 dark:bg-zinc-950/90 backdrop-blur border-b border-zinc-200/70 dark:border-zinc-800">
    @if($isAccountSidebarContext)
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />
    @endif

    <a href="{{ home_url('/') }}" wire:navigate class="inline-flex items-center gap-2 text-sm sm:text-base font-black tracking-tight text-zinc-900 dark:text-zinc-100">
        <span class="size-2 rounded-full bg-accent-500"></span>
        <span>{{ $siteName ?? get_bloginfo('name') }}</span>
    </a>

    <flux:spacer />

    <div class="hidden md:flex items-center gap-1">
        @foreach(array_slice($topMenuItems, 0, 4) as $item)
            <flux:button variant="ghost" size="sm" href="{{ $item->url }}" wire:navigate>{{ $item->title }}</flux:button>
        @endforeach
    </div>

    <div class="md:hidden">
        <flux:dropdown position="bottom" align="end">
            <flux:button variant="ghost" size="sm" icon="bars-3-bottom-right" />
            <flux:menu class="w-72">
                @foreach($topMenuItems as $item)
                    <flux:menu.item href="{{ $item->url }}" wire:navigate>{{ $item->title }}</flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    </div>

    @if(class_exists('WooCommerce'))
        <livewire:cart-icon />
    @endif

    @if(current_user_can('manage_options'))
        <livewire:theme-settings />
    @endif
</flux:header>
