@php
    $title = $sectionData['title'] ?? '';
    $email = $sectionData['email'] ?? '';
    $phone = $sectionData['phone'] ?? '';
    $address = $sectionData['address'] ?? '';
@endphp

@if(!empty($title) || !empty($email) || !empty($phone) || !empty($address))
<flux:main class="py-14 sm:py-16 bg-zinc-900 dark:bg-black border-b border-zinc-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        @if($title)
            <flux:heading size="4xl" class="mb-8 tracking-tight !font-black text-white">
                {{ $title }}
            </flux:heading>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @if($email)
                <div class="text-center">
                    <div class="w-14 h-14 mx-auto rounded-full bg-white/10 flex items-center justify-center mb-4">
                        <flux:icon icon="envelope" class="size-6 text-white" />
                    </div>
                    <flux:heading size="md" class="!font-semibold text-white">{{ __('Email', 'sage') }}</flux:heading>
                    <a href="mailto:{{ $email }}" class="text-zinc-300 hover:text-white transition-colors">{{ $email }}</a>
                </div>
            @endif
            @if($phone)
                <div class="text-center">
                    <div class="w-14 h-14 mx-auto rounded-full bg-white/10 flex items-center justify-center mb-4">
                        <flux:icon icon="phone" class="size-6 text-white" />
                    </div>
                    <flux:heading size="md" class="!font-semibold text-white">{{ __('Phone', 'sage') }}</flux:heading>
                    <a href="tel:{{ $phone }}" class="text-zinc-300 hover:text-white transition-colors">{{ $phone }}</a>
                </div>
            @endif
            @if($address)
                <div class="text-center">
                    <div class="w-14 h-14 mx-auto rounded-full bg-white/10 flex items-center justify-center mb-4">
                        <flux:icon icon="map-pin" class="size-6 text-white" />
                    </div>
                    <flux:heading size="md" class="!font-semibold text-white">{{ __('Address', 'sage') }}</flux:heading>
                    <p class="text-zinc-300">{{ $address }}</p>
                </div>
            @endif
        </div>
    </div>
</flux:main>
@endif