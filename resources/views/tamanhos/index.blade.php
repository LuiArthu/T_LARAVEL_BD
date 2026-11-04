<x-crud.layout title="Tamanhos" subtitle="Faixa e kimono de cada cliente">
    <x-slot name="actions">
        @can('create', \App\Models\Tamanho::class)
            <a href="{{ route('tamanhos.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium">
                + Novos tamanhos
            </a>
        @endcan
    </x-slot>

    <form method="GET" action="{{ route('tamanhos.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
        <input type="search" name="busca" value="{{ $filtros['busca'] ?? '' }}" placeholder="Cliente (nome ou CPF)"
               class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">Buscar</button>
        @if (filled($filtros['busca'] ?? null))
            <a href="{{ route('tamanhos.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-center">Limpar</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($tamanhos->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <x-crud.th>Cliente</x-crud.th>
                            <x-crud.th>Faixa</x-crud.th>
                            <x-crud.th>Kimono</x-crud.th>
                            <x-crud.th :right="true">Ações</x-crud.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($tamanhos as $t)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('clientes.show', $t->cliente_id) }}" class="font-semibold text-gray-800 hover:text-blue-700">{{ $t->cliente->nome }}</a>
                                    <p class="text-xs text-gray-500">{{ $t->cliente->cpf }}</p>
                                </td>
                                <td class="px-6 py-4"><x-crud.badge color="gray">{{ $t->tamanho_faixa }}</x-crud.badge></td>
                                <td class="px-6 py-4"><x-crud.badge color="gray">{{ $t->tamanho_kimono }}</x-crud.badge></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('tamanhos.show', $t) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Ver</a>
                                        @can('update', $t)
                                            <a href="{{ route('tamanhos.edit', $t) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Editar</a>
                                        @endcan
                                        @can('delete', $t)
                                            <x-crud.delete :action="route('tamanhos.destroy', $t)" confirm="Excluir este registro?" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($tamanhos->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">{{ $tamanhos->links() }}</div>
            @endif
        @else
            <x-crud.vazio icone="📏" titulo="Nenhum registro encontrado" />
        @endif
    </div>
</x-crud.layout>
