<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_jogadors', function (Blueprint $table) {

            $table->id();

            $table->foreignId('time_id')
                ->constrained('times')
                ->cascadeOnDelete();

            $table->foreignId('jogador_id')
                ->constrained('jogadors')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_jogadors');
    }
};