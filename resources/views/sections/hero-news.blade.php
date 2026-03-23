<flux:main class="relative bg-zinc-50 dark:bg-zinc-950 overflow-hidden min-h-[70vh] flex items-center">
    <div class="absolute inset-0 opacity-10 dark:opacity-20 pointer-events-none">
        <div class="absolute top-0 right-0 w-96 h-96 bg-orange-500 rounded-full blur-3xl -mr-48 -mt-24"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-red-600 rounded-full blur-3xl -ml-48 -mb-24"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <flux:badge color="orange" size="sm" class="mb-6 uppercase tracking-widest font-bold">{{ __('Última Hora', 'flux-press') }}</flux:badge>
                <flux:heading size="6xl" class="mb-6 leading-tight !font-black !text-zinc-900 dark:!text-white">
                    {{ __('Información Real para un Mundo Digital.', 'flux-press') }}
                </flux:heading>
                <flux:subheading size="xl" class="mb-8 text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    {{ __('Tu fuente confiable de noticias sobre tecnología, cultura y negocios. Actualizado al minuto por periodistas expertos.', 'flux-press') }}
                </flux:subheading>
                <div class="flex flex-wrap gap-4">
                    <flux:button variant="primary" size="base" icon="newspaper" class="shadow-lg shadow-orange-500/20">
                        {{ __('Leer Portada', 'flux-press') }}
                    </flux:button>
                    <flux:button variant="ghost" size="base">{{ __('Suscribirse', 'flux-press') }}</flux:button>
                </div>
            </div>
            <div class="lg:block hidden">
                <div class="relative bg-white dark:bg-zinc-900 p-2 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 rotate-2">
                    <div class="aspect-video bg-zinc-100 dark:bg-zinc-800 rounded-xl overflow-hidden flex items-center justify-center">
                        <flux:icon.photo class="size-12 text-zinc-300 dark:text-zinc-700" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</flux:main>
