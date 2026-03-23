<div class="fixed top-4 right-4 z-[99]">
    <flux:dropdown position="bottom-end">
        <flux:button icon="sparkles" variant="primary">
            {{ config("theme.home_demos.{$demo}.name") ?? 'Seleccionar Demo' }}
        </flux:button>
        
        <flux:menu>
            @foreach($demos as $key => $demoData)
                <flux:menu.item 
                    wire:click="setDemo('{{ $key }}')"
                    icon="{{ $demoData['icon'] }}"
                    wire:key="demo-selector-{{ $key }}">
                    {{ $demoData['name'] }}
                </flux:menu.item>
            @endforeach
        </flux:menu>
    </flux:dropdown>
</div>
