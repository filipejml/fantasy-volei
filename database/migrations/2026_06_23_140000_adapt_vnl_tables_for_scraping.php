<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('selecoes', function (Blueprint $table) {
            $table->string('external_ref')->nullable()->after('sigla');
            $table->string('source_url')->nullable()->after('bandeira');
            $table->unique(['external_ref', 'genero']);
        });

        Schema::table('partidas', function (Blueprint $table) {
            $table->enum('genero', ['masculino', 'feminino'])->default('masculino')->after('id');
            $table->unsignedSmallInteger('temporada')->after('genero');
            $table->string('fase')->nullable()->after('temporada');
            $table->string('rodada')->nullable()->after('fase');
            $table->string('local')->nullable()->after('rodada');
            $table->json('sets')->nullable()->after('placar_fora');
            $table->string('external_hash')->nullable()->unique()->after('status');
            $table->string('source_url')->nullable()->after('external_hash');
            $table->enum('origem', ['manual', 'scraping'])->default('manual')->after('source_url');
            $table->timestamp('importado_em')->nullable()->after('origem');
        });
    }

    public function down(): void
    {
        Schema::table('partidas', function (Blueprint $table) {
            $table->dropUnique(['external_hash']);
            $table->dropColumn([
                'genero', 'temporada', 'fase', 'rodada', 'local', 'sets',
                'external_hash', 'source_url', 'origem', 'importado_em',
            ]);
        });

        Schema::table('selecoes', function (Blueprint $table) {
            $table->dropUnique(['external_ref', 'genero']);
            $table->dropColumn(['external_ref', 'source_url']);
        });
    }
};
