@php
    $title = $sectionData['title'] ?? '';
    $subtitle = $sectionData['subtitle'] ?? '';
    $badge = $sectionData['badge'] ?? '';
    $badgeColor = $sectionData['badge_color'] ?? 'sky';
    $image = $sectionData['image'] ?? '';
    $primaryLabel = $sectionData['primary_label'] ?? __('Get Started', 'sage');
    $primaryUrl = $sectionData['primary_url'] ?? '#';
@endphp

@if(!empty($title))
<flux:main class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-sky-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-slate-900 border-b border-zinc-200 dark:border-zinc-800 py-16 sm:py-20">
    <div class="absolute inset-0 opacity-30 pointer-events-none">
        <div class="absolute top-0 right-0 h-72 w-72 rounded-full bg-white/70 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-72 w-72 rounded-full bg-zinc-200/60 dark:bg-zinc-700/40 blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="max-w-2xl">
                @if($badge)
                    <flux:badge :color="$badgeColor" class="mb-4 uppercase tracking-widest">{{ $badge }}</flux:badge>
                @endif
                <flux:heading size="6xl" class="tracking-tight !font-black text-zinc-900 dark:text-zinc-100">
                    {{ $title }}
                </flux:heading>
                @if($subtitle)
                    <flux:text class="mt-5 text-lg text-zinc-600 dark:text-zinc-300 leading-relaxed">
                        {{ $subtitle }}
                    </flux:text>
                @endif
                @if($primaryLabel)
                    <div class="mt-8">
                        <flux:button size="lg" href="{{ $primaryUrl }}">
                            {{ $primaryLabel }}
                        </flux:button>
                    </div>
                @endif
            </div>
            @if($image)
                <div class="hidden lg:block">
                    <img src="{{ $image }}" alt="{{ $title }}" class="rounded-2xl shadow-xl" />
                </div>
            @endif
        </div>
    </div>
</flux:main>
@endif