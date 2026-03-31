<flux:main class="relative bg-white dark:bg-zinc-950 py-32 flex items-center min-h-[80vh]">
    <div class="absolute inset-0 z-0 opacity-5 dark:opacity-10 pointer-events-none overflow-hidden">
        <svg fill="none" class="absolute right-0 top-0 h-full w-1/2 text-zinc-900" viewBox="0 0 400 400" preserveAspectRatio="none">
            <path d="M400 400V0H0L400 400Z" fill="currentColor"/>
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="max-w-3xl">
            <div class="w-16 h-1.5 bg-zinc-900 dark:bg-white mb-10"></div>
            <flux:heading size="6xl" class="mb-8 !font-bold !text-zinc-900 dark:!text-white uppercase tracking-tight leading-tight">
                {{ __('Business Solutions for the New Era.', 'sage') }}
            </flux:heading>
            <flux:subheading size="xl" class="mb-12 text-zinc-600 dark:text-zinc-400 !font-light leading-relaxed max-w-2xl">
                {{ __('We accelerate the digital transformation of government and private organizations with cutting-edge technology and strategic consulting.', 'sage') }}
            </flux:subheading>
            
            <div class="flex flex-col sm:flex-row gap-6">
                <flux:button variant="primary" size="base" class="bg-zinc-900 dark:bg-white text-white dark:text-zinc-950 rounded-none px-10 border-none">
                    {{ __('Our Services', 'sage') }}
                </flux:button>
                <flux:button variant="ghost" size="base" class="border-2 border-zinc-900 dark:border-white rounded-none px-10">
                    {{ __('Contact Sales', 'sage') }}
                </flux:button>
            </div>
        </div>
    </div>
</flux:main>
