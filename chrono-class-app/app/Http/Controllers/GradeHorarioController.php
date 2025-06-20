<?php

namespace App\Http\Controllers;

use App\Models\GradeHorario;
use App\Models\Materia;
use App\Models\Professor;
use App\Models\Sala;
use App\Models\Turma; // Importar o modelo Turma
use Inertia\Inertia; // Para trabalhar com Inertia.js
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB; // Para transações de banco de dados
use App\Http\Requests\StoreGradeHorarioRequest; // Importar o Form Request

class GradeHorarioController extends Controller
{
    // Definir opções para dias da semana e horários de aula, similar ao ProfessorController
    private $diasDaSemana = [
        'segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado'
    ];

    private $horariosDeAula = [
        '19:00-20:30', '20:45-22:15' 
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Carrega as grades com os relacionamentos necessários (professor, materia, sala e turmas)
        $grades = GradeHorario::with(['materia', 'professor', 'sala', 'turmas'])->get();

        return Inertia::render('GradeHorarios/Index', [
            'grades' => $grades,
            'diasDaSemana' => $this->diasDaSemana,
            'horariosDeAula' => $this->horariosDeAula,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Passa os dados necessários para o formulário de criação
        return Inertia::render('GradeHorarios/Create', [
            'materias' => Materia::all(),
            'professores' => Professor::all(),
            'salas' => Sala::all(),
            'turmas' => Turma::all(), // Todas as turmas para seleção
            'diasDaSemana' => $this->diasDaSemana,
            'horariosDeAula' => $this->horariosDeAula,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreGradeHorarioRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreGradeHorarioRequest $request)
    {
        // Os dados já foram validados pelo StoreGradeHorarioRequest
        $validatedData = $request->validated();

        // --- Lógica para as Validações de Conflito Customizadas (Será implementada aqui) ---
        // 1. Disponibilidade do Professor
        // 2. Capacidade da Sala
        // 3. Conflito de Professor/Sala/Turma
        // 4. Consistência em Aulas Multiturma (se aplicável)

        // Por enquanto, apenas cria a grade e anexa as turmas se as validações do Form Request passarem.
        DB::transaction(function () use ($validatedData) {
            $gradeHorario = GradeHorario::create([
                'materia_id' => $validatedData['materia_id'],
                'professor_id' => $validatedData['professor_id'],
                'sala_id' => $validatedData['sala_id'],
                'dia_semana' => $validatedData['dia_semana'],
                'horario' => $validatedData['horario'],
            ]);

            // Anexa as turmas à grade horária usando o relacionamento muitos-para-muitos
            // O attach espera um array de IDs
            $gradeHorario->turmas()->attach($validatedData['turmas_ids']);
        });

        return Redirect::route('grade_horarios.index')
            ->with('message', 'Grade horária criada com sucesso!');
    }

    // Métodos show, edit, update, destroy serão implementados posteriormente
}