{{-- Footer Variant: Clean --}}
<footer class="bg-white dark:bg-zinc-950 border-t border-zinc-200 dark:border-zinc-800/50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="flex flex-col items-center text-center space-y-8">
            {{-- Logo centrado --}}
            <a href="{{ home_url('/') }}" wire:navigate class="group flex items-center gap-2 transition-opacity hover:opacity-80 decoration-transparent">
                <flux:icon.bolt class="size-8 text-accent-500 drop-shadow-sm group-hover:rotate-12 transition-transform duration-300" />
                <flux:heading size="xl" class="!font-extrabold tracking-tight text-zinc-900 dark:text-white">{!! $siteName !!}</flux:heading>
            </a>

            {{-- Tagline --}}
            <flux:subheading class="max-w-md text-base leading-relaxed text-balance">
                {{ __('Built with cutting-edge technology for exceptional high-speed websites.', 'sage') }}
            </flux:subheading>

            <flux:separator class="!w-16 my-2" />

            {{-- Enlaces legales en línea --}}
            <div class="flex flex-wrap justify-center items-center gap-2 sm:gap-4">
                <flux:button variant="ghost" size="sm" href="#" wire:navigate class="!text-zinc-500 hover:!text-accent-600 dark:hover:!text-accent-400">{{ __('Privacy', 'sage') }}</flux:button>
                <flux:button variant="ghost" size="sm" href="#" wire:navigate class="!text-zinc-500 hover:!text-accent-600 dark:hover:!text-accent-400">{{ __('Terms', 'sage') }}</flux:button>
                <flux:button variant="ghost" size="sm" href="#" wire:navigate class="!text-zinc-500 hover:!text-accent-600 dark:hover:!text-accent-400">{{ __('Contact', 'sage') }}</flux:button>
                <flux:button variant="ghost" size="sm" href="#" wire:navigate class="!text-zinc-500 hover:!text-accent-600 dark:hover:!text-accent-400">{{ __('Sitemap', 'sage') }}</flux:button>
            </div>

            {{-- Copyright --}}
            <flux:text class="text-zinc-400 dark:text-zinc-600 mt-8">
                &copy; {{ $currentYear }} {!! $siteName !!}. {{ __('All rights reserved.', 'sage') }}
            </flux:text>
        </div>
    </div>
</footer>
