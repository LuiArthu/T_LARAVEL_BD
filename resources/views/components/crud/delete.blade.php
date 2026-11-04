@props(['action', 'confirm' => 'Tem certeza que deseja excluir?', 'button' => false])

<form action="{{ $action }}" method="POST" class="inline">
    @csrf
    @method('DELETE')

    <button
        type="submit"
        onclick="return confirm({{ Js::from($confirm) }})"
        @class([
            'px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700' => $button,
            'text-sm font-medium text-red-600 hover:text-red-800' => ! $button,
        ])>
        {{ $slot->isEmpty() ? 'Excluir' : $slot }}
    </button>
</form>
