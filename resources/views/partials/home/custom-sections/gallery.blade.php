@php
    $title = $sectionData['title'] ?? '';
    $images = $sectionData['images'] ?? [];
@endphp

@if(!empty($images))
<flux:main class="py-14 sm:py-16 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <flux:heading size="4xl" class="mb-8 text-center tracking-tight !font-black text-zinc-900 dark:text-zinc-100">
                {{ $title }}
            </flux:heading>
        @endif
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($images as $image)
                @if(!empty($image))
                    <div class="aspect-square rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                        <img src="{{ $image }}" alt="" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" loading="lazy" />
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</flux:main>
@endif