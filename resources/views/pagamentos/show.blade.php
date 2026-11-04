<x-crud.layout title="Detalhes do pagamento" :subtitle="'Pagamento #'.$pagamento->id" width="max-w-3xl">
    <x-slot name="actions">
        <a href="{{ route('pagamentos.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Voltar</a>
        @can('update', $pagamento)
            <a href="{{ route('pagamentos.edit', $pagamento) }}" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">Editar</a>
        @endcan
        @can('delete', $pagamento)
            <x-crud.delete :action="route('pagamentos.destroy', $pagamento)" confirm="Excluir este pagamento?" :button="true" />
        @endcan
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
        <x-crud.dado label="Cliente">
            <a href="{{ route('clientes.show', $pagamento->cliente) }}" class="text-blue-700 hover:underline">{{ $pagamento->cliente->nome }}</a>
        </x-crud.dado>
        <x-crud.dado label="Plano">{{ $pagamento->plano }}</x-crud.dado>
        <x-crud.dado label="Forma de pagamento">{{ $pagamento->forma_pagamento === 'Credito' ? 'Crédito' : 'Débito' }}</x-crud.dado>
        <x-crud.dado label="Dia do pagamento">{{ $pagamento->dia_pagamento }}</x-crud.dado>
        <x-crud.dado label="Cadastro">
            <x-crud.badge :color="$pagamento->ativo ? 'green' : 'gray'">{{ $pagamento->ativo ? 'Ativo' : 'Inativo' }}</x-crud.badge>
        </x-crud.dado>
        <x-crud.dado label="Situação">
            <x-crud.badge :color="$pagamento->status_pagamento === 'Pago' ? 'green' : 'red'">{{ $pagamento->status_pagamento }}</x-crud.badge>
        </x-crud.dado>
    </div>
</x-crud.layout>
