<x-crud.layout title="Novo cliente" subtitle="Cadastre um novo cliente na academia" width="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        @include('clientes._form', ['action' => route('clientes.store')])
    </div>
</x-crud.layout>
