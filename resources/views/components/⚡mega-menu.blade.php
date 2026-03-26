<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public array $items = [];
    public array $config = [];

    public function mount($items = [], $config = []): void
    {
        $this->items = is_array($items) ? $items : [];
        $this->config = is_array($config) ? $config : [];
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

<nav
    class="hidden lg:flex items-center gap-1.5"
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
                class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-200 {{ $item['current'] ? 'bg-accent-600 text-white shadow-sm shadow-accent-600/30' : 'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white' }}"
            >
                {{ $item['title'] }}
            </a>
        @else
            @php
                $itemId = (string) $item['id'];
                $panelSections = [];

                if (! empty($smartCategories)) {
                    $panelSections[] = ['key' => 'categories', 'label' => __('Categorias', 'flux-press')];
                }
                if (! empty($smartTopRated)) {
                    $panelSections[] = ['key' => 'top_rated', 'label' => __('Mejor valorados', 'flux-press')];
                }
                if (! empty($smartBestSelling)) {
                    $panelSections[] = ['key' => 'best_selling', 'label' => __('Mas vendidos', 'flux-press')];
                }
                if (! empty($smartPages)) {
                    $panelSections[] = ['key' => 'pages', 'label' => __('Paginas', 'flux-press')];
                }

                $panelDefaultSection = ! empty($panelSections) ? $panelSections[0]['key'] : 'menu';
            @endphp

            <div
                class="relative"
                @focusin="openId = @js($itemId)"
            >
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-200"
                    :class="isOpen(@js($itemId))
                        ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                        : '{{ $item['current'] ? 'bg-accent-600 text-white shadow-sm shadow-accent-600/30' : 'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white' }}'"
                    @click.prevent="togglePanel(@js($itemId), @js($panelDefaultSection))"
                    aria-haspopup="dialog"
                    :aria-expanded="isOpen(@js($itemId)) ? 'true' : 'false'"
                    aria-controls="mega-panel-{{ $itemId }}"
                >
                    <span>{{ $item['title'] }}</span>
                    <flux:icon.chevron-down class="size-4 transition-transform duration-200" :class="isOpen(@js($itemId)) ? 'rotate-180' : ''" />
                </button>

                <div
                    id="mega-panel-{{ $itemId }}"
                    x-cloak
                    x-show="isOpen(@js($itemId))"
                    x-transition:enter="transition ease-out duration-220"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-160"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="absolute left-1/2 top-full z-[60] -translate-x-1/2 pt-3"
                    role="dialog"
                    aria-label="{{ __('Panel de navegacion', 'flux-press') }}"
                >
                    <div class="w-[76rem] max-w-[calc(100vw-2.5rem)] rounded-[2rem] border border-zinc-200/80 bg-white/95 p-6 shadow-2xl shadow-zinc-900/10 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/95 dark:shadow-black/35">
                        <div class="mb-5 flex items-center justify-between border-b border-zinc-200/70 pb-3 dark:border-zinc-700">
                            <p class="text-sm font-black uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">{{ $item['title'] }}</p>
                            <a href="{{ $item['url'] }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold text-accent-600 dark:text-accent-400 hover:underline">
                                {{ __('Ver todo', 'flux-press') }}
                                <flux:icon.arrow-right class="size-3.5" />
                            </a>
                        </div>

                        <div class="grid grid-cols-12 gap-6">
                            <section class="col-span-4 space-y-2">
                                <h3 class="text-[11px] font-black uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Navegacion', 'flux-press') }}</h3>
                                @if(! empty($item['children']))
                                    @foreach($item['children'] as $child)
                                        <a
                                            href="{{ $child['url'] }}"
                                            wire:navigate
                                            class="group flex items-center justify-between rounded-xl border px-3 py-2.5 transition-all {{ $child['current'] ? 'border-accent-300 bg-accent-50 dark:border-accent-500/60 dark:bg-accent-500/10' : 'border-zinc-200/80 dark:border-zinc-700 hover:border-accent-300 dark:hover:border-accent-500/60 hover:bg-zinc-50 dark:hover:bg-zinc-800/60' }}"
                                        >
                                            <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $child['title'] }}</span>
                                            <flux:icon.arrow-up-right class="size-4 text-zinc-400 group-hover:text-accent-500 transition-colors" />
                                        </a>
                                    @endforeach
                                @else
                                    <a
                                        href="{{ $item['url'] }}"
                                        wire:navigate
                                        class="group flex items-center justify-between rounded-xl border border-zinc-200/80 dark:border-zinc-700 px-3 py-2.5 hover:border-accent-300 dark:hover:border-accent-500/60 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition-all"
                                    >
                                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Ir a esta seccion', 'flux-press') }}</span>
                                        <flux:icon.arrow-up-right class="size-4 text-zinc-400 group-hover:text-accent-500 transition-colors" />
                                    </a>
                                @endif
                            </section>

                            <section class="col-span-8">
                                @if(! empty($panelSections))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($panelSections as $section)
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold uppercase tracking-wide transition-all"
                                                @click.prevent="setSection(@js($itemId), @js($section['key']))"
                                                :class="sectionFor(@js($itemId), @js($panelDefaultSection)) === @js($section['key'])
                                                    ? 'border-accent-300 bg-accent-50 text-accent-700 dark:border-accent-500/70 dark:bg-accent-500/15 dark:text-accent-300'
                                                    : 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-zinc-500 dark:hover:text-zinc-100'"
                                            >
                                                {{ $section['label'] }}
                                            </button>
                                        @endforeach
                                    </div>

                                    <div class="mt-4 rounded-2xl border border-zinc-200/80 bg-zinc-50/70 p-4 dark:border-zinc-700 dark:bg-zinc-800/35">
                                        <div
                                            x-show="sectionFor(@js($itemId), @js($panelDefaultSection)) === 'categories'"
                                            class="grid grid-cols-2 gap-2 xl:grid-cols-3"
                                        >
                                            @foreach($smartCategories as $category)
                                                <a href="{{ $category['url'] }}" wire:navigate class="rounded-lg border border-transparent px-2 py-1.5 text-sm text-zinc-700 transition-colors hover:border-zinc-200 hover:bg-white dark:text-zinc-200 dark:hover:border-zinc-700 dark:hover:bg-zinc-900">
                                                    <span class="font-semibold">{{ $category['name'] }}</span>
                                                    <span class="ml-1 text-xs text-zinc-500 dark:text-zinc-400">({{ $category['count'] }})</span>
                                                </a>
                                            @endforeach
                                        </div>

                                        <div
                                            x-show="sectionFor(@js($itemId), @js($panelDefaultSection)) === 'top_rated'"
                                            class="grid grid-cols-1 gap-3 xl:grid-cols-2"
                                        >
                                            @foreach($smartTopRated as $product)
                                                <a href="{{ $product['url'] }}" wire:navigate class="flex items-center gap-2.5 rounded-xl border border-transparent px-2 py-2 transition-colors hover:border-zinc-200 hover:bg-white dark:hover:border-zinc-700 dark:hover:bg-zinc-900">
                                                    @if($product['image'])
                                                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="size-10 rounded-lg border border-zinc-200/60 object-cover dark:border-zinc-700/60" />
                                                    @else
                                                        <div class="flex size-10 items-center justify-center rounded-lg border border-zinc-200/60 bg-zinc-100 dark:border-zinc-700/60 dark:bg-zinc-800">
                                                            <flux:icon.photo class="size-4 text-zinc-400" />
                                                        </div>
                                                    @endif
                                                    <div class="min-w-0 flex-1">
                                                        <p class="line-clamp-1 text-sm font-semibold leading-tight text-zinc-900 dark:text-zinc-100">{{ $product['name'] }}</p>
                                                        @if($product['price'] !== '')
                                                            <p class="mt-0.5 text-[11px] font-bold text-accent-600 dark:text-accent-400">{!! $product['price'] !!}</p>
                                                        @endif
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>

                                        <div
                                            x-show="sectionFor(@js($itemId), @js($panelDefaultSection)) === 'best_selling'"
                                            class="grid grid-cols-1 gap-3 xl:grid-cols-2"
                                        >
                                            @if(! empty($smartBestSelling))
                                                @foreach($smartBestSelling as $product)
                                                    <a href="{{ $product['url'] }}" wire:navigate class="flex items-center gap-2.5 rounded-xl border border-transparent px-2 py-2 transition-colors hover:border-zinc-200 hover:bg-white dark:hover:border-zinc-700 dark:hover:bg-zinc-900">
                                                        @if($product['image'])
                                                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="size-10 rounded-lg border border-zinc-200/60 object-cover dark:border-zinc-700/60" />
                                                        @else
                                                            <div class="flex size-10 items-center justify-center rounded-lg border border-zinc-200/60 bg-zinc-100 dark:border-zinc-700/60 dark:bg-zinc-800">
                                                                <flux:icon.photo class="size-4 text-zinc-400" />
                                                            </div>
                                                        @endif
                                                        <div class="min-w-0 flex-1">
                                                            <p class="line-clamp-1 text-sm font-semibold leading-tight text-zinc-900 dark:text-zinc-100">{{ $product['name'] }}</p>
                                                            <div class="mt-0.5 flex items-center gap-2">
                                                                @if($product['price'] !== '')
                                                                    <p class="text-[11px] font-bold text-accent-600 dark:text-accent-400">{!! $product['price'] !!}</p>
                                                                @endif
                                                                @if($product['total_sales'] > 0)
                                                                    <span class="text-[10px] font-medium tabular-nums text-zinc-500 dark:text-zinc-400">
                                                                        {{ sprintf(_n('%d venta', '%d ventas', $product['total_sales'], 'flux-press'), $product['total_sales']) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            @else
                                                <p class="rounded-lg border border-dashed border-zinc-200 px-3 py-3 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                                    {{ __('Aun no hay ventas registradas.', 'flux-press') }}
                                                </p>
                                            @endif
                                        </div>

                                        <div
                                            x-show="sectionFor(@js($itemId), @js($panelDefaultSection)) === 'pages'"
                                            class="grid grid-cols-1 gap-2 xl:grid-cols-2"
                                        >
                                            @foreach($smartPages as $page)
                                                <a
                                                    href="{{ $page['url'] }}"
                                                    wire:navigate
                                                    class="rounded-lg border border-transparent px-2 py-1.5 text-sm transition-colors {{ $page['current'] ? 'bg-accent-50 font-semibold text-accent-700 dark:bg-accent-500/10 dark:text-accent-400' : 'text-zinc-700 hover:border-zinc-200 hover:bg-white dark:text-zinc-200 dark:hover:border-zinc-700 dark:hover:bg-zinc-900' }}"
                                                >
                                                    {{ $page['title'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-2xl border border-dashed border-zinc-200 px-4 py-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                        {{ __('Agrega categorias, productos o paginas para enriquecer este panel.', 'flux-press') }}
                                    </div>
                                @endif
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</nav>
