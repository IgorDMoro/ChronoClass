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
            $table->integer('capacidade')->unsigned(); // Capacidade da sala (inteiro positivo)
            $table->enum('campus', ['campus ipolon', 'campus sede']); // Campo para o campus
            $table->timestamps();
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