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
            $table->integer('matricula')->unique();
            $table->string('nome'); 
            $table->string('email')->nullable(); 
            $table->string('telefone')->nullable(); 
            $table->timestamps(); 
            $table->string('dias_disponiveis')->nullable(); 
            $table->string('horarios_disponiveis')->nullable();
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
