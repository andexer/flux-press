<article @php(post_class('h-entry max-w-4xl mx-auto'))>
  {{-- Hero de artículo --}}
  <header class="mb-12 text-center">
    <div class="flex justify-center mb-6">
      @include('partials.entry-meta')
    </div>

    <flux:heading size="2xl" class="p-name mb-8 font-bold tracking-tight text-4xl sm:text-5xl lg:text-6xl text-balance">
      {!! $title !!}
    </flux:heading>

    @if (has_post_thumbnail())
      <div class="aspect-[21/9] rounded-2xl overflow-hidden shadow-2xl relative">
        {!! get_the_post_thumbnail(null, 'full', ['class' => 'w-full h-full object-cover']) !!}
        <div class="absolute inset-0 ring-1 ring-inset ring-black/10 rounded-2xl"></div>
      </div>
    @endif
  </header>

  {{-- Contenido del artículo --}}
  <div class="e-content prose prose-lg prose-zinc dark:prose-invert max-w-none mx-auto
    prose-headings:font-bold prose-headings:tracking-tight prose-headings:text-zinc-900 dark:prose-headings:text-zinc-100
    prose-a:text-accent-600 dark:prose-a:text-accent-400 prose-a:font-medium hover:prose-a:text-accent-700
    prose-img:rounded-2xl prose-img:shadow-xl
    prose-blockquote:border-accent-500 prose-blockquote:bg-accent-50/50 dark:prose-blockquote:bg-accent-500/10 prose-blockquote:rounded-r-xl prose-blockquote:py-3 prose-blockquote:px-6 prose-blockquote:not-italic prose-blockquote:text-zinc-700 dark:prose-blockquote:text-zinc-300">
    @php(the_content())
  </div>

  {{-- Paginación --}}
  @if ($pagination())
    <div class="mt-12 py-8 border-t border-zinc-200 dark:border-zinc-800">
      <nav class="page-nav" aria-label="Page">
        {!! $pagination !!}
      </nav>
    </div>
  @endif

  {{-- Comentarios --}}
  <div class="mt-16 pt-16 border-t-2 border-zinc-100 dark:border-zinc-800/50">
    @php(comments_template())
  </div>
</article>
