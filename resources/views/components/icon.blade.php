@props(['name'])
@php
    $paths = [
        'check' => '<polyline points="20 6 9 17 4 12"></polyline>',
        'undo' => '<path d="M3 12a9 9 0 1 0 3-6.7"></path><polyline points="3 4 3 9 8 9"></polyline>',
        'trash' => '<polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? '' !!}</svg>
