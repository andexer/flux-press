@php
    $demoVariant = $homeDemoVariant ?? 'corporate';
    $layoutKey = $homeLayout ?? 'corporate';
    $layoutLabel = (string) (config("theme-interface.home.layouts.{$layoutKey}") ?? $layoutKey);
    $supportsStatsVariant = in_array($demoVariant, ['corporate', 'ecommerce', 'gaming'], true);
@endphp

<section class="home-builder space-y-0">
    <livewire:sections.hero-section :demo="$demoVariant" />

    @if($homeShowFeatures)
        <livewire:sections.features-section :demo="$demoVariant" />
    @endif

    @if($homeShowStats && $supportsStatsVariant)
        <livewire:sections.stats-section :demo="$demoVariant" />
    @endif

    @if($homeShowPosts)
        <flux:main class="py-20 bg-zinc-50 dark:bg-zinc-950/80 border-y border-zinc-200 dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-10 flex items-end justify-between gap-6">
                    <div>
                        <flux:badge color="sky" class="mb-3">{{ __('Entradas Recientes', 'flux-press') }}</flux:badge>
                        <flux:heading size="4xl" class="tracking-tight !font-black">
                            {{ sprintf(__('Landing: %s', 'flux-press'), __($layoutLabel, 'flux-press')) }}
                        </flux:heading>
                        <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                            {{ sprintf(__('Version activa: %s', 'flux-press'), __($layoutLabel, 'flux-press')) }}
                        </flux:text>
                    </div>
                    <flux:button href="{{ get_permalink(get_option('page_for_posts')) ?: home_url('/blog') }}" icon="arrow-right" variant="ghost" class="max-sm:hidden">
                        {{ __('Ver Blog', 'flux-press') }}
                    </flux:button>
                </div>

                @if(! empty($homePosts))
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($homePosts as $postItem)
                            <article class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                @if(has_post_thumbnail($postItem))
                                    <a href="{{ get_permalink($postItem) }}" wire:navigate class="block aspect-[16/9] overflow-hidden">
                                        {!! get_the_post_thumbnail($postItem, 'large', ['class' => 'h-full w-full object-cover']) !!}
                                    </a>
                                @endif

                                <div class="p-5">
                                    <p class="text-xs uppercase tracking-widest text-zinc-500 dark:text-zinc-400 mb-2">
                                        {{ get_the_date('', $postItem) }}
                                    </p>
                                    <h3 class="text-lg font-bold leading-tight text-zinc-900 dark:text-zinc-100">
                                        <a href="{{ get_permalink($postItem) }}" wire:navigate class="hover:text-accent-600 dark:hover:text-accent-400 transition-colors">
                                            {{ get_the_title($postItem) }}
                                        </a>
                                    </h3>
                                    <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ wp_trim_words(wp_strip_all_tags(get_the_excerpt($postItem)), 18, '...') }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <flux:callout color="zinc" icon="document-text">
                        <flux:callout.heading>{{ __('Aun no hay entradas publicadas', 'flux-press') }}</flux:callout.heading>
                        <flux:callout.text>{{ __('Publica articulos para mostrar esta seccion en el Home.', 'flux-press') }}</flux:callout.text>
                    </flux:callout>
                @endif
            </div>
        </flux:main>
    @endif

    @if($homeShowCta)
        <livewire:sections.cta-section :demo="$demoVariant" />
    @endif

    @if($homeShowWidgets && is_active_sidebar('sidebar-home'))
        <flux:main class="py-16 bg-white dark:bg-zinc-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <flux:heading size="xl" class="tracking-tight !font-bold">
                        {{ __('Widgets del Home', 'flux-press') }}
                    </flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
                        {{ __('Bloques intercambiables configurados desde Apariencia > Widgets.', 'flux-press') }}
                    </flux:text>
                </div>
                @php dynamic_sidebar('sidebar-home'); @endphp
            </div>
        </flux:main>
    @endif
</section>
