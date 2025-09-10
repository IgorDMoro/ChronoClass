<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'description',
        'semestre',
        'curso',
        'pinned_at',
    ];

    protected $casts = [
        'curso' => 'array',
    ];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }
}