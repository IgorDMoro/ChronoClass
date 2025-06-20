<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Importar BelongsToMany

class Turma extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'curso',
        'semestre',
    ];

    /**
     * Get the grade_horarios for the turma.
     */
    public function gradeHorarios(): BelongsToMany // Novo método para o relacionamento muitos-para-muitos
    {
        return $this->belongsToMany(GradeHorario::class, 'grade_horario_turma', 'turma_id', 'grade_horario_id');
    }
}