<x-crud.layout title="Matrículas" subtitle="Cliente, modalidade e graduação">
    <x-slot name="actions">
        @can('create', \App\Models\Matricula::class)
            <a href="{{ route('matriculas.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium">
                + Nova matrícula
            </a>
        @endcan
    </x-slot>

    <form method="GET" action="{{ route('matriculas.index') }}" class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <input type="search" name="busca" value="{{ $filtros['busca'] ?? '' }}" placeholder="Cliente (nome ou CPF)"
               class="sm:col-span-2 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

        <select name="modalidade" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Todas as modalidades</option>
            @foreach (\App\Models\Matricula::MODALIDADES as $mod)
                <option value="{{ $mod }}" @selected(($filtros['modalidade'] ?? '') === $mod)>{{ $mod }}</option>
            @endforeach
        </select>

        <div class="flex gap-3">
            <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">Filtrar</button>
            @if (collect($filtros)->filter()->isNotEmpty())
                <a href="{{ route('matriculas.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Limpar</a>
            @endif
        </div>
    </form>

    <div class="mb-6 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm text-gray-500">{{ collect($filtros)->filter()->isNotEmpty() ? 'Matrículas encontradas' : 'Total de matrículas' }}</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $matriculas->total() }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($matriculas->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <x-crud.th>Cliente</x-crud.th>
                            <x-crud.th>Modalidade</x-crud.th>
                            <x-crud.th>Faixa</x-crud.th>
                            <x-crud.th>Início</x-crud.th>
                            <x-crud.th :right="true">Ações</x-crud.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($matriculas as $m)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('clientes.show', $m->cliente_id) }}" class="font-semibold text-gray-800 hover:text-blue-700">{{ $m->cliente->nome }}</a>
                                    <p class="text-xs text-gray-500">{{ $m->cliente->cpf }}</p>
                                </td>
                                <td class="px-6 py-4"><x-crud.badge>{{ $m->modalidade }}</x-crud.badge></td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $m->faixa }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $m->data_inicio->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('matriculas.show', $m) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Ver</a>
                                        @can('update', $m)
                                            <a href="{{ route('matriculas.edit', $m) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Editar</a>
                                        @endcan
                                        @can('delete', $m)
                                            <x-crud.delete :action="route('matriculas.destroy', $m)" confirm="Excluir esta matrícula?" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($matriculas->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">{{ $matriculas->links() }}</div>
            @endif
        @else
            <x-crud.vazio icone="🥋" titulo="Nenhuma matrícula encontrada" texto="Ajuste os filtros ou cadastre uma nova matrícula." />
        @endif
    </div>
</x-crud.layout>
