<x-crud.layout title="Nova matrícula" subtitle="Matricule um cliente em uma modalidade" width="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        @include('matriculas._form', ['action' => route('matriculas.store')])
    </div>
</x-crud.layout>
