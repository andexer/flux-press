{{-- Footer Variant: SaaS (Fat Footer) --}}
<footer class="relative bg-zinc-950 text-zinc-400 overflow-hidden">
    {{-- Sección superior: CTA Inmersivo --}}
    <div class="relative py-24 sm:py-32 overflow-hidden border-b border-white/5">
        {{-- Patrón de fondo y gradientes para el CTA --}}
        <div class="absolute inset-0 bg-accent-900/20"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-transparent to-transparent"></div>
        
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
            <flux:heading size="xl" class="!text-white mb-6 tracking-tight !font-bold text-4xl sm:text-5xl lg:text-6xl">
                {{ __('Ready to transform your web experience?', 'sage') }}
            </flux:heading>
            <flux:subheading class="!text-zinc-300/80 mb-10 max-w-2xl mx-auto text-lg text-balance">
                {{ __('Join thousands of modern developers already using the full power of Flux Press and Livewire 4.', 'sage') }}
            </flux:subheading>
            <div class="flex flex-wrap justify-center gap-4">
                <flux:button variant="primary" class="!bg-white !text-zinc-900 hover:!bg-zinc-100 font-bold px-8 shadow-xl shadow-white/10 h-14 text-lg" icon="rocket-launch" href="#" wire:navigate>
                    {{ __('Start Free', 'sage') }}
                </flux:button>
                <flux:button variant="ghost" class="!text-white !border-white/20 hover:!bg-white/10 px-8 backdrop-blur-sm shadow-xl h-14 text-lg" icon="play-circle" href="#" wire:navigate>
                    {{ __('View Demo', 'sage') }}
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Sección inferior oscura: enlaces organizados --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-10 lg:gap-8 mb-4">
            {{-- Brand info --}}
            <div class="col-span-2 lg:col-span-2 space-y-6">
                <a href="{{ home_url('/') }}" wire:navigate class="flex items-center gap-2 transition-opacity hover:opacity-80 decoration-transparent">
                    <flux:icon.bolt class="size-7 text-accent-500 drop-shadow-md" />
                    <flux:heading size="xl" class="!text-white tracking-tight">{!! $siteName !!}</flux:heading>
                </a>
                <flux:text class="text-zinc-400 max-w-sm">
                    {{ __('Building the future of the web, one reactive component at a time. Designed for non-conformist creators.', 'sage') }}
                </flux:text>
                <div class="flex items-center gap-2">
                    <flux:button variant="ghost" size="sm" icon="globe-alt" href="#" aria-label="Website" class="!text-zinc-400 hover:!text-white" wire:navigate />
                    <flux:button variant="ghost" size="sm" icon="envelope" href="#" aria-label="Email" class="!text-zinc-400 hover:!text-white" wire:navigate />
                </div>
            </div>

            {{-- Columna: Producto --}}
            <div>
                <flux:heading size="sm" class="!text-white mb-4 uppercase tracking-wider">{{ __('Product', 'sage') }}</flux:heading>
                <flux:navlist>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Features', 'sage') }}</flux:navlist.item>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Pricing', 'sage') }}</flux:navlist.item>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Changelog', 'sage') }}</flux:navlist.item>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Roadmap', 'sage') }}</flux:navlist.item>
                </flux:navlist>
            </div>

            {{-- Columna: Compañía --}}
            <div>
                <flux:heading size="sm" class="!text-white mb-4 uppercase tracking-wider">{{ __('Company', 'sage') }}</flux:heading>
                <flux:navlist>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('About', 'sage') }}</flux:navlist.item>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Blog', 'sage') }}</flux:navlist.item>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Careers', 'sage') }}</flux:navlist.item>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Contact', 'sage') }}</flux:navlist.item>
                </flux:navlist>
            </div>

            {{-- Columna: Legal --}}
            <div>
                <flux:heading size="sm" class="!text-white mb-4 uppercase tracking-wider">{{ __('Legal', 'sage') }}</flux:heading>
                <flux:navlist>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Privacy', 'sage') }}</flux:navlist.item>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Terms', 'sage') }}</flux:navlist.item>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('Cookies', 'sage') }}</flux:navlist.item>
                    <flux:navlist.item href="#" wire:navigate class="!text-zinc-400 hover:!text-white">{{ __('GDPR', 'sage') }}</flux:navlist.item>
                </flux:navlist>
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

    {{-- Copyright --}}
    <div class="bg-black/30 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center sm:text-left flex flex-col sm:flex-row justify-between items-center gap-4">
            <flux:text class="text-zinc-500">
                &copy; {{ $currentYear }} <span class="text-zinc-300">{!! $siteName !!}</span>. {{ __('Created with precision.', 'sage') }}
            </flux:text>
            <flux:text class="text-zinc-500 flex items-center justify-center gap-1.5">
                {{ __('Made with', 'sage') }} <flux:icon.heart class="size-4 text-rose-500 drop-shadow-sm" />
            </flux:text>
        </div>
    </div>
</footer>
