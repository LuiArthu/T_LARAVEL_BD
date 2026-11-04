<x-crud.layout title="Editar cliente" :subtitle="$cliente->nome" width="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        @include('clientes._form', ['action' => route('clientes.update', $cliente)])
    </div>
</x-crud.layout>
