<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'materias'; // Geralmente o Laravel infere, mas é bom ser explícito.

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo',       
        'nome',         
        'carga_horaria',
        'modalidade',   
        'comp_tipo',    
        'ensw_tipo',    
        
        
        
    ];

    /**
     * Get the grade_horarios for the materia.
     */
    public function gradeHorarios()
    {
        return $this->hasMany(GradeHorario::class);
    }
}