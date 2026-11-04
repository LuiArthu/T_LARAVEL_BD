<x-crud.layout title="Clientes" subtitle="Cadastro geral de clientes da academia">
    <x-slot name="actions">
        @can('create', \App\Models\Cliente::class)
            <a href="{{ route('clientes.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium">
                + Novo cliente
            </a>
        @endcan
    </x-slot>

    <form method="GET" action="{{ route('clientes.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
        <input type="search" name="busca" value="{{ $filtros['busca'] ?? '' }}" placeholder="Buscar por nome ou CPF"
               class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">Buscar</button>
        @if (filled($filtros['busca'] ?? null))
            <a href="{{ route('clientes.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-center">Limpar</a>
        @endif
    </form>

    <div class="mb-6 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm text-gray-500">{{ filled($filtros['busca'] ?? null) ? 'Clientes encontrados' : 'Total de clientes' }}</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $clientes->total() }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($clientes->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <x-crud.th>Cliente</x-crud.th>
                            <x-crud.th>CPF</x-crud.th>
                            <x-crud.th>Idade</x-crud.th>
                            <x-crud.th>Matrículas</x-crud.th>
                            <x-crud.th :right="true">Ações</x-crud.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($clientes as $cliente)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center font-bold">
                                            {{ mb_strtoupper(mb_substr($cliente->nome, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ $cliente->nome }}</p>
                                            <p class="text-xs text-gray-500">Cliente #{{ $cliente->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $cliente->cpf }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $cliente->idade }} anos</td>
                                <td class="px-6 py-4">
                                    <x-crud.badge :color="$cliente->matriculas_count ? 'blue' : 'gray'">{{ $cliente->matriculas_count }}</x-crud.badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('clientes.show', $cliente) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Ver</a>
                                        @can('update', $cliente)
                                            <a href="{{ route('clientes.edit', $cliente) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Editar</a>
                                        @endcan
                                        @can('delete', $cliente)
                                            <x-crud.delete :action="route('clientes.destroy', $cliente)" confirm="Excluir este cliente?" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($clientes->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">{{ $clientes->links() }}</div>
            @endif
        @else
            <x-crud.vazio icone="👥" titulo="Nenhum cliente encontrado"
                          :texto="filled($filtros['busca'] ?? null) ? 'Tente outro termo de busca.' : 'Comece cadastrando o primeiro cliente.'" />
        @endif
    </div>
</x-crud.layout>
