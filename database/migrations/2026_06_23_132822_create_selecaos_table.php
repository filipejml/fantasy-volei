<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selecoes', function (Blueprint $table) {

            $table->id();

            $table->string('nome');
            $table->enum('genero', ['masculino', 'feminino']);

            $table->string('sigla', 5)->nullable();

            $table->string('bandeira')->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selecoes');
    }
};
