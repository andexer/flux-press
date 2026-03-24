@php
    $contentMode = (string) ($homeEcommerceContentMode ?? 'hybrid');
    $allowedModes = ['builder', 'hybrid', 'editor'];
    if (! in_array($contentMode, $allowedModes, true)) {
        $contentMode = 'hybrid';
    }

    $isVisualBuilderSession = is_user_logged_in()
        && current_user_can('edit_theme_options')
        && isset($_GET['flux_builder'])
        && (string) $_GET['flux_builder'] !== '0';

    $editorContent = trim((string) ($homeEditorContent ?? ''));
    $hasEditorContent = $editorContent !== '';
    $showBuilder = in_array($contentMode, ['builder', 'hybrid'], true) || $isVisualBuilderSession;
    $showEditor = in_array($contentMode, ['editor', 'hybrid'], true) && ! $isVisualBuilderSession;
    $visibleSections = [];
    $brandSectionAvailable = false;
    $editorSections = is_array($homeEcommerceEditorSections ?? null) ? $homeEcommerceEditorSections : [];

    if ($showBuilder) {
        $ecommerceService = app(\App\Services\HomeEcommerceDataService::class);
        $visibleSections = $ecommerceService->visibleSections();
        $brandSectionAvailable = $ecommerceService->isSectionAvailable('brands');
    }

    $builderHiddenSections = ($showEditor && $hasEditorContent && ! $isVisualBuilderSession)
        ? $editorSections
        : [];
    $renderBuilder = $showBuilder;
    if ($renderBuilder && ! empty($builderHiddenSections) && ! empty($visibleSections)) {
        $remainingBuilderSections = array_values(array_diff($visibleSections, $builderHiddenSections));
        $renderBuilder = ! empty($remainingBuilderSections);
    }
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

    @if($renderBuilder)
        <livewire:ecommerce-home-builder :hidden-sections="$builderHiddenSections" />

        @if(
            $brandSectionAvailable
            && ! in_array('brands', $visibleSections, true)
            && ! in_array('brands', $editorSections, true)
        )
            <livewire:ecommerce-home-brands :key="'ecommerce-home-brands-fallback'" />
        @endif
    @endif

    @if(
        is_user_logged_in()
        && current_user_can('edit_theme_options')
        && $isVisualBuilderSession
    )
        <livewire:home-visual-builder :key="'home-visual-builder-frontend-drawer'" />
    @endif
</section>
