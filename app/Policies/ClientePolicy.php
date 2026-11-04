<?php

namespace App\Policies;

class ClientePolicy extends StaffPolicy
{
    // Regras herdadas de StaffPolicy. O Laravel descobre esta classe sozinho
    // pelo nome (App\Models\Cliente -> App\Policies\ClientePolicy).
}
