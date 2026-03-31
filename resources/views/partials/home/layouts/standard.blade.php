@php
    $homeThemes = [
            'corporate' => [
                'badge' => __('Corporate', 'sage'),
                'badge_color' => 'sky',
                'hero_bg' => 'bg-gradient-to-br from-slate-50 via-white to-sky-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-slate-900',
                'hero_title' => __('Digital solutions for demanding teams', 'sage'),
                'hero_text' => __('Structure, speed and premium experience for businesses that need measurable results.', 'sage'),
                'features' => [
                    ['icon' => 'briefcase', 'title' => __('Stable operation', 'sage'), 'text' => __('Architecture prepared for frictionless growth.', 'sage')],
                    ['icon' => 'shield-check', 'title' => __('Real security', 'sage'), 'text' => __('Good security practices at every layer of the site.', 'sage')],
                    ['icon' => 'chart-bar', 'title' => __('Clear KPIs', 'sage'), 'text' => __('Actionable metrics for data-driven decisions.', 'sage')],
                ],
                'stats' => [
                    ['value' => '99.9%', 'label' => __('Uptime', 'sage')],
                    ['value' => '42%', 'label' => __('Conversion improvement', 'sage')],
                    ['value' => '3x', 'label' => __('Delivery speed', 'sage')],
                    ['value' => '24/7', 'label' => __('Support', 'sage')],
                ],
                'cta_bg' => 'bg-slate-900 dark:bg-black',
            ],
            'marketing' => [
                'badge' => __('Marketing', 'sage'),
                'badge_color' => 'lime',
                'hero_bg' => 'bg-gradient-to-br from-amber-50 via-white to-lime-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
                'hero_title' => __('Conversion-focused landing', 'sage'),
                'hero_text' => __('Structure oriented to campaigns, lead generation and sales with a clear narrative.', 'sage'),
                'features' => [
                    ['icon' => 'megaphone', 'title' => __('Messages that convert', 'sage'), 'text' => __('Content designed for commercial intent.', 'sage')],
                    ['icon' => 'cursor-arrow-rays', 'title' => __('Visible CTAs', 'sage'), 'text' => __('Highlighted and well-distributed actions.', 'sage')],
                    ['icon' => 'sparkles', 'title' => __('Memorable design', 'sage'), 'text' => __('Clear visual identity without overload.', 'sage')],
                ],
                'stats' => [
                    ['value' => '+31%', 'label' => __('Leads', 'sage')],
                    ['value' => '2.8x', 'label' => __('Average ROAS', 'sage')],
                    ['value' => '14d', 'label' => __('Campaign iteration', 'sage')],
                    ['value' => '87%', 'label' => __('Message retention', 'sage')],
                ],
                'cta_bg' => 'bg-lime-600 dark:bg-lime-700',
            ],
            'news' => [
                'badge' => __('News', 'sage'),
                'badge_color' => 'orange',
                'hero_bg' => 'bg-gradient-to-br from-zinc-50 via-white to-orange-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
                'hero_title' => __('Clear and ordered editorial cover', 'sage'),
                'hero_text' => __('Prioritizes content, reading and navigation for media and digital publications.', 'sage'),
                'features' => [
                    ['icon' => 'newspaper', 'title' => __('Smart cover', 'sage'), 'text' => __('Relevant content at top visual level.', 'sage')],
                    ['icon' => 'funnel', 'title' => __('Topic filtering', 'sage'), 'text' => __('Clear navigation by sections and tags.', 'sage')],
                    ['icon' => 'clock', 'title' => __('Constant updates', 'sage'), 'text' => __('Publish and organize information in real time.', 'sage')],
                ],
                'stats' => [
                    ['value' => '5m', 'label' => __('Avg reading time', 'sage')],
                    ['value' => '74%', 'label' => __('Returning users', 'sage')],
                    ['value' => '120+', 'label' => __('Weekly posts', 'sage')],
                    ['value' => '4.7/5', 'label' => __('Editorial rating', 'sage')],
                ],
                'cta_bg' => 'bg-orange-600 dark:bg-orange-700',
            ],
            'profile' => [
                'badge' => __('Professional Profile', 'sage'),
                'badge_color' => 'cyan',
                'hero_bg' => 'bg-gradient-to-br from-cyan-50 via-white to-blue-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
                'hero_title' => __('Personal presentation with professional focus', 'sage'),
                'hero_text' => __('Ideal for personal brand, portfolio and high-value client acquisition.', 'sage'),
                'features' => [
                    ['icon' => 'user-circle', 'title' => __('Personal brand', 'sage'), 'text' => __('Position your profile with clarity and personality.', 'sage')],
                    ['icon' => 'folder-open', 'title' => __('Portfolio', 'sage'), 'text' => __('Show cases and results in visual format.', 'sage')],
                    ['icon' => 'envelope', 'title' => __('Direct contact', 'sage'), 'text' => __('Reduce friction between visit and opportunity.', 'sage')],
                ],
                'stats' => [
                    ['value' => '12+', 'label' => __('Years of experience', 'sage')],
                    ['value' => '180+', 'label' => __('Completed projects', 'sage')],
                    ['value' => '95%', 'label' => __('Returning clients', 'sage')],
                    ['value' => '48h', 'label' => __('Response time', 'sage')],
                ],
                'cta_bg' => 'bg-cyan-700 dark:bg-cyan-800',
            ],
        ];

    $theme = $homeThemes[$layoutKey] ?? $homeThemes['corporate'];
