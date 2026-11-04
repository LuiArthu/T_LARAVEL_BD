@props(['icone' => '📄', 'titulo', 'texto' => null])

<div class="p-12 text-center">
    <div class="text-4xl mb-4">{{ $icone }}</div>
    <h3 class="text-lg font-bold text-gray-800">{{ $titulo }}</h3>

    @if ($texto)
        <p class="text-gray-500 mt-2">{{ $texto }}</p>
    @endif

    {{ $slot }}
</div>
