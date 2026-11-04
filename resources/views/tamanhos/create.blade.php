<x-crud.layout title="Novos tamanhos" subtitle="Tamanho de faixa e kimono do cliente" width="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        @include('tamanhos._form', ['action' => route('tamanhos.store')])
    </div>
</x-crud.layout>
