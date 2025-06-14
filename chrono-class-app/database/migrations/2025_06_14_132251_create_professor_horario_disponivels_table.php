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
            $table->id(); // Chave primária para este registro

            // Chave estrangeira para a tabela 'professores'
            $table->foreignId('professor_id')
                  ->constrained('professores')
                  ->onDelete('cascade'); // Se o professor for excluído, sua disponibilidade também será

            $table->string('dia_semana'); // Ex: 'segunda', 'terça'
            $table->string('horario');    // Ex: '08:00-09:40', '19:00-20:30'

            $table->timestamps(); // created_at, updated_at

            // Garante que um professor não possa ter o mesmo dia e horário duplicados
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