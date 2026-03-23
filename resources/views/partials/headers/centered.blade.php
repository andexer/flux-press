{{-- Header Variant: Centered — Logo centrado arriba, menú centrado abajo --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Fila superior: Logo centrado --}}
    <div class="flex justify-center py-6">
        <a href="{{ home_url('/') }}" class="flex items-center gap-2 text-2xl font-bold tracking-tight" wire:navigate>
            <flux:icon.bolt class="size-7 text-accent-500" />
            {!! $siteName !!}
        </a>
    </div>

    <flux:separator />

    {{-- Fila inferior: Navegación centrada --}}
    <div class="hidden lg:block relative">
        <flux:navbar class="justify-center py-3">
            @if (!empty($menuItems))
                @foreach ($menuItems as $item)
                    <flux:navbar.item
                        href="{{ $item->url }}"
                        wire:navigate
                        :current="url()->current() === $item->url"
                    >
                        {{ $item->title }}
                    </flux:navbar.item>
                @endforeach
            @endif

            <flux:separator vertical variant="subtle" class="mx-2" />

            @if(class_exists('WooCommerce'))
                <livewire:cart-icon />
            @endif

            <livewire:theme-settings />
        </flux:navbar>
    </div>


    {{-- Menú móvil responsive --}}
    <div class="lg:hidden" x-data="{ open: false }">
        <div class="flex justify-center py-3">
            <flux:button variant="ghost" icon="bars-3" x-on:click="open = !open" aria-label="Menú">
                {{ __('Menú', 'sage') }}
            </flux:button>
        </div>

        <div x-show="open" x-transition x-cloak class="pb-4 space-y-1">
            @if (!empty($menuItems))
                @foreach ($menuItems as $item)
                    <flux:navlist.item
                        href="{{ $item->url }}"
                        wire:navigate
                        :current="url()->current() === $item->url"
                        class="justify-center"
                    >
                        {{ $item->title }}
                    </flux:navlist.item>
                @endforeach
            @endif
        </div>
    </div>
</div>
