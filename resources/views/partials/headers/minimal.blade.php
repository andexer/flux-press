{{-- Header Variant: Minimal — Solo logo + botón hamburguesa con off-canvas (drawer) --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between py-4">
        {{-- Logo --}}
        <a href="{{ home_url('/') }}" class="flex items-center gap-2 font-bold text-lg tracking-tight" wire:navigate>
            <flux:icon.bolt class="size-6 text-accent-500" />
            {!! $siteName !!}
        </a>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            @if(class_exists('WooCommerce'))
                <livewire:cart-icon />
            @endif

            {{-- Boton hamburguesa para abrir drawer --}}
            <flux:modal.trigger name="nav-drawer">
                <flux:button variant="ghost" icon="bars-3" aria-label="Abrir menu" />
            </flux:modal.trigger>
        </div>
    </div>
</div>

{{-- Drawer off-canvas (Sidebar) --}}
<flux:modal name="nav-drawer" variant="flyout" class="max-w-xs w-full">
    <div class="p-6 space-y-6">
        {{-- Brand dentro del drawer --}}
        <div class="flex items-center gap-2 mb-6">
            <flux:icon.bolt class="size-6 text-accent-500" />
            <span class="font-bold text-lg">{!! $siteName !!}</span>
        </div>

        <flux:separator />

        {{-- Navegación --}}
        <flux:navlist>
            @if (!empty($menuItems))
                @foreach ($menuItems as $item)
                    <flux:navlist.item
                        href="{{ $item->url }}"
                        wire:navigate
                        :current="url()->current() === $item->url"
                    >
                        {{ $item->title }}
                    </flux:navlist.item>
                @endforeach
            @endif
        </flux:navlist>

        <flux:separator />

        <livewire:theme-settings :asNavLink="true" />

        {{-- CTA en el drawer --}}
        <flux:button variant="primary" class="w-full" icon="rocket-launch">
            {{ __('Empezar', 'flux-press') }}
        </flux:button>
    </div>
</flux:modal>

