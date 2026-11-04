<?php

namespace App\Services;

use App\Models\Matricula;
use App\Services\Concerns\BuscaTexto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Regras de matrícula, compartilhadas pelo CRUD web e pela API.
 * Espelham a procedure SQL sp_registrar_matricula().
 */
class MatriculaService
{
    use BuscaTexto;

    /** @param array{busca?: ?string, modalidade?: ?string, faixa?: ?string, cliente_id?: int|string|null} $filtros */
    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Matricula::query()->with('cliente:id,nome,cpf');

        $this->doCliente($query, $filtros['busca'] ?? null);

        return $query
            ->when(filled($filtros['modalidade'] ?? null), fn (Builder $q) => $q->where('modalidade', $filtros['modalidade']))
            ->when(filled($filtros['faixa'] ?? null), fn (Builder $q) => $q->where('faixa', $filtros['faixa']))
            ->when(filled($filtros['cliente_id'] ?? null), fn (Builder $q) => $q->where('cliente_id', $filtros['cliente_id']))
            ->orderByDesc('data_inicio')
            ->orderByDesc('id')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public function detalhar(Matricula $matricula): Matricula
    {
        return $matricula->load('cliente');
    }

    public function criar(array $dados): Matricula
    {
        return Matricula::create($this->normalizar($dados));
    }

    public function atualizar(Matricula $matricula, array $dados): Matricula
    {
        $matricula->update($this->normalizar($dados));

        return $matricula;
    }

    public function excluir(Matricula $matricula): void
    {
        $matricula->delete();
    }

    /** Boxe, Muay Thai e Personal não têm graduação: a faixa é sempre "N/A". */
    private function normalizar(array $dados): array
    {
        if (isset($dados['modalidade']) && ! Matricula::usaFaixa($dados['modalidade'])) {
            $dados['faixa'] = Matricula::FAIXA_NAO_SE_APLICA;
        }

        return $dados;
    }
}
