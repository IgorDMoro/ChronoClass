<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turma_id')->nullable()->constrained('turmas')->nullOnDelete();
            $table->string('nome');
            $table->longText('curso');
            $table->tinyInteger('bimestre')->nullable();
            $table->smallInteger('ano')->nullable();
            $table->timestamp('pinned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};