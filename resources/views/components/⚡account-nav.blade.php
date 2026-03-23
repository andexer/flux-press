<?php
use Livewire\Component;
use Livewire\Attributes\Computed;
new class extends Component
{
    #[Computed]
    public function menuItems(): array
    {
        if (! class_exists('WooCommerce')) return [];
        $items = wc_get_account_menu_items();
        $icon_map = apply_filters('woocommerce_account_menu_icons', []);
        $navigation = [];
        foreach ($items as $endpoint => $label) {
            $navigation[] = [
                'endpoint' => $endpoint,
                'label'    => $label,
                'url'      => wc_get_account_endpoint_url($endpoint),
                'active'   => wc_is_current_account_menu_item($endpoint),
                'icon'     => $icon_map[$endpoint] ?? 'chevron-right',
                'is_logout' => $endpoint === 'customer-logout',
            ];
        }
        return $navigation;
    }
}; ?>

<div>
    @foreach ($this->menuItems as $item)
        @if($item['is_logout'])
            <flux:separator class="my-4" />
        @endif


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