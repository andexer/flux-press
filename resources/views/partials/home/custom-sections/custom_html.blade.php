@php
    $htmlContent = $sectionData['html_content'] ?? '';
@endphp

@if(!empty($htmlContent))
<flux:main class="py-8">
    <div class="max-w-7xl mx-auto px-4">
        {!! $htmlContent !!}
    </div>
</flux:main>
@endif