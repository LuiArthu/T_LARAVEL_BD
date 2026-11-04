@props(['label'])

{{-- Par rótulo/valor das telas de detalhes. --}}
<div>
    <p class="text-xs uppercase font-semibold text-gray-400">{{ $label }}</p>
    <div class="mt-1 text-gray-800 font-medium">{{ $slot }}</div>
</div>
