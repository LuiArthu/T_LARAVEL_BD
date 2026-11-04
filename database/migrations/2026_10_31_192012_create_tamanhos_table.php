<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tamanhos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id');
            $table->string('tamanho_faixa', 5);
            $table->string('tamanho_kimono', 5);

            $table->foreign('cliente_id')->references('id')->on('clientes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tamanhos');
    }
};
