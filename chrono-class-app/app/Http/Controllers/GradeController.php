<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Professor;
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
            'materias_presenciais' => Materia::where('modalidade', 'Presencial')->get(),
            'materias_ucd' => Materia::where('modalidade', 'UCD')->get(),
            'professores' => Professor::with('materias', 'disponibilidade')->get(),
            'existingHorarios' => Horario::with('grade:id,nome')->select('dia_semana', 'horario_bloco', 'professor_id', 'grade_id')->get(),
        ]);
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

        DB::transaction(function () use ($validated) {
            $grade = Grade::create([
                'nome' => $validated['nome'],
                'description' => $validated['description'],
                'semestre' => $validated['semestre'],
                'curso' => $validated['curso'],
            ]);

            foreach ($validated['horarios'] as $horarioData) {
                $grade->horarios()->create($horarioData);
            }
        });

        return redirect()->route('grades.index')->with('success', 'Grade criada com sucesso!');
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
        // Implementar a edição se necessário
    }

    public function update(Request $request, Grade $grade)
    {
        // Implementar a atualização se necessário
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