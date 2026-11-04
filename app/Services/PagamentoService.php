<?php

namespace App\Services;

use App\Models\Pagamento;
use App\Services\Concerns\BuscaTexto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PagamentoService
{
    use BuscaTexto;

    /** @param array{busca?: ?string, status?: ?string, plano?: ?string, ativo?: ?string} $filtros */
    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Pagamento::query()->with('cliente:id,nome,cpf');

        $this->doCliente($query, $filtros['busca'] ?? null);

        return $query
            ->when(filled($filtros['status'] ?? null), fn (Builder $q) => $q->where('status_pagamento', $filtros['status']))
            ->when(filled($filtros['plano'] ?? null), fn (Builder $q) => $q->where('plano', $filtros['plano']))
            ->when(in_array($filtros['ativo'] ?? null, ['0', '1'], true), fn (Builder $q) => $q->where('ativo', $filtros['ativo'] === '1'))
            ->orderByDesc('id')
            ->paginate($porPagina)
            ->withQueryString();
    }

    /** Números do topo da listagem. "Inadimplentes" = ativos e devendo (como fn_inadimplentes). */
    public function resumo(): array
    {
        return [
            'total' => Pagamento::count(),
            'inadimplentes' => Pagamento::inadimplentes()->count(),
        ];
    }

    public function detalhar(Pagamento $pagamento): Pagamento
    {
        return $pagamento->load('cliente');
    }

    public function criar(array $dados): Pagamento
    {
        return Pagamento::create($dados);
    }

    public function atualizar(Pagamento $pagamento, array $dados): Pagamento
    {
        $pagamento->update($dados);

        return $pagamento;
    }

    public function excluir(Pagamento $pagamento): void
    {
        $pagamento->delete();
    }

    /**
     * Troca o status de todos os pagamentos ATIVOS de um plano, de uma vez,
     * chamando a procedure atualizar_pagamento do PostgreSQL.
     *
     * A procedure só existe se o banco foi criado pelo script
     * database/sql/academia_completo.sql (as migrations não a criam).
     */
    public function atualizarStatus(
        string $statusAtual = 'Devendo',
        string $plano = 'Anual',
        string $novoStatus = 'Pago',
    ): void {
        if (! in_array($statusAtual, Pagamento::STATUS, true)) {
            throw new InvalidArgumentException('Status atual inválido.');
        }

        if (! in_array($novoStatus, Pagamento::STATUS, true)) {
            throw new InvalidArgumentException('Novo status inválido.');
        }

        if (! in_array($plano, Pagamento::PLANOS, true)) {
            throw new InvalidArgumentException('Plano inválido.');
        }

        DB::statement('CALL atualizar_pagamento(?, ?, ?)', [$statusAtual, $plano, $novoStatus]);
    }
}
