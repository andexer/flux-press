@php
    $contentMode = (string) ($homeEcommerceContentMode ?? 'hybrid');
    $allowedModes = ['builder', 'hybrid', 'editor'];
    if (! in_array($contentMode, $allowedModes, true)) {
        $contentMode = 'hybrid';
    }

    $editorContent = trim((string) ($homeEditorContent ?? ''));
    $hasEditorContent = $editorContent !== '';
    $showBuilder = in_array($contentMode, ['builder', 'hybrid'], true);
    $showEditor = in_array($contentMode, ['editor', 'hybrid'], true);
@endphp

<section class="overflow-hidden" wire:key="home-ecommerce-layout">
    @if($showEditor && $hasEditorContent)
        <div class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 [&>*:first-child]:mt-0">
                {!! $editorContent !!}
            </div>
        </div>
    @elseif($contentMode === 'editor')
        <section class="py-12 sm:py-14 border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <flux:callout color="amber" icon="pencil-square">
                    <flux:callout.heading>{{ __('Modo Editor activo pero no hay bloques publicados.', 'flux-press') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('Edita la pagina de inicio con Gutenberg o Elementor y publica contenido para mostrarlo aqui.', 'flux-press') }}</flux:callout.text>
                </flux:callout>
            </div>
        </section>
    @endif

    @if($showBuilder)
        <livewire:ecommerce-home-builder />
    @endif
</section>
