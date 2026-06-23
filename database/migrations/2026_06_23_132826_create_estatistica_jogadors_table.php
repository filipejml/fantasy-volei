<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estatistica_jogadors', function (Blueprint $table) {

            $table->id();

            $table->foreignId('jogador_id')
                ->constrained('jogadors')
                ->cascadeOnDelete();

            $table->foreignId('partida_id')
                ->constrained('partidas')
                ->cascadeOnDelete();

            $table->integer('pontos')
                ->default(0);

            $table->integer('aces')
                ->default(0);

            $table->integer('bloqueios')
                ->default(0);

            $table->integer('erros')
                ->default(0);

            $table->decimal('pontuacao_fantasy', 8, 2)
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estatistica_jogadors');
    }
};