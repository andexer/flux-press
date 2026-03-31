@php
    $title = $sectionData['title'] ?? '';
    $videoUrl = $sectionData['video_url'] ?? '';
    $poster = $sectionData['poster'] ?? '';
@endphp

@if(!empty($videoUrl))
<flux:main class="py-14 sm:py-16 bg-zinc-900 dark:bg-black border-b border-zinc-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <flux:heading size="4xl" class="mb-8 text-center tracking-tight !font-black text-white">
                {{ $title }}
            </flux:heading>
        @endif
        <div class="relative aspect-video rounded-2xl overflow-hidden bg-zinc-800">
            @if($poster)
                <img src="{{ $poster }}" alt="{{ $title }}" class="absolute inset-0 w-full h-full object-cover" />
            @endif
            <div class="absolute inset-0 flex items-center justify-center">
                <a href="{{ $videoUrl }}" target="_blank" class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center hover:bg-white/30 transition-colors">
                    <flux:icon icon="play" class="size-10 text-white ml-1" />
                </a>
            </div>
        </div>
    </div>
</flux:main>
@endif