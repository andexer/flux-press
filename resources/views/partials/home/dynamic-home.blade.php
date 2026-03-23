@php
    $layoutKey = $homeLayout ?? 'corporate';
    $layoutLabel = (string) (config("theme-interface.home.layouts.{$layoutKey}") ?? $layoutKey);
@endphp

@if($layoutKey === 'ecommerce')
    @include('partials.home.layouts.ecommerce')
@else
    @include('partials.home.layouts.standard')
@endif
