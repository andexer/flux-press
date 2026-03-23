<article @php(post_class('group'))>
  <flux:card class="overflow-hidden transition-all duration-300 hover:shadow-md border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-accent-200 dark:hover:border-accent-900">
    <div class="flex flex-col sm:flex-row gap-6 p-5 sm:p-6">
      {{-- Thumbnail --}}
      @if (has_post_thumbnail())
        <a href="{{ get_permalink() }}" class="shrink-0 sm:w-56 overflow-hidden rounded-xl" wire:navigate>
          <div class="aspect-video sm:aspect-[4/3] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
            {!! get_the_post_thumbnail(null, 'medium', ['class' => 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-105']) !!}
          </div>
        </a>
      @endif

      {{-- Contenido --}}
      <div class="flex-1 min-w-0 flex flex-col justify-center">
        <div class="mb-3">
          @include('partials.entry-meta')
        </div>

        <flux:heading size="lg" class="mb-2 font-semibold">
          <a href="{{ get_permalink() }}" class="text-zinc-900 dark:text-zinc-100 hover:text-accent-600 dark:hover:text-accent-400 transition-colors" wire:navigate>
            {!! $title !!}
          </a>
        </flux:heading>

        <div class="text-zinc-600 dark:text-zinc-400 line-clamp-2 leading-relaxed">
          @php(the_excerpt())
        </div>
      </div>
    </div>
  </flux:card>
</article>
