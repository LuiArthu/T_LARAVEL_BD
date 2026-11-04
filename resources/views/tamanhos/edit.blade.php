<x-crud.layout title="Editar tamanhos" :subtitle="'Registro #'.$tamanho->id" width="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        @include('tamanhos._form', ['action' => route('tamanhos.update', $tamanho)])
    </div>
</x-crud.layout>
