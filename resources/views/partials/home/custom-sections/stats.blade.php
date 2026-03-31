@php
    $title = $sectionData['title'] ?? '';
    $items = $sectionData['items'] ?? [];
@endphp

@if(!empty($items))
<flux:main class="py-10 sm:py-12 bg-zinc-900 dark:bg-black border-b border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <div class="text-center mb-8">
                <flux:heading size="2xl" class="tracking-tight !font-black text-white">
                    {{ $title }}
                </flux:heading>
            </div>
        @endif
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($items as $stat)
                @if(!empty($stat['value']))
                    <div class="rounded-2xl bg-white/5 border border-white/10 p-4 sm:p-5">
                        <p class="text-2xl sm:text-3xl font-black text-white">{{ $stat['value'] }}</p>
                        @if(!empty($stat['label']))
                            <p class="mt-1 text-xs sm:text-sm uppercase tracking-wider text-zinc-300">{{ $stat['label'] }}</p>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</flux:main>
@endif