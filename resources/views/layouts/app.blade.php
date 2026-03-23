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
    @php wp_head(); @endphp
    @vite(['resources/css/app.css', 'resources/css/woocommerce.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxAppearance
</head>

<body @php echo body_class('min-h-screen bg-white dark:bg-zinc-800 antialiased'); @endphp>
    @php wp_body_open(); @endphp

    @php do_action('get_header'); @endphp

    @php
        $isHomeBuilderPage = is_front_page()
            || (function_exists('is_page_template') && is_page_template('page-home.blade.php'));
    @endphp

    @if(($isWooCommerceActive ?? false) && is_account_page() && is_user_logged_in())
        <flux:sidebar sticky collapsible class="flux-account-sidebar bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.header class="flux-account-sidebar-header">
                <div class="in-data-flux-sidebar-collapsed-desktop:hidden min-w-0">
                    <flux:heading size="sm" class="text-zinc-500 dark:text-zinc-400 font-semibold uppercase tracking-widest text-[10px]">
                        {{ __('Mi cuenta', 'flux-press') }}
                    </flux:heading>
                </div>

                <div class="ms-auto flex items-center gap-1">
                    <flux:sidebar.toggle class="hidden lg:inline-flex" icon="bars-2" />
                    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
                </div>
            </flux:sidebar.header>

            <flux:sidebar.nav class="mt-1">
                <livewire:account-nav />
            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            @php
                $current_user = wp_get_current_user();
                $custom_photo_id = (int) get_user_meta($current_user->ID, 'flux_profile_photo_id', true);
                $profile_avatar = $custom_photo_id
                    ? wp_get_attachment_image_url($custom_photo_id, 'thumbnail')
                    : null;
                $profile_avatar = $profile_avatar ?: get_avatar_url($current_user->ID, ['size' => 96]);
            @endphp
            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:sidebar.profile 
                    :avatar="$profile_avatar"
                    :name="$current_user->display_name" 
                />
                <flux:menu class="w-64">
                    @foreach($accountEndpoints as $endpoint => $label)
                        @if($endpoint !== 'dashboard' && $endpoint !== 'customer-logout')
                            <flux:menu.item icon="{{ $accountIcons[$endpoint] ?? 'chevron-right' }}" href="{{ wc_get_account_endpoint_url($endpoint) }}" wire:navigate>{{ $label }}</flux:menu.item>
                        @endif
                    @endforeach
                    <flux:separator />
                    <flux:menu.item icon="{{ $accountIcons['customer-logout'] ?? 'arrow-right-start-on-rectangle' }}" href="{{ wp_logout_url(home_url('/')) }}">{{ __('Cerrar Sesión', 'flux-press') }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>
    @endif



    <flux:main class="flex flex-col min-h-screen !p-0 lg:!p-0">
        {{-- Contenedor de Contenido Principal --}}
        <div @class([
            'flex-1 w-full' => $isHomeBuilderPage,
            'flex-1 w-full max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8' => ! $isHomeBuilderPage,
        ])>
            @yield('content')
        </div>

        {{-- Footer Empujado al Fondo --}}
        <div class="w-full mt-auto">
            @php do_action('get_footer'); @endphp
        </div>
    </flux:main>

    @php wp_footer(); @endphp
    @livewireScripts
    @fluxScripts
</body>
</html>
