@extends('layouts.app')

@section('sub_navigation')
  @php
    $show_sidebar = (bool) get_theme_mod('woocommerce_show_shop_sidebar', config('theme-interface.woocommerce.show_shop_sidebar', true));
  @endphp

  @if($show_sidebar)
    <div class="space-y-8">
      @php dynamic_sidebar('sidebar-shop'); @endphp
    </div>
  @endif
@endsection

@section('content')
  @php
    $shopBanner = is_array($shopBanner ?? null) ? $shopBanner : [];
    $shopFilters = is_array($shopFilters ?? null) ? $shopFilters : [];
    $filterCategories = is_array($shopFilters['categories'] ?? null) ? $shopFilters['categories'] : [];
    $filterBrands = is_array($shopFilters['brands'] ?? null) ? $shopFilters['brands'] : [];
    $filterVendors = is_array($shopFilters['vendors'] ?? null) ? $shopFilters['vendors'] : [];
    $priceRanges = is_array($shopFilters['price_ranges'] ?? null) ? $shopFilters['price_ranges'] : [];
    $selectedCategories = is_array($shopFilters['selected_categories'] ?? null) ? $shopFilters['selected_categories'] : [];
    $selectedBrands = is_array($shopFilters['selected_brands'] ?? null) ? $shopFilters['selected_brands'] : [];
    $selectedVendors = is_array($shopFilters['selected_vendors'] ?? null) ? $shopFilters['selected_vendors'] : [];
    $selectedPrice = (string) ($shopFilters['selected_price'] ?? '');
    $selectedRating = (int) ($shopFilters['selected_rating'] ?? 0);
    $selectedOnSale = (bool) ($shopFilters['selected_on_sale'] ?? false);
    $activeFilters = is_array($shopFilters['active_filters'] ?? null) ? $shopFilters['active_filters'] : [];
    $filterAction = (string) ($shopFilters['form_action'] ?? '');
    if ($filterAction === '' && function_exists('wc_get_page_permalink')) {
      $filterAction = (string) wc_get_page_permalink('shop');
    }
    if ($filterAction === '') {
      $filterAction = home_url('/');
    }
    $preserve = is_array($shopFilters['preserve'] ?? null) ? $shopFilters['preserve'] : [];
    $currentMinPrice = isset($shopFilters['min_price']) ? (float) $shopFilters['min_price'] : '';
    $currentMaxPrice = isset($shopFilters['max_price']) ? (float) $shopFilters['max_price'] : '';
    $clearFiltersUrl = remove_query_arg(['fp_cat','fp_brand','fp_vendor','fp_price','fp_rating','on_sale','min_price','max_price']);
  @endphp

  <div class="mb-6">
    <flux:breadcrumbs>
      <flux:breadcrumbs.item href="{{ home_url('/') }}" icon="home" />
      @if(is_shop())
        <flux:breadcrumbs.item>{{ woocommerce_page_title(false) }}</flux:breadcrumbs.item>
      @elseif(is_product_category() || is_product_tag())
        <flux:breadcrumbs.item href="{{ wc_get_page_permalink('shop') }}" wire:navigate>{{ __('Shop', 'sage') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ single_term_title('', false) }}</flux:breadcrumbs.item>
      @else
        <flux:breadcrumbs.item>{{ woocommerce_page_title(false) }}</flux:breadcrumbs.item>
      @endif
    </flux:breadcrumbs>
  </div>

  @if(($shopBanner['enabled'] ?? false) === true)
    <section class="mb-7 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden">
      <div class="grid lg:grid-cols-12">
        <div class="lg:col-span-7 p-6 sm:p-8 lg:p-10">
          @if(($shopBanner['title'] ?? '') !== '')
            <flux:heading size="4xl" class="!font-black tracking-tight text-zinc-900 dark:text-zinc-100">
              {{ $shopBanner['title'] }}
            </flux:heading>
          @endif

          @if(($shopBanner['subtitle'] ?? '') !== '')
            <flux:text class="mt-3 text-zinc-600 dark:text-zinc-300 text-base sm:text-lg">
              {{ $shopBanner['subtitle'] }}
            </flux:text>
          @endif

          @if(($shopBanner['content_html'] ?? '') !== '')
            <div class="mt-4 prose prose-zinc dark:prose-invert max-w-none">
              {!! $shopBanner['content_html'] !!}
            </div>
          @endif

          <div class="mt-6 flex flex-wrap gap-3">
            @if(($shopBanner['primary_label'] ?? '') !== '' && ($shopBanner['primary_url'] ?? '') !== '')
              <flux:button href="{{ $shopBanner['primary_url'] }}" variant="primary" icon="sparkles" wire:navigate>
                {{ $shopBanner['primary_label'] }}
              </flux:button>
            @endif
            @if(($shopBanner['secondary_label'] ?? '') !== '' && ($shopBanner['secondary_url'] ?? '') !== '')
              <flux:button href="{{ $shopBanner['secondary_url'] }}" variant="outline" icon="tag" wire:navigate>
                {{ $shopBanner['secondary_label'] }}
              </flux:button>
            @endif
          </div>
        </div>

        <div class="lg:col-span-5 bg-zinc-100 dark:bg-zinc-800 min-h-[220px] sm:min-h-[260px] lg:min-h-full">
          @if(($shopBanner['image_url'] ?? '') !== '')
            <img src="{{ $shopBanner['image_url'] }}" alt="{{ $shopBanner['title'] ?? __('Store Banner', 'sage') }}" class="h-full w-full object-cover" loading="lazy" />
          @else
            <div class="h-full w-full flex items-center justify-center">
              <flux:icon.photo class="size-14 text-zinc-400" />
            </div>
          @endif
        </div>
      </div>
    </section>
  @endif

  @php do_action('woocommerce_before_main_content'); @endphp

  <section class="flux-shop-layout mb-8 grid gap-5 lg:grid-cols-[280px_minmax(0,1fr)] xl:grid-cols-[300px_minmax(0,1fr)]">
    <aside class="self-start lg:sticky lg:top-24">
      <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 p-4 sm:p-4">
        <div class="mb-4">
          <flux:heading size="base" class="!font-black tracking-tight">{{ __('Filters', 'sage') }}</flux:heading>
          <flux:text class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">{{ __('Refine by price, category and rating.', 'sage') }}</flux:text>
        </div>

        @if(! empty($activeFilters))
          <div class="mb-4 flex flex-wrap gap-1.5">
            @foreach($activeFilters as $activeFilter)
              <span class="inline-flex items-center rounded-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 px-2 py-0.5 text-[11px] font-medium text-zinc-600 dark:text-zinc-300">
                {{ $activeFilter }}
              </span>
            @endforeach
          </div>
        @endif

        <form method="get" action="{{ $filterAction }}" class="space-y-4">
          @foreach($preserve as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
          @endforeach

          <div class="space-y-3">
            <div>
              <label for="fp_price" class="mb-1 block text-xs font-semibold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ __('Range', 'sage') }}</label>
              <select id="fp_price" name="fp_price" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-700 dark:text-zinc-200">
                <option value="">{{ __('All', 'sage') }}</option>
                @foreach($priceRanges as $range)
                  <option value="{{ $range['key'] }}" @selected($selectedPrice === $range['key'])>{{ $range['label'] }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label for="fp_rating" class="mb-1 block text-xs font-semibold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ __('Rating', 'sage') }}</label>
              <select id="fp_rating" name="fp_rating" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-700 dark:text-zinc-200">
                <option value="0">{{ __('Any', 'sage') }}</option>
                @for($rate = 5; $rate >= 1; $rate--)
                  <option value="{{ $rate }}" @selected($selectedRating === $rate)>{{ sprintf(__('From %d stars', 'sage'), $rate) }}</option>
                @endfor
              </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label for="min_price" class="mb-1 block text-xs font-semibold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ __('Min', 'sage') }}</label>
                <input id="min_price" type="number" min="0" step="0.01" name="min_price" value="{{ $currentMinPrice }}" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-700 dark:text-zinc-200" />
              </div>
              <div>
                <label for="max_price" class="mb-1 block text-xs font-semibold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ __('Max', 'sage') }}</label>
                <input id="max_price" type="number" min="0" step="0.01" name="max_price" value="{{ $currentMaxPrice }}" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-700 dark:text-zinc-200" />
              </div>
            </div>
          </div>

          @if(! empty($filterCategories))
            <fieldset class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-3 bg-white dark:bg-zinc-900">
              <legend class="px-1 text-[11px] uppercase tracking-widest font-semibold text-zinc-500 dark:text-zinc-400">{{ __('Categories', 'sage') }}</legend>
              <div class="mt-2 max-h-44 overflow-auto space-y-1.5 pr-1">
                @foreach($filterCategories as $category)
                  <label class="flex items-start gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" name="fp_cat[]" value="{{ $category['slug'] }}" @checked(in_array($category['slug'], $selectedCategories, true)) class="mt-0.5 rounded border-zinc-300 dark:border-zinc-600 text-accent-600 focus:ring-accent-500" />
                    <span class="leading-snug">
                      {{ $category['name'] }}
                      <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $category['count'] }})</span>
                    </span>
                  </label>
                @endforeach
              </div>
            </fieldset>
          @endif

          @if(! empty($filterBrands))
            <fieldset class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-3 bg-white dark:bg-zinc-900">
              <legend class="px-1 text-[11px] uppercase tracking-widest font-semibold text-zinc-500 dark:text-zinc-400">{{ __('Brands', 'sage') }}</legend>
              <div class="mt-2 max-h-40 overflow-auto space-y-1.5 pr-1">
                @foreach($filterBrands as $brand)
                  <label class="flex items-start gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" name="fp_brand[]" value="{{ $brand['slug'] }}" @checked(in_array($brand['slug'], $selectedBrands, true)) class="mt-0.5 rounded border-zinc-300 dark:border-zinc-600 text-accent-600 focus:ring-accent-500" />
                    <span class="leading-snug">
                      {{ $brand['name'] }}
                      <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $brand['count'] }})</span>
                    </span>
                  </label>
                @endforeach
              </div>
            </fieldset>
          @endif

          @if(! empty($filterVendors))
            <fieldset class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-3 bg-white dark:bg-zinc-900">
              <legend class="px-1 text-[11px] uppercase tracking-widest font-semibold text-zinc-500 dark:text-zinc-400">{{ __('Vendors', 'sage') }}</legend>
              <div class="mt-2 max-h-40 overflow-auto space-y-1.5 pr-1">
                @foreach($filterVendors as $vendor)
                  <label class="flex items-start gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" name="fp_vendor[]" value="{{ $vendor['slug'] }}" @checked(in_array($vendor['slug'], $selectedVendors, true)) class="mt-0.5 rounded border-zinc-300 dark:border-zinc-600 text-accent-600 focus:ring-accent-500" />
                    <span class="leading-snug">
                      {{ $vendor['name'] }}
                      <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $vendor['count'] }})</span>
                    </span>
                  </label>
                @endforeach
              </div>
            </fieldset>
          @endif

          <label class="inline-flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
            <input type="checkbox" name="on_sale" value="1" @checked($selectedOnSale) class="rounded border-zinc-300 dark:border-zinc-600 text-accent-600 focus:ring-accent-500" />
            {{ __('On Sale Only', 'sage') }}
          </label>

          <div class="grid grid-cols-1 gap-2 pt-1">
            <flux:button type="submit" variant="primary" icon="funnel" class="w-full justify-center">
              {{ __('Apply Filters', 'sage') }}
            </flux:button>
            <a href="{{ $clearFiltersUrl }}" class="inline-flex items-center justify-center rounded-xl border border-zinc-300 dark:border-zinc-700 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:border-red-400 hover:text-red-600 transition-colors">
              {{ __('Reset', 'sage') }}
            </a>
          </div>
        </form>
      </div>
    </aside>

    <div class="min-w-0" x-data="{ view: 'v4' }">
      <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-3 sm:p-4">
        @if(($shopBanner['enabled'] ?? false) !== true)
          <header class="woocommerce-products-header mb-4">
            @if (apply_filters('woocommerce_show_page_title', true))
              <flux:heading size="xl" level="h1" class="woocommerce-products-header__title page-title">
                {!! woocommerce_page_title(false) !!}
              </flux:heading>
            @endif

            @php do_action('woocommerce_archive_description'); @endphp
          </header>
        @endif

        @if (woocommerce_product_loop())
          <div class="flux-shop-toolbar mb-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/60 px-3 py-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex flex-wrap items-center gap-4 flux-shop-toolbar-meta">
                @php do_action('woocommerce_before_shop_loop'); @endphp
              </div>

              <div class="hidden lg:flex items-center gap-1.5 border-l border-zinc-200 dark:border-zinc-700 pl-3">
                <button type="button" x-on:click="view = 'v2'" class="inline-flex items-center justify-center rounded-md border px-2 py-1 transition-colors" :class="view === 'v2' ? 'border-accent-500 text-accent-700 dark:text-accent-400 bg-white dark:bg-zinc-900' : 'border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-600'" aria-label="{{ __('2 column view', 'sage') }}">
                  <span class="text-[11px] font-semibold">2</span>
                </button>
                <button type="button" x-on:click="view = 'v3'" class="inline-flex items-center justify-center rounded-md border px-2 py-1 transition-colors" :class="view === 'v3' ? 'border-accent-500 text-accent-700 dark:text-accent-400 bg-white dark:bg-zinc-900' : 'border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-600'" aria-label="{{ __('3 column view', 'sage') }}">
                  <span class="text-[11px] font-semibold">3</span>
                </button>
                <button type="button" x-on:click="view = 'v4'" class="inline-flex items-center justify-center rounded-md border px-2 py-1 transition-colors" :class="view === 'v4' ? 'border-accent-500 text-accent-700 dark:text-accent-400 bg-white dark:bg-zinc-900' : 'border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-600'" aria-label="{{ __('4 column view', 'sage') }}">
                  <span class="text-[11px] font-semibold">4</span>
                </button>
                <button type="button" x-on:click="view = 'v5'" class="inline-flex items-center justify-center rounded-md border px-2 py-1 transition-colors" :class="view === 'v5' ? 'border-accent-500 text-accent-700 dark:text-accent-400 bg-white dark:bg-zinc-900' : 'border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-600'" aria-label="{{ __('5 column view', 'sage') }}">
                  <span class="text-[11px] font-semibold">5</span>
                </button>
              </div>
            </div>
          </div>

          @if (wc_get_loop_prop('total'))
            <div
              class="flux-shop-grid grid grid-cols-2 gap-2.5 sm:gap-3"
              :class="{
                'md:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-3': view === 'v2',
                'md:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4': view === 'v3',
                'md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5': view === 'v4',
                'md:grid-cols-3 xl:grid-cols-5 2xl:grid-cols-6': view === 'v5'
              }"
            >
              @while (have_posts())
                @php
                  the_post();
                  global $product;
                  do_action('woocommerce_shop_loop');
                @endphp
                <livewire:product-card :product-id="$product->get_id()" :key="'product-'.$product->get_id()" />
              @endwhile
            </div>
          @endif

          <div class="mt-6 flex justify-center">
            @php do_action('woocommerce_after_shop_loop'); @endphp
          </div>
        @else
          @php do_action('woocommerce_no_products_found'); @endphp
        @endif
      </div>
    </div>
  </section>

  @php do_action('woocommerce_after_main_content'); @endphp
@endsection
