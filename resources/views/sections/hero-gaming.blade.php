<flux:main class="relative bg-zinc-950 dark:bg-black min-h-[90vh] flex items-center overflow-hidden font-mono">
    {{-- Gaming Grid Background --}}
    <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at center, #ef4444 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="border-l-4 border-red-600 pl-8 py-12">
            <flux:badge color="red" size="sm" class="mb-4 rounded-none uppercase tracking-tighter">{{ __('New Season', 'sage') }}</flux:badge>
            <flux:heading size="8xl" class="mb-6 !font-black !text-white uppercase tracking-tighter">
                {{ __('LEVEL UP YOUR <br/>EXPERIENCE.', 'sage') }}
            </flux:heading>
            <flux:subheading size="xl" class="mb-10 text-zinc-400 max-w-2xl leading-relaxed">
                {{ __('Join the competitive elite. Hardware, guides and tournaments all in one place.', 'sage') }}
            </flux:subheading>
            <div class="flex flex-wrap gap-4">
                <flux:button variant="primary" size="base" class="bg-red-600 hover:bg-red-700 text-white rounded-none uppercase px-12 border-b-4 border-red-800">
                    {{ __('Play Now', 'sage') }}
                </flux:button>
                <div class="flex items-center gap-4 ml-4">
                    <div class="flex -space-x-3">
                        @foreach(range(1,4) as $i)
                            <div class="size-10 rounded-full border-2 border-zinc-900 bg-zinc-800 flex items-center justify-center text-[10px] text-zinc-500">P{{ $i }}</div>
                        @endforeach
                    </div>
                    <span class="text-xs text-zinc-500 uppercase">{{ __('+1200 Playing', 'sage') }}</span>
                </div>
            </div>
        </div>
    </div>
</flux:main>
