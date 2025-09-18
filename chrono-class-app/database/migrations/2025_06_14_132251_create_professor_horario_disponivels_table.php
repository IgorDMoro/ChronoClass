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
        Schema::create('professor_horarios_disponiveis', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('professor_id')
                  ->constrained('professores')
                  ->onDelete('cascade');
            $table->string('dia_semana');
            $table->string('horario');
            $table->timestamps();
            $table->unique(['professor_id', 'dia_semana', 'horario'], 'professor_dia_horario_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professor_horarios_disponiveis');
    }
};