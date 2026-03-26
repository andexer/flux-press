<?php

use Livewire\Component;

new class extends Component
{
    public string $query = '';
    public array $results = [];
    public string $variant = 'default';
    public string $scope = 'all';
    public bool $showScope = false;

    public function mount(?string $variant = null, ?bool $showScope = null): void
    {
        $allowedVariants = ['default', 'market'];
        $candidateVariant = sanitize_key((string) ($variant ?? 'default'));

        $this->variant = in_array($candidateVariant, $allowedVariants, true)
            ? $candidateVariant
            : 'default';

        $this->showScope = is_bool($showScope) ? $showScope : $this->variant === 'market';
        $this->query = get_search_query() ?: '';
    }

    public function updatedScope($value): void
    {
        $allowedScopes = ['all', 'product', 'post', 'page'];
        $candidateScope = sanitize_key((string) $value);

        if (! in_array($candidateScope, $allowedScopes, true)) {
            $this->scope = 'all';
        }

        if (strlen($this->query) >= 2) {
            $this->updatedQuery($this->query);
        }
    }

    public function updatedQuery($value): void
    {
        $searchValue = trim((string) $value);

        if (strlen($searchValue) < 2) {
            $this->results = [];

            return;
        }

        $postTypes = $this->resolvePostTypes();
        $args = [
            's'              => $searchValue,
            'post_type'      => $postTypes,
            'post_status'    => 'publish',
            'posts_per_page' => 6,
        ];

        $query = new \WP_Query($args);

        $this->results = array_map(function ($post) {
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
                'id'    => $post->ID,
                'title' => get_the_title($post->ID),
                'url'   => get_permalink($post->ID),
                'type'  => $post->post_type,
                'price' => $priceHtml,
                'image' => $imageUrl,
            ];
        }, $query->posts);
    }

    public function submitSearch(): void
    {
        if ($this->query === '') {
            return;
        }

        $params = [
            's' => $this->query,
        ];

        $postTypes = $this->resolvePostTypes();
        if (count($postTypes) === 1) {
            $params['post_type'] = $postTypes[0];
        }

        $this->redirect(home_url('/?' . http_build_query($params)));
    }

    /**
     * @return array<int,string>
     */
    protected function resolvePostTypes(): array
    {
        return match ($this->scope) {
            'product' => ['product'],
            'post' => ['post'],
            'page' => ['page'],
            default => ['post', 'page', 'product'],
        };
    }
}; ?>

@php
    $isMarket = $variant === 'market';
    $livewireComponentId = (string) ($this->getId() ?? '');
    if ($livewireComponentId === '') {
        $livewireComponentId = (string) spl_object_id($this);
    }
    $scopeFieldId = 'global-search-scope-' . $livewireComponentId;
@endphp

<div class="relative w-full" x-data="{ open: true }" @click.away="open = false">
    <form
        wire:submit="submitSearch"
        role="search"
        method="get"
        class="search-form flex w-full items-center {{ $isMarket ? 'flux-market-search gap-2' : 'gap-2' }}"
        action="{{ home_url('/') }}"
    >
        @if($showScope)
            <label class="sr-only" for="{{ $scopeFieldId }}">{{ __('Alcance', 'flux-press') }}</label>
            <div class="flex items-center gap-2">
                <select
                    id="{{ $scopeFieldId }}"
                    wire:model.live="scope"
                    class="{{ $isMarket ? 'flux-market-search__scope' : 'h-11 min-w-[6.6rem] rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm font-semibold text-zinc-600 focus:border-accent-500 focus:outline-none focus:ring-2 focus:ring-accent-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200' }}"
                    name="post_type"
                >
                    <option value="all">{{ __('All', 'flux-press') }}</option>
                    <option value="product">{{ __('Products', 'flux-press') }}</option>
                    <option value="post">{{ __('Posts', 'flux-press') }}</option>
                    <option value="page">{{ __('Pages', 'flux-press') }}</option>
                </select>
                @if($isMarket)
                    <span class="hidden h-7 w-px shrink-0 bg-zinc-200 md:block dark:bg-zinc-700/80" aria-hidden="true"></span>
                @endif
            </div>
        @endif

        <flux:input
            wire:model.live.debounce.300ms="query"
            type="search"
            name="s"
            placeholder="{{ $isMarket ? __('Search for anything', 'flux-press') : _x('Search ...', 'placeholder', 'flux-press') }}"
            aria-label="{{ _x('Search for:', 'label', 'flux-press') }}"
            class="{{ $isMarket ? 'flux-market-search__input' : 'flex-1' }}"
            autocomplete="off"
            @focus="open = true"
            @input="open = true"
        />

        @if($isMarket)
            <button
                type="submit"
                class="flux-market-search__submit"
                aria-label="{{ esc_attr(_x('Search', 'submit button', 'flux-press')) }}"
            >
                <flux:icon.magnifying-glass class="size-5" />
            </button>
        @else
            <flux:button type="submit" variant="primary">
                {{ _x('Search', 'submit button', 'flux-press') }}
            </flux:button>
        @endif
    </form>

    @if(strlen($query) >= 2)
        <div x-show="open" x-transition.opacity class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
            @if(count($results) > 0)
                <ul class="m-0 max-h-96 w-full list-none divide-y divide-zinc-100 overflow-y-auto p-0 dark:divide-zinc-800">
                    @foreach($results as $result)
                        <li class="m-0 p-0">
                            <a href="{{ $result['url'] }}" wire:navigate class="flex w-full items-center gap-4 p-3 no-underline transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                @if($result['image'])
                                    <img src="{{ $result['image'] }}" alt="{!! esc_attr($result['title']) !!}" class="h-12 w-12 rounded-md bg-zinc-100 object-cover dark:bg-zinc-800">
                                @endif

                                <div class="flex min-w-0 flex-1 flex-col justify-center">
                                    <div class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {!! $result['title'] !!}
                                    </div>
                                    <div class="mt-0.5 flex items-center gap-2 text-xs text-zinc-500">
                                        <span class="rounded-md bg-zinc-100 px-1.5 py-0.5 capitalize dark:bg-zinc-800">{{ $result['type'] }}</span>
                                        @if($result['price'])
                                            <span class="ml-auto flex items-center font-medium text-accent-600 dark:text-accent-400">{!! $result['price'] !!}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-zinc-100 bg-zinc-50 p-2 text-center dark:border-zinc-800 dark:bg-zinc-900/50">
                    <button type="button" wire:click="submitSearch" class="w-full py-1 text-center text-sm font-medium text-accent-600 hover:text-accent-700 dark:text-accent-400">
                        {{ __('View all results', 'flux-press') }}
                    </button>
                </div>
            @else
                <div class="flex flex-col items-center p-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    <svg class="mb-3 h-8 w-8 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>{{ __('No results found for', 'flux-press') }} "<span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $query }}</span>"</span>
                </div>
            @endif
        </div>
    @endif
</div>
