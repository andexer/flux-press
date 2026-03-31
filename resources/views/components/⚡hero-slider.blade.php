<?php

use Livewire\Component;

new class extends Component
{
    public $currentSlide = 0;

    public $slides = [
        [
            'title' => 'Transforma tu sitio web',
            'subtitle' => 'Escalabilidad empresarial con Sage 10, Livewire 4, y los componentes dinámicos de Flux UI.',
            'primaryCta' => 'Comenzar Gratis',
            'secondaryCta' => 'Ver Demo',
            'primaryHref' => '#',
            'secondaryHref' => '#',
        ],
        [
            'title' => 'Diseño Premium Reactivo',
            'subtitle' => 'Layouts modernos y componentes modulares listos para Producción con Tailwind CSS.',
            'primaryCta' => 'Explorar Componentes',
            'secondaryCta' => 'Documentación',
            'primaryHref' => '#',
            'secondaryHref' => '#',
        ],
        [
            'title' => 'Rendimiento Inigualable',
            'subtitle' => 'Arquitectura híbrida optimizada para máxima velocidad y mínima carga en el servidor.',
            'primaryCta' => 'Evaluar Rendimiento',
            'secondaryCta' => 'Casos de Éxito',
            'primaryHref' => '#',
            'secondaryHref' => '#',
        ]
    ];

    public function nextSlide()
    {
        $this->currentSlide = ($this->currentSlide + 1) % count($this->slides);
    }

    public function prevSlide()
    {
        $this->currentSlide = ($this->currentSlide - 1 + count($this->slides)) % count($this->slides);
    }
    
    public function goToSlide($index)
    {
        $this->currentSlide = $index;
    }
};
?>

<div class="relative w-full overflow-hidden group">
    @php
        $slide = $slides[$currentSlide];
    @endphp
    {{-- Agregamos key de Livewire para forzar la re-renderización de la transición y que no se pierda --}}
    <section wire:key="slide-{{ $currentSlide }}" class="relative min-h-[60vh] md:min-h-[70vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-950 transition-all duration-500 ease-in-out">
        {{-- Patrón de fondo decorativo --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 1px); background-size: 40px 40px;"></div>

        {{-- Gradientes decorativos dinámicos --}}
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-accent-500/20 rounded-full blur-[120px] transition-transform duration-[2s] ease-in-out" style="transform: translate({{ $currentSlide * 10 }}%, {{ $currentSlide * 5 }}%)"></div>
        <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-accent-600/10 rounded-full blur-[120px] transition-transform duration-[2s] ease-in-out" style="transform: translate(-{{ $currentSlide * 10 }}%, -{{ $currentSlide * 5 }}%)"></div>

        {{-- Contenido principal animado en cambio de slide --}}
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 py-20 text-center animate-in fade-in slide-in-from-bottom-4 duration-700">
            <flux:card class="!bg-white/5 !backdrop-blur-md !border-white/10 p-8 md:p-14 shadow-2xl transform transition-all duration-500 hover:scale-[1.02]">
                <flux:heading size="xl" class="!text-white mb-4 !text-3xl md:!text-4xl lg:!text-5xl !leading-tight">
                    {{ $slide['title'] }}
                </flux:heading>

                <flux:subheading class="!text-zinc-300 text-lg max-w-2xl mx-auto mb-8">
                    {{ $slide['subtitle'] }}
                </flux:subheading>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <flux:button variant="primary" size="base" icon="sparkles" href="{{ $slide['primaryHref'] }}" wire:navigate>
                        {{ $slide['primaryCta'] }}
                    </flux:button>
                    <flux:button variant="ghost" size="base" icon="play-circle" href="{{ $slide['secondaryHref'] }}" class="!text-white !border-white/20 hover:!bg-white/10" wire:navigate>
                        {{ $slide['secondaryCta'] }}
                    </flux:button>
                </div>
            </flux:card>

            {{-- Trust indicators --}}
            <div class="mt-10 flex flex-wrap justify-center items-center gap-6 text-zinc-400 text-sm">
                <div class="flex items-center gap-2">
                    <flux:icon.shield-check class="size-5 text-green-400" />
                    {{ __('Seguro & Rápido', 'sage') }}
                </div>
                <div class="flex items-center gap-2">
                    <flux:icon.bolt class="size-5 text-yellow-400" />
                    {{ __('Alto Rendimiento', 'sage') }}
                </div>
                <div class="flex items-center gap-2">
                    <flux:icon.code-bracket class="size-5 text-accent-400" />
                    {{ __('Open Source', 'sage') }}
                </div>
            </div>
        </div>

        {{-- Navegación del Slider --}}
        <button wire:click="prevSlide" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-full bg-black/20 text-white backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity hover:bg-black/40 focus:opacity-100">
            <flux:icon.chevron-left class="size-6" />
        </button>
        <button wire:click="nextSlide" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-full bg-black/20 text-white backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity hover:bg-black/40 focus:opacity-100">
            <flux:icon.chevron-right class="size-6" />
        </button>

        {{-- Puntos de Paginación --}}
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-4">
            @foreach($slides as $index => $s)
                <button wire:click="goToSlide({{ $index }})" 
                        class="w-3 h-3 rounded-full transition-all duration-300 outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 focus:ring-offset-zinc-900 {{ $currentSlide === $index ? 'bg-accent-500 scale-125' : 'bg-white/30 hover:bg-white/50' }}"
                        aria-label="Ir a la diapositiva {{ $index + 1 }}"></button>
            @endforeach
        </div>
    </section>
</div>