<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** A página inicial leva ao sistema (dashboard; sem login, o dashboard manda para o login). */
    public function test_a_pagina_inicial_leva_ao_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }
}
