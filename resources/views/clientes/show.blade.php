<x-crud.layout title="Detalhes do cliente" subtitle="Cadastro, matrículas, tamanhos e pagamento">
    <x-slot name="actions">
        <a href="{{ route('clientes.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Voltar</a>
        @can('update', $cliente)
            <a href="{{ route('clientes.edit', $cliente) }}" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">Editar</a>
        @endcan
        @can('delete', $cliente)
            <x-crud.delete :action="route('clientes.destroy', $cliente)" confirm="Excluir este cliente?" :button="true" />
        @endcan
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gray-900 px-6 py-8 text-white flex items-center gap-5">
            <div class="w-16 h-16 rounded-full bg-white text-gray-900 flex items-center justify-center text-2xl font-bold">
                {{ mb_strtoupper(mb_substr($cliente->nome, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $cliente->nome }}</h1>
                <p class="text-gray-300 mt-1">Cliente #{{ $cliente->id }}</p>
            </div>
        </div>

        <div class="p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
            <x-crud.dado label="CPF">{{ $cliente->cpf }}</x-crud.dado>
            <x-crud.dado label="Idade">{{ $cliente->idade }} anos</x-crud.dado>
            <x-crud.dado label="Endereço">{{ $cliente->endereco }}</x-crud.dado>
        </div>
    </div>

    {{-- Matrículas --}}
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Matrículas</h3>
            @can('create', \App\Models\Matricula::class)
                <a href="{{ route('matriculas.create', ['cliente_id' => $cliente->id]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">+ Matrícula</a>
            @endcan
        </div>
        @forelse ($cliente->matriculas as $m)
            <a href="{{ route('matriculas.show', $m) }}" class="flex items-center justify-between px-6 py-3 border-b border-gray-50 hover:bg-gray-50">
                <span class="font-medium text-gray-800">{{ $m->modalidade }}</span>
                <span class="text-sm text-gray-500">Faixa {{ $m->faixa }} · desde {{ $m->data_inicio->format('d/m/Y') }}</span>
            </a>
        @empty
            <p class="px-6 py-4 text-sm text-gray-500">Nenhuma matrícula.</p>
        @endforelse
    </div>

    {{-- Tamanhos --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Tamanhos</h3>
            @can('create', \App\Models\Tamanho::class)
                <a href="{{ route('tamanhos.create', ['cliente_id' => $cliente->id]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">+ Tamanhos</a>
            @endcan
        </div>
        @forelse ($cliente->tamanhos as $t)
            <a href="{{ route('tamanhos.show', $t) }}" class="flex items-center justify-between px-6 py-3 border-b border-gray-50 hover:bg-gray-50">
                <span class="text-gray-800">Faixa <strong>{{ $t->tamanho_faixa }}</strong></span>
                <span class="text-gray-800">Kimono <strong>{{ $t->tamanho_kimono }}</strong></span>
            </a>
        @empty
            <p class="px-6 py-4 text-sm text-gray-500">Nenhum tamanho registrado.</p>
        @endforelse
    </div>

    {{-- Pagamento --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Pagamento</h3>
            @can('create', \App\Models\Pagamento::class)
                <a href="{{ route('pagamentos.create', ['cliente_id' => $cliente->id]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">+ Pagamento</a>
            @endcan
        </div>
        @forelse ($cliente->pagamentos as $p)
            <a href="{{ route('pagamentos.show', $p) }}" class="flex items-center justify-between px-6 py-3 border-b border-gray-50 hover:bg-gray-50">
                <span class="text-gray-800">{{ $p->plano }} · {{ $p->forma_pagamento }} · dia {{ $p->dia_pagamento }}</span>
                <span class="flex gap-2">
                    <x-crud.badge :color="$p->ativo ? 'green' : 'gray'">{{ $p->ativo ? 'Ativo' : 'Inativo' }}</x-crud.badge>
                    <x-crud.badge :color="$p->status_pagamento === 'Pago' ? 'green' : 'red'">{{ $p->status_pagamento }}</x-crud.badge>
                </span>
            </a>
        @empty
            <p class="px-6 py-4 text-sm text-gray-500">Nenhum pagamento cadastrado.</p>
        @endforelse
    </div>
</x-crud.layout>
