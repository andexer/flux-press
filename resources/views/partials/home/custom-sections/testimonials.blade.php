@php
    $title = $sectionData['title'] ?? '';
    $items = $sectionData['items'] ?? [];
@endphp

@if(!empty($items))
<flux:main class="py-14 sm:py-16 bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <flux:heading size="4xl" class="mb-10 text-center tracking-tight !font-black text-zinc-900 dark:text-zinc-100">
                {{ $title }}
            </flux:heading>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($items as $testimonial)
                @if(!empty($testimonial['quote']))
                    <flux:card class="p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800">
                        <div class="flex gap-1 mb-3">
                            @for($i = 0; $i < 5; $i++)
                                <flux:icon icon="star" class="size-4 text-yellow-500" />
                            @endfor
                        </div>
                        <flux:text class="text-zinc-700 dark:text-zinc-300 italic">
                            "{{ $testimonial['quote'] }}"
                        </flux:text>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-accent-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($testimonial['author'] ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $testimonial['author'] ?? '' }}</p>
                                @if(!empty($testimonial['role']))
                                    <p class="text-sm text-zinc-500">{{ $testimonial['role'] }}</p>
                                @endif
                            </div>
                        </div>
                    </flux:card>
                @endif
            @endforeach
        </div>
    </div>
</flux:main>
@endif