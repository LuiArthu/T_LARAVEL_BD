<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Matricula;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Matricula> */
class MatriculaFactory extends Factory
{
    public function definition(): array
    {
        $modalidade = fake()->randomElement(Matricula::MODALIDADES);

        return [
            'cliente_id' => Cliente::factory(),
            'modalidade' => $modalidade,
            'faixa' => Matricula::usaFaixa($modalidade)
                ? fake()->randomElement(Matricula::FAIXAS)
                : Matricula::FAIXA_NAO_SE_APLICA,
            'data_inicio' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
        ];
    }
}
