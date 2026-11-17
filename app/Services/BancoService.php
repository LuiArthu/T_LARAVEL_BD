<?php

namespace App\Services;

use App\Models\Pagamento;
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
 *   CALL atualizar_pagamento(...)                -> quitarPlano()
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
     * ATIVOS e 'Devendo' do plano. Devolve quantos mudaram.
     */
    public function quitarPlano(string $plano): int
    {
        $pendentes = fn () => Pagamento::where('plano', $plano)
            ->where('ativo', true)
            ->where('status_pagamento', 'Devendo')
            ->count();

        $antes = $pendentes();
        $this->pagamentos->atualizarStatus('Devendo', $plano, 'Pago');

        return $antes - $pendentes();
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
