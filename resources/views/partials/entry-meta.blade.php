<div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-sm font-medium">
  {{-- Author con Avatar --}}
  <a href="{{ get_author_posts_url(get_the_author_meta('ID')) }}" class="p-author h-card flex items-center gap-2 group/author" wire:navigate>
    <div class="w-7 h-7 rounded-full overflow-hidden bg-zinc-100 ring-2 ring-white dark:ring-zinc-900 group-hover/author:ring-accent-500 transition-all">
      {!! get_avatar(get_the_author_meta('ID'), 28, '', '', ['class' => 'w-full h-full object-cover']) !!}
    </div>
    <span class="text-zinc-700 dark:text-zinc-300 group-hover/author:text-accent-600 dark:group-hover/author:text-accent-400 transition-colors">
      {{ get_the_author() }}
    </span>
  </a>

  <span class="text-zinc-300 dark:text-zinc-700 hidden sm:inline">&bull;</span>

  {{-- Fecha --}}
  <time class="dt-published flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400" datetime="{{ get_post_time('c', true) }}">
    <flux:icon.calendar-days class="size-4" />
    {{ get_the_date() }}
  </time>

  {{-- Categoría --}}
  @if (has_category())
    <span class="text-zinc-300 dark:text-zinc-700 hidden sm:inline">&bull;</span>
    <div class="flex items-center gap-1.5 text-accent-600 dark:text-accent-400">
      <flux:icon.tag class="size-4" />
      <span class="[&>a]:hover:text-accent-700 [&>a]:transition-colors">{!! str_replace('<a ', '<a wire:navigate ', get_the_category_list(', ')) !!}</span>
    </div>
  @endif
</div>
