<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Cliente> */
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'idade' => fake()->numberBetween(5, 70),
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'endereco' => fake()->streetAddress().' - Centro - Guarapuava/PR',
        ];
    }
}
