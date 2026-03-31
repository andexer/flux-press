@php
    $title = $sectionData['title'] ?? '';
    $items = $sectionData['items'] ?? [];
@endphp

@if(!empty($items))
<flux:main class="py-14 sm:py-16 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <flux:heading size="4xl" class="mb-10 text-center tracking-tight !font-black text-zinc-900 dark:text-zinc-100">
                {{ $title }}
            </flux:heading>
        @endif
        <div class="space-y-4">
            @foreach($items as $faq)
                @if(!empty($faq['question']))
                    <flux:card class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <details class="group">
                            <summary class="flex justify-between items-center cursor-pointer list-none">
                                <flux:heading size="md" class="!font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $faq['question'] }}
                                </flux:heading>
                                <flux:icon icon="chevron-down" class="size-5 text-zinc-500 group-open:rotate-180 transition-transform" />
                            </summary>
                            <div class="mt-4 text-zinc-600 dark:text-zinc-400">
                                {{ $faq['answer'] ?? '' }}
                            </div>
                        </details>
                    </flux:card>
                @endif
            @endforeach
        </div>
    </div>
</flux:main>
@endif