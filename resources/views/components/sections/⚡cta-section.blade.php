<?php
use Livewire\Component;

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
    <flux:main class="py-24 relative overflow-hidden bg-accent-600 dark:bg-accent-700">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom,rgba(255,255,255,0.2)_0%,transparent_60%)] pointer-events-none"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            @php
                $ctaConfigs = [
                    'red-social' => [
                        'title' => __('¿Listo para unirte a la conversación?', 'sage'),
                        'subtitle' => __('Crea tu cuenta hoy y empieza a conectar con el mundo de forma instantánea.', 'sage'),
                        'primary_label' => __('Unirme Ahora', 'sage'),
                        'primary_icon' => 'user-plus'
                    ],
                    'ecommerce' => [
                        'title' => __('Empieza a vender profesionalmente.', 'sage'),
                        'subtitle' => __('Sube tus productos y lanza tu tienda online en menos de 5 minutos con Flux.', 'sage'),
                        'primary_label' => __('Crear Tienda', 'sage'),
                        'primary_icon' => 'shopping-bag'
                    ],
                    'gaming' => [
                        'title' => __('¿Listo para subir de nivel?', 'sage'),
                        'subtitle' => __('Únete a la liga competitiva y demuestra tus habilidades frente a miles.', 'sage'),
                        'primary_label' => __('Jugar Ahora', 'sage'),
                        'primary_icon' => 'gamepad'
                    ],
                    'default' => [
                        'title' => __('¿Listo para transformar tu web?', 'sage'),
                        'subtitle' => __('Descubre por qué cientos de desarrolladores eligen Flux Press para sus proyectos más exigentes.', 'sage'),
                        'primary_label' => __('Comenzar Ahora', 'sage'),
                        'primary_icon' => 'rocket-launch'
                    ]
                ];
                $currentCta = $ctaConfigs[$demo] ?? $ctaConfigs['default'];
            @endphp
            <flux:heading size="5xl" class="!text-white tracking-tight !font-bold mb-6">
                {{ $currentCta['title'] }}
            </flux:heading>
            <flux:subheading size="xl" class="!text-white/90 mb-10 text-balance">
                {{ $currentCta['subtitle'] }}
            </flux:subheading>
            <div class="flex justify-center gap-4">
                <flux:button size="xl" class="!bg-white !text-accent-700 border-none hover:shadow-xl shadow-lg" :icon="$currentCta['primary_icon']">{{ $currentCta['primary_label'] }}</flux:button>
                <flux:button size="xl" variant="ghost" class="!text-white hover:!bg-white/10" icon="chat-bubble-left-ellipsis">{{ __('Contactar Ventas', 'sage') }}</flux:button>
            </div>
        </div>
    </flux:main>
</div>