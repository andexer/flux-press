<flux:main class="min-h-screen bg-gradient-to-br from-pink-500 via-purple-500 to-indigo-500 relative overflow-hidden flex items-center">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_80%,white_0%,transparent_50%)]"></div>
    </div>
    
    <div class="relative z-10 w-full max-w-5xl mx-auto text-center py-20 px-4">
        <div class="flex justify-center mb-8">
            <div class="flex -space-x-4">
                <img class="w-16 h-16 rounded-full border-4 border-purple-500 shadow-xl" src="https://i.pravatar.cc/150?u=1" alt="User 1">
                <img class="w-16 h-16 rounded-full border-4 border-purple-500 shadow-xl" src="https://i.pravatar.cc/150?u=2" alt="User 2">
                <img class="w-16 h-16 rounded-full border-4 border-purple-500 shadow-xl" src="https://i.pravatar.cc/150?u=3" alt="User 3">
                <div class="w-16 h-16 rounded-full border-4 border-purple-500 bg-white/20 backdrop-blur-md flex items-center justify-center text-white font-bold shadow-xl">+99</div>
            </div>
        </div>
        
        <flux:heading size="5xl" class="!text-white mb-6 font-extrabold tracking-tight">{{ __('Join the Community', 'sage') }}</flux:heading>
        <flux:subheading size="xl" class="!text-white/90 max-w-2xl mx-auto mb-12 text-balance">{{ __('Connect, share and grow with thousands of active users in real time thanks to the magic of Livewire 4.', 'sage') }}</flux:subheading>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
            <flux:button size="base" variant="filled" class="!bg-white !text-purple-900 shadow-xl border-none hover:!bg-zinc-100" icon="user-plus">{{ __('Sign Up Free', 'sage') }}</flux:button>
            <flux:button size="base" variant="ghost" icon="play-circle" class="!text-white hover:!bg-white/10">{{ __('See It in Action', 'sage') }}</flux:button>
        </div>
    </div>
</flux:main>
