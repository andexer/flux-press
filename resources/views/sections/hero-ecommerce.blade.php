<flux:main class="min-h-screen border-b border-zinc-200 dark:border-zinc-800 bg-gradient-to-r from-emerald-500 to-emerald-700 overflow-hidden relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 grid md:grid-cols-2 gap-12 items-center relative z-10">
        <div>
            <flux:badge color="zinc" class="!bg-white/20 !text-white !border-transparent mb-4">🛒 Tienda Premium</flux:badge>
            <flux:heading size="4xl" class="!text-white font-extrabold tracking-tight">Tu Tienda Online<br>Perfecta</flux:heading>
            <flux:subheading size="lg" class="!text-white/90 mt-6 max-w-lg text-balance">Todo lo que necesitas para vender online con máxima velocidad y conversión asegurada.</flux:subheading>
            <div class="mt-8 flex flex-wrap gap-4">
                <flux:button size="lg" variant="filled" class="!bg-white !text-emerald-900 border-none hover:!bg-zinc-100 shadow-xl" icon="shopping-cart">Comprar Ahora</flux:button>
                <flux:button variant="ghost" size="lg" icon="play-circle" class="!text-white hover:!bg-white/10">Ver Catálogo</flux:button>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <flux:card class="h-48 !bg-white/10 !border-white/20 backdrop-blur-md shadow-2xl"></flux:card>
            <flux:card class="h-48 !bg-white/10 !border-white/20 backdrop-blur-md shadow-2xl"></flux:card>
            <flux:card class="h-48 !bg-white/10 !border-white/20 backdrop-blur-md col-span-2 shadow-2xl"></flux:card>
        </div>
    </div>
</flux:main>
