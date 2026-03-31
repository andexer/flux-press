@php
    $title = $sectionData['title'] ?? '';
    $limit = $sectionData['limit'] ?? 3;
    
    $homeEcommerceDataService = app(\App\Services\HomeEcommerceDataService::class);
    $posts = $homeEcommerceDataService->latestPostsData($limit);
@endphp

@if(!empty($posts))
<flux:main class="py-14 sm:py-16 bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <flux:heading size="4xl" class="mb-8 tracking-tight !font-black text-zinc-900 dark:text-zinc-100">
                {{ $title }}
            </flux:heading>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($posts as $post)
                <article class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    @if(!empty($post['image']))
                        <a href="{{ $post['url'] }}" class="block aspect-[16/9] overflow-hidden">
                            <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="h-full w-full object-cover" loading="lazy" />
                        </a>
                    @endif
                    <div class="p-5">
                        <p class="text-xs uppercase tracking-widest text-zinc-500 dark:text-zinc-400 mb-2">{{ $post['date'] }}</p>
                        <h3 class="text-lg font-bold leading-tight text-zinc-900 dark:text-zinc-100">
                            <a href="{{ $post['url'] }}" class="hover:text-accent-600 dark:hover:text-accent-400 transition-colors">{{ $post['title'] }}</a>
                        </h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">{{ $post['excerpt'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</flux:main>
@endif