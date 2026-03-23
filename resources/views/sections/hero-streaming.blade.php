<flux:main class="relative bg-black text-white min-h-[85vh] flex items-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-br from-purple-900/40 to-transparent"></div>
        <div class="absolute bottom-0 left-0 w-full h-full bg-gradient-to-tr from-zinc-950 to-transparent"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full text-center">
        <div class="max-w-4xl mx-auto">
            <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md px-4 py-2 rounded-full mb-8 border border-white/20">
                <span class="flex h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                <span class="text-sm font-semibold tracking-wide uppercase">{{ __('En Vivo Ahora', 'sage') }}</span>
            </div>
            
            <flux:heading size="7xl" class="mb-8 !font-black !text-white tracking-tighter italic">
                {{ __('CONTENIDO SIN LÍMITES.', 'sage') }}
            </flux:heading>
            
            <flux:subheading size="2xl" class="mb-12 text-zinc-400 !font-medium">
                {{ __('Películas, series y directos exclusivos en la plataforma más rápida.', 'sage') }}
            </flux:subheading>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                <flux:button variant="primary" size="xl" class="w-full sm:w-auto bg-purple-600 hover:bg-purple-500 text-white rounded-xl shadow-xl shadow-purple-900/40">
                    {{ __('Empezar Prueba Gratis', 'sage') }}
                </flux:button>
                <flux:button variant="ghost" size="xl" class="w-full sm:w-auto text-white border-white/20 hover:bg-white/5">
                    {{ __('Ver Tráiler', 'sage') }}
                </flux:button>
            </div>
        </div>
    </div>
</flux:main>
