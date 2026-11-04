<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Tamanho;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tamanho> */
class TamanhoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'tamanho_faixa' => fake()->randomElement(Tamanho::TAMANHOS),
            'tamanho_kimono' => fake()->randomElement(Tamanho::TAMANHOS),
        ];
    }
}
