@props(['status'])
@php
    $labels = \App\Models\Task::STATUSES;
    $label = $labels[$status] ?? ucfirst($status);
@endphp
<span {{ $attributes->merge(['class' => 'badge badge-status-' . $status]) }}>{{ $label }}</span>
