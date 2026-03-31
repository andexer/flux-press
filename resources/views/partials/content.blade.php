<article @php(post_class('group flex flex-col h-full'))>
  <flux:card class="flex-1 overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border-transparent dark:border-zinc-800 bg-white dark:bg-zinc-900/50 backdrop-blur-sm">
    {{-- Thumbnail --}}
    @if (has_post_thumbnail())
      <a href="{{ get_permalink() }}" class="block overflow-hidden relative" wire:navigate>
        <div class="aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
          {!! get_the_post_thumbnail(null, 'medium_large', ['class' => 'w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105']) !!}
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      </a>
    @endif

    <div class="p-6 sm:p-8 flex flex-col flex-1">
      {{-- Meta --}}
      <div class="mb-4">
        @include('partials.entry-meta')
      </div>

      {{-- Título --}}
      <flux:heading size="xl" class="mb-3 font-semibold tracking-tight">
        <a href="{{ get_permalink() }}" class="text-zinc-900 dark:text-zinc-100 hover:text-accent-600 dark:hover:text-accent-400 transition-colors line-clamp-2" wire:navigate>
          {!! $title !!}
        </a>
      </flux:heading>

      {{-- Extracto --}}
      <div class="mt-2 text-zinc-600 dark:text-zinc-400 line-clamp-3 leading-relaxed flex-1">
        @php(the_excerpt())
      </div>

      {{-- Read more --}}
      <div class="mt-6 pt-6 border-t border-zinc-100 dark:border-zinc-800/50 flex items-center justify-between">
        <a href="{{ get_permalink() }}" class="inline-flex items-center gap-2 text-sm font-medium text-accent-600 dark:text-accent-400 hover:text-accent-700 dark:hover:text-accent-300 transition-colors group/link" wire:navigate>
          {{ __('Read full article', 'sage') }}
          <flux:icon.arrow-right class="size-4 transition-transform duration-300 group-hover/link:translate-x-1" />
        </a>
      </div>
    </div>
  </flux:card>
</article>
