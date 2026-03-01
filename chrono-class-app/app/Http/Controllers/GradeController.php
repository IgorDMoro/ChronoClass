<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Professor;
use App\Models\Sala;
use App\Models\GrupoMateria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    public function index()
    {
        return Inertia::render('Grades/Index', [
            'grades' => Grade::orderBy('pinned_at', 'desc')->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Grades/Create', [
            'grupos' => GrupoMateria::with('materias')->get(),
            'materias_presenciais' => Materia::where('modalidade', 'Presencial')->with('grupo')->get(),
            'materias_ucd' => Materia::where('modalidade', 'UCD')->with('grupo')->get(),
            'professores' => Professor::with(['materias', 'gruposMaterias', 'horariosDisponiveisPivot'])->get(),
            'existingHorarios' => Horario::with('grade:id,nome')->select('dia_semana', 'horario_bloco', 'professor_id', 'grade_id')->get(),
            'salas' => Sala::all(),
        ]);
    }

    /**
     * Get professores that teach a specific materia.
     */
    public function getProfessoresPorMateria($materiaId)
    {
        $materia = Materia::with('grupo.professores')->findOrFail($materiaId);
        
        if ($materia->grupo) {
            return response()->json($materia->grupo->professores);
        }

        return response()->json(Professor::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'description' => 'nullable|string',
            'semestre' => 'required|string|max:255',
            'curso' => 'required|array|min:1',
            'horarios' => 'present|array',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $grade = Grade::create([
                    'nome' => $validated['nome'],
                    'description' => $validated['description'],
                    'semestre' => $validated['semestre'],
                    'curso' => $validated['curso'],
                ]);

                // Criar horários com grade_id
                foreach ($validated['horarios'] as $horarioData) {
                    $horarioData['grade_id'] = $grade->id;
                    Horario::create($horarioData);
                }
            });

            return redirect()->route('grades.index')->with('success', 'Grade criada com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Erro ao criar grade: ' . $e->getMessage());
            \Log::error('Dados recebidos: ' . json_encode($validated));
            return back()->withInput()->withErrors(['error' => 'Erro ao criar grade: ' . $e->getMessage()]);
        }
    }

    public function show(Grade $grade)
    {
        $grade->load('horarios.materia', 'horarios.professor');
        return Inertia::render('Grades/Show', [
            'grade' => $grade,
        ]);
    }

    public function edit(Grade $grade)
    {
        $materias_presenciais = Materia::all();
        $professores = Professor::all();
        $salas = Sala::all();
        
        // Carrega os horários relacionados
        $existingHorarios = $grade->horarios()->get();

        return inertia('Grades/Edit', [
            'grade' => $grade,
            'existingHorarios' => $existingHorarios,
            'materias_presenciais' => $materias_presenciais,
            'professores' => $professores,
            'salas' => $salas,
        ]);
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'description' => 'nullable|string',
            'semestre' => 'required|string|max:255',
            'curso' => 'required|array|min:1',
            'horarios' => 'present|array',
        ]);

        try {
            DB::transaction(function () use ($validated, $grade) {
                $grade->update([
                    'nome' => $validated['nome'],
                    'description' => $validated['description'],
                    'semestre' => $validated['semestre'],
                    'curso' => $validated['curso'],
                ]);

                // Remove horários antigos
                $grade->horarios()->delete();

                // Cria novos horários
                foreach ($validated['horarios'] as $horarioData) {
                    $horarioData['grade_id'] = $grade->id;
                    Horario::create($horarioData);
                }
            });

            return redirect()->route('grades.index')->with('success', 'Grade atualizada com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar grade: ' . $e->getMessage());
            \Log::error('Dados recebidos: ' . json_encode($validated));
            return back()->withInput()->withErrors(['error' => 'Erro ao atualizar grade: ' . $e->getMessage()]);
        }
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        return redirect()->route('grades.index')->with('success', 'Grade excluída com sucesso.');
    }

    public function pin(Grade $grade)
    {
        $grade->pinned_at = $grade->pinned_at ? null : now();
        $grade->save();
        return back();
    }
}