<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
// use Illuminate\Database\Eloquent\Casts\Attribute; // <-- 1. REMOVEMOS ISSO

class Professor extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     */
    protected $table = 'professores';

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'matricula',
        'nome',
        'email',
        'telefone',
    ];

    /**
     * Os accessors para anexar ao array do modelo.
     */
    protected $appends = ['disponibilidade'];

    /**
     * Get the grade_horarios for the professor.
     */
    public function gradeHorarios()
    {
        return $this->hasMany(GradeHorario::class);
    }
    
    /**
     * Get the horarios_disponiveis for the professor.
     */
    public function horariosDisponiveisPivot()
    {
        return $this->hasMany(ProfessorHorarioDisponivel::class);
    }

    /**
     * O relacionamento de matérias que o professor leciona.
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'materia_professor', 'professor_id', 'materia_id');
    }

    /**
     *
     * Accessor para a disponibilidade formatada (estilo antigo do Laravel).
     * Isso é chamado automaticamente por causa do '$appends' acima.
     *
     * @return array|null
     */
    public function getDisponibilidadeAttribute()
    {
        // Verifica se a relação foi carregada (para evitar N+1)
        // O seu GradeController.php já faz o ->with('horariosDisponiveisPivot')
        if (! $this->relationLoaded('horariosDisponiveisPivot')) {
            return null;
        }

        // Se foi carregada, processa e agrupa os dados
        return $this->horariosDisponiveisPivot
            ->groupBy('dia_semana')
            ->map(fn ($group) => $group->pluck('horario')->all())
            ->all();
    }
}