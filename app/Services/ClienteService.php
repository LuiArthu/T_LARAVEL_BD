<?php

namespace App\Services;

use App\Models\Cliente;
use App\Services\Concerns\BuscaTexto;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ClienteService
{
    use BuscaTexto;

    /** @param array{busca?: ?string} $filtros */
    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $busca = $filtros['busca'] ?? null;

        return Cliente::query()
            ->withCount(['matriculas', 'tamanhos', 'pagamentos'])
            ->when(filled($busca), fn (Builder $q) => $q->where(function (Builder $q) use ($busca) {
                $this->contem($q, 'nome', $busca);
                $q->orWhere(fn (Builder $c) => $this->contem($c, 'cpf', $busca));
            }))
            ->orderBy('nome')
            ->paginate($porPagina)
            ->withQueryString();
    }

    /** Lista enxuta para os <select> de outros formulários. */
    public function opcoes(): Collection
    {
        return Cliente::query()->orderBy('nome')->get(['id', 'nome', 'cpf']);
    }

    public function detalhar(Cliente $cliente): Cliente
    {
        return $cliente->load([
            'matriculas' => fn ($q) => $q->orderBy('data_inicio', 'desc'),
            'tamanhos',
            'pagamentos',
        ]);
    }

    public function criar(array $dados): Cliente
    {
        return Cliente::create($dados);
    }

    public function atualizar(Cliente $cliente, array $dados): Cliente
    {
        $cliente->update($dados);

        return $cliente;
    }

    /**
     * As chaves estrangeiras não têm ON DELETE CASCADE, então um cliente com
     * registros dependentes não pode ser removido sem apagar o histórico.
     * Em vez de deixar o banco estourar uma exceção, avisamos com clareza.
     *
     * @throws DomainException
     */
    public function excluir(Cliente $cliente): void
    {
        $dependentes = collect([
            'matrículas' => $cliente->matriculas()->count(),
            'tamanhos' => $cliente->tamanhos()->count(),
            'pagamentos' => $cliente->pagamentos()->count(),
        ])->filter();

        if ($dependentes->isNotEmpty()) {
            $lista = $dependentes->map(fn ($qtd, $nome) => "{$qtd} {$nome}")->implode(', ');

            throw new DomainException("Não é possível excluir {$cliente->nome}: há registros vinculados ({$lista}). Remova-os primeiro.");
        }

        $cliente->delete();
    }
}
