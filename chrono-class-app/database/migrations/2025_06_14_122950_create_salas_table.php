<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salas', function (Blueprint $table) {
            $table->id(); // id: bigint, PK, auto-increment
            $table->string('nome'); // nome: string (Nome ou código da sala, ex: B101)
            $table->integer('capacidade')->nullable(); // capacidade: integer, opcional
            $table->timestamps(); // criado_em e atualizado_em: timestamp, opcional (gerenciados pelo Laravel)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salas');
    }
};