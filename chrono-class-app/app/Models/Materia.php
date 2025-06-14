<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo',
        'nome',
        'curso',
        'semestre',
    ];

    /**
     * Get the grade_horarios for the materia.
     */
    public function gradeHorarios()
    {
        return $this->hasMany(GradeHorario::class);
    }
}