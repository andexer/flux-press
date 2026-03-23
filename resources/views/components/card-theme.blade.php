<section class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-4 flex flex-col gap-6">
            <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <flux:heading size="lg">Modo de visualización</flux:heading>
                <flux:subheading class="mb-6">Alterna entre tema claro u oscuro.</flux:subheading>
                
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun" />
                    <flux:radio value="dark" icon="moon" />
                    <flux:radio value="system" icon="computer-desktop" />
                </flux:radio.group>
            </div>
            <livewire:counter />
        </div>
        
        <div class="lg:col-span-8">
            <livewire:theme-settings />
        </div>
    </div>
</section>