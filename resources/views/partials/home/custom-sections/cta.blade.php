@php
    $title = $sectionData['title'] ?? '';
    $description = $sectionData['description'] ?? '';
    $buttonText = $sectionData['button_text'] ?? __('Get Started', 'sage');
    $buttonUrl = $sectionData['button_url'] ?? '#';
    $bgColor = $sectionData['bg_color'] ?? 'bg-slate-900';
@endphp

@if(!empty($title))
<flux:main class="py-14 sm:py-16 {{ $bgColor }} border-b border-white/10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <flux:heading size="5xl" class="!font-black !text-white tracking-tight">
            {{ $title }}
        </flux:heading>
        @if($description)
            <flux:text class="mt-4 text-white/80 text-lg">
                {{ $description }}
            </flux:text>
        @endif
        @if($buttonText)
            <div class="mt-8">
                <flux:button size="lg" variant="filled" href="{{ $buttonUrl }}">
                    {{ $buttonText }}
                </flux:button>
            </div>
        @endif
    </div>
</flux:main>
@endif