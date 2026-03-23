<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function user(): ?\WP_User
    {
        if (! is_user_logged_in()) {
            return null;
        }

        return wp_get_current_user();
    }

    #[Computed]
    public function menuItems(): array
    {
        if (! class_exists('WooCommerce')) {
            return [];
        }

        $items = wc_get_account_menu_items();
        $iconMap = apply_filters('woocommerce_account_menu_icons', []);
        $navigation = [];

        foreach ($items as $endpoint => $label) {
            if ($endpoint === 'customer-logout') {
                continue;
            }

            $navigation[] = [
                'endpoint' => $endpoint,
                'label'    => $label,
                'url'      => wc_get_account_endpoint_url($endpoint),
                'active'   => wc_is_current_account_menu_item($endpoint),
                'icon'     => $iconMap[$endpoint] ?? 'chevron-right',
            ];
        }

        return $navigation;
    }

    #[Computed]
    public function logoutItem(): array
    {
        if (! class_exists('WooCommerce')) {
            return [
                'label' => __('Cerrar sesión', 'flux-press'),
                'url'   => wp_logout_url(home_url('/')),
                'icon'  => 'arrow-right-start-on-rectangle',
            ];
        }

        $iconMap = apply_filters('woocommerce_account_menu_icons', []);

        return [
            'label' => __('Cerrar sesión', 'flux-press'),
            'url'   => wc_get_account_endpoint_url('customer-logout'),
            'icon'  => $iconMap['customer-logout'] ?? 'arrow-right-start-on-rectangle',
        ];
    }
}; ?>

<div class="space-y-4">
    @if($this->user)
        <div class="rounded-2xl border border-zinc-200/80 dark:border-zinc-700 bg-white/70 dark:bg-zinc-800/40 p-3 shadow-sm">
            <div class="flex items-center gap-3">
                <livewire:profile-photo />
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->user->display_name }}</p>
                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $this->user->user_email }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-1">
        @foreach ($this->menuItems as $item)
            <flux:sidebar.item
                href="{{ $item['url'] }}"
                :icon="$item['icon']"
                :current="$item['active']"
                wire:navigate
            >
                {{ $item['label'] }}
            </flux:sidebar.item>
        @endforeach
    </div>

    <flux:separator class="my-2" />

    <flux:sidebar.item href="{{ $this->logoutItem['url'] }}" :icon="$this->logoutItem['icon']">
        {{ $this->logoutItem['label'] }}
    </flux:sidebar.item>
</div>
