@php
    $layoutKey = $homeLayout ?? 'corporate';
    $layoutLabel = (string) (config("theme-interface.home.layouts.{$layoutKey}") ?? $layoutKey);
    $content = $homeEditableContent ?? [];

    $heroTitle = $content['hero']['title'] ?? '';
    $heroSubtitle = $content['hero']['subtitle'] ?? '';
    $heroBadge = $content['hero']['badge'] ?? '';
    $heroBadgeColor = $content['hero']['badge_color'] ?? 'sky';

    $featuresTitle = $content['features']['title'] ?? '';
    $featuresItems = $content['features']['items'] ?? [];

    $statsTitle = $content['stats']['title'] ?? '';
    $statsItems = $content['stats']['items'] ?? [];

    $ctaTitle = $content['cta']['title'] ?? '';
    $ctaDescription = $content['cta']['description'] ?? '';
    $ctaButtonText = $content['cta']['button_text'] ?? '';
    $ctaButtonUrl = $content['cta']['button_url'] ?? '';
    $ctaBgColor = $content['cta']['bg_color'] ?? '';

    $themeDefaults = [
        'corporate' => [
            'hero_bg' => 'bg-gradient-to-br from-slate-50 via-white to-sky-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-slate-900',
            'cta_bg' => 'bg-slate-900 dark:bg-black',
        ],
        'marketing' => [
            'hero_bg' => 'bg-gradient-to-br from-amber-50 via-white to-lime-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
            'cta_bg' => 'bg-lime-600 dark:bg-lime-700',
        ],
        'news' => [
            'hero_bg' => 'bg-gradient-to-br from-zinc-50 via-white to-orange-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
            'cta_bg' => 'bg-orange-600 dark:bg-orange-700',
        ],
        'profile' => [
            'hero_bg' => 'bg-gradient-to-br from-cyan-50 via-white to-blue-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
            'cta_bg' => 'bg-cyan-700 dark:bg-cyan-800',
        ],
        'saas' => [
            'hero_bg' => 'bg-gradient-to-br from-violet-50 via-white to-indigo-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-violet-950',
            'cta_bg' => 'bg-violet-600 dark:bg-violet-700',
        ],
        'startup' => [
            'hero_bg' => 'bg-gradient-to-br from-emerald-50 via-white to-teal-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-emerald-950',
            'cta_bg' => 'bg-emerald-600 dark:bg-emerald-700',
        ],
        'portfolio' => [
            'hero_bg' => 'bg-gradient-to-br from-rose-50 via-white to-pink-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-rose-950',
            'cta_bg' => 'bg-rose-600 dark:bg-rose-700',
        ],
        'restaurant' => [
            'hero_bg' => 'bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-amber-950',
            'cta_bg' => 'bg-orange-600 dark:bg-orange-700',
        ],
        'medical' => [
            'hero_bg' => 'bg-gradient-to-br from-cyan-50 via-white to-sky-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-cyan-950',
            'cta_bg' => 'bg-cyan-700 dark:bg-cyan-800',
        ],
        'education' => [
            'hero_bg' => 'bg-gradient-to-br from-violet-50 via-purple-50 to-indigo-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-violet-950',
            'cta_bg' => 'bg-violet-600 dark:bg-violet-700',
        ],
    ];

    $theme = $themeDefaults[$layoutKey] ?? $themeDefaults['corporate'];

    $sectionsOrder = $homeSectionsOrder ?? ['hero', 'features', 'stats', 'posts', 'cta', 'widgets'];
@endphp

