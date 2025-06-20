<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Importar BelongsToMany

class GradeHorario extends Model
{
    use HasFactory;

    protected $table = 'grade_horarios';

    protected $fillable = [
        'materia_id',
        'professor_id',
        'sala_id',
        'dia_semana',
        'horario',
    ];

    /**
     * Get the turmas that belong to the grade horario.
     */
    public function turmas(): BelongsToMany // Novo método para o relacionamento muitos-para-muitos
    {
        return $this->belongsToMany(Turma::class, 'grade_horario_turma', 'grade_horario_id', 'turma_id');
    }

    /**
     * Get the materia that owns the grade horario.
     */
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Get the professor that owns the grade horario.
     */
    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }

    /**
     * Get the sala that owns the grade horario.
     */
    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }
}