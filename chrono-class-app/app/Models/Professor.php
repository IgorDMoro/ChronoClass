<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'professores';
    protected $fillable = [
        'nome',
        'email',
        'telefone',
    ];

    /**
     * Get the grade_horarios for the professor.
     */
    public function gradeHorarios()
    {
        return $this->hasMany(GradeHorario::class);
    }
    
        public function horariosDisponiveisPivot()
    {
        return $this->hasMany(ProfessorHorarioDisponivel::class);
    }
}
