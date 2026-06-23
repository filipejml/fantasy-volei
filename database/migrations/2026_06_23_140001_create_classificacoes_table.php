<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('selecao_id')->constrained('selecoes')->cascadeOnDelete();
            $table->enum('genero', ['masculino', 'feminino']);
            $table->unsignedSmallInteger('temporada');
            $table->unsignedTinyInteger('posicao');
            $table->unsignedTinyInteger('jogos')->default(0);
            $table->unsignedTinyInteger('vitorias')->default(0);
            $table->unsignedTinyInteger('derrotas')->default(0);
            $table->unsignedSmallInteger('pontos')->default(0);
            $table->unsignedSmallInteger('sets_pro')->default(0);
            $table->unsignedSmallInteger('sets_contra')->default(0);
            $table->decimal('set_ratio', 8, 3)->nullable();
            $table->unsignedSmallInteger('pontos_pro')->default(0);
            $table->unsignedSmallInteger('pontos_contra')->default(0);
            $table->decimal('ponto_ratio', 8, 3)->nullable();
            $table->enum('origem', ['manual', 'scraping', 'calculada'])->default('manual');
            $table->string('source_url')->nullable();
            $table->timestamp('importado_em')->nullable();
            $table->timestamps();

            $table->unique(['selecao_id', 'genero', 'temporada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classificacoes');
    }
};
