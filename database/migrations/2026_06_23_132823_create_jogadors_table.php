<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jogadors', function (Blueprint $table) {

            $table->id();

            $table->foreignId('selecao_id')
                ->constrained('selecoes')
                ->cascadeOnDelete();

            $table->foreignId('posicao_id')
                ->constrained('posicaos')
                ->cascadeOnDelete();

            $table->string('nome');

            $table->enum('genero', ['masculino', 'feminino']);

            $table->decimal('valor_creditos', 8, 2);

            $table->decimal('media_pontos', 8, 2)
                ->default(0);

            $table->integer('idade')
                ->nullable();

            $table->decimal('altura', 4, 2)
                ->nullable();

            $table->string('foto')
                ->nullable();

            $table->boolean('ativo')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jogadors');
    }
};
