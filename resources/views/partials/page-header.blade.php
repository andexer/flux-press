<div class="page-header relative mb-12 py-16 px-6 sm:px-12 rounded-3xl bg-zinc-900 text-white overflow-hidden shadow-xl">
  {{-- Elementos decorativos --}}
  <div class="absolute inset-0 opacity-[0.1]" style="background-image: radial-gradient(circle at 2px 2px, white 2px, transparent 2px); background-size: 32px 32px;"></div>
  <div class="absolute -top-24 -right-24 w-96 h-96 bg-accent-500/30 rounded-full blur-[80px]"></div>
  
  <div class="relative z-10 max-w-3xl">
    <flux:heading size="2xl" class="!text-white mb-4 tracking-tight font-bold !text-4xl sm:!text-5xl">
      {!! $title !!}
    </flux:heading>

    @if (is_archive())
      <div class="text-zinc-300 text-lg sm:text-xl font-medium leading-relaxed max-w-2xl">
        {!! get_the_archive_description() !!}
      </div>
    @endif
  </div>
</div>