<section class="overflow-hidden">
        @foreach($sectionsOrder as $section)
            @switch($section)
                @case('hero')
        <flux:main class="relative overflow-hidden {{ $theme['hero_bg'] }} border-b border-zinc-200 dark:border-zinc-800 py-16 sm:py-20">
            <div class="absolute inset-0 opacity-30 pointer-events-none">
                <div class="absolute top-0 right-0 h-72 w-72 rounded-full bg-white/70 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 h-72 w-72 rounded-full bg-zinc-200/60 dark:bg-zinc-700/40 blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    @if($heroBadge)
                        <flux:badge :color="$heroBadgeColor" class="mb-4 uppercase tracking-widest">{{ $heroBadge }}</flux:badge>
                    @endif
                    <flux:heading size="6xl" class="tracking-tight !font-black text-zinc-900 dark:text-zinc-100">
                        {{ $heroTitle }}
                    </flux:heading>
                    @if($heroSubtitle)
                        <flux:text class="mt-5 text-lg text-zinc-600 dark:text-zinc-300 leading-relaxed">
                            {{ $heroSubtitle }}
                        </flux:text>
                    @endif
                </div>
            </div>
        </flux:main>
                @break

                @case('features')
                    @if($homeShowFeatures && !empty($featuresItems))
            <flux:main class="py-14 sm:py-16 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @if($featuresTitle)
                        <flux:heading size="3xl" class="mb-8 tracking-tight !font-black text-zinc-900 dark:text-zinc-100">
                            {{ $featuresTitle }}
                        </flux:heading>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach($featuresItems as $feature)
                            @if(!empty($feature['title']))
                                <flux:card class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/60 p-5 shadow-sm">
                                    @if(!empty($feature['icon']))
                                        <flux:icon :icon="$feature['icon']" class="size-6 text-accent-600 dark:text-accent-400 mb-4" />
                                    @endif
                                    <flux:heading size="lg" class="!font-bold text-zinc-900 dark:text-zinc-100">{{ $feature['title'] }}</flux:heading>
                                    @if(!empty($feature['text']))
                                        <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ $feature['text'] }}</flux:text>
                                    @endif
                                </flux:card>
                            @endif
                        @endforeach
                    </div>
                </div>
            </flux:main>
                    @endif
                @break

                @case('stats')
                    @if($homeShowStats && !empty($statsItems))
            <flux:main class="py-10 sm:py-12 bg-zinc-900 dark:bg-black border-b border-zinc-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @if($statsTitle)
                        <div class="text-center mb-8">
                            <flux:heading size="2xl" class="tracking-tight !font-black text-white">
                                {{ $statsTitle }}
                            </flux:heading>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        @foreach($statsItems as $stat)
                            @if(!empty($stat['value']))
                                <div class="rounded-2xl bg-white/5 border border-white/10 p-4 sm:p-5">
                                    <p class="text-2xl sm:text-3xl font-black text-white">{{ $stat['value'] }}</p>
                                    @if(!empty($stat['label']))
                                        <p class="mt-1 text-xs sm:text-sm uppercase tracking-wider text-zinc-300">{{ $stat['label'] }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </flux:main>
                    @endif
                @break

                @case('posts')
                    @if($homeShowPosts)
            <flux:main class="py-14 sm:py-16 bg-zinc-50 dark:bg-zinc-950/80 border-b border-zinc-200 dark:border-zinc-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="mb-8 flex items-end justify-between gap-6">
                        <div>
                            <flux:badge color="sky" class="mb-3">{{ __('Recent Posts', 'sage') }}</flux:badge>
                            <flux:heading size="4xl" class="tracking-tight !font-black">
                                {{ sprintf(__('Landing: %s', 'sage'), $layoutLabel) }}
                            </flux:heading>
                        </div>
                    </div>

                    @if(! empty($homePosts))
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($homePosts as $postItem)
                                <article class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                    @if(($postItem['image'] ?? '') !== '')
                                        <a href="{{ $postItem['url'] }}" wire:navigate class="block aspect-[16/9] overflow-hidden">
                                            <img src="{{ $postItem['image'] }}" alt="{{ $postItem['title'] }}" class="h-full w-full object-cover" loading="lazy" />
                                        </a>
                                    @endif
                                    <div class="p-5">
                                        <p class="text-xs uppercase tracking-widest text-zinc-500 dark:text-zinc-400 mb-2">{{ $postItem['date'] }}</p>
                                        <h3 class="text-lg font-bold leading-tight text-zinc-900 dark:text-zinc-100">
                                            <a href="{{ $postItem['url'] }}" wire:navigate class="hover:text-accent-600 dark:hover:text-accent-400 transition-colors">{{ $postItem['title'] }}</a>
                                        </h3>
                                        <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $postItem['excerpt'] }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <flux:callout color="zinc" icon="document-text">
                            <flux:callout.heading>{{ __('No posts published yet', 'sage') }}</flux:callout.heading>
                            <flux:callout.text>{{ __('Publish articles to show this section on the Home.', 'sage') }}</flux:callout.text>
                        </flux:callout>
                    @endif
                </div>
            </flux:main>
                    @endif
                @break

                @case('cta')
                    @if($homeShowCta)
            @php
                $ctaBg = !empty($ctaBgColor) ? $ctaBgColor : ($theme['cta_bg'] ?? 'bg-slate-900');
            @endphp
            <flux:main class="py-14 sm:py-16 {{ $ctaBg }} border-b border-white/10">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    @if(!empty($ctaTitle))
                        <flux:heading size="5xl" class="!font-black !text-white tracking-tight">
                            {{ $ctaTitle }}
                        </flux:heading>
                    @else
                        <flux:heading size="5xl" class="!font-black !text-white tracking-tight">
                            {{ __('Your home should no longer look improvised', 'sage') }}
                        </flux:heading>
                    @endif
                    @if(!empty($ctaDescription))
                        <flux:text class="mt-4 text-white/80 text-lg">
                            {{ $ctaDescription }}
                        </flux:text>
                    @else
                        <flux:text class="mt-4 text-white/80 text-lg">
                            {{ __('Activate or deactivate sections from the Customizer and adapt this landing to each objective.', 'sage') }}
                        </flux:text>
                    @endif
                    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                        @if(!empty($ctaButtonText))
                            <flux:button size="base" variant="filled" href="{{ !empty($ctaButtonUrl) ? $ctaButtonUrl : admin_url('customize.php') }}" icon="cog-6-tooth">
                                {{ $ctaButtonText }}
                            </flux:button>
                        @else
                            <flux:button size="base" variant="filled" href="{{ admin_url('customize.php') }}" icon="cog-6-tooth">
                                {{ __('Customize Home', 'sage') }}
                            </flux:button>
                        @endif
                        <flux:button size="base" variant="ghost" href="{{ home_url('/contacto') }}" icon="chat-bubble-left-right" class="!text-white hover:!bg-white/10" wire:navigate>
                            {{ __('Talk to Sales', 'sage') }}
                        </flux:button>
                    </div>
                </div>
            </flux:main>
                    @endif
                @break

                @case('widgets')
                    @if($homeShowWidgets && is_active_sidebar('sidebar-home'))
            <flux:main class="py-12 sm:py-14 bg-white dark:bg-zinc-950">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @php dynamic_sidebar('sidebar-home'); @endphp
                </div>
            </flux:main>
                    @endif
                @break
            @endswitch
        @endforeach
</section>