@endphp

<section class="overflow-hidden">
        <flux:main class="relative overflow-hidden {{ $theme['hero_bg'] }} border-b border-zinc-200 dark:border-zinc-800 py-16 sm:py-20">
            <div class="absolute inset-0 opacity-30 pointer-events-none">
                <div class="absolute top-0 right-0 h-72 w-72 rounded-full bg-white/70 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 h-72 w-72 rounded-full bg-zinc-200/60 dark:bg-zinc-700/40 blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <flux:badge :color="$theme['badge_color']" class="mb-4 uppercase tracking-widest">{{ $theme['badge'] }}</flux:badge>
                    <flux:heading size="6xl" class="tracking-tight !font-black text-zinc-900 dark:text-zinc-100">
                        {{ $theme['hero_title'] }}
                    </flux:heading>
                    <flux:text class="mt-5 text-lg text-zinc-600 dark:text-zinc-300 leading-relaxed">
                        {{ $theme['hero_text'] }}
                    </flux:text>
                </div>
            </div>
        </flux:main>

        @if($homeShowFeatures)
            <flux:main class="py-14 sm:py-16 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach($theme['features'] as $feature)
                            <flux:card class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/60 p-5 shadow-sm">
                                <flux:icon :icon="$feature['icon']" class="size-6 text-accent-600 dark:text-accent-400 mb-4" />
                                <flux:heading size="lg" class="!font-bold text-zinc-900 dark:text-zinc-100">{{ $feature['title'] }}</flux:heading>
                                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ $feature['text'] }}</flux:text>
                            </flux:card>
                        @endforeach
                    </div>
                </div>
            </flux:main>
        @endif

        @if($homeShowStats)
            <flux:main class="py-10 sm:py-12 bg-zinc-900 dark:bg-black border-b border-zinc-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        @foreach($theme['stats'] as $stat)
                            <div class="rounded-2xl bg-white/5 border border-white/10 p-4 sm:p-5">
                                <p class="text-2xl sm:text-3xl font-black text-white">{{ $stat['value'] }}</p>
                                <p class="mt-1 text-xs sm:text-sm uppercase tracking-wider text-zinc-300">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </flux:main>
        @endif

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

        @if($homeShowCta)
            <flux:main class="py-14 sm:py-16 {{ $theme['cta_bg'] }} border-b border-white/10">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <flux:heading size="5xl" class="!font-black !text-white tracking-tight">
                        {{ __('Your home should no longer look improvised', 'sage') }}
                    </flux:heading>
                    <flux:text class="mt-4 text-white/80 text-lg">
                        {{ __('Activate or deactivate sections from the Customizer and adapt this landing to each objective.', 'sage') }}
                    </flux:text>
                    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                        <flux:button size="base" variant="filled" href="{{ admin_url('customize.php') }}" icon="cog-6-tooth">
                            {{ __('Customize Home', 'sage') }}
                        </flux:button>
                        <flux:button size="base" variant="ghost" href="{{ home_url('/contacto') }}" icon="chat-bubble-left-right" class="!text-white hover:!bg-white/10" wire:navigate>
                            {{ __('Talk to Sales', 'sage') }}
                        </flux:button>
                    </div>
                </div>
            </flux:main>
        @endif

        @if($homeShowWidgets && is_active_sidebar('sidebar-home'))
            <flux:main class="py-12 sm:py-14 bg-white dark:bg-zinc-950">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @php dynamic_sidebar('sidebar-home'); @endphp
                </div>
            </flux:main>
        @endif
</section>
