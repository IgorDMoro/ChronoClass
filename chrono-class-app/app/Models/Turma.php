<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // 1. Importar o tipo de relacionamento correto

class Turma extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // 2. Corrigir os campos para corresponder à migration e ao controller
    protected $fillable = [
        'nome',
        'periodo',
        'ano_letivo',
    ];

    /**
     * Get the grade_horarios for the turma.
     */
    // 3. Corrigir o método do relacionamento para hasMany
    public function gradeHorarios(): HasMany
    {
        return $this->hasMany(GradeHorario::class);
    }
}