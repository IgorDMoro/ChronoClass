<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = ['turma_id', 'nome', 'curso', 'pinned_at', 'bimestre', 'ano'];

    protected $casts = [
        'curso' => 'array',
    ];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    public function getBimestreLabel(): string
    {
        return $this->bimestre ? "{$this->bimestre}º Bimestre" : '—';
    }
}