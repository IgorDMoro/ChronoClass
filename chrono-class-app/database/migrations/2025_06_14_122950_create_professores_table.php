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
        Schema::create('professores', function (Blueprint $table) {
            $table->id(); // id: bigint, PK, auto-increment
            $table->string('nome'); // nome: string
            $table->string('email')->nullable(); // email: string, opcional
            $table->string('telefone')->nullable(); // telefone: string, opcional
            $table->timestamps(); // criado_em e atualizado_em: timestamp, opcional (gerenciados pelo Laravel)
            $table->string('dias_disponiveis')->nullable(); // Dias da semana disponíveis
            $table->string('horarios_disponiveis')->nullable(); // Horários disponíveis
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professores');
    }
};
