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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($items as $member)
                @if(!empty($member['name']))
                    <flux:card class="text-center p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800">
                        <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-accent-500 to-accent-700 flex items-center justify-center text-white text-2xl font-bold mb-4">
                            @if(!empty($member['image']))
                                <img src="{{ $member['image'] }}" alt="{{ $member['name'] }}" class="w-full h-full object-cover rounded-full" />
                            @else
                                {{ strtoupper(substr($member['name'], 0, 2)) }}
                            @endif
                        </div>
                        <flux:heading size="lg" class="!font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $member['name'] }}
                        </flux:heading>
                        @if(!empty($member['role']))
                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                {{ $member['role'] }}
                            </flux:text>
                        @endif
                    </flux:card>
                @endif
            @endforeach
        </div>
    </div>
</flux:main>
@endif