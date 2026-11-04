<x-crud.layout title="Pagamentos" subtitle="Planos, formas de pagamento e inadimplência">
    <x-slot name="actions">
        @can('create', \App\Models\Pagamento::class)
            <a href="{{ route('pagamentos.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium">
                + Novo pagamento
            </a>
        @endcan
    </x-slot>

    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500">Total de pagamentos</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $resumo['total'] }}</p>
        </div>
        <a href="{{ route('pagamentos.index', ['status' => 'Devendo', 'ativo' => 1]) }}"
           class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 transition">
            <p class="text-sm text-gray-500">Inadimplentes (ativos e devendo)</p>
            <p class="text-3xl font-bold text-red-600 mt-1">{{ $resumo['inadimplentes'] }}</p>
        </a>
    </div>

    <form method="GET" action="{{ route('pagamentos.index') }}" class="mb-6 grid grid-cols-1 sm:grid-cols-5 gap-3">
        <input type="search" name="busca" value="{{ $filtros['busca'] ?? '' }}" placeholder="Cliente (nome ou CPF)"
               class="sm:col-span-2 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

        <select name="status" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Toda situação</option>
            @foreach (\App\Models\Pagamento::STATUS as $v)
                <option value="{{ $v }}" @selected(($filtros['status'] ?? '') === $v)>{{ $v }}</option>
            @endforeach
        </select>

        <select name="ativo" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Ativo e inativo</option>
            <option value="1" @selected(($filtros['ativo'] ?? '') === '1')>Somente ativos</option>
            <option value="0" @selected(($filtros['ativo'] ?? '') === '0')>Somente inativos</option>
        </select>

        <div class="flex gap-3">
            <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">Filtrar</button>
            @if (collect($filtros)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
                <a href="{{ route('pagamentos.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Limpar</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($pagamentos->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <x-crud.th>Cliente</x-crud.th>
                            <x-crud.th>Plano</x-crud.th>
                            <x-crud.th>Forma</x-crud.th>
                            <x-crud.th>Dia</x-crud.th>
                            <x-crud.th>Cadastro</x-crud.th>
                            <x-crud.th>Situação</x-crud.th>
                            <x-crud.th :right="true">Ações</x-crud.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($pagamentos as $p)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('clientes.show', $p->cliente_id) }}" class="font-semibold text-gray-800 hover:text-blue-700">{{ $p->cliente->nome }}</a>
                                    <p class="text-xs text-gray-500">{{ $p->cliente->cpf }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $p->plano }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $p->forma_pagamento === 'Credito' ? 'Crédito' : 'Débito' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $p->dia_pagamento }}</td>
                                <td class="px-6 py-4"><x-crud.badge :color="$p->ativo ? 'green' : 'gray'">{{ $p->ativo ? 'Ativo' : 'Inativo' }}</x-crud.badge></td>
                                <td class="px-6 py-4"><x-crud.badge :color="$p->status_pagamento === 'Pago' ? 'green' : 'red'">{{ $p->status_pagamento }}</x-crud.badge></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('pagamentos.show', $p) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Ver</a>
                                        @can('update', $p)
                                            <a href="{{ route('pagamentos.edit', $p) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Editar</a>
                                        @endcan
                                        @can('delete', $p)
                                            <x-crud.delete :action="route('pagamentos.destroy', $p)" confirm="Excluir este pagamento?" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($pagamentos->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">{{ $pagamentos->links() }}</div>
            @endif
        @else
            <x-crud.vazio icone="💳" titulo="Nenhum pagamento encontrado" texto="Ajuste os filtros ou cadastre um novo pagamento." />
        @endif
    </div>
</x-crud.layout>
