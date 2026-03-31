<flux:main class="relative bg-zinc-900 py-32 overflow-hidden min-h-[90vh] flex items-center">
    <div class="absolute top-0 inset-x-0 h-40 bg-gradient-to-b from-zinc-800/50 to-transparent"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="text-center max-w-4xl mx-auto">
            <flux:badge color="lime" size="sm" class="mb-8 rounded-full px-6 py-2 border-none bg-lime-400 text-lime-950 font-bold uppercase tracking-widest italic">
                {{ __('Guaranteed Impact', 'sage') }}
            </flux:badge>
            <flux:heading size="8xl" class="mb-8 !font-black text-white uppercase tracking-tighter leading-none italic">
                {{ __('DOMINATE YOUR MARKET.', 'sage') }}
            </flux:heading>
            <flux:subheading size="xl" class="mb-12 text-zinc-400 font-medium leading-relaxed">
                {{ __('Growth strategies that work. Unlock your revenue potential with our marketing automation tools.', 'sage') }}
            </flux:subheading>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <flux:input placeholder="{{ __('Your email address...', 'sage') }}" class="min-w-[300px] h-14 rounded-full bg-white/5 border-white/10 text-white text-lg px-8 focus:bg-white/10" />
                <flux:button variant="primary" size="base" class="bg-lime-400 hover:bg-lime-300 text-lime-950 rounded-full px-12 font-black !h-14">
                    {{ __('GET STARTED', 'sage') }}
                </flux:button>
            </div>
            
            <div class="mt-16 flex items-center justify-center gap-12 text-zinc-500 font-bold tracking-widest uppercase text-xs opacity-50">
                <span>Google</span>
                <span>Meta</span>
                <span>Amazon</span>
                <span>Shopify</span>
            </div>
        </div>
    </div>
</flux:main>
