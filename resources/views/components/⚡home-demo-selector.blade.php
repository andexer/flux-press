<?php
use Livewire\Component;

new class extends Component {
    public string $demo = 'corporate';
    public array $demos = [];

    public function mount(): void {
        $this->demos = config('theme.home_demos', []);
        $this->demo = get_option('flux_home_demo', 'corporate');
    }

    public function setDemo(string $demo): void {
        update_option('flux_home_demo', $demo);
        $this->demo = $demo;
        $this->dispatch('demo-changed', demo: $demo);
    }
};
?>
<div>
    <div class="fixed top-4 right-4 z-[99]">
        <flux:dropdown position="bottom-end">
            <flux:button icon="sparkles" variant="primary">
                {{ $this->demos[$demo]['name'] ?? 'Demo' }}
            </flux:button>
            
            <flux:menu>
                @foreach($demos as $key => $data)
                    <flux:menu.item 
                        wire:click="setDemo('{{ $key }}')"
                        icon="{{ $data['icon'] }}"
                        wire:key="demo-selector-{{ $key }}"
                        class="{{ $demo === $key ? 'bg-accent-50 dark:bg-accent-500/10 text-accent-600 dark:text-accent-400' : '' }}">
                        {{ $data['name'] }}
                    </flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    </div>
</div>