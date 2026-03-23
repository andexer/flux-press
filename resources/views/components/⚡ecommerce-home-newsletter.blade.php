<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function newsletter(): array
    {
        $settings = app(HomeEcommerceDataService::class)->settings();
        $newsletter = is_array($settings['newsletter'] ?? null) ? $settings['newsletter'] : [];

        return [
            'title'        => (string) ($newsletter['title'] ?? ''),
            'text'         => (string) ($newsletter['text'] ?? ''),
            'button_label' => (string) ($newsletter['button_label'] ?? ''),
            'button_url'   => (string) ($newsletter['button_url'] ?? '#'),
        ];
    }
}; ?>

<section class="py-14 sm:py-16 bg-accent-600 dark:bg-accent-700 border-b border-accent-700/30">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <flux:heading size="4xl" class="!font-black !text-white tracking-tight">
            {{ $this->newsletter['title'] }}
        </flux:heading>
        <flux:text class="mt-3 text-white/90 text-base sm:text-lg">
            {{ $this->newsletter['text'] }}
        </flux:text>

        <div class="mt-7 flex justify-center">
            <flux:button href="{{ $this->newsletter['button_url'] }}" wire:navigate icon="envelope" class="!bg-white !text-accent-700 hover:!bg-zinc-100">
                {{ $this->newsletter['button_label'] }}
            </flux:button>
        </div>
    </div>
</section>
