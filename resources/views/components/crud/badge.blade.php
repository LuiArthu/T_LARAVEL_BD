@props(['color' => 'blue'])

@php
    $cores = [
        'blue' => 'bg-blue-100 text-blue-700',
        'green' => 'bg-green-100 text-green-700',
        'red' => 'bg-red-100 text-red-700',
        'gray' => 'bg-gray-100 text-gray-600',
    ];
@endphp

<span {{ $attributes->class(['inline-flex px-3 py-1 rounded-full text-xs font-semibold', $cores[$color] ?? $cores['blue']]) }}>
    {{ $slot }}
</span>
