{{-- Header Variant: Classic — Logo izquierda, navegación centro, CTA derecha --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <flux:navbar class="py-4">
        {{-- Logo / Brand --}}
        <flux:navbar.item href="{{ home_url('/') }}" class="!font-bold !text-lg tracking-tight" wire:navigate>
            <flux:icon.bolt class="size-6 text-accent-500 mr-1" />
            {!! $siteName !!}
        </flux:navbar.item>

        <flux:spacer />

        {{-- Navegación principal --}}
        @forelse ($menuItems as $item)
            <flux:navbar.item
                href="{{ $item->url }}"
                wire:navigate
                :current="url()->current() === $item->url"
            >
                {{ $item->title }}
            </flux:navbar.item>
        @empty
            <flux:navbar.item href="{{ home_url('/') }}" :current="true" wire:navigate>Home</flux:navbar.item>
            <flux:navbar.item href="{{ home_url('/about') }}" wire:navigate>About</flux:navbar.item>
            <flux:navbar.item href="{{ home_url('/contact') }}" wire:navigate>Contact</flux:navbar.item>
        @endforelse

        <flux:spacer />

        {{-- CTA & Settings --}}
        <div class="flex items-center gap-2">
            <flux:navbar.item icon="magnifying-glass" href="#" aria-label="Buscar" class="lg:hidden" />
            
            @if(class_exists('WooCommerce'))
                <livewire:cart-icon />
            @endif

            <livewire:theme-settings />

            @if(isset($cta['show']) && $cta['show'])
                <flux:button variant="primary" size="sm" icon="rocket-launch" href="{{ $cta['url'] }}" wire:navigate>
                    {{ $cta['label'] }}
                </flux:button>
            @endif
        </div>
    </flux:navbar>


    {{-- Menú móvil responsive --}}
    <div class="lg:hidden" x-data="{ open: false }">
        <div class="flex items-center justify-between py-3">
            <a href="{{ home_url('/') }}" class="font-bold text-lg tracking-tight flex items-center gap-1" wire:navigate>
                <flux:icon.bolt class="size-5 text-accent-500" />
                {!! $siteName !!}
            </a>
            <flux:button variant="ghost" icon="bars-3" x-on:click="open = !open" aria-label="Menú" />
        </div>

        <div x-show="open" x-transition x-cloak class="pb-4 space-y-1">
            @forelse ($menuItems as $item)
                <flux:navlist.item
                    href="{{ $item->url }}"
                    wire:navigate
                    :current="url()->current() === $item->url"
                >
                    {{ $item->title }}
                </flux:navlist.item>
            @empty
                <flux:navlist.item href="{{ home_url('/') }}" :current="true" wire:navigate>Home</flux:navlist.item>
                <flux:navlist.item href="{{ home_url('/about') }}" wire:navigate>About</flux:navlist.item>
                <flux:navlist.item href="{{ home_url('/contact') }}" wire:navigate>Contact</flux:navlist.item>
            @endforelse

            @if(isset($cta['show']) && $cta['show'])
                <div class="pt-3">
                    <flux:button variant="primary" class="w-full" icon="rocket-launch" href="{{ $cta['url'] }}" wire:navigate>
                        {{ $cta['label'] }}
                    </flux:button>
                </div>
            @endif
        </div>
    </div>
</div>
