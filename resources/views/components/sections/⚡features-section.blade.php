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
    <flux:main class="py-24 bg-white dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <flux:badge color="accent" class="mb-4">{{ __('Características Premium', 'flux-press') }}</flux:badge>
                <flux:heading size="4xl" class="mb-4 tracking-tight !font-bold">
                    {{ __('Diseñado para Diferenciarte', 'flux-press') }}
                </flux:heading>
                <flux:subheading size="lg" class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Todo lo que necesitas para tu proyecto ') }} 
                    <span class="capitalize font-semibold text-accent-600 dark:text-accent-400">{{ $demo }}</span>
                    {{ __(', compilado con precisión y máxima velocidad.', 'flux-press') }}
                </flux:subheading>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @php
                    $features = [
                        'red-social' => [
                            ['icon' => 'users', 'title' => __('Comunidad Viva', 'flux-press'), 'desc' => __('Conecta con miles de usuarios en tiempo real.', 'flux-press')],
                            ['icon' => 'chat-bubble-left-right', 'title' => __('Mensajería', 'flux-press'), 'desc' => __('Chat instantáneo con cifrado de extremo a extremo.', 'flux-press')],
                            ['icon' => 'share', 'title' => __('Viralidad', 'flux-press'), 'desc' => __('Algoritmos diseñados para potenciar tu contenido.', 'flux-press')],
                        ],
                        'ecommerce' => [
                            ['icon' => 'shopping-cart', 'title' => __('Pago Seguro', 'flux-press'), 'desc' => __('Pasarelas de pago integradas y certificadas.', 'flux-press')],
                            ['icon' => 'truck', 'title' => __('Envíos Rápidos', 'flux-press'), 'desc' => __('Seguimiento en tiempo real de todos tus pedidos.', 'flux-press')],
                            ['icon' => 'archive-box', 'title' => __('Stock Infinito', 'flux-press'), 'desc' => __('Gestión de inventario automatizada y escalable.', 'flux-press')],
                        ],
                        'corporate' => [
                            ['icon' => 'building-office', 'title' => __('Soluciones Enterprise', 'flux-press'), 'desc' => __('Infraestructura robusta para grandes organizaciones.', 'flux-press')],
                            ['icon' => 'shield-check', 'title' => __('Seguridad Total', 'flux-press'), 'desc' => __('Cumplimiento de estándares internacionales de datos.', 'flux-press')],
                            ['icon' => 'presentation-chart-line', 'title' => __('Analítica Avanzada', 'flux-press'), 'desc' => __('Reportes detallados para decisiones estratégicas.', 'flux-press')],
                        ],
                        'default' => [
                            ['icon' => 'bolt', 'title' => __('Rendimiento SPA', 'flux-press'), 'desc' => __('Navegaciones instantáneas sin recargas de página.', 'flux-press')],
                            ['icon' => 'sparkles', 'title' => __('Componentes Flux', 'flux-press'), 'desc' => __('Suite increíble de componentes UI preconstruidos.', 'flux-press')],
                            ['icon' => 'swatch', 'title' => __('Múltiples Demos', 'flux-press'), 'desc' => __('Adapta el tema instantáneamente a tu negocio.', 'flux-press')],
                        ]
                    ];
                    $currentFeatures = $features[$demo] ?? $features['default'];
                @endphp

                @foreach($currentFeatures as $feature)
                    <flux:card class="border-none shadow-sm hover:shadow-md transition-all hover:-translate-y-1 bg-zinc-50 dark:bg-zinc-900/50">
                        <flux:icon :icon="$feature['icon']" class="size-10 text-accent-500 mb-6" />
                        <flux:heading size="xl" class="mb-3">{{ $feature['title'] }}</flux:heading>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                            {{ $feature['desc'] }}
                        </flux:text>
                    </flux:card>
                @endforeach
            </div>
        </div>
    </flux:main>
</div>