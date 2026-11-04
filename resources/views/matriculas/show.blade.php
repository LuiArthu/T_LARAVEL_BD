<x-crud.layout title="Detalhes da matrícula" :subtitle="'Matrícula #'.$matricula->id" width="max-w-3xl">
    <x-slot name="actions">
        <a href="{{ route('matriculas.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Voltar</a>
        @can('update', $matricula)
            <a href="{{ route('matriculas.edit', $matricula) }}" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">Editar</a>
        @endcan
        @can('delete', $matricula)
            <x-crud.delete :action="route('matriculas.destroy', $matricula)" confirm="Excluir esta matrícula?" :button="true" />
        @endcan
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
        <x-crud.dado label="Cliente">
            <a href="{{ route('clientes.show', $matricula->cliente) }}" class="text-blue-700 hover:underline">{{ $matricula->cliente->nome }}</a>
        </x-crud.dado>
        <x-crud.dado label="Modalidade"><x-crud.badge>{{ $matricula->modalidade }}</x-crud.badge></x-crud.dado>
        <x-crud.dado label="Faixa">{{ $matricula->faixa }}</x-crud.dado>
        <x-crud.dado label="Data de início">{{ $matricula->data_inicio->format('d/m/Y') }}</x-crud.dado>
    </div>
</x-crud.layout>
