<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Professor;
use App\Models\Sala;
use App\Models\Turma;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    public function index()
    {
        return Inertia::render('Grades/Index', [
            'grades' => Grade::with('turma')->orderBy('pinned_at', 'desc')->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Grades/Create', [
            'materias_presenciais' => Materia::where('modalidade', 'Presencial')->get(),
            'materias_ucd'         => Materia::where('modalidade', 'UCD')->get(),
            'professores'          => Professor::with(['materias', 'horariosDisponiveisPivot'])->get(),
            'existingHorarios'     => Horario::with('grade:id,nome')->select('dia_semana', 'horario_bloco', 'professor_id', 'grade_id')->get(),
            'salas'                => Sala::all(),
            'turmas'               => Turma::all(),
        ]);
    }

    public function getProfessoresPorMateria($materiaId)
    {
        $materia = Materia::with('professores')->findOrFail($materiaId);

        return response()->json($materia->professores);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'curso'    => 'required|array|min:1',
            'curso.*'  => 'required|string|in:Engenharia de Software,Ciências da Computação',
            'horarios' => 'present|array',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $turma = Turma::findOrFail($validated['turma_id']);

                $grade = Grade::create([
                    'nome'     => $turma->nome . ' — ' . $turma->semestre,
                    'curso'    => $validated['curso'],
                    'turma_id' => $validated['turma_id'],
                ]);

                foreach ($validated['horarios'] as $horarioData) {
                    $horarioData['grade_id'] = $grade->id;
                    Horario::create($horarioData);
                }
            });

            return redirect()->route('grades.index')->with('success', 'Grade criada com sucesso!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Erro ao criar grade: ' . $e->getMessage()]);
        }
    }

    public function show(Grade $grade)
    {
        $grade->load('horarios.materia', 'horarios.professor', 'turma');

        return Inertia::render('Grades/Show', [
            'grade' => $grade,
        ]);
    }

    public function edit(Grade $grade)
    {
        return Inertia::render('Grades/Edit', [
            'grade'                => $grade->load('turma'),
            'existingHorarios'     => $grade->horarios()->get(),
            'materias_presenciais' => Materia::all(),
            'materias_ucd'         => Materia::where('modalidade', 'UCD')->get(),
            'professores'          => Professor::with(['materias'])->get(),
            'salas'                => Sala::all(),
            'turmas'               => Turma::all(),
        ]);
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'curso'    => 'required|array|min:1',
            'curso.*'  => 'required|string|in:Engenharia de Software,Ciências da Computação',
            'horarios' => 'present|array',
        ]);

        try {
            DB::transaction(function () use ($validated, $grade) {
                $turma = Turma::findOrFail($validated['turma_id']);

                $grade->update([
                    'nome'     => $turma->nome . ' — ' . $turma->semestre,
                    'curso'    => $validated['curso'],
                    'turma_id' => $validated['turma_id'],
                ]);

                $grade->horarios()->delete();

                foreach ($validated['horarios'] as $horarioData) {
                    $horarioData['grade_id'] = $grade->id;
                    Horario::create($horarioData);
                }
            });

            return redirect()->route('grades.index')->with('success', 'Grade atualizada com sucesso!');
        } catch (\Exception $e) {
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