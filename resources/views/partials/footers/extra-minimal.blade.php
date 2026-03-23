<footer class="bg-transparent border-t border-zinc-200/80 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                &copy; {{ $currentYear }} {!! $siteName !!}.
            </p>

            <div class="flex flex-wrap items-center gap-1">
                @php
                    $links = is_array($quickLinks) ? array_slice($quickLinks, 0, 4) : [];
                @endphp
                @foreach($links as $item)
                    <flux:button
                        variant="ghost"
                        size="sm"
                        href="{{ $item->url }}"
                        wire:navigate
                        class="!text-zinc-500 dark:!text-zinc-400 hover:!text-zinc-900 dark:hover:!text-zinc-100"
                    >
                        {{ $item->title }}
                    </flux:button>
                @endforeach
            </div>
        </div>
    </div>
</footer>
