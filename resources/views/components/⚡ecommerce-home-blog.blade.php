<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function posts(): array
    {
        $service = app(HomeEcommerceDataService::class);
        $settings = $service->settings();
        $limits = is_array($settings['limits'] ?? null) ? $settings['limits'] : [];

        return $service->latestPostsData(max(1, (int) ($limits['blog'] ?? 6)));
    }
}; ?>

<section class="py-12 sm:py-14 bg-zinc-50 dark:bg-zinc-950/80 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 sm:mb-8 flex items-end justify-between gap-4">
            <div>
                <flux:badge color="sky" class="mb-2 uppercase tracking-widest">{{ __('Blog', 'flux-press') }}</flux:badge>
                <flux:heading size="3xl" class="!font-black tracking-tight">{{ __('Contenido reciente', 'flux-press') }}</flux:heading>
            </div>
            @php $postsPage = get_permalink(get_option('page_for_posts')); @endphp
            @if(is_string($postsPage) && $postsPage !== '')
                <flux:button href="{{ $postsPage }}" wire:navigate variant="ghost" icon="arrow-right" class="max-sm:hidden">
                    {{ __('Ver blog', 'flux-press') }}
                </flux:button>
            @endif
        </div>

        @if(empty($this->posts))
            <flux:callout color="zinc" icon="document-text">
                <flux:callout.heading>{{ __('No hay entradas publicadas.', 'flux-press') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Publica posts para mostrar esta seccion en el Home ecommerce.', 'flux-press') }}</flux:callout.text>
            </flux:callout>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($this->posts as $post)
                    <article class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
                        @if($post['image'] !== '')
                            <a href="{{ $post['url'] }}" wire:navigate class="block aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="h-full w-full object-cover transition-transform duration-500 hover:scale-105" loading="lazy" />
                            </a>
                        @endif
                        <div class="p-5">
                            <p class="text-xs uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ $post['date'] }}</p>
                            <h3 class="mt-2 text-lg font-bold text-zinc-900 dark:text-zinc-100 line-clamp-2">
                                <a href="{{ $post['url'] }}" wire:navigate class="hover:text-accent-700 dark:hover:text-accent-400 transition-colors">{{ $post['title'] }}</a>
                            </h3>
                            <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-3">{{ $post['excerpt'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
