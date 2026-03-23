<?php
use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    public string $demo = 'corporate';

    public function mount(string $demo = 'corporate'): void {
        $this->demo = $demo;
    }

    #[On('demo-changed')]
    public function changeDemo(string $demo): void {
        $this->demo = $demo;
    }
};
?>
<div>
    @php
        $variants = [
            'red-social' => 'hero-social',
            'ecommerce' => 'hero-ecommerce',
            'blog' => 'hero-default',
            'news' => 'hero-news',
            'streaming' => 'hero-streaming',
            'gaming' => 'hero-gaming',
            'galeria' => 'hero-galeria',
            'profile' => 'hero-profile',
            'marketing' => 'hero-marketing',
            'corporate' => 'hero-corporate',
            'default' => 'hero-default'
        ];
        $variant = $variants[$demo] ?? $variants['default'];
    @endphp

    @include("sections.{$variant}")
</div>