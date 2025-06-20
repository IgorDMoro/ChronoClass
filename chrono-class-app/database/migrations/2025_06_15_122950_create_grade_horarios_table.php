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
        // Tabela principal de Grade Horária (uma ocorrência de aula)
        Schema::create('grade_horarios', function (Blueprint $table) {
            $table->id(); // id: bigint, PK, auto-increment
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('professor_id')->constrained('professores')->onDelete('cascade');
            $table->foreignId('sala_id')->constrained('salas')->onDelete('cascade');
            $table->string('dia_semana'); // Ex: "segunda", "terça", "quarta"
            $table->string('horario'); // Ex: "08:00-09:40", "10:00-11:40"
            $table->timestamps(); // criado_em e atualizado_em: timestamp

            // Índices únicos para evitar conflitos na OCASÃO DA AULA
            // Uma sala não pode estar ocupada no mesmo dia/horário por aulas diferentes
            $table->unique(['dia_semana', 'horario', 'sala_id'], 'unique_sala_horario');
            // Um professor não pode dar aula em dois lugares ao mesmo tempo
            $table->unique(['dia_semana', 'horario', 'professor_id'], 'unique_professor_horario');
            // Note: Removemos o índice 'unique_turma_materia_horario' daqui porque 'turma_id' será movido para a pivô
        });

        // Tabela pivô para o relacionamento muitos-para-muitos entre grade_horarios e turmas
        Schema::create('grade_horario_turma', function (Blueprint $table) {
            $table->foreignId('grade_horario_id')->constrained('grade_horarios')->onDelete('cascade');
            $table->foreignId('turma_id')->constrained('turmas')->onDelete('cascade');
            $table->primary(['grade_horario_id', 'turma_id']); // Chave primária composta para unicidade
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_horario_turma'); // Primeiro, dropa a tabela pivô
        Schema::dropIfExists('grade_horarios');     // Depois, dropa a tabela principal
    }
};