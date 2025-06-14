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
        Schema::create('grade_horarios', function (Blueprint $table) {
            $table->id(); // id: bigint, PK, auto-increment

            // Chaves Estrangeiras (Foreign Keys)
            $table->foreignId('turma_id')
                  ->constrained('turmas') // Referencia a tabela 'turmas'
                  ->onDelete('cascade'); // Opcional: Se uma turma for deletada, as entradas da grade relacionadas também serão

            $table->foreignId('materia_id')
                  ->constrained('materias') // Referencia a tabela 'materias'
                  ->onDelete('cascade');

            $table->foreignId('professor_id')
                  ->constrained('professores') // Referencia a tabela 'professores'
                  ->onDelete('cascade');

            $table->foreignId('sala_id')
                  ->constrained('salas') // Referencia a tabela 'salas'
                  ->onDelete('cascade');

            $table->string('dia_semana'); // Ex: "segunda", "terça", "quarta"
            $table->string('horario'); // Ex: "08:00-09:40", "10:00-11:40"

            $table->timestamps(); // criado_em e atualizado_em: timestamp, opcional (gerenciados pelo Laravel)

            // Adicionando um índice único para evitar conflitos de agendamento na base de dados
            // Exemplo: uma sala não pode estar ocupada pelo mesmo professor na mesma hora no mesmo dia
            // Você pode ajustar ou adicionar mais restrições de unicidade conforme sua lógica de negócio
            $table->unique(['dia_semana', 'horario', 'sala_id'], 'unique_sala_horario');
            $table->unique(['dia_semana', 'horario', 'professor_id'], 'unique_professor_horario');
            $table->unique(['turma_id', 'dia_semana', 'horario', 'materia_id'], 'unique_turma_materia_horario'); // Uma turma não pode ter a mesma matéria ou duas matérias no mesmo horário
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_horarios');
    }
};