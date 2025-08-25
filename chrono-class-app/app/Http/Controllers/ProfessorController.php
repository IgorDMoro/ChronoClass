<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use App\Models\ProfessorHorarioDisponivel;

class ProfessorController extends Controller
{
    // Definir as opções de dias e horários para reuso
    private $diasDaSemana = [
        'segunda',
        'terça',
        'quarta',
        'quinta',
        'sexta',
    ];

    private $finaisDeSemana = [
        'sábado',
    ];

    private $horariosDeAula = [
        '19:00-20:30',
        '20:45-22:15'
    ];

    private $horariosDeAulaFinaisDeSemana = [
        '08:00-09:30',
        '10:00-12:00'
    ];


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // A lógica do Index não precisa de alteração, pois apenas exibe os dados salvos.
        $professores = Professor::with('horariosDisponiveisPivot')->get();

        return Inertia::render('Professores/Index', [
            'professores' => $professores,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Envia as 4 listas de dados para a view de criação
        return Inertia::render('Professores/Create', [
            'diasDaSemana' => $this->diasDaSemana,
            'finaisDeSemana' => $this->finaisDeSemana,
            'horariosDeAula' => $this->horariosDeAula,
            'horariosDeAulaFinaisDeSemana' => $this->horariosDeAulaFinaisDeSemana,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'matricula' => 'required|integer|unique:professores,matricula',
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:professores,email',
            'telefone' => 'nullable|string|max:20',
            'horarios_disponiveis_selecionados' => 'nullable|array',
            'horarios_disponiveis_selecionados.*' => 'string|regex:/^[a-zA-ZáàâãéèêíìîóòôõúùûçÇ]+-\d{2}:\d{2}-\d{2}:\d{2}$/',
        ]);

        DB::transaction(function () use ($validatedData) {
            $professor = Professor::create([
                'matricula' => $validatedData['matricula'],
                'nome' => $validatedData['nome'],
                'email' => $validatedData['email'],
                'telefone' => $validatedData['telefone'],
            ]);

            if (!empty($validatedData['horarios_disponiveis_selecionados'])) {
                foreach ($validatedData['horarios_disponiveis_selecionados'] as $disponibilidade) {
                    list($dia_semana, $horario) = explode('-', $disponibilidade, 2);
                    $professor->horariosDisponiveisPivot()->create([
                        'dia_semana' => $dia_semana,
                        'horario' => $horario,
                    ]);
                }
            }
        });

        return Redirect::route('professores.index')
            ->with('message', 'Professor cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */ 
    public function edit(Professor $professor)
    {
        $professor->load('horariosDisponiveisPivot');

        // Envia as 4 listas de dados para a view de edição
        return Inertia::render('Professores/Edit', [
            'professor' => $professor,
            'diasDaSemana' => $this->diasDaSemana,
            'finaisDeSemana' => $this->finaisDeSemana,
            'horariosDeAula' => $this->horariosDeAula,
            'horariosDeAulaFinaisDeSemana' => $this->horariosDeAulaFinaisDeSemana,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Professor $professor)
    {
        // A validação e a lógica de atualização também não precisam de alteração.
        $validatedData = $request->validate([
            'matricula' => 'required|integer|unique:professores,matricula,' . $professor->id, 
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:professores,email,' . $professor->id,
            'telefone' => 'nullable|string|max:20',
            'horarios_disponiveis_selecionados' => 'nullable|array',
            'horarios_disponiveis_selecionados.*' => 'string|regex:/^[a-zA-ZáàâãéèêíìîóòôõúùûçÇ]+-\d{2}:\d{2}-\d{2}:\d{2}$/',
        ]);

        DB::transaction(function () use ($validatedData, $professor) {
            $professor->update([
                'matricula' => $validatedData['matricula'],
                'nome' => $validatedData['nome'],
                'email' => $validatedData['email'],
                'telefone' => $validatedData['telefone'],
            ]);

            $professor->horariosDisponiveisPivot()->delete();

            if (!empty($validatedData['horarios_disponiveis_selecionados'])) {
                foreach ($validatedData['horarios_disponiveis_selecionados'] as $disponibilidade) {
                    list($dia_semana, $horario) = explode('-', $disponibilidade, 2);
                    $professor->horariosDisponiveisPivot()->create([
                        'dia_semana' => $dia_semana,
                        'horario' => $horario,
                    ]);
                }
            }
        });

        return Redirect::route('professores.index')
            ->with('message', 'Professor atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor)
    {
        DB::transaction(function () use ($professor) {
            $professor->delete();
        });

        return Redirect::route('professores.index')
            ->with('message', 'Professor excluído com sucesso!');
    }

    public function updateWithPost(Request $request, Professor $professor)
    {
        return $this->update($request, $professor);
    }

    /**
     * Remove the specified resource from storage using a POST request.
     */
    public function destroyWithPost(Professor $professor)
    {
        return $this->destroy($professor);
    }
}