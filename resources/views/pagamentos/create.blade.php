<x-crud.layout title="Novo pagamento" subtitle="Plano, forma e situação do pagamento" width="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        @include('pagamentos._form', ['action' => route('pagamentos.store')])
    </div>
</x-crud.layout>
