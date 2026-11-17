<x-crud.layout title="Banco de dados" subtitle="Functions, procedure e trigger do PostgreSQL usados pelo sistema">

    @unless ($disponivel)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg">
            Estes recursos só existem no PostgreSQL. Configure o <code>.env</code> com o banco do Neon
            e rode os scripts de <code>database/sql/</code>.
        </div>
    @else

        {{-- Filtros usados pelas functions --}}
        <form method="GET" action="{{ route('banco.index') }}" class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <select name="modalidade" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todas as modalidades</option>
                @foreach (\App\Models\Matricula::MODALIDADES as $m)
                    <option value="{{ $m }}" @selected($modalidade === $m)>{{ $m }}</option>
                @endforeach
            </select>

            <select name="plano" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                @foreach (\App\Models\Pagamento::PLANOS as $p)
                    <option value="{{ $p }}" @selected($plano === $p)>Plano {{ $p }}</option>
                @endforeach
            </select>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                Mensalidade R$
                <input type="number" name="valor" value="{{ $valor }}" min="1" max="10000"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </label>

            <button class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 font-medium">Calcular</button>
        </form>

        {{-- Resultados das functions --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <p class="text-sm text-gray-500">Mensalidades pagas · {{ $modalidade ?? 'Jiu-Jitsu' }} · {{ $plano }}</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $mensalidadesPagas }}</p>
                <code class="block mt-3 text-xs text-gray-400">SELECT calculo_total_mensalidade('{{ $modalidade ?? 'Jiu-Jitsu' }}', '{{ $plano }}')</code>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <p class="text-sm text-gray-500">Faixas pretas com 10% de desconto (R$ {{ $valor }})</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">R$ {{ number_format($faixasPretas, 0, ',', '.') }}</p>
                <code class="block mt-3 text-xs text-gray-400">SELECT desconto_faixas_pretas({{ $valor }})</code>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <p class="text-sm text-gray-500">Inadimplentes · {{ $modalidade ?? 'todas as modalidades' }}</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $inadimplentes->count() }}</p>
                <code class="block mt-3 text-xs text-gray-400">SELECT * FROM fn_inadimplentes({{ $modalidade ? "'$modalidade'" : 'NULL' }})</code>
            </div>
        </div>

        {{-- Procedure (somente admin) --}}
        @if (auth()->user()->isAdmin())
            <div class="mt-6 bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-800">Quitar pagamentos de um plano</h3>
                    <p class="text-sm text-gray-500">Marca como <strong>Pago</strong> todos os pagamentos <strong>ativos</strong> que estão devendo no plano escolhido.</p>
                    <code class="block mt-2 text-xs text-gray-400">CALL atualizar_pagamento('Devendo', plano, 'Pago')</code>
                </div>
                <form method="POST" action="{{ route('banco.quitar-plano') }}" class="flex gap-3"
                      onsubmit="return confirm('Marcar como Pago todos os pagamentos ativos devendo deste plano?')">
                    @csrf
                    <select name="plano" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        @foreach (\App\Models\Pagamento::PLANOS as $p)
                            <option value="{{ $p }}" @selected($p === 'Anual')>{{ $p }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-medium whitespace-nowrap">Executar procedure</button>
                </form>
            </div>
        @endif

        {{-- Resultado da function fn_inadimplentes --}}
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Inadimplentes</h3>
                <p class="text-xs text-gray-400">function <code>fn_inadimplentes</code>: pagamento ativo com status Devendo</p>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left sticky top-0">
                        <tr>
                            <th class="px-6 py-2">Cliente</th>
                            <th class="px-6 py-2">CPF</th>
                            <th class="px-6 py-2">Modalidade</th>
                            <th class="px-6 py-2">Plano</th>
                            <th class="px-6 py-2">Dia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inadimplentes as $i)
                            <tr class="border-t border-gray-50 hover:bg-gray-50">
                                <td class="px-6 py-2"><a href="{{ route('clientes.show', $i->cliente_id) }}" class="text-blue-600 hover:underline">{{ $i->nome }}</a></td>
                                <td class="px-6 py-2 text-gray-600">{{ $i->cpf }}</td>
                                <td class="px-6 py-2">{{ $i->modalidade }}</td>
                                <td class="px-6 py-2">{{ $i->plano }}</td>
                                <td class="px-6 py-2">{{ $i->dia_pagamento }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-4 text-gray-500">Nenhum inadimplente.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabela alimentada pelo trigger trg_historico_faixa --}}
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Histórico de faixas (últimas 20)</h3>
                <p class="text-xs text-gray-400">gravado pelo trigger <code>trg_historico_faixa</code> sempre que a faixa de uma matrícula muda</p>
            </div>
            @include('banco._historico', ['historico' => $historico, 'mostrarCliente' => true])
        </div>

    @endunless
</x-crud.layout>
