@php
    $defaultVariant = (string) config('theme-interface.footer.default_style', 'corporate');
@endphp

@includeFirst([
    "partials.footers.{$variant}",
    "partials.footers.{$defaultVariant}",
    'partials.footers.corporate',
])
