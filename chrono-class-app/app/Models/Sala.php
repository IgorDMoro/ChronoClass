<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'capacidade',
    ];

    /**
     * Get the grade_horarios for the sala.
     */
    public function gradeHorarios()
    {
        return $this->hasMany(GradeHorario::class);
    }
}