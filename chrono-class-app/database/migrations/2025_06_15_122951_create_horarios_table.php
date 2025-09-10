<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Classe nomeada corretamente
class CreateHorariosTable extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('professor_id')->constrained('professores')->onDelete('cascade');
            $table->string('dia_semana');
            $table->string('horario_bloco');
            $table->string('sala')->nullable();
            $table->string('classroom_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};