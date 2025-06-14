<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeHorario extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grade_horarios'; // Garante que o nome da tabela está correto

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'turma_id',
        'materia_id',
        'professor_id',
        'sala_id',
        'dia_semana',
        'horario',
    ];

    /**
     * Get the turma that owns the grade horario.
     */
    public function turma()
    {
        return $this->belongsTo(Turma::class);
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