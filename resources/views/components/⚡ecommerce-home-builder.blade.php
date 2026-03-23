<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function sections(): array
    {
        return app(HomeEcommerceDataService::class)->visibleSections();
    }
}; ?>

<div class="space-y-0" wire:key="ecommerce-home-builder">
    @forelse($this->sections as $section)
        @switch($section)
            @case('hero')
                <livewire:ecommerce-home-hero :key="'ecommerce-home-hero'" />
                @break

            @case('categories')
                <livewire:ecommerce-home-categories :key="'ecommerce-home-categories'" />
                @break

            @case('best_sellers')
                <livewire:ecommerce-home-best-sellers :key="'ecommerce-home-best-sellers'" />
                @break

            @case('top_rated')
                <livewire:ecommerce-home-top-rated :key="'ecommerce-home-top-rated'" />
                @break

            @case('brands')
                <livewire:ecommerce-home-brands :key="'ecommerce-home-brands'" />
                @break

            @case('promos')
                <livewire:ecommerce-home-promos :key="'ecommerce-home-promos'" />
                @break

            @case('newsletter')
                <livewire:ecommerce-home-newsletter :key="'ecommerce-home-newsletter'" />
                @break

            @case('blog')
                <livewire:ecommerce-home-blog :key="'ecommerce-home-blog'" />
                @break
        @endswitch
    @empty
        <section class="py-10 sm:py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <flux:callout color="amber" icon="information-circle">
                    <flux:callout.heading>{{ __('No hay secciones activas para el Home ecommerce.', 'flux-press') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('Activa secciones desde Apariencia > Personalizar > Flux Press: Home Ecommerce.', 'flux-press') }}</flux:callout.text>
                </flux:callout>
            </div>
        </section>
    @endforelse
</div>
