@php
    $items = isset($items) && is_array($items) ? $items : [];
    $containerClass = (string) ($containerClass ?? 'flex items-center gap-2');
    $linkClass = (string) ($linkClass ?? 'inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:hover:text-zinc-50');
    $limit = isset($limit) ? max(1, (int) $limit) : null;

    if ($limit !== null) {
        $items = array_slice($items, 0, $limit);
    }
@endphp

@if(! empty($items))
    <div class="{{ $containerClass }}">
        @foreach($items as $item)
            @php
                $title = is_object($item)
                    ? (string) ($item->title ?? '')
                    : (string) ($item['title'] ?? '');
                $url = is_object($item)
                    ? (string) ($item->url ?? '')
                    : (string) ($item['url'] ?? '');
            @endphp

            @continue($title === '' || $url === '')

            <a href="{{ $url }}" wire:navigate class="{{ $linkClass }}">
                {{ $title }}
            </a>
        @endforeach
    </div>
@endif
