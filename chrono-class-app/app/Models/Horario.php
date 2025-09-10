<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = [
        'grade_id',
        'materia_id',
        'professor_id',
        'dia_semana',
        'horario_bloco',
        'sala',
        'classroom_code',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }
}