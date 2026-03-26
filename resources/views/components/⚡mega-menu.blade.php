<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public array $items = [];
    public array $config = [];
    public string $layout = 'default';

    public function mount($items = [], $config = [], ?string $layout = null): void
    {
        $this->items = is_array($items) ? $items : [];
        $this->config = is_array($config) ? $config : [];
        $candidateLayout = sanitize_key((string) ($layout ?? 'default'));
        $this->layout = in_array($candidateLayout, ['default', 'centered'], true) ? $candidateLayout : 'default';
    }

    #[Computed]
    public function options(): array
    {
        return [
            'show_categories'    => (bool) ($this->config['show_categories'] ?? true),
            'show_top_rated'     => (bool) ($this->config['show_top_rated'] ?? true),
            'show_best_selling'  => (bool) ($this->config['show_best_selling'] ?? true),
            'show_pages'         => (bool) ($this->config['show_pages'] ?? true),
            'categories_limit'   => max(1, min(20, (int) ($this->config['categories_limit'] ?? 6))),
            'top_rated_limit'    => max(1, min(20, (int) ($this->config['top_rated_limit'] ?? 4))),
            'best_selling_limit' => max(1, min(20, (int) ($this->config['best_selling_limit'] ?? 4))),
            'pages_limit'        => max(1, min(20, (int) ($this->config['pages_limit'] ?? 6))),
            'featured_item_text' => (string) ($this->config['featured_item_text'] ?? __('Descubrir', 'flux-press')),
        ];
    }

    #[Computed]
    public function menuTree(): array
    {
        $options = $this->options();
        $roots = [];
        $childrenByParent = [];

        foreach ($this->items as $item) {
            if (! is_object($item)) {
                continue;
            }

            $id = $this->itemId($item);
            if ($id === '') {
                continue;
            }

            $parentId = (string) ($item->menu_item_parent ?? '0');
            if ($parentId === '' || $parentId === '0') {
                $roots[$id] = $item;
                continue;
            }

            $childrenByParent[$parentId][] = $item;
        }

        $hasSmartSections = $this->hasSmartSectionsEnabled();
        $tree = [];

        foreach ($roots as $rootId => $rootItem) {
            $children = [];
            foreach ($childrenByParent[$rootId] ?? [] as $child) {
                $children[] = [
                    'id'      => $this->itemId($child),
                    'title'   => (string) ($child->title ?? ''),
                    'url'     => (string) ($child->url ?? '#'),
                    'current' => $this->isCurrentUrl((string) ($child->url ?? '')),
                ];
            }

            $tree[] = [
                'id'        => $rootId,
                'title'     => (string) ($rootItem->title ?? ''),
                'url'       => (string) ($rootItem->url ?? '#'),
                'current'   => $this->isCurrentUrl((string) ($rootItem->url ?? '')),
                'children'  => $children,
                'has_panel' => ! empty($children) || $hasSmartSections,
            ];
        }

        if (empty($tree) && $hasSmartSections) {
            $shopUrl = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';
            $tree[] = [
                'id'        => 'featured',
                'title'     => $options['featured_item_text'],
                'url'       => $shopUrl ?: home_url('/'),
                'current'   => false,
                'children'  => [],
                'has_panel' => true,
            ];
        }

        return $tree;
    }

    #[Computed]
    public function productCategories(): array
    {
        $options = $this->options();

        if (! $options['show_categories'] || ! class_exists('WooCommerce')) {
            return [];
        }

        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'number'     => $options['categories_limit'],
            'orderby'    => 'count',
            'order'      => 'DESC',
        ]);

        if (! is_array($terms) || is_wp_error($terms)) {
            return [];
        }

        $result = [];
        foreach ($terms as $term) {
            if (! $term instanceof \WP_Term) {
                continue;
            }

            $termLink = get_term_link($term);
            if (is_wp_error($termLink)) {
                continue;
            }

            $result[] = [
                'id'    => (int) $term->term_id,
                'name'  => (string) $term->name,
                'url'   => (string) $termLink,
                'count' => (int) $term->count,
            ];
        }

        return $result;
    }

    #[Computed]
    public function topRatedProducts(): array
    {
        $options = $this->options();

        if (! $options['show_top_rated'] || ! class_exists('WooCommerce') || ! function_exists('wc_get_products')) {
            return [];
        }

        $products = wc_get_products([
            'limit'   => $options['top_rated_limit'],
            'status'  => 'publish',
            'orderby' => 'rating',
            'order'   => 'DESC',
            'return'  => 'objects',
        ]);

        if (! is_array($products)) {
            return [];
        }

        $result = [];
        foreach ($products as $product) {
            if (! $product instanceof \WC_Product) {
                continue;
            }

            $result[] = [
                'id'     => $product->get_id(),
                'name'   => $product->get_name(),
                'url'    => $product->get_permalink(),
                'image'  => get_the_post_thumbnail_url($product->get_id(), 'thumbnail'),
                'price'  => $product->get_price_html(),
                'rating' => (float) $product->get_average_rating(),
            ];
        }

        return $result;
    }

    #[Computed]
    public function wpPages(): array
    {
        $options = $this->options();

        if (! $options['show_pages']) {
            return [];
        }

        $pages = get_pages([
            'post_status' => 'publish',
            'sort_column' => 'menu_order,post_title',
            'sort_order'  => 'ASC',
            'number'      => $options['pages_limit'],
        ]);

        if (! is_array($pages)) {
            return [];
        }

        $result = [];
        foreach ($pages as $page) {
            if (! $page instanceof \WP_Post) {
                continue;
            }

            $url = get_permalink($page);
            if (! is_string($url) || $url === '') {
                continue;
            }

            $result[] = [
                'id'      => (int) $page->ID,
                'title'   => (string) $page->post_title,
                'url'     => $url,
                'current' => $this->isCurrentUrl($url),
            ];
        }

        return $result;
    }

    #[Computed]
    public function bestSellingProducts(): array
    {
        $options = $this->options();

        if (! $options['show_best_selling'] || ! class_exists('WooCommerce') || ! function_exists('wc_get_products')) {
            return [];
        }

        $limit = max(1, min(20, (int) $options['best_selling_limit']));
        $result = [];
        $seen = [];

        $products = wc_get_products([
            'limit'   => $options['best_selling_limit'],
            'status'  => 'publish',
            'orderby' => 'popularity',
            'order'   => 'DESC',
            'return'  => 'objects',
        ]);

        if (is_array($products)) {
            foreach ($products as $product) {
                if (! $product instanceof \WC_Product) {
                    continue;
                }

                $productId = (int) $product->get_id();
                if ($productId <= 0 || isset($seen[$productId])) {
                    continue;
                }

                $seen[$productId] = true;
                $result[] = [
                    'id'          => $productId,
                    'name'        => $product->get_name(),
                    'url'         => $product->get_permalink(),
                    'image'       => get_the_post_thumbnail_url($productId, 'thumbnail'),
                    'price'       => $product->get_price_html(),
                    'total_sales' => (int) $product->get_total_sales(),
                ];
            }
        }

        // Fallback when popularity lookup is empty/out of sync:
        // pull products by total_sales meta directly.
        if (count($result) < $limit) {
            $fallbackQuery = new \WP_Query([
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => max($limit * 3, 12),
                'fields'         => 'ids',
                'meta_key'       => 'total_sales',
                'orderby'        => [
                    'meta_value_num' => 'DESC',
                    'date'           => 'DESC',
                ],
            ]);

            $ids = $fallbackQuery->posts;
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $productId = (int) $id;
                    if ($productId <= 0 || isset($seen[$productId])) {
                        continue;
                    }

                    $product = wc_get_product($productId);
                    if (! $product instanceof \WC_Product) {
                        continue;
                    }

                    $seen[$productId] = true;
                    $result[] = [
                        'id'          => $productId,
                        'name'        => $product->get_name(),
                        'url'         => $product->get_permalink(),
                        'image'       => get_the_post_thumbnail_url($productId, 'thumbnail'),
                        'price'       => $product->get_price_html(),
                        'total_sales' => (int) $product->get_total_sales(),
                    ];

                    if (count($result) >= $limit) {
                        break;
                    }
                }
            }
        }

        // Final fallback: ensure the section is populated if products exist
        // but still without sales meta.
        if (count($result) < $limit) {
            $latestProducts = wc_get_products([
                'limit'   => $limit,
                'status'  => 'publish',
                'orderby' => 'date',
                'order'   => 'DESC',
                'return'  => 'objects',
            ]);

            if (is_array($latestProducts)) {
                foreach ($latestProducts as $product) {
                    if (! $product instanceof \WC_Product) {
                        continue;
                    }

                    $productId = (int) $product->get_id();
                    if ($productId <= 0 || isset($seen[$productId])) {
                        continue;
                    }

                    $seen[$productId] = true;
                    $result[] = [
                        'id'          => $productId,
                        'name'        => $product->get_name(),
                        'url'         => $product->get_permalink(),
                        'image'       => get_the_post_thumbnail_url($productId, 'thumbnail'),
                        'price'       => $product->get_price_html(),
                        'total_sales' => (int) $product->get_total_sales(),
                    ];

                    if (count($result) >= $limit) {
                        break;
                    }
                }
            }
        }

        return array_slice($result, 0, $limit);
    }

    private function itemId(object $item): string
    {
        $id = $item->ID ?? $item->id ?? $item->db_id ?? null;

        return $id !== null ? (string) $id : '';
    }

    private function isCurrentUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $requestUri = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '/';
        $currentUrl = home_url($requestUri);

        return untrailingslashit($currentUrl) === untrailingslashit($url);
    }

    private function hasSmartSectionsEnabled(): bool
    {
        $options = $this->options();

        return (bool) ($options['show_categories'] || $options['show_top_rated'] || $options['show_best_selling'] || $options['show_pages']);
    }
}; ?>

@php
    $isCenteredLayout = $layout === 'centered';
    $navClasses = $isCenteredLayout
        ? 'hidden lg:flex w-full items-center justify-center gap-3'
        : 'hidden lg:flex items-center gap-1.5';
@endphp

<nav
    class="{{ $navClasses }}"
    x-data="{
        openId: null,
        activeSectionByItem: {},
        closeAll() {
            this.openId = null;
        },
        isOpen(id) {
            return this.openId === id;
        },
        togglePanel(id, defaultSection) {
            if (this.openId === id) {
                this.openId = null;
                return;
            }

            this.openId = id;

            if (! this.activeSectionByItem[id]) {
                this.activeSectionByItem[id] = defaultSection;
            }
        },
        sectionFor(id, fallback) {
            return this.activeSectionByItem[id] ?? fallback;
        },
        setSection(id, section) {
            this.activeSectionByItem[id] = section;
        },
    }"
    role="navigation"
    aria-label="{{ __('Mega menu principal', 'flux-press') }}"
    @keydown.escape.window="closeAll()"
    @click.outside="closeAll()"
>
    @php
        $smartCategories = $this->productCategories;
        $smartTopRated = $this->topRatedProducts;
        $smartBestSelling = $this->bestSellingProducts;
        $smartPages = $this->wpPages;
    @endphp

    @foreach($this->menuTree as $item)
        @if(! $item['has_panel'])
            <a
                href="{{ $item['url'] }}"
                wire:navigate
                class="inline-flex items-center rounded-2xl px-4 py-2.5 text-sm font-bold tracking-tight transition-all duration-300 {{ $item['current'] ? 'bg-accent-600 text-white shadow-lg shadow-accent-600/30' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/80 hover:text-zinc-900 dark:hover:text-white' }}"
            >
                {{ $item['title'] }}
            </a>
        @else
            @php
                $itemId = (string) $item['id'];
                $panelSections = [];

                if (! empty($smartCategories)) {
                    $panelSections[] = ['key' => 'categories', 'label' => __('Categorias', 'flux-press'), 'icon' => 'squares-2x2'];
                }
                if (! empty($smartTopRated)) {
                    $panelSections[] = ['key' => 'top_rated', 'label' => __('Mejor valorados', 'flux-press'), 'icon' => 'star'];
                }
                if (! empty($smartBestSelling)) {
                    $panelSections[] = ['key' => 'best_selling', 'label' => __('Mas vendidos', 'flux-press'), 'icon' => 'bolt'];
                }
                if (! empty($smartPages)) {
                    $panelSections[] = ['key' => 'pages', 'label' => __('Paginas', 'flux-press'), 'icon' => 'document-text'];
                }

                $panelDefaultSection = ! empty($panelSections) ? $panelSections[0]['key'] : 'menu';
            @endphp

            <div
                class="relative"
                @focusin="openId = @js($itemId)"
            >
                <button
                    type="button"
                    class="{{ $isCenteredLayout ? 'inline-flex items-center gap-2.5 rounded-full px-5 py-3 text-base font-black tracking-tight shadow-sm transition-all duration-300' : 'inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-bold tracking-tight transition-all duration-300' }}"
                    :class="isOpen(@js($itemId))
                        ? '{{ $isCenteredLayout ? 'bg-zinc-950 text-white translate-y-[-2px] shadow-[0_12px_32px_-12px_rgba(15,23,42,.5)] dark:bg-white dark:text-zinc-950' : 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' }}'
                        : '{{ $item['current'] ? 'bg-accent-600 text-white shadow-lg shadow-accent-600/30' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/80 hover:text-zinc-900 dark:hover:text-white' }}'"
                    @click.prevent="togglePanel(@js($itemId), @js($panelDefaultSection))"
                    aria-haspopup="dialog"
                    :aria-expanded="isOpen(@js($itemId)) ? 'true' : 'false'"
                    aria-controls="mega-panel-{{ $itemId }}"
                >
                    <span>{{ $item['title'] }}</span>
                    <flux:icon.chevron-down class="size-4 opacity-60 transition-transform duration-300" :class="isOpen(@js($itemId)) ? 'rotate-180 opacity-100' : ''" />
                </button>

                <div
                    id="mega-panel-{{ $itemId }}"
                    x-cloak
                    x-show="isOpen(@js($itemId))"
                    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-400"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition cubic-bezier(0.32, 0, 0.67, 0) duration-250"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-[0.99]"
                    class="absolute left-1/2 top-full z-[60] -translate-x-1/2 {{ $isCenteredLayout ? 'pt-6' : 'pt-4' }}"
                    role="dialog"
                    aria-label="{{ __('Panel de navegacion', 'flux-press') }}"
                >
                    <div class="{{ $isCenteredLayout ? 'flux-mega-menu-centered-panel' : 'w-[76rem] max-w-[calc(100vw-3rem)] rounded-[2rem] border border-zinc-200/80 bg-white/95 p-6 shadow-2xl shadow-zinc-900/10 backdrop-blur-3xl dark:border-zinc-700/80 dark:bg-zinc-900/95 dark:shadow-black/40' }}">
                        <div class="{{ $isCenteredLayout ? 'mb-8 flex flex-col gap-5 border-b border-zinc-200/60 pb-6 dark:border-zinc-700/60 xl:flex-row xl:items-end xl:justify-between' : 'mb-5 flex items-center justify-between border-b border-zinc-200/60 pb-3 dark:border-zinc-700/60' }}">
                            <div>
                                <span class="inline-flex items-center gap-2 rounded-full bg-accent-50 px-3 py-0.5 text-[9px] font-black uppercase tracking-widest text-accent-700 dark:bg-accent-500/10 dark:text-accent-400">
                                    <span class="size-1 rounded-full bg-accent-500 animate-pulse"></span>
                                    {{ $item['title'] }}
                                </span>
                                @if($isCenteredLayout)
                                    <h3 class="mt-3 text-3xl font-black tracking-tighter text-zinc-950 dark:text-zinc-50 xl:text-[2rem]">
                                        {{ __('Descubre este espacio', 'flux-press') }}
                                    </h3>
                                    <p class="mt-2.5 max-w-xl text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                                        {{ __('Explora secciones, categorías y productos destacados en un panel compacto guiado paso a paso.', 'flux-press') }}
                                    </p>
                                @endif
                            </div>
                            <a href="{{ $item['url'] }}" wire:navigate class="group inline-flex items-center gap-2 {{ $isCenteredLayout ? 'rounded-full bg-zinc-950 px-5 py-2.5 text-sm font-bold text-white transition-all hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100' : 'text-[11px] font-bold text-accent-600 dark:text-accent-400 hover:text-accent-700' }}">
                                {{ __('Ver todo', 'flux-press') }}
                                <flux:icon.arrow-right class="size-3.5 transition-transform group-hover:translate-x-1" />
                            </a>
                        </div>

                        <div class="{{ $isCenteredLayout ? 'grid gap-6 xl:grid-cols-[19rem_minmax(0,1fr)] 2xl:gap-8' : 'grid grid-cols-12 gap-6' }}">
                            <section class="{{ $isCenteredLayout ? 'space-y-4' : 'col-span-4 space-y-3' }}">
                                <div class="rounded-[1.75rem] border border-zinc-200/60 bg-zinc-100/30 p-5 dark:border-zinc-800/60 dark:bg-zinc-800/20">
                                    <h3 class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">{{ __('Navegación Paso a Paso', 'flux-press') }}</h3>
                                    <div class="mt-4 space-y-2">
                                        @foreach($panelSections as $section)
                                            <button
                                                type="button"
                                                class="group flex w-full items-center gap-2.5 rounded-xl border px-3.5 py-2.5 text-left transition-all duration-300"
                                                @click.prevent="setSection(@js($itemId), @js($section['key']))"
                                                :class="sectionFor(@js($itemId), @js($panelDefaultSection)) === @js($section['key'])
                                                    ? 'border-accent-500/30 bg-white text-accent-700 shadow-lg shadow-accent-950/5 ring-1 ring-accent-500/10 dark:bg-zinc-800 dark:text-accent-400'
                                                    : 'border-transparent text-zinc-500 hover:bg-white/80 hover:text-zinc-900 dark:hover:bg-zinc-800/80 dark:hover:text-zinc-200'"
                                            >
                                                <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition-colors group-hover:bg-accent-50 dark:bg-zinc-800 dark:group-hover:bg-accent-500/10"
                                                    :class="sectionFor(@js($itemId), @js($panelDefaultSection)) === @js($section['key']) ? '!bg-accent-600 !text-white' : 'text-zinc-500'"
                                                >
                                                    @switch($section['icon'])
                                                        @case('squares-2x2') <flux:icon.squares-2x2 class="size-4" /> @break
                                                        @case('star') <flux:icon.star class="size-4" /> @break
                                                        @case('bolt') <flux:icon.bolt class="size-4" /> @break
                                                        @case('document-text') <flux:icon.document-text class="size-4" /> @break
                                                    @endswitch
                                                </div>
                                                <span class="text-xs font-bold tracking-tight">{{ $section['label'] }}</span>
                                                <flux:icon.chevron-right class="ml-auto size-3.5 opacity-0 transition-all group-hover:opacity-100" />
                                            </button>
                                        @endforeach
                                    </div>

                                    <div class="mt-6 border-t border-zinc-200/60 pt-5 dark:border-zinc-800/60">
                                        <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400">{{ __('Accesos Rápidos', 'flux-press') }}</h4>
                                        <div class="mt-3 space-y-1.5">
                                            @if(! empty($item['children']))
                                                @foreach($item['children'] as $child)
                                                    <a href="{{ $child['url'] }}" wire:navigate class="group flex items-center justify-between rounded-lg px-2 py-1.5 text-[13px] font-bold text-zinc-600 transition-all hover:bg-accent-50 hover:text-accent-700 dark:text-zinc-400 dark:hover:bg-accent-500/5 dark:hover:text-accent-300">
                                                        <span>{{ $child['title'] }}</span>
                                                        <flux:icon.arrow-up-right class="size-3 opacity-0 transition-all group-hover:opacity-100" />
                                                    </a>
                                                @endforeach
                                            @else
                                                <a href="{{ $item['url'] }}" wire:navigate class="group flex items-center justify-between rounded-lg px-2 py-1.5 text-[13px] font-bold text-zinc-600 transition-all hover:bg-accent-50 hover:text-accent-700 dark:text-zinc-400 dark:hover:bg-accent-500/5 dark:hover:text-accent-300">
                                                    <span>{{ __('Ver sección', 'flux-press') }}</span>
                                                    <flux:icon.arrow-up-right class="size-3 opacity-0 transition-all group-hover:opacity-100" />
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="{{ $isCenteredLayout ? 'min-w-0' : 'col-span-8' }}">
                                <div class="flux-mega-menu-centered-shell">
                                    <div
                                        x-show="sectionFor(@js($itemId), @js($panelDefaultSection)) === 'categories'"
                                        x-transition:enter="transition ease-out duration-500 delay-100"
                                        x-transition:enter-start="opacity-0 translate-x-4"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        class="{{ $isCenteredLayout ? 'flux-mega-menu-centered-grid' : 'grid grid-cols-2 gap-4 xl:grid-cols-3' }}"
                                    >
                                        @foreach($smartCategories as $category)
                                            <a href="{{ $category['url'] }}" wire:navigate class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-zinc-200/60 bg-white p-5 transition-all duration-300 hover:border-accent-300 hover:shadow-xl hover:shadow-accent-950/5 dark:border-zinc-800/60 dark:bg-zinc-900 dark:hover:border-accent-500/50">
                                                <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-zinc-50 transition-colors group-hover:bg-accent-50 dark:bg-zinc-800 dark:group-hover:bg-accent-500/10">
                                                    <flux:icon.tag class="size-5 text-zinc-400 group-hover:text-accent-600 dark:text-zinc-500 dark:group-hover:text-accent-400" />
                                                </div>
                                                <div>
                                                    <span class="block text-base font-black tracking-tight text-zinc-900 dark:text-zinc-100">{{ $category['name'] }}</span>
                                                    <span class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">
                                                        {{ sprintf(_n('%d producto', '%d productos', $category['count'], 'flux-press'), $category['count']) }}
                                                        <flux:icon.arrow-right class="size-3 opacity-0 transition-all group-hover:translate-x-1 group-hover:opacity-100" />
                                                    </span>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>

                                    <div
                                        x-show="sectionFor(@js($itemId), @js($panelDefaultSection)) === 'top_rated'"
                                        x-transition:enter="transition ease-out duration-500 delay-100"
                                        x-transition:enter-start="opacity-0 translate-x-4"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        class="{{ $isCenteredLayout ? 'flux-mega-menu-centered-product-grid' : 'grid grid-cols-1 gap-4 xl:grid-cols-2' }}"
                                    >
                                        @foreach($smartTopRated as $product)
                                            <a href="{{ $product['url'] }}" wire:navigate class="group flex items-center gap-4 rounded-2xl border border-zinc-200/60 bg-white p-3 transition-all duration-300 hover:border-accent-300 hover:shadow-lg dark:border-zinc-800/60 dark:bg-zinc-900 dark:hover:border-accent-500/50">
                                                @if($product['image'])
                                                    <div class="relative size-16 shrink-0 overflow-hidden rounded-xl border border-zinc-100 dark:border-zinc-800">
                                                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="size-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                                    </div>
                                                @else
                                                    <div class="flex size-16 shrink-0 items-center justify-center rounded-xl border border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/50">
                                                        <flux:icon.photo class="size-5 text-zinc-300" />
                                                    </div>
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <p class="line-clamp-2 text-[15px] font-black leading-tight tracking-tight text-zinc-900 dark:text-zinc-100">{{ $product['name'] }}</p>
                                                    <div class="mt-2.5 flex items-center justify-between">
                                                        <p class="text-[13px] font-black text-accent-600 dark:text-accent-400">{!! $product['price'] !!}</p>
                                                        <div class="flex items-center gap-1">
                                                            <flux:icon.star class="size-2.5 text-amber-400" variant="solid" />
                                                            <span class="text-[9px] font-black text-zinc-400">{{ $product['rating'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>

                                    <div
                                        x-show="sectionFor(@js($itemId), @js($panelDefaultSection)) === 'best_selling'"
                                        x-transition:enter="transition ease-out duration-500 delay-100"
                                        x-transition:enter-start="opacity-0 translate-x-4"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        class="{{ $isCenteredLayout ? 'flux-mega-menu-centered-product-grid' : 'grid grid-cols-1 gap-4 xl:grid-cols-2' }}"
                                    >
                                        @foreach($smartBestSelling as $product)
                                            <a href="{{ $product['url'] }}" wire:navigate class="group flex items-center gap-4 rounded-2xl border border-zinc-200/60 bg-white p-3 transition-all duration-300 hover:border-accent-300 hover:shadow-lg dark:border-zinc-800/60 dark:bg-zinc-900 dark:hover:border-accent-500/50">
                                                @if($product['image'])
                                                    <div class="relative size-16 shrink-0 overflow-hidden rounded-xl border border-zinc-100 dark:border-zinc-800">
                                                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="size-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                                    </div>
                                                @else
                                                    <div class="flex size-16 shrink-0 items-center justify-center rounded-xl border border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/50">
                                                        <flux:icon.photo class="size-5 text-zinc-300" />
                                                    </div>
                                                @endif
                                                <div class="min-w-0 flex-1"
                                                    <p class="line-clamp-2 text-[15px] font-black leading-tight tracking-tight text-zinc-900 dark:text-zinc-100">{{ $product['name'] }}</p>
                                                    <div class="mt-2.5 flex items-center justify-between">
                                                        <p class="text-[13px] font-black text-accent-600 dark:text-accent-400">{!! $product['price'] !!}</p>
                                                        @if($product['total_sales'] > 0)
                                                            <span class="rounded-full bg-accent-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-tight text-accent-700 dark:bg-accent-500/10 dark:text-accent-400">
                                                                {{ sprintf(_n('%d vta', '%d vtas', $product['total_sales'], 'flux-press'), $product['total_sales']) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>

                                    <div
                                        x-show="sectionFor(@js($itemId), @js($panelDefaultSection)) === 'pages'"
                                        x-transition:enter="transition ease-out duration-500 delay-100"
                                        x-transition:enter-start="opacity-0 translate-x-4"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        class="{{ $isCenteredLayout ? 'grid gap-5 xl:grid-cols-2 2xl:grid-cols-3' : 'grid grid-cols-1 gap-4 xl:grid-cols-2' }}"
                                    >
                                        @foreach($smartPages as $page)
                                            <a
                                                href="{{ $page['url'] }}"
                                                wire:navigate
                                                class="group relative flex items-center justify-between overflow-hidden rounded-xl border px-5 py-4 transition-all duration-300 {{ $page['current'] ? 'border-accent-500/30 bg-accent-50 text-accent-700 dark:bg-accent-500/10 dark:text-accent-400' : 'border-zinc-200/60 bg-white text-zinc-700 hover:border-accent-300 hover:bg-zinc-50 dark:border-zinc-800/60 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800' }}"
                                            >
                                                <span class="text-[15px] font-black tracking-tight">{{ $page['title'] }}</span>
                                                <flux:icon.chevron-right class="size-3.5 opacity-0 transition-all group-hover:translate-x-1 group-hover:opacity-100" />
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</nav>
