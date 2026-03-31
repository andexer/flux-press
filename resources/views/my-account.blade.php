{{--
  Template Name: WooCommerce My Account
--}}
@extends('layouts.app')

@section('sub_navigation')
    <div class="px-3 mb-2 mt-2 lg:mt-0">
        <flux:heading size="sm" class="text-zinc-400 dark:text-zinc-500 font-semibold uppercase tracking-widest text-[10px]">{{ __('My Account', 'sage') }}</flux:heading>
    </div>
@endsection

@section('content')


  @while(have_posts()) 
    @php
        the_post();
        $current_endpoint = WC()->query->get_current_endpoint();
    @endphp



    <div class="woocommerce w-full px-4 sm:px-0">

        {{-- Premium Account Dashboard Heading --}}
        @if(is_account_page() && !$current_endpoint || $current_endpoint === 'dashboard')
            @php($current_user = wp_get_current_user())
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center gap-6 rounded-3xl bg-zinc-50 dark:bg-zinc-900/50 p-6 sm:p-10 border border-zinc-200 dark:border-zinc-800 shadow-sm relative overflow-hidden group">
                <!-- Abstract Background Decoration -->
                <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-accent-500/5 blur-3xl group-hover:bg-accent-500/10 transition-colors duration-700 pointer-events-none"></div>
                <div class="absolute -bottom-24 left-0 gap-4 w-48 h-48 rounded-full bg-emerald-500/5 blur-3xl group-hover:bg-emerald-500/10 transition-colors duration-700 pointer-events-none"></div>

                <livewire:profile-photo :user_id="$current_user->ID" />

                <div class="relative z-10">
                    <flux:heading size="xl" level="1" class="mb-2 font-black tracking-tight text-zinc-900 dark:text-zinc-50">
                        {{ __('Welcome back,', 'sage') }} <span class="text-accent-600 dark:text-accent-400">{{ $current_user->display_name }}</span>
                    </flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400 text-base max-w-xl leading-relaxed">
                        {{ __('Welcome to your personal area. From here you have full control over your orders, addresses and account security.', 'sage') }}
                    </flux:text>
                </div>
            </div>
        @endif


        @php
            $isEditAccountEndpoint = $current_endpoint === 'edit-account';
            $contentShellClasses = $isEditAccountEndpoint
                ? 'p-0'
                : 'bg-white dark:bg-zinc-900 rounded-3xl shadow-sm border border-zinc-200 dark:border-zinc-800 p-6 sm:p-12';
            $contentBodyClasses = $isEditAccountEndpoint
                ? 'max-w-none'
                : 'prose dark:prose-invert max-w-none';
        @endphp

        <div class="{{ $contentShellClasses }}">
            {{-- Nav Grid (Only on main Dashboard) --}}
            @if($current_endpoint === 'dashboard' || !$current_endpoint)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">

                    @foreach ($accountEndpoints as $endpoint => $label)
                        @php($icon = $accountIcons[$endpoint] ?? 'chevron-right')
                        @if($endpoint !== 'dashboard' && $endpoint !== 'customer-logout')
                            <a href="{{ wc_get_account_endpoint_url($endpoint) }}" wire:navigate class="flex flex-col items-center justify-center p-8 text-center rounded-[2rem] border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/30 hover:border-accent-400 dark:hover:border-accent-500/50 hover:bg-white dark:hover:bg-zinc-800 hover:shadow-xl transition-all duration-500 group relative overflow-hidden">
                                <div class="absolute inset-0 bg-linear-to-br from-accent-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div class="p-5 rounded-2xl bg-white dark:bg-zinc-700 shadow-md border border-zinc-100 dark:border-zinc-600 mb-6 group-hover:scale-110 group-hover:-translate-y-1 transition-all duration-300">
                                    <flux:icon :name="$icon" class="size-8 text-accent-600 dark:text-accent-400" />
                                </div>
                                <span class="text-base font-black text-zinc-900 dark:text-zinc-100 mb-1 relative z-10 uppercase tracking-wide">{{ $label }}</span>
                                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-[0.2em] relative z-10">{{ __('Manage', 'sage') }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="{{ $contentBodyClasses }}">
                @php(the_content())
            </div>
        </div>


    </div>
  @endwhile
@endsection
