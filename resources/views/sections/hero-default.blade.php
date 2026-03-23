<flux:main class="min-h-[80vh] flex items-center relative overflow-hidden bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,var(--color-accent-600)_0%,transparent_40%)] opacity-10 dark:opacity-20 pointer-events-none"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full py-20">
        <div class="max-w-3xl">
            <flux:badge color="zinc" class="mb-6 border-zinc-300 dark:border-zinc-700">🚀 {!! wp_get_theme()->get('Name') !!} 1.0</flux:badge>
            <flux:heading size="6xl" class="mb-6 tracking-tight !font-extrabold text-zinc-900 dark:text-white">
                El Futuro de los Temas en <span class="bg-gradient-to-r from-accent-600 to-indigo-500 text-transparent bg-clip-text">WordPress</span>
            </flux:heading>
            <flux:subheading size="xl" class="mb-10 text-balance text-zinc-600 dark:text-zinc-400">
                Lleva tu presencia web al siguiente nivel con nuestro constructor reactivo de última generación en tiempo real.
            </flux:subheading>
            <div class="flex flex-wrap gap-4">
                <flux:button size="base" variant="primary" icon="rocket-launch">Empezar Ahora</flux:button>
                <flux:button size="base" variant="subtle" icon="book-open">Leer Documentación</flux:button>
            </div>
        </div>
    </div>
</flux:main>
