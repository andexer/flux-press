@php
    $homeThemes = [
            'corporate' => [
                'badge' => __('Corporativo', 'flux-press'),
                'badge_color' => 'sky',
                'hero_bg' => 'bg-gradient-to-br from-slate-50 via-white to-sky-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-slate-900',
                'hero_title' => __('Soluciones digitales para equipos exigentes', 'flux-press'),
                'hero_text' => __('Estructura, velocidad y experiencia premium para empresas que necesitan resultados medibles.', 'flux-press'),
                'features' => [
                    ['icon' => 'briefcase', 'title' => __('Operacion estable', 'flux-press'), 'text' => __('Arquitectura preparada para crecimiento sin friccion.', 'flux-press')],
                    ['icon' => 'shield-check', 'title' => __('Seguridad real', 'flux-press'), 'text' => __('Buenas practicas de seguridad en cada capa del sitio.', 'flux-press')],
                    ['icon' => 'chart-bar', 'title' => __('KPIs claros', 'flux-press'), 'text' => __('Metricas accionables para decidir con datos.', 'flux-press')],
                ],
                'stats' => [
                    ['value' => '99.9%', 'label' => __('Disponibilidad', 'flux-press')],
                    ['value' => '42%', 'label' => __('Mejora de conversion', 'flux-press')],
                    ['value' => '3x', 'label' => __('Velocidad de entrega', 'flux-press')],
                    ['value' => '24/7', 'label' => __('Soporte', 'flux-press')],
                ],
                'cta_bg' => 'bg-slate-900 dark:bg-black',
            ],
            'marketing' => [
                'badge' => __('Marketing', 'flux-press'),
                'badge_color' => 'lime',
                'hero_bg' => 'bg-gradient-to-br from-amber-50 via-white to-lime-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
                'hero_title' => __('Landing enfocada en conversiones', 'flux-press'),
                'hero_text' => __('Estructura orientada a campanas, captacion y venta con una narrativa clara.', 'flux-press'),
                'features' => [
                    ['icon' => 'megaphone', 'title' => __('Mensajes que convierten', 'flux-press'), 'text' => __('Contenido pensado para intencion comercial.', 'flux-press')],
                    ['icon' => 'cursor-arrow-rays', 'title' => __('CTAs visibles', 'flux-press'), 'text' => __('Acciones destacadas y bien distribuidas.', 'flux-press')],
                    ['icon' => 'sparkles', 'title' => __('Diseno memorable', 'flux-press'), 'text' => __('Identidad visual clara sin sobrecarga.', 'flux-press')],
                ],
                'stats' => [
                    ['value' => '+31%', 'label' => __('Leads', 'flux-press')],
                    ['value' => '2.8x', 'label' => __('ROAS promedio', 'flux-press')],
                    ['value' => '14d', 'label' => __('Iteracion de campanas', 'flux-press')],
                    ['value' => '87%', 'label' => __('Retencion de mensaje', 'flux-press')],
                ],
                'cta_bg' => 'bg-lime-600 dark:bg-lime-700',
            ],
            'news' => [
                'badge' => __('Noticias', 'flux-press'),
                'badge_color' => 'orange',
                'hero_bg' => 'bg-gradient-to-br from-zinc-50 via-white to-orange-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
                'hero_title' => __('Portada editorial clara y ordenada', 'flux-press'),
                'hero_text' => __('Prioriza contenido, lectura y navegacion para medios y publicaciones digitales.', 'flux-press'),
                'features' => [
                    ['icon' => 'newspaper', 'title' => __('Portada inteligente', 'flux-press'), 'text' => __('Contenido relevante en primer nivel visual.', 'flux-press')],
                    ['icon' => 'funnel', 'title' => __('Filtrado por temas', 'flux-press'), 'text' => __('Navegacion clara por secciones y etiquetas.', 'flux-press')],
                    ['icon' => 'clock', 'title' => __('Actualizacion constante', 'flux-press'), 'text' => __('Publica y ordena informacion en tiempo real.', 'flux-press')],
                ],
                'stats' => [
                    ['value' => '5m', 'label' => __('Tiempo medio lectura', 'flux-press')],
                    ['value' => '74%', 'label' => __('Usuarios recurrentes', 'flux-press')],
                    ['value' => '120+', 'label' => __('Notas semanales', 'flux-press')],
                    ['value' => '4.7/5', 'label' => __('Valoracion editorial', 'flux-press')],
                ],
                'cta_bg' => 'bg-orange-600 dark:bg-orange-700',
            ],
            'profile' => [
                'badge' => __('Perfil profesional', 'flux-press'),
                'badge_color' => 'cyan',
                'hero_bg' => 'bg-gradient-to-br from-cyan-50 via-white to-blue-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
                'hero_title' => __('Presentacion personal con enfoque profesional', 'flux-press'),
                'hero_text' => __('Ideal para marca personal, portafolio y captacion de clientes de alto valor.', 'flux-press'),
                'features' => [
                    ['icon' => 'user-circle', 'title' => __('Marca personal', 'flux-press'), 'text' => __('Posiciona tu perfil con claridad y personalidad.', 'flux-press')],
                    ['icon' => 'folder-open', 'title' => __('Portafolio', 'flux-press'), 'text' => __('Muestra casos y resultados en formato visual.', 'flux-press')],
                    ['icon' => 'envelope', 'title' => __('Contacto directo', 'flux-press'), 'text' => __('Reduce friccion entre visita y oportunidad.', 'flux-press')],
                ],
                'stats' => [
                    ['value' => '12+', 'label' => __('Anos de experiencia', 'flux-press')],
                    ['value' => '180+', 'label' => __('Proyectos completados', 'flux-press')],
                    ['value' => '95%', 'label' => __('Clientes recurrentes', 'flux-press')],
                    ['value' => '48h', 'label' => __('Tiempo de respuesta', 'flux-press')],
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
                            <flux:badge color="sky" class="mb-3">{{ __('Entradas recientes', 'flux-press') }}</flux:badge>
                            <flux:heading size="4xl" class="tracking-tight !font-black">
                                {{ sprintf(__('Landing: %s', 'flux-press'), $layoutLabel) }}
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
                            <flux:callout.heading>{{ __('Aun no hay entradas publicadas', 'flux-press') }}</flux:callout.heading>
                            <flux:callout.text>{{ __('Publica articulos para mostrar esta seccion en el Home.', 'flux-press') }}</flux:callout.text>
                        </flux:callout>
                    @endif
                </div>
            </flux:main>
        @endif

        @if($homeShowCta)
            <flux:main class="py-14 sm:py-16 {{ $theme['cta_bg'] }} border-b border-white/10">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <flux:heading size="5xl" class="!font-black !text-white tracking-tight">
                        {{ __('Tu home ya no debe verse improvisado', 'flux-press') }}
                    </flux:heading>
                    <flux:text class="mt-4 text-white/80 text-lg">
                        {{ __('Activa o desactiva secciones desde el Customizer y adapta esta portada a cada objetivo.', 'flux-press') }}
                    </flux:text>
                    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                        <flux:button size="base" variant="filled" href="{{ admin_url('customize.php') }}" icon="cog-6-tooth">
                            {{ __('Personalizar Home', 'flux-press') }}
                        </flux:button>
                        <flux:button size="base" variant="ghost" href="{{ home_url('/contacto') }}" icon="chat-bubble-left-right" class="!text-white hover:!bg-white/10" wire:navigate>
                            {{ __('Hablar con ventas', 'flux-press') }}
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
