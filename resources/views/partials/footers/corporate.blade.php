{{-- Footer Variant: Corporate --}}
<footer class="relative bg-zinc-950 border-t border-zinc-800 overflow-hidden">
    {{-- Decorative Glow --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-accent-500/10 dark:bg-accent-500/5 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">
            {{-- Columna 1: Brand & About --}}
            <div class="space-y-6">
                <div class="mb-4">
                    <flux:brand 
                        href="{{ home_url('/') }}" 
                        :name="$siteName ?? get_bloginfo('name')" 
                        :logo="$logoUrl ?? null" 
                        class="!text-white"
                    />
                </div>
                <flux:text class="text-zinc-400">
                    {{ __('Tema premium de alto rendimiento con tecnología Laravel, Livewire y componentes Flux UI.', 'sage') }}
                </flux:text>
                <div class="flex items-center gap-2 pt-2">
                    @foreach($socialLinks as $social)
                        <flux:button variant="ghost" size="sm" icon="{{ $social['icon'] }}" href="{{ $social['url'] }}" aria-label="{{ $social['label'] }}" class="!text-zinc-400 hover:!text-white" />
                    @endforeach
                </div>
            </div>

            {{-- Columna 2: Quick Links --}}
            <div>
                <flux:heading size="sm" class="!text-white mb-4 uppercase tracking-wider">{{ __('Enlaces Rápidos', 'sage') }}</flux:heading>
                <flux:navlist>
                    @forelse ($quickLinks as $item)
                        <flux:navlist.item href="{{ $item->url }}" wire:navigate class="!text-zinc-400 hover:!text-white">{{ $item->title }}</flux:navlist.item>
                    @empty
                        <flux:navlist.item href="{{ home_url('/') }}" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Inicio', 'sage') }}</flux:navlist.item>
                        <flux:navlist.item href="{{ home_url('/about') }}" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Acerca de', 'sage') }}</flux:navlist.item>
                        <flux:navlist.item href="{{ home_url('/blog') }}" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Blog', 'sage') }}</flux:navlist.item>
                    @endforelse
                </flux:navlist>
            </div>

            {{-- Columna 3: Resources --}}
            <div>
                <flux:heading size="sm" class="!text-white mb-4 uppercase tracking-wider">{{ __('Recursos', 'sage') }}</flux:heading>
                <flux:navlist>
                    @forelse ($resourcesMenu as $item)
                        <flux:navlist.item href="{{ $item->url }}" wire:navigate class="!text-zinc-400 hover:!text-white">{{ $item->title }}</flux:navlist.item>
                    @empty
                        <flux:navlist.item href="{{ home_url('/docs') }}" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Documentación', 'sage') }}</flux:navlist.item>
                        <flux:navlist.item href="{{ home_url('/support') }}" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Soporte', 'sage') }}</flux:navlist.item>
                        <flux:navlist.item href="{{ home_url('/terms') }}" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Términos', 'sage') }}</flux:navlist.item>
                    @endforelse
                </flux:navlist>
            </div>

            {{-- Columna 4: Newsletter --}}
            <div>
                <flux:heading size="sm" class="!text-white mb-4 uppercase tracking-wider">{{ __('Newsletter', 'sage') }}</flux:heading>
                <flux:text class="text-zinc-400 mb-4">
                    {{ __('Suscríbete a nuestro boletín para recibir las últimas novedades.', 'sage') }}
                </flux:text>
                <form class="flex gap-2" wire:submit.prevent>
                    <flux:input type="email" placeholder="{{ __('tu@email.com', 'sage') }}" class="flex-1" aria-label="Email" required />
                    <flux:button variant="primary" type="submit" icon="paper-airplane" aria-label="Suscribirse" />
                </form>
            </div>
        </div>

        {{-- Widgets de WordPress --}}
        @if ($footerWidgets)
            <flux:separator class="mt-16 mb-10 !border-zinc-800/50" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @php(dynamic_sidebar('sidebar-footer'))
            </div>
        @endif
    </div>

    {{-- Barra de copyright --}}
    <div class="bg-black/50 border-t border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <flux:text class="text-zinc-500 text-center md:text-left">
                &copy; {{ $currentYear }} <span class="text-zinc-300 font-medium">{!! $siteName !!}</span>. {{ __('Todos los derechos reservados.', 'sage') }}
            </flux:text>
            <div class="flex flex-wrap justify-center items-center gap-2">
                <flux:button variant="ghost" size="sm" href="#" wire:navigate class="!text-zinc-500 hover:!text-zinc-300">{{ __('Privacidad', 'sage') }}</flux:button>
                <flux:button variant="ghost" size="sm" href="#" wire:navigate class="!text-zinc-500 hover:!text-zinc-300">{{ __('Términos', 'sage') }}</flux:button>
                <flux:button variant="ghost" size="sm" href="#" wire:navigate class="!text-zinc-500 hover:!text-zinc-300">{{ __('Cookies', 'sage') }}</flux:button>
            </div>
        </div>
    </div>
</footer>
