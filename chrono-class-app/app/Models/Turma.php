<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turma extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'curso',
        'semestre',
    ];

    /**
     * Get the grade_horarios for the turma.
     */
    public function gradeHorarios()
    {
        return $this->hasMany(GradeHorario::class);
    }
}