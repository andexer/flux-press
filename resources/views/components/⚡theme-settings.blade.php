<?php

use Livewire\Component;

new class extends Component
{
	public string $selectedAccent = 'sky';
	public string $customColor = '#3b82f6';
	public string $selectedAppearance = 'system';
	public bool $asNavLink = false;
	public bool $canAccess = false;

	private const TAILWIND_COLORS = [
		'gray',
		'red',
		'orange',
		'amber',
		'yellow',
		'lime',
		'green',
		'emerald',
		'teal',
		'cyan',
		'sky',
		'blue',
		'indigo',
		'violet',
		'purple',
		'fuchsia',
		'pink',
		'rose',
	];

	public function mount(): void
	{
		$this->canAccess = current_user_can('edit_posts');

		if (!$this->canAccess) {
			return;
		}

		$accent = get_option('flux_theme_accent', 'sky');
		$this->selectedAccent = $accent;
		$this->selectedAppearance = get_option('flux_theme_appearance', 'system');
		$this->customColor = $this->isHex($accent) ? $accent : '#3b82f6';
	}

	public function updatedSelectedAccent(string $value): void
	{
		if (!$this->canAccess) return;
		$value = trim($value);

		if (!in_array($value, self::TAILWIND_COLORS, true) && !$this->isHex($value)) {
			return;
		}

		if ($this->isHex($value)) {
			$this->customColor = $value;
		}

		update_option('flux_theme_accent', $value);
		$this->injectAccentCss($value);
	}

	public function updatedCustomColor(string $value): void
	{
		$this->applyCustomColor($value);
	}

	public function applyCustomColor(string $hex): void
	{
		if (!$this->canAccess) return;
		$hex = trim($hex);

		if (!$this->isHex($hex)) {
			return;
		}

		$this->selectedAccent = $hex;
		$this->customColor = $hex;
		update_option('flux_theme_accent', $hex);
		$this->injectAccentCss($hex);
	}

	public function updatedSelectedAppearance(string $value): void
	{
		if (!$this->canAccess) return;
		$value = trim($value);
		$allowed = ['light', 'dark', 'system'];

		if (!in_array($value, $allowed, true)) {
			return;
		}

		update_option('flux_theme_appearance', $value);
		$this->injectAppearanceCss($value);
	}

	private function injectAccentCss(string $color): void
	{
		$shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

		if ($this->isHex($color)) {
			$cssRules = '';
			foreach ($shades as $s) {
				$cssRules .= "--color-accent-{$s}: {$color} !important;";
			}
			$cssRules .= "--color-accent: {$color} !important;";
			$cssRules .= "--color-accent-content: {$color} !important;";
			$cssRules .= "--color-accent-foreground: #ffffff !important;";
			$darkCss = "--color-accent: {$color} !important; --color-accent-content: {$color} !important;";
		} else {
			$cssRules = '';
			foreach ($shades as $s) {
				$cssRules .= "--color-accent-{$s}: var(--color-{$color}-{$s}) !important;";
			}
			$cssRules .= "--color-accent: var(--color-accent-600) !important;";
			$cssRules .= "--color-accent-content: var(--color-accent-600) !important;";
			$cssRules .= "--color-accent-foreground: #ffffff !important;";
			$darkCss = "--color-accent: var(--color-{$color}-400) !important; --color-accent-content: var(--color-{$color}-400) !important;";
		}

		$this->js("
            (() => {
                let tag = document.getElementById('flux-dynamic-vars-overrides');
                if (!tag) { tag = document.createElement('style'); tag.id = 'flux-dynamic-vars-overrides'; document.head.appendChild(tag); }
                tag.textContent = ':root, [data-flux-appearance] { {$cssRules} } .dark, [data-flux-appearance=\"dark\"] { {$darkCss} }';
            })()
        ");
	}

	private function injectAppearanceCss(string $mode): void
	{
		// GUARDAR LOCALSTORAGE + DISPATCH GLOBAL
		update_option('flux_theme_appearance', $mode);

		$this->js("
            // Guardar persistente
            localStorage.setItem('flux-appearance', '{$mode}');
            
            // Aplicar inmediatamente
            const html = document.documentElement;
            html.classList.remove('dark', 'light');
            
            if ('{$mode}' === 'dark') {
                html.classList.add('dark');
            } else if ('{$mode}' === 'system') {
                const mql = window.matchMedia('(prefers-color-scheme: dark)');
                html.classList.toggle('dark', mql.matches);
                mql.addEventListener('change', e => {
                    html.classList.toggle('dark', e.matches);
                });
            }
            
            // Notificar a otros componentes
            window.dispatchEvent(new CustomEvent('flux:theme-changed', { 
                detail: { mode: '{$mode}' } 
            }));
        ");
	}

	private function isHex(string $value): bool
	{
		return (bool) preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value);
	}

	public function getColors(): array
	{
		return [
			'gray'    => ['label' => 'Gris',       'hex' => '#71717a'],
			'red'     => ['label' => 'Rojo',       'hex' => '#ef4444'],
			'orange'  => ['label' => 'Naranja',    'hex' => '#f97316'],
			'amber'   => ['label' => 'Ámbar',      'hex' => '#f59e0b'],
			'yellow'  => ['label' => 'Amarillo',   'hex' => '#eab308'],
			'lime'    => ['label' => 'Lima',       'hex' => '#84cc16'],
			'green'   => ['label' => 'Verde',      'hex' => '#22c55e'],
			'emerald' => ['label' => 'Esmeralda',  'hex' => '#10b981'],
			'teal'    => ['label' => 'Teal',       'hex' => '#14b8a6'],
			'cyan'    => ['label' => 'Cian',       'hex' => '#06b6d4'],
			'sky'     => ['label' => 'Cielo',      'hex' => '#0ea5e9'],
			'blue'    => ['label' => 'Azul',       'hex' => '#3b82f6'],
			'indigo'  => ['label' => 'Índigo',     'hex' => '#6366f1'],
			'violet'  => ['label' => 'Violeta',    'hex' => '#8b5cf6'],
			'purple'  => ['label' => 'Púrpura',    'hex' => '#a855f7'],
			'fuchsia' => ['label' => 'Fucsia',     'hex' => '#d946ef'],
			'pink'    => ['label' => 'Rosa',       'hex' => '#ec4899'],
			'rose'    => ['label' => 'Rosa Roja',  'hex' => '#f43f5e'],
		];
	}
};
?>

<div>
	@if($canAccess)
	<div class="flux-theme-customizer">
		<flux:modal.trigger name="flux-theme-settings-modal">
			@if($asNavLink)
			<flux:navlist.item icon="swatch">Configurar Tema</flux:navlist.item>
			@else
			<flux:navbar.item icon="swatch" tooltip="Configurar Tema" />
			@endif
		</flux:modal.trigger>

		<flux:modal name="flux-theme-settings-modal" flyout variant="floating" class="md:min-w-[28rem] space-y-8">
			<header>
				<flux:heading size="lg">Ajustes Visuales</flux:heading>
				<flux:subheading>Personaliza los colores globales y la apariencia.</flux:subheading>
			</header>

			{{-- Modo de Visualización --}}
			<section>
				<flux:heading size="md" class="mb-4">Tema de Interfaz</flux:heading>
				<flux:radio.group variant="segmented" wire:model.live="selectedAppearance">
					<flux:radio value="light" label="Claro" icon="sun" />
					<flux:radio value="dark" label="Oscuro" icon="moon" />
					<flux:radio value="system" label="Sistema" icon="computer-desktop" />
				</flux:radio.group>
			</section>

			{{-- Color de Acento --}}
			<section>
				<div class="flex items-center justify-between mb-4">
					<flux:heading size="md">Color Principal</flux:heading>
					<div
						class="relative flex items-center gap-2 px-3 py-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer overflow-hidden">
						<div
							class="size-5 rounded-full border border-black/10 shadow-inner"
							style="background-color: {{ $customColor }}"></div>
						<span class="text-xs font-mono font-bold uppercase">{{ $customColor }}</span>
						<input
							type="color"
							wire:model.live.debounce.200ms="customColor"
							class="absolute inset-[-10px] w-[calc(100%+20px)] h-[calc(100%+20px)] opacity-0 cursor-pointer" />
					</div>
				</div>

				<flux:radio.group wire:model.live="selectedAccent" variant="cards" class="grid grid-cols-3 sm:grid-cols-4 gap-2">
					@foreach ($this->getColors() as $value => $color)
					<flux:radio value="{{ $value }}" class="!p-2">
						<div class="flex items-center gap-2">
							<div class="size-3.5 rounded-full border border-black/10 shrink-0" style="background-color: {{ $color['hex'] }}"></div>
							<span class="text-[11px] font-medium leading-none">{{ $color['label'] }}</span>
						</div>
					</flux:radio>
					@endforeach
				</flux:radio.group>
			</section>

			<x-slot name="footer">
				<flux:spacer />
				<flux:modal.close>
					<flux:button variant="ghost">Cerrar</flux:button>
				</flux:modal.close>
			</x-slot>
		</flux:modal>
	</div>
	@endif
</div>