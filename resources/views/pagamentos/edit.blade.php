<x-crud.layout title="Editar pagamento" :subtitle="'Pagamento #'.$pagamento->id" width="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        @include('pagamentos._form', ['action' => route('pagamentos.update', $pagamento)])
    </div>
</x-crud.layout>
