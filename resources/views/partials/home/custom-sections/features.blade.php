@php
    $title = $sectionData['title'] ?? '';
    $items = $sectionData['items'] ?? [];
@endphp

@if(!empty($title) || !empty($items))
<flux:main class="py-14 sm:py-16 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <flux:heading size="3xl" class="mb-8 tracking-tight !font-black text-zinc-900 dark:text-zinc-100 text-center">
                {{ $title }}
            </flux:heading>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($items as $feature)
                @if(!empty($feature['title']))
                    <flux:card class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/60 p-5 shadow-sm">
                        @if(!empty($feature['icon']))
                            <flux:icon :icon="$feature['icon']" class="size-6 text-accent-600 dark:text-accent-400 mb-4" />
                        @endif
                        <flux:heading size="lg" class="!font-bold text-zinc-900 dark:text-zinc-100">{{ $feature['title'] }}</flux:heading>
                        @if(!empty($feature['text']))
                            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ $feature['text'] }}</flux:text>
                        @endif
                    </flux:card>
                @endif
            @endforeach
        </div>
    </div>
</flux:main>
@endif