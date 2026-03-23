<flux:main class="bg-zinc-50 dark:bg-zinc-950 min-h-[80vh] flex items-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full text-center">
        <div class="mb-12">
            <flux:heading size="5xl" class="mb-4 !font-serif !font-light text-zinc-900 dark:text-white">
                {{ __('Un vistazo a lo Extraordinario.', 'sage') }}
            </flux:heading>
            <flux:subheading size="lg" class="text-zinc-500 dark:text-zinc-400 max-w-2xl mx-auto">
                {{ __('Capturando momentos, texturas y emociones. Una colección seleccionada de arte fotográfico contemporáneo.', 'sage') }}
            </flux:subheading>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 grayscale hover:grayscale-0 transition-all duration-700">
            @foreach(range(1, 4) as $i)
                <div class="aspect-[3/4] bg-zinc-200 dark:bg-zinc-900 rounded-lg overflow-hidden shadow-sm hover:scale-[1.02] transition-transform"></div>
            @endforeach
        </div>
        
        <div class="mt-12 flex justify-center gap-8 border-t border-zinc-200 dark:border-zinc-800 pt-8">
            <div class="text-center">
                <div class="text-2xl font-bold dark:text-white">500+</div>
                <div class="text-xs text-zinc-500 uppercase tracking-widest">{{ __('Obras', 'sage') }}</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold dark:text-white">12k</div>
                <div class="text-xs text-zinc-500 uppercase tracking-widest">{{ __('Coleccionistas', 'sage') }}</div>
            </div>
        </div>
    </div>
</flux:main>
