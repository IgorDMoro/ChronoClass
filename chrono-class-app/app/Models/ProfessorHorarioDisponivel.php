<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessorHorarioDisponivel extends Model
{
    use HasFactory;

    protected $table = 'professor_horarios_disponiveis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'professor_id',
        'dia_semana',
        'horario',
    ];

    /**
     * Get the professor that owns the availability.
     */
    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }

    
}