<?php

use Livewire\Component;

new class extends Component
{
    public string $query = '';
    public array $results = [];

    public function mount()
    {
        $this->query = get_search_query() ?: '';
    }

    public function updatedQuery($value)
    {
        if (strlen($value) < 2) {
            $this->results = [];
            return;
        }

        // Search for posts, pages, and products
        $args = [
            's' => $value,
            'post_type' => ['post', 'page', 'product'],
            'post_status' => 'publish',
            'posts_per_page' => 5,
        ];

        $query = new \WP_Query($args);
        
        $this->results = array_map(function($post) {
            $isProduct = $post->post_type === 'product';
            $priceHtml = '';
            $imageUrl = '';
            
            if ($isProduct && function_exists('wc_get_product')) {
                $product = wc_get_product($post->ID);
                if ($product) {
                    $priceHtml = $product->get_price_html();
                    $imageId = $product->get_image_id();
                    $imageUrl = $imageId ? wp_get_attachment_image_url($imageId, 'thumbnail') : wc_placeholder_img_src('thumbnail');
                }
            } else {
                $imageId = get_post_thumbnail_id($post->ID);
                $imageUrl = $imageId ? wp_get_attachment_image_url($imageId, 'thumbnail') : '';
            }

            return [
                'id' => $post->ID,
                'title' => get_the_title($post->ID),
                'url' => get_permalink($post->ID),
                'type' => $post->post_type,
                'price' => $priceHtml,
                'image' => $imageUrl,
            ];
        }, $query->posts);
    }
    
    public function submitSearch()
    {
        if (!empty($this->query)) {
            $this->redirect(home_url('/?s=' . urlencode($this->query)));
        }
    }
}; ?>

<div class="relative w-full" x-data="{ open: true }" @click.away="open = false">
    {{-- Search Form --}}
    <form wire:submit="submitSearch" role="search" method="get" class="search-form flex w-full items-center gap-2" action="{{ home_url('/') }}">
        {{-- Livewire model.live triggers AJAX search as user types --}}
        <flux:input 
            wire:model.live.debounce.300ms="query"
            type="search" 
            name="s" 
            placeholder="{!! esc_attr_x('Search &hellip;', 'placeholder', 'sage') !!}" 
            aria-label="{{ _x('Search for:', 'label', 'sage') }}" 
            class="flex-1"
            autocomplete="off"
            @focus="open = true"
            @input="open = true"
        />

        <flux:button type="submit" variant="primary">
            {{ _x('Search', 'submit button', 'sage') }}
        </flux:button>
    </form>

    {{-- Dropdown Results --}}
    @if(strlen($query) >= 2)
        <div x-show="open" x-transition.opacity class="absolute z-50 mt-2 w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-xl overflow-hidden">
            @if(count($results) > 0)
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800 max-h-96 overflow-y-auto w-full list-none p-0 m-0">
                    @foreach($results as $result)
                        <li class="m-0 p-0">
                            <a href="{{ $result['url'] }}" wire:navigate class="flex items-center gap-4 p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors w-full no-underline">
                                @if($result['image'])
                                    <img src="{{ $result['image'] }}" alt="{!! esc_attr($result['title']) !!}" class="w-12 h-12 object-cover rounded-md bg-zinc-100 dark:bg-zinc-800">
                                @endif
                                
                                <div class="flex-1 min-w-0 flex flex-col justify-center">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                        {!! $result['title'] !!}
                                    </div>
                                    <div class="text-xs text-zinc-500 flex items-center gap-2 mt-0.5">
                                        <span class="px-1.5 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 capitalize">{{ $result['type'] }}</span>
                                        @if($result['price'])
                                            <span class="text-accent-600 dark:text-accent-400 font-medium ml-auto flex items-center">{!! $result['price'] !!}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="p-2 border-t border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 text-center">
                    <button type="button" wire:click="submitSearch" class="w-full text-center text-sm text-accent-600 dark:text-accent-400 hover:text-accent-700 font-medium py-1">
                        {{ __('View all results', 'sage') }}
                    </button>
                </div>
            @else
                <div class="p-6 text-center text-sm text-zinc-500 dark:text-zinc-400 flex flex-col items-center">
                    <svg class="w-8 h-8 text-zinc-300 dark:text-zinc-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>{{ __('No results found for', 'sage') }} "<span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $query }}</span>"</span>
                </div>
            @endif
        </div>
    @endif
</div>