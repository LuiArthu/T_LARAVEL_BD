@props(['name', 'label', 'span' => false])

{{-- Rótulo + controle (slot) + mensagem de erro de validação. --}}
<div @class(['md:col-span-2' => $span])>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>

    {{ $slot }}

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
