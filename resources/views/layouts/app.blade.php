<!doctype html>
<html @php echo language_attributes(); @endphp x-data="{ 
        init() {
            this.applySavedTheme();
            document.addEventListener('livewire:navigated', () => this.applySavedTheme());
        },
        applySavedTheme() {
            const saved = localStorage.getItem('flux-appearance') || 'system';
            this.applyTheme(saved);
        },
        applyTheme(mode) {
            const html = document.documentElement;
            html.classList.remove('dark', 'light');
            if (mode === 'dark') {
                html.classList.add('dark');
            } else if (mode === 'system') {
                const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                html.classList.toggle('dark', isDark);
            }
        }
      }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php do_action('get_header'); @endphp
    @php wp_head(); @endphp

    @vite(['resources/css/app.css', 'resources/css/woocommerce.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxAppearance
</head>

<body @php echo body_class('min-h-screen bg-white dark:bg-zinc-800 antialiased'); @endphp>
    @php wp_body_open(); @endphp

    @include('sections.header')

    @if(class_exists('WooCommerce') && is_account_page() && is_user_logged_in())
        <flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.header>
                <flux:sidebar.brand 
                    href="{{ home_url('/') }}" 
                    :name="get_bloginfo('name')" 
                    logo="https://fluxui.dev/img/demo/logo.png" 
                />
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
            </flux:sidebar.header>

            <flux:sidebar.search placeholder="Buscar..." />

            <flux:sidebar.nav>
                @if(view()->hasSection('sub_navigation'))
                    @yield('sub_navigation')
                    <flux:separator class="my-4" />
                @endif

                @php
                    $locations = get_nav_menu_locations();
                    $menu_id = $locations['primary_navigation'] ?? null;
                    $items = $menu_id ? wp_get_nav_menu_items($menu_id) : [];
                @endphp

                @if(!empty($items) && is_array($items))
                    <flux:heading size="sm" class="mb-2 px-2 opacity-50">{{ __('Menú', 'sage') }}</flux:heading>
                    @foreach($items as $item)
                        @if(is_object($item) && empty($item->menu_item_parent))
                            <flux:sidebar.item href="{{ $item->url }}" wire:navigate>{{ $item->title }}</flux:sidebar.item>
                        @endif
                    @endforeach
                @endif
            </flux:sidebar.nav>

            <flux:separator class="my-4 opacity-50" />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" href="#">{{ __('Ajustes', 'sage') }}</flux:sidebar.item>
                <flux:sidebar.item icon="information-circle" href="#">{{ __('Ayuda', 'sage') }}</flux:sidebar.item>
            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            @php $current_user = wp_get_current_user(); @endphp
            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:sidebar.profile 
                    :avatar="get_avatar_url($current_user->ID)" 
                    :name="$current_user->display_name" 
                />
                <flux:menu class="w-64">
                    <flux:menu.item icon="user" href="{{ wc_get_account_endpoint_url('edit-account') }}">{{ __('Mi Perfil', 'sage') }}</flux:menu.item>
                    <flux:menu.item icon="shopping-bag" href="{{ wc_get_account_endpoint_url('orders') }}">{{ __('Mis Pedidos', 'sage') }}</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ wp_logout_url(home_url('/')) }}">{{ __('Cerrar Sesión', 'sage') }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>
    @endif



    <flux:main class="flex flex-col min-h-screen !p-0 lg:!p-0">
        {{-- Contenedor de Contenido Principal --}}
        <div class="flex-1 w-full max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
            @yield('content')
        </div>

        {{-- Footer Empujado al Fondo --}}
        <div class="w-full mt-auto">
            @include('sections.footer')
        </div>
    </flux:main>

    @php do_action('get_footer'); @endphp
    @php wp_footer(); @endphp
    @livewireScripts
    @fluxScripts
</body>
</html>
