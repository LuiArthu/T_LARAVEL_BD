@props(['right' => false])

<th {{ $attributes->class(['px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider', $right ? 'text-right' : 'text-left']) }}>
    {{ $slot }}
</th>
