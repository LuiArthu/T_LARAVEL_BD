<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BuscaTexto
{
    /**
     * Filtro "contém", sem diferenciar maiúsculas, com %, _ e ! tratados como texto.
     * O escape é "!" (e não "\"): o PDO do PostgreSQL confunde '\' com aspas
     * escapadas e perde o parâmetro seguinte.
     */
    protected function contem(Builder $query, string $coluna, ?string $termo): Builder
    {
        $termo = trim((string) $termo);

        if ($termo === '') {
            return $query;
        }

        $termo = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($termo));

        return $query->whereRaw("LOWER({$coluna}) LIKE ? ESCAPE '!'", ["%{$termo}%"]);
    }

    /** Busca pelo nome ou CPF do cliente relacionado. */
    protected function doCliente(Builder $query, ?string $termo): Builder
    {
        if (trim((string) $termo) === '') {
            return $query;
        }

        return $query->whereHas('cliente', function (Builder $cliente) use ($termo) {
            $cliente->where(function (Builder $q) use ($termo) {
                $this->contem($q, 'nome', $termo);
                $q->orWhere(fn (Builder $c) => $this->contem($c, 'cpf', $termo));
            });
        });
    }
}
