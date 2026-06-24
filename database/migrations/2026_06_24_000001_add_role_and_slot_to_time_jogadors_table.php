<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_jogadors', function (Blueprint $table) {
            $table->string('tipo', 20)->default('titular')->after('jogador_id');
            $table->string('slot', 20)->nullable()->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('time_jogadors', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'slot']);
        });
    }
};
