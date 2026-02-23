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
        // COLOQUE ESTE CÓDIGO DENTRO DA FUNÇÃO UP()
        Schema::create('materia_professor', function (Blueprint $table) {
            // Cria a coluna 'professor_id'
            $table->foreignId('professor_id')
                  ->constrained('professores') // Diz que é uma chave estrangeira para a tabela 'professores'
                  ->onDelete('cascade'); // Se um professor for deletado, apaga o link

            // Cria a coluna 'materia_id'
            $table->foreignId('materia_id')
                  ->constrained('materias') // Chave estrangeira para a tabela 'materias'
                  ->onDelete('cascade'); // Se uma matéria for deletada, apaga o link

            // Define que a combinação das duas colunas é a chave primária
            // Isso impede entradas duplicadas (mesmo professor na mesma matéria)
            $table->primary(['professor_id', 'materia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_professor');
    }
};