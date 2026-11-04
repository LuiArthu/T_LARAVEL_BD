<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 150);
            $table->smallInteger('idade');
            $table->string('cpf', 14)->unique();
            $table->string('endereco', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
