<x-crud.layout title="Editar matrícula" :subtitle="'Matrícula #'.$matricula->id" width="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        @include('matriculas._form', ['action' => route('matriculas.update', $matricula)])
    </div>
</x-crud.layout>
