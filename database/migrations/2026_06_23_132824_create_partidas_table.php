<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidas', function (Blueprint $table) {

            $table->id();

            $table->foreignId('selecao_casa_id')
                ->constrained('selecoes');

            $table->foreignId('selecao_fora_id')
                ->constrained('selecoes');

            $table->dateTime('data_partida');

            $table->integer('placar_casa')
                ->nullable();

            $table->integer('placar_fora')
                ->nullable();

            $table->string('status')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas');
    }
};
