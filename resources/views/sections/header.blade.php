@php
    $defaultVariant = (string) config('theme-interface.header.default_style', 'classic');
@endphp

@includeFirst([
    "partials.headers.{$variant}",
    "partials.headers.{$defaultVariant}",
    'partials.headers.classic',
])
