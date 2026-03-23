@extends('layouts.app')

@section('sub_navigation')
  {{-- Shop Sidebar / Filters --}}
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
  {{-- Breadcrumbs --}}
  <div class="mb-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ home_url('/') }}" icon="home" />
        @if(is_shop())
            <flux:breadcrumbs.item>{{ woocommerce_page_title(false) }}</flux:breadcrumbs.item>
        @elseif(is_product_category() || is_product_tag())
            <flux:breadcrumbs.item href="{{ wc_get_page_permalink('shop') }}" wire:navigate>{{ __('Tienda', 'flux-press') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ single_term_title('', false) }}</flux:breadcrumbs.item>
        @else
            <flux:breadcrumbs.item>{{ woocommerce_page_title(false) }}</flux:breadcrumbs.item>
        @endif
    </flux:breadcrumbs>
  </div>

  @php
      do_action('woocommerce_before_main_content');
  @endphp

  <header class="woocommerce-products-header mb-8">
      @if (apply_filters('woocommerce_show_page_title', true))
          <flux:heading size="xl" level="h1" class="woocommerce-products-header__title page-title">
              {!! woocommerce_page_title(false) !!}
          </flux:heading>
      @endif

      @php do_action('woocommerce_archive_description'); @endphp
  </header>

  @if (woocommerce_product_loop())
      <div class="flex items-center justify-between gap-4 flex-wrap mb-6">
          @php do_action('woocommerce_before_shop_loop'); @endphp
      </div>

      {{-- 
          Fix: Eliminamos woocommerce_product_loop_start() y end() que inyectan <ul> conflictivos.
          Usamos el grid directo de Tailwind para mayor control y evitar "espacios muertos".
      --}}
      @if (wc_get_loop_prop('total'))
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 lg:gap-8">
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

      <div class="mt-8 flex justify-center">
          @php do_action('woocommerce_after_shop_loop'); @endphp
      </div>
  @else
      @php do_action('woocommerce_no_products_found'); @endphp
  @endif

  @php do_action('woocommerce_after_main_content'); @endphp
@endsection
