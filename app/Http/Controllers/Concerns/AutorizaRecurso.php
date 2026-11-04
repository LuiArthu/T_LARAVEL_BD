<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Routing\Controllers\Middleware;

/**
 * Liga as policies às ações de um resource controller via middleware "can:".
 * Roda antes da validação, então quem não pode agir recebe 403 (e não 422).
 * (Em Laravel 11+ o Controller base não tem mais authorizeResource().)
 */
trait AutorizaRecurso
{
    /** @return array<int, Middleware> */
    protected static function autorizacoes(string $modelo, string $parametro): array
    {
        return [
            new Middleware("can:viewAny,{$modelo}", only: ['index']),
            new Middleware("can:create,{$modelo}", only: ['create', 'store']),
            new Middleware("can:view,{$parametro}", only: ['show']),
            new Middleware("can:update,{$parametro}", only: ['edit', 'update']),
            new Middleware("can:delete,{$parametro}", only: ['destroy']),
        ];
    }
}
