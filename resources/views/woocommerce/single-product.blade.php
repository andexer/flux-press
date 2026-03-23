@extends('layouts.app')

@section('content')
  {{-- Breadcrumbs --}}
  <div class="mb-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ home_url('/') }}" icon="home" />
        <flux:breadcrumbs.item href="{{ wc_get_page_permalink('shop') }}" wire:navigate>{{ __('Tienda', 'sage') }}</flux:breadcrumbs.item>
        
        @php
           global $post;
           $terms = wc_get_product_terms( $post->ID, 'product_cat', array( 'orderby' => 'parent', 'order' => 'DESC' ) );
           $primary_term = !empty($terms) ? $terms[0] : null;
        @endphp
        
        @if($primary_term)
            <flux:breadcrumbs.item href="{{ get_term_link($primary_term, 'product_cat') }}" wire:navigate>
                {{ $primary_term->name }}
            </flux:breadcrumbs.item>
        @endif
        
        <flux:breadcrumbs.item>{!! get_the_title() !!}</flux:breadcrumbs.item>
    </flux:breadcrumbs>
  </div>

  @php
      remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
      do_action('woocommerce_before_main_content');
  @endphp

  @while(have_posts())
      @php
          the_post();
          wc_get_template_part('content', 'single-product');
      @endphp
  @endwhile

  @php
      do_action('woocommerce_after_main_content');
  @endphp
@endsection
