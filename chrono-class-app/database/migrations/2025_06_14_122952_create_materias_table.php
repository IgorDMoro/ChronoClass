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
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // 'Cód' - Único para cada matéria
            $table->string('nome');             // 'Disciplina de Origem: Unidade Curricular'
            $table->integer('carga_horaria')-> nullable(); // 'CH'
            $table->string('modalidade')-> nullable();       // 'Mod' (Presencial, UCD)
            $table->string('comp_tipo')-> nullable();        // 'Comp' (Core, Flex)
            $table->string('ensw_tipo')-> nullable();        // 'Ensw' (Core, Flex)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};