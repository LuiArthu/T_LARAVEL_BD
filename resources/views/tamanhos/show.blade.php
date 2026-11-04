<x-crud.layout title="Detalhes dos tamanhos" :subtitle="'Registro #'.$tamanho->id" width="max-w-3xl">
    <x-slot name="actions">
        <a href="{{ route('tamanhos.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Voltar</a>
        @can('update', $tamanho)
            <a href="{{ route('tamanhos.edit', $tamanho) }}" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">Editar</a>
        @endcan
        @can('delete', $tamanho)
            <x-crud.delete :action="route('tamanhos.destroy', $tamanho)" confirm="Excluir este registro?" :button="true" />
        @endcan
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
        <x-crud.dado label="Cliente">
            <a href="{{ route('clientes.show', $tamanho->cliente) }}" class="text-blue-700 hover:underline">{{ $tamanho->cliente->nome }}</a>
        </x-crud.dado>
        <x-crud.dado label="Tamanho da faixa">{{ $tamanho->tamanho_faixa }}</x-crud.dado>
        <x-crud.dado label="Tamanho do kimono">{{ $tamanho->tamanho_kimono }}</x-crud.dado>
    </div>
</x-crud.layout>
