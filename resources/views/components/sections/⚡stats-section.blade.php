<?php
use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    public string $demo = 'corporate';

    public function mount(string $demo = 'corporate'): void {
        $this->demo = $demo;
    }

    #[On('demo-changed')]
    public function changeDemo(string $demo): void {
        $this->demo = $demo;
    }
};
?>
<div>
    @if(in_array($demo, ['corporate', 'ecommerce', 'gaming']))
    <flux:main class="py-20 bg-zinc-900 dark:bg-black border-y border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 divide-x divide-zinc-800/50">
                @php
                    $statsMap = [
                        'red-social' => [
                            ['label' => __('Cuentas Activas', 'sage'), 'value' => '2M+'],
                            ['label' => __('Posts Diarios', 'sage'), 'value' => '800k'],
                            ['label' => __('Interacciones', 'sage'), 'value' => '15M'],
                            ['label' => __('Países', 'sage'), 'value' => '190+'],
                        ],
                        'ecommerce' => [
                            ['label' => __('Productos', 'sage'), 'value' => '15k+'],
                            ['label' => __('Ventas Totales', 'sage'), 'value' => '$4.2M'],
                            ['label' => __('Clientes Felices', 'sage'), 'value' => '50k'],
                            ['label' => __('Conversión', 'sage'), 'value' => '4.5%'],
                        ],
                        'gaming' => [
                            ['label' => __('Jugadores', 'sage'), 'value' => '120k'],
                            ['label' => __('Torneos', 'sage'), 'value' => '500'],
                            ['label' => __('Premios', 'sage'), 'value' => '$1M'],
                            ['label' => __('Matchmaking', 'sage'), 'value' => '0.5s'],
                        ],
                        'default' => [
                            ['label' => __('Satisfacción', 'sage'), 'value' => '99%'],
                            ['label' => __('Soporte', 'sage'), 'value' => '24/7'],
                            ['label' => __('Usuarios', 'sage'), 'value' => '10k+'],
                            ['label' => __('Velocidad', 'sage'), 'value' => '50x'],
                        ]
                    ];
                    $currentStats = $statsMap[$demo] ?? $statsMap['default'];
                @endphp

                @foreach($currentStats as $stat)
                    <div>
                        <flux:heading size="5xl" class="!text-white font-extrabold mb-2">{{ $stat['value'] }}</flux:heading>
                        <flux:text class="text-zinc-400 font-medium uppercase tracking-wider text-sm">{{ $stat['label'] }}</flux:text>
                    </div>
                @endforeach
            </div>
        </div>
    </flux:main>
    @endif
</div>