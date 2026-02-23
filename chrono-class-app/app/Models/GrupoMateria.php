<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GrupoMateria extends Model
{
    use HasFactory;

    protected $table = 'grupos_materias';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'grupo_id');
    }

    public function professores(): BelongsToMany
    {
        return $this->belongsToMany(Professor::class, 'professor_grupo_materia', 'grupo_id', 'professor_id');
    }
}
