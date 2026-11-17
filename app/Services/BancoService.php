<?php

namespace App\Services;

use App\Models\Pagamento;
use App\Models\Quitacao;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ponte entre o Laravel e os objetos programados no PostgreSQL
 * (database/sql/academia_completo.sql e database/sql/trabalho_final.sql).
 *
 * Aqui o app NÃO reescreve as regras em PHP: chama as functions, a
 * procedure e lê a tabela alimentada pelo trigger, direto no banco.
 *
 *   fn_inadimplentes(modalidade)                 -> inadimplentes()
 *   calculo_total_mensalidade(modalidade, plano) -> totalMensalidadesPagas()
 *   desconto_faixas_pretas(valor)                -> totalFaixasPretasComDesconto()
 *   CALL atualizar_pagamento(...)                -> quitarPlano()  (desfazível: desfazerQuitacao())
 *   trigger trg_historico_faixa -> historico_faixa -> historicoFaixas()
 *
 * Esses objetos só existem no PostgreSQL. Nos testes (SQLite em memória)
 * disponivel() devolve false e as telas mostram um aviso em vez dos dados.
 */
class BancoService
{
    public function __construct(private readonly PagamentoService $pagamentos) {}

    public function disponivel(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    /** Function fn_inadimplentes: clientes ativos e devendo (opcionalmente de uma modalidade). */
    public function inadimplentes(?string $modalidade = null): Collection
    {
        return collect(DB::select('SELECT * FROM fn_inadimplentes(?)', [$modalidade ?: null]));
    }

    /** Function calculo_total_mensalidade: quantas mensalidades pagas há na modalidade/plano. */
    public function totalMensalidadesPagas(string $modalidade, string $plano): int
    {
        return (int) DB::scalar('SELECT calculo_total_mensalidade(?, ?)', [$modalidade, $plano]);
    }

    /** Function desconto_faixas_pretas: total a receber dos faixas pretas com 10% de desconto. */
    public function totalFaixasPretasComDesconto(int $valorMensalidade): int
    {
        return (int) DB::scalar('SELECT desconto_faixas_pretas(?)', [$valorMensalidade]);
    }

    /**
     * Procedure atualizar_pagamento: marca como 'Pago' todos os pagamentos
     * ATIVOS e 'Devendo' do plano.
     *
     * Antes de chamar a procedure, anota QUAIS pagamentos estão pendentes
     * (com lock, para ninguém alterá-los no meio do caminho) e guarda essa
     * lista em uma Quitacao. É ela que permite desfazer depois.
     * Devolve null quando não havia nada a quitar.
     */
    public function quitarPlano(string $plano): ?Quitacao
    {
        return DB::transaction(function () use ($plano) {
            $pendentes = Pagamento::where('plano', $plano)
                ->where('ativo', true)
                ->where('status_pagamento', 'Devendo')
                ->lockForUpdate()
                ->pluck('id');

            if ($pendentes->isEmpty()) {
                return null;
            }

            $this->pagamentos->atualizarStatus('Devendo', $plano, 'Pago');

            $quitados = Pagamento::whereIn('id', $pendentes)
                ->where('status_pagamento', 'Pago')
                ->pluck('id');

            $quitacao = Quitacao::create([
                'plano' => $plano,
                'quantidade' => $quitados->count(),
                'user_id' => auth()->id(),
            ]);
            $quitacao->pagamentos()->attach($quitados);

            return $quitacao;
        });
    }

    /**
     * Desfaz uma quitação: volta para 'Devendo' SOMENTE os pagamentos que ela
     * marcou como 'Pago' (quem já estava pago antes continua pago).
     * Devolve quantos pagamentos voltaram.
     *
     * @throws DomainException se a quitação já foi desfeita
     */
    public function desfazerQuitacao(Quitacao $quitacao): int
    {
        if ($quitacao->foiDesfeita()) {
            throw new DomainException("A quitação #{$quitacao->id} já foi desfeita.");
        }

        return DB::transaction(function () use ($quitacao) {
            $voltaram = Pagamento::whereIn('id', $quitacao->pagamentos()->pluck('pagamento.id'))
                ->where('status_pagamento', 'Pago')
                ->update(['status_pagamento' => 'Devendo']);

            $quitacao->update(['desfeita_em' => now()]);

            return $voltaram;
        });
    }

    /** Últimas quitações feitas pela tela, com quem executou. */
    public function ultimasQuitacoes(int $limite = 10): Collection
    {
        return Quitacao::with('usuario:id,name')->latest('id')->limit($limite)->get();
    }

    /** Tabela historico_faixa, preenchida pelo trigger trg_historico_faixa. */
    public function historicoFaixas(?int $clienteId = null, int $limite = 20): Collection
    {
        return DB::table('historico_faixa')
            ->leftJoin('clientes', 'clientes.id', '=', 'historico_faixa.cliente_id')
            ->when($clienteId, fn ($q) => $q->where('historico_faixa.cliente_id', $clienteId))
            ->orderByDesc('historico_faixa.data_alteracao')
            ->orderByDesc('historico_faixa.id_historico')
            ->limit($limite)
            ->get(['historico_faixa.*', 'clientes.nome']);
    }
}
