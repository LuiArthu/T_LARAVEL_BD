<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Regras comuns às entidades de gestão (clientes, matrículas, tamanhos, pagamento).
 *
 * Aqui até a LEITURA é restrita à equipe: estas telas expõem CPF, endereço
 * e situação financeira, e o cadastro público do Breeze cria usuários com
 * papel "aluno".
 *
 *   ver / criar / editar : admin e instrutor
 *   excluir              : somente admin
 */
abstract class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->role === 'admin';
    }

    private function isStaff(User $user): bool
    {
        return in_array($user->role, ['admin', 'instrutor'], true);
    }
}
