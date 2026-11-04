<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Pagamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Pagamento> */
class PagamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'dia_pagamento' => fake()->numberBetween(1, 28),
            'plano' => fake()->randomElement(Pagamento::PLANOS),
            'forma_pagamento' => fake()->randomElement(Pagamento::FORMAS),
            'ativo' => true,
            'status_pagamento' => 'Pago',
        ];
    }

    public function devendo(): static
    {
        return $this->state(fn () => ['status_pagamento' => 'Devendo']);
    }
}
