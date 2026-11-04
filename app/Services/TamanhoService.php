<?php

namespace App\Services;

use App\Models\Tamanho;
use App\Services\Concerns\BuscaTexto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TamanhoService
{
    use BuscaTexto;

    /** @param array{busca?: ?string} $filtros */
    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Tamanho::query()->with('cliente:id,nome,cpf');

        return $this->doCliente($query, $filtros['busca'] ?? null)
            ->orderByDesc('id')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public function detalhar(Tamanho $tamanho): Tamanho
    {
        return $tamanho->load('cliente');
    }

    public function criar(array $dados): Tamanho
    {
        return Tamanho::create($dados);
    }

    public function atualizar(Tamanho $tamanho, array $dados): Tamanho
    {
        $tamanho->update($dados);

        return $tamanho;
    }

    public function excluir(Tamanho $tamanho): void
    {
        $tamanho->delete();
    }
}
