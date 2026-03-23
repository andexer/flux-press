@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  @if (! have_posts())
    <flux:callout color="amber" icon="exclamation-triangle" class="mb-6">
      <flux:callout.heading>{{ __('Sin resultados', 'flux-press') }}</flux:callout.heading>
      <flux:callout.text>{!! __('No se encontraron resultados.', 'flux-press') !!}</flux:callout.text>
    </flux:callout>

    {!! get_search_form(false) !!}
  @endif

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @while(have_posts()) @php(the_post())
      @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
    @endwhile
  </div>

  {!! get_the_posts_navigation() !!}
@endsection

@section('sidebar')
  @include('sections.sidebar')
@endsection
