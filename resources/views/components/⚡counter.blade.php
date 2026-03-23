<?php

use Livewire\Component;

new class extends Component
{
	public int $counter = 0;

	public function increment(): void
	{
		$this->counter++;
	}

	public function decrement(): void
	{
		$this->counter--;
	}
};
?>

<div>
	<flux:card>
		<div>
			<flux:heading size="lg">Counter {{ $counter }}</flux:heading>
			<flux:subheading>Gestión de contador con Flux</flux:subheading>
		</div>

		<div class="flex gap-2 mt-4">
			<flux:button wire:click="increment" variant="primary">+ 1</flux:button>
			<flux:button wire:click="decrement" variant="filled">- 1</flux:button>
		</div>
	</flux:card>
</div>