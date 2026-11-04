<?php

namespace App\Policies;

class PagamentoPolicy extends StaffPolicy
{
    // Regras herdadas de StaffPolicy. O Laravel descobre esta classe sozinho
    // pelo nome (App\Models\Pagamento -> App\Policies\PagamentoPolicy).
}
