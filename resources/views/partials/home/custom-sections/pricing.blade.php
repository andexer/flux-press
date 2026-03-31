@php
    $title = $sectionData['title'] ?? '';
    $items = $sectionData['items'] ?? [];
@endphp

@if(!empty($items))
<flux:main class="py-14 sm:py-16 bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <flux:heading size="4xl" class="mb-10 text-center tracking-tight !font-black text-zinc-900 dark:text-zinc-100">
                {{ $title }}
            </flux:heading>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-{{ count($items) > 2 ? 3 : 2 }} gap-6">
            @foreach($items as $plan)
                @if(!empty($plan['name']))
                    <flux:card class="p-6 rounded-2xl border-2 border-zinc-200 dark:border-zinc-700 hover:border-accent-500 transition-colors">
                        <flux:heading size="xl" class="!font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $plan['name'] }}
                        </flux:heading>
                        <div class="mt-4 flex items-baseline">
                            <span class="text-4xl font-black text-zinc-900 dark:text-zinc-100">{{ $plan['price'] ?? '' }}</span>
                            @if(!empty($plan['price']))
                                <span class="ml-1 text-zinc-500">/mo</span>
                            @endif
                        </div>
                        @if(!empty($plan['features']) && is_array($plan['features']))
                            <ul class="mt-6 space-y-3">
                                @foreach($plan['features'] as $feature)
                                    @if(!empty($feature))
                                        <li class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                                            <flux:icon icon="check-circle" class="size-5 text-green-500" />
                                            {{ $feature }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                        <div class="mt-8">
                            <flux:button variant="primary" class="w-full">
                                {{ __('Choose Plan', 'sage') }}
                            </flux:button>
                        </div>
                    </flux:card>
                @endif
            @endforeach
        </div>
    </div>
</flux:main>
@endif