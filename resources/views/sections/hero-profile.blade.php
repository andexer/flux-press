<flux:main class="relative py-24 bg-white dark:bg-zinc-950 flex items-center min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="flex flex-col md:flex-row items-center gap-16">
            <div class="size-64 md:size-80 rounded-full bg-gradient-to-tr from-cyan-400 to-blue-600 p-1.5 shrink-0 shadow-2xl">
                <div class="w-full h-full rounded-full bg-white dark:bg-zinc-900 border-4 border-white dark:border-zinc-900 overflow-hidden flex items-center justify-center">
                    <flux:icon.user class="size-32 text-zinc-200 dark:text-zinc-800" />
                </div>
            </div>
            
            <div class="text-center md:text-left">
                <flux:badge color="cyan" class="mb-6">{{ __('Disponible para Proyectos', 'flux-press') }}</flux:badge>
                <flux:heading size="7xl" class="mb-4 !font-black !text-zinc-900 dark:!text-white tracking-tight">
                    {{ __('Juan Pérez.', 'flux-press') }}
                </flux:heading>
                <flux:heading size="3xl" class="mb-6 font-medium text-cyan-600 dark:text-cyan-400">
                    {{ __('Diseñador UI & Desarrollador Fullstack.', 'flux-press') }}
                </flux:heading>
                <flux:text size="xl" class="mb-8 text-zinc-600 dark:text-zinc-400 max-w-xl">
                    {{ __('Ayudo a startups a construir productos digitales escalables y hermosos. Especializado en Laravel, Livewire y Tailwind CSS.', 'flux-press') }}
                </flux:text>
                
                <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                    <flux:button variant="primary" size="base" icon="envelope" class="bg-cyan-600 hover:bg-cyan-500 text-white rounded-full px-8">
                        {{ __('Contactar', 'flux-press') }}
                    </flux:button>
                    <flux:button variant="ghost" size="base" icon="arrow-down-tray">{{ __('Descargar CV', 'flux-press') }}</flux:button>
                </div>
            </div>
        </div>
    </div>
</flux:main>
