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
    // Definir as opções de dias e horários em um array para reuso
    private $diasDaSemana = [
        'segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado',
    ];

    private $horariosDeAula = [
        '08:00-09:40', '10:00-11:40', '14:00-15:40', '16:00-17:40', '19:00-20:30', '20:45-22:15'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $professores = Professor::with('horariosDisponiveisPivot')->get();

        return Inertia::render('Professores/Index', [
            'professores' => $professores,
            'diasDaSemana' => $this->diasDaSemana,
            'horariosDeAula' => $this->horariosDeAula,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Professores/Create', [
            'diasDaSemana' => $this->diasDaSemana,
            'horariosDeAula' => $this->horariosDeAula,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:professores,email',
            'telefone' => 'nullable|string|max:20',
            'horarios_disponiveis_selecionados' => 'nullable|array',
            'horarios_disponiveis_selecionados.*' => 'string|regex:/^[a-zA-ZáàâãéèêíìîóòôõúùûçÇ]+-\d{2}:\d{2}-\d{2}:\d{2}$/',
        ]);

        DB::transaction(function () use ($validatedData) {
            $professor = Professor::create([
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
        // Carrega o professor com seus horários de disponibilidade para o formulário de edição
        $professor->load('horariosDisponiveisPivot');

        return Inertia::render('Professores/Edit', [
            'professor' => $professor,
            'diasDaSemana' => $this->diasDaSemana,
            'horariosDeAula' => $this->horariosDeAula,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Professor $professor)
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:professores,email,' . $professor->id,
            'telefone' => 'nullable|string|max:20',
            'horarios_disponiveis_selecionados' => 'nullable|array',
            'horarios_disponiveis_selecionados.*' => 'string|regex:/^[a-zA-ZáàâãéèêíìîóòôõúùûçÇ]+-\d{2}:\d{2}-\d{2}:\d{2}$/',
        ]);

        DB::transaction(function () use ($validatedData, $professor) {
            $professor->update([
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
}