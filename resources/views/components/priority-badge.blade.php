@props(['priority'])
@php
    $labels = \App\Models\Task::PRIORITIES;
    $label = $labels[$priority] ?? ucfirst($priority);
@endphp
<span {{ $attributes->merge(['class' => 'badge badge-priority-' . $priority]) }}>{{ $label }}</span>
