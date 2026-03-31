@php
    $layoutKey = $homeLayout ?? 'corporate';
    $layoutLabel = (string) (config("theme-interface.home.layouts.{$layoutKey}") ?? $layoutKey);
@endphp

@if($layoutKey === 'ecommerce')
    @include('partials.home.layouts.ecommerce')
@else
    @include('partials.home.layouts.standard')
@endif

@if(!empty($homeCustomSections) && is_array($homeCustomSections))
    @foreach($homeCustomSections as $section)
        @if($section['enabled'] ?? true)
            @includeIf('partials.home.custom-sections.' . $section['type'], ['sectionData' => $section['data'] ?? []])
        @endif
    @endforeach
@endif
