@extends('layouts.app')

@section('content')
<div class="min-h-[65vh] flex flex-col items-center justify-center px-4 py-16 text-center">
    <flux:badge size="lg" color="zinc" class="mb-6 font-mono tracking-widest text-lg">404 ERROR</flux:badge>
    
    <flux:heading size="xl" level="h1" class="mb-4 text-4xl sm:text-6xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
        {!! __('Página no encontrada', 'flux-press') !!}
    </flux:heading>
    
    <flux:subheading class="max-w-lg mx-auto mb-10 text-lg leading-relaxed text-zinc-600 dark:text-zinc-400">
        {!! __('Lo sentimos, pero parece que te has perdido. La página que estás buscando no existe, ha sido eliminada o está temporalmente fuera de servicio.', 'flux-press') !!}
    </flux:subheading>
    
    <div class="w-full max-w-md mx-auto space-y-8">
        <div class="text-left w-full shadow-sm rounded-xl relative z-30">
            {{-- Inyecta automáticamente el <livewire:global-search> gracias a Phase 3 --}}
            {!! get_search_form(false) !!}
        </div>
        
        <div class="flex items-center justify-center gap-4 pt-6 border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ home_url('/') }}" variant="primary" icon="home" wire:navigate>
                {!! __('Volver al Inicio', 'flux-press') !!}
            </flux:button>
        </div>
    </div>
</div>
@endsection
