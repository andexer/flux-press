<?php

use App\Services\HomeEcommerceDataService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    /** @var string[] */
    public array $hiddenSections = [];

    public int $refreshTick = 0;

    /**
     * @param array<int,string> $hiddenSections
     */
    public function mount(array $hiddenSections = []): void
    {
        $this->hiddenSections = array_values(array_filter(
            array_map(static fn ($section): string => sanitize_key((string) $section), $hiddenSections),
            static fn (string $section): bool => in_array($section, HomeEcommerceDataService::SECTION_KEYS, true)
        ));
    }

    #[Computed]
    public function sections(): array
    {
        $sections = app(HomeEcommerceDataService::class)->visibleSections();

        if (empty($this->hiddenSections)) {
            return $sections;
        }

        return array_values(array_filter(
            $sections,
            fn (string $section): bool => ! in_array($section, $this->hiddenSections, true)
        ));
    }

    #[On('flux-home-builder-refresh')]
    #[On('fluxHomeBuilderRefresh')]
    public function refreshBuilder(): void
    {
        $this->refreshTick++;
    }
}; ?>

<div
    class="space-y-0"
    wire:key="ecommerce-home-builder-{{ $refreshTick }}"
>
    @forelse($this->sections as $section)
        @switch($section)
            @case('hero')
                <livewire:ecommerce-home-hero :key="'ecommerce-home-hero-'.$refreshTick" />
                @break

            @case('categories')
                <livewire:ecommerce-home-categories :key="'ecommerce-home-categories-'.$refreshTick" />
                @break

            @case('best_sellers')
                <livewire:ecommerce-home-best-sellers :key="'ecommerce-home-best-sellers-'.$refreshTick" />
                @break

            @case('top_rated')
                <livewire:ecommerce-home-top-rated :key="'ecommerce-home-top-rated-'.$refreshTick" />
                @break

            @case('brands')
                <livewire:ecommerce-home-brands :key="'ecommerce-home-brands-'.$refreshTick" />
                @break

            @case('promos')
                <livewire:ecommerce-home-promos :key="'ecommerce-home-promos-'.$refreshTick" />
                @break

            @case('newsletter')
                <livewire:ecommerce-home-newsletter :key="'ecommerce-home-newsletter-'.$refreshTick" />
                @break

            @case('blog')
                <livewire:ecommerce-home-blog :key="'ecommerce-home-blog-'.$refreshTick" />
                @break
        @endswitch
    @empty
        <section class="py-10 sm:py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <flux:callout color="amber" icon="information-circle">
                    <flux:callout.heading>{{ __('No hay secciones activas para el Home ecommerce.', 'sage') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('Activa secciones desde Apariencia > Personalizar > Flux Press: Home Ecommerce.', 'sage') }}</flux:callout.text>
                </flux:callout>
            </div>
        </section>
    @endforelse
</div>
