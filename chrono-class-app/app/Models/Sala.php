<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'capacidade',
        'campus',
    ];

    /**
     * Get the grade_horarios for the sala.
     */
    public function gradeHorarios()
    {
        return $this->hasMany(GradeHorario::class);
    }
}