<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraping_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->enum('status', ['executando', 'sucesso', 'parcial', 'erro']);
            $table->string('source_url')->nullable();
            $table->text('mensagem')->nullable();
            $table->json('detalhes')->nullable();
            $table->unsignedInteger('registros_processados')->default(0);
            $table->timestamp('iniciado_em');
            $table->timestamp('finalizado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraping_logs');
    }
};
