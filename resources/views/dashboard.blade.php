<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl font-bold text-gray-800">
                Dashboard
            </h2>
            <p class="text-sm text-gray-500">
                Visão geral da Academia de Lutas
            </p>
        </div>
    </x-slot>

```
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Saudação --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Olá, {{ Auth::user()->name }}! 👋
            </h1>

            <p class="mt-2 text-gray-600">
                Bem-vindo ao sistema de gerenciamento da Academia de Lutas.
            </p>
        </div>

        {{-- Cards principais --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            @if ($equipe)
            {{-- Clientes (só equipe) --}}
            <a href="{{ route('clientes.index') }}"
               class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Clientes
                        </p>

                        <p class="text-3xl font-bold text-gray-800 mt-2">
                            {{ $totalClientes }}
                        </p>
                    </div>

                    <div class="bg-blue-100 text-blue-600 rounded-full p-4">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-7 h-7"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 0a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                    </div>
                </div>

                <p class="text-sm text-blue-600 mt-4 font-medium">
                    Ver clientes →
                </p>
            </a>
            @endif

            {{-- Modalidades --}}
            <a href="{{ route('modalidades.index') }}"
               class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Modalidades
                        </p>

                        <p class="text-3xl font-bold text-gray-800 mt-2">
                            {{ $totalModalidades }}
                        </p>
                    </div>

                    <div class="bg-purple-100 text-purple-600 rounded-full p-4">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-7 h-7"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M14.5 3.5L20.5 9.5M8 7l9 9M5 21l4.5-4.5M19 5l-3 3M12 12l-4 4"/>
                        </svg>
                    </div>
                </div>

                <p class="text-sm text-purple-600 mt-4 font-medium">
                    Ver modalidades →
                </p>
            </a>

            {{-- Perfil --}}
            <a href="{{ route('profile.edit') }}"
               class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Meu perfil
                        </p>

                        <p class="text-lg font-bold text-gray-800 mt-2">
                            {{ Auth::user()->name }}
                        </p>

                        <p class="text-sm text-gray-500 mt-1">
                            {{ ucfirst(Auth::user()->role) }}
                        </p>
                    </div>

                    <div class="bg-green-100 text-green-600 rounded-full p-4">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-7 h-7"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>

                <p class="text-sm text-green-600 mt-4 font-medium">
                    Acessar perfil →
                </p>
            </a>
        </div>

        @if ($equipe)
        {{-- Clientes recentes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Clientes cadastrados
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Últimos clientes adicionados ao sistema
                    </p>
                </div>

                <a href="{{ route('clientes.index') }}"
                   class="text-sm font-medium text-blue-600 hover:text-blue-800">
                    Ver todos
                </a>
            </div>

            @if ($clientesRecentes->count())
                <div class="divide-y divide-gray-100">
                    @foreach ($clientesRecentes as $cliente)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">

                            <div class="flex items-center gap-4">

                                <div class="w-11 h-11 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold">
                                    {{ strtoupper(substr($cliente->nome, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="font-semibold text-gray-800">
                                        {{ $cliente->nome }}
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        {{ $cliente->matriculas->pluck('modalidade')->implode(', ') ?: 'Sem matrícula' }}
                                    </p>
                                </div>

                            </div>

                            <a href="{{ route('clientes.show', $cliente) }}"
                               class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Detalhes →
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-10 text-center">
                    <p class="text-gray-500">
                        Nenhum cliente cadastrado ainda.
                    </p>

                    @can('create', \App\Models\Cliente::class)
                        <a href="{{ route('clientes.create') }}"
                           class="inline-block mt-4 text-blue-600 font-medium">
                            Cadastrar primeiro cliente →
                        </a>
                    @endcan
                </div>
            @endif

        </div>
        @endif

    </div>
</div>
```

</x-app-layout>
