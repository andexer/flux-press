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
    public function profileUrl(): string
    {
        if (class_exists('WooCommerce')) {
            return (string) wc_get_account_endpoint_url('edit-account');
        }

        return (string) home_url('/');
    }

    #[Computed]
    public function avatarUrl(): string
    {
        $user = $this->user;
        if (! $user instanceof \WP_User) {
            return '';
        }

        $customPhotoId = (int) get_user_meta($user->ID, 'flux_profile_photo_id', true);
        $customPhoto = $customPhotoId > 0 ? (string) wp_get_attachment_image_url($customPhotoId, 'thumbnail') : '';

        if ($customPhoto !== '') {
            return $customPhoto;
        }

        return (string) get_avatar_url($user->ID, ['size' => 96]);
    }

    #[Computed]
    public function logoutItem(): array
    {
        if (! class_exists('WooCommerce')) {
            return [
                'label' => __('Cerrar sesión', 'sage'),
                'url'   => wp_logout_url(home_url('/')),
                'icon'  => 'arrow-right-start-on-rectangle',
            ];
        }

        $iconMap = apply_filters('woocommerce_account_menu_icons', []);

        return [
            'label' => __('Cerrar sesión', 'sage'),
            'url'   => wc_get_account_endpoint_url('customer-logout'),
            'icon'  => $iconMap['customer-logout'] ?? 'arrow-right-start-on-rectangle',
        ];
    }
}; ?>

<div class="flux-account-nav space-y-3 in-data-flux-sidebar-collapsed-desktop:space-y-2">
    @if($this->user)
        <a
            href="{{ $this->profileUrl }}"
            wire:navigate
            class="flux-account-nav-user block rounded-2xl border border-zinc-200/80 dark:border-zinc-700 bg-white/70 dark:bg-zinc-800/40 p-3 shadow-sm hover:border-accent-300 dark:hover:border-accent-500/40 transition-colors in-data-flux-sidebar-collapsed-desktop:hidden"
        >
            <div class="flex items-center gap-3">
                <img
                    src="{{ esc_url($this->avatarUrl) }}"
                    alt="{{ esc_attr($this->user->display_name) }}"
                    class="size-12 rounded-full object-cover border border-zinc-200 dark:border-zinc-700"
                />
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->user->display_name }}</p>
                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $this->user->user_email }}</p>
                </div>
            </div>
        </a>

        <a
            href="{{ $this->profileUrl }}"
            wire:navigate
            aria-label="{{ esc_attr__('Editar perfil', 'sage') }}"
            class="hidden in-data-flux-sidebar-collapsed-desktop:inline-flex w-full items-center justify-center"
        >
            <img
                src="{{ esc_url($this->avatarUrl) }}"
                alt="{{ esc_attr($this->user->display_name) }}"
                class="size-10 rounded-full object-cover border border-zinc-200 dark:border-zinc-700 shadow-sm"
            />
        </a>
    @endif

    <div class="space-y-1">
        @foreach ($this->menuItems as $item)
            <flux:sidebar.item
                href="{{ $item['url'] }}"
                :icon="$item['icon']"
                :current="$item['active']"
                wire:navigate
                class="!rounded-xl"
            >
                {{ $item['label'] }}
            </flux:sidebar.item>
        @endforeach
    </div>

    <flux:separator class="my-2 in-data-flux-sidebar-collapsed-desktop:my-1" />

    <flux:sidebar.item href="{{ $this->logoutItem['url'] }}" :icon="$this->logoutItem['icon']" class="!rounded-xl">
        {{ $this->logoutItem['label'] }}
    </flux:sidebar.item>
</div>
