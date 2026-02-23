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
        Schema::create('grupos_materias', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->foreignId('grupo_id')->nullable()->constrained('grupos_materias')->onDelete('set null');
        });

        Schema::create('professor_grupo_materia', function (Blueprint $table) {
            $table->foreignId('professor_id')->constrained('professores')->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained('grupos_materias')->onDelete('cascade');
            $table->primary(['professor_id', 'grupo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professor_grupo_materia');
        Schema::table('materias', function (Blueprint $table) {
            $table->dropForeignIdFor('grupos_materias');
        });
        Schema::dropIfExists('grupos_materias');
    }
};
