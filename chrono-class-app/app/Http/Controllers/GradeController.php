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
            'grades' => Grade::with('turma')
                ->orderBy('pinned_at', 'desc')
                ->orderBy('ano', 'desc')
                ->orderBy('bimestre', 'desc')
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Grades/Create', [
            'materias_presenciais' => Materia::where('modalidade', 'Presencial')->whereHas('professores')->get(),
            'materias_ucd'         => Materia::where('modalidade', 'UCD')->whereHas('professores')->get(),
            // Matérias flex: comp_tipo e ensw_tipo ambos 'Flex'
            'materias_flex'        => Materia::where('modalidade', 'Presencial')
                                        ->where('comp_tipo', 'Flex')
                                        ->where('ensw_tipo', 'Flex')
                                        ->whereHas('professores')
                                        ->get(),
            'professores'          => Professor::with(['materias', 'horariosDisponiveisPivot'])->get(),
            'existingHorarios'     => Horario::with('grade:id,nome,bimestre,ano')
                                        ->select('dia_semana', 'horario_bloco', 'professor_id', 'grade_id')
                                        ->get(),
            'salas'                => Sala::all(),
            'turmas'               => Turma::all(),
            'anoAtual'             => now()->year,
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
            'turma_id'  => 'required|exists:turmas,id',
            'curso'     => 'required|array|min:1',
            'curso.*'   => 'required|string|in:Engenharia de Software,Ciências da Computação',
            'bimestre'  => 'required|integer|in:1,2,3,4',
            'ano'       => 'required|integer|min:2020|max:2100',
            'horarios'  => 'present|array',
        ]);

        try {
            $horarios = $request->input('horarios', []);
            DB::transaction(function () use ($validated, $horarios) {
                $turma = Turma::findOrFail($validated['turma_id']);

                $grade = Grade::create([
                    'nome'     => $turma->nome . ' — ' . $turma->periodo,
                    'curso'    => $validated['curso'],
                    'turma_id' => $validated['turma_id'],
                    'bimestre' => $validated['bimestre'],
                    'ano'      => $validated['ano'],
                ]);

                foreach ($horarios as $horarioData) {
                    Horario::create([
                        'grade_id'      => $grade->id,
                        'materia_id'    => $horarioData['materia_id'],
                        'professor_id'  => $horarioData['professor_id'],
                        'dia_semana'    => $horarioData['dia_semana'],
                        'horario_bloco' => $horarioData['horario_bloco'],
                        'sala'          => $horarioData['sala'] ?? null,
                        'classroom_code'=> $horarioData['classroom_code'] ?? null,
                        'tipo_slot'     => $horarioData['tipo_slot'] ?? null,
                    ]);
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
            'materias_presenciais' => Materia::where('modalidade', 'Presencial')->whereHas('professores')->get(),
            'materias_ucd'         => Materia::where('modalidade', 'UCD')->whereHas('professores')->get(),
            'materias_flex'        => Materia::where('modalidade', 'Presencial')
                                        ->where('comp_tipo', 'Flex')
                                        ->where('ensw_tipo', 'Flex')
                                        ->whereHas('professores')
                                        ->get(),
            'professores'          => Professor::with(['materias'])->get(),
            'salas'                => Sala::all(),
            'turmas'               => Turma::all(),
            'anoAtual'             => now()->year,
            'existingHorariosOutrasGrades' => Horario::with('grade:id,nome,bimestre,ano')
                ->select('dia_semana', 'horario_bloco', 'professor_id', 'grade_id')
                ->whereHas('grade', fn($q) => $q
                    ->where('bimestre', $grade->bimestre)
                    ->where('ano', $grade->ano)
                    ->where('id', '!=', $grade->id)
                )
                ->get(),
        ]);
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'turma_id'  => 'required|exists:turmas,id',
            'curso'     => 'required|array|min:1',
            'curso.*'   => 'required|string|in:Engenharia de Software,Ciências da Computação',
            'bimestre'  => 'required|integer|in:1,2,3,4',
            'ano'       => 'required|integer|min:2020|max:2100',
            'horarios'  => 'present|array',
        ]);

        try {
            $horarios = $request->input('horarios', []);
            DB::transaction(function () use ($validated, $grade, $horarios) {
                $turma = Turma::findOrFail($validated['turma_id']);

                $grade->update([
                    'nome'     => $turma->nome . ' — ' . $turma->periodo,
                    'curso'    => $validated['curso'],
                    'turma_id' => $validated['turma_id'],
                    'bimestre' => $validated['bimestre'],
                    'ano'      => $validated['ano'],
                ]);

                $grade->horarios()->delete();

                foreach ($horarios as $horarioData) {
                    Horario::create([
                        'grade_id'      => $grade->id,
                        'materia_id'    => $horarioData['materia_id'],
                        'professor_id'  => $horarioData['professor_id'],
                        'dia_semana'    => $horarioData['dia_semana'],
                        'horario_bloco' => $horarioData['horario_bloco'],
                        'sala'          => $horarioData['sala'] ?? null,
                        'classroom_code'=> $horarioData['classroom_code'] ?? null,
                        'tipo_slot'     => $horarioData['tipo_slot'] ?? null,
                    ]);
                }
            });

            return redirect()->route('grades.index')->with('success', 'Grade atualizada com sucesso!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Erro ao atualizar grade: ' . $e->getMessage()]);
        }
    }

    public function updatePost(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'turma_id'  => 'required|exists:turmas,id',
            'curso'     => 'required|array|min:1',
            'curso.*'   => 'required|string|in:Engenharia de Software,Ciências da Computação',
            'bimestre'  => 'required|integer|in:1,2,3,4',
            'ano'       => 'required|integer|min:2020|max:2100',
            'horarios'  => 'present|array',
        ]);

        try {
            $horarios = $request->input('horarios', []);
            DB::transaction(function () use ($validated, $grade, $horarios) {
                $turma = Turma::findOrFail($validated['turma_id']);

                $outrasGradesIds = Grade::where('bimestre', $validated['bimestre'])
                    ->where('ano', $validated['ano'])
                    ->where('id', '!=', $grade->id)
                    ->pluck('id');

                foreach ($horarios as $horarioData) {
                    if (empty($horarioData['professor_id'])) continue;

                    $conflito = Horario::whereIn('grade_id', $outrasGradesIds)
                        ->where('dia_semana', $horarioData['dia_semana'])
                        ->where('horario_bloco', $horarioData['horario_bloco'])
                        ->where('professor_id', $horarioData['professor_id'])
                        ->with('grade:id,nome')
                        ->first();

                    if ($conflito) {
                        $nomeProf = Professor::find($horarioData['professor_id'])?->nome ?? 'Professor';
                        throw new \Exception(
                            "Conflito: {$nomeProf} já está alocado em {$horarioData['dia_semana']} {$horarioData['horario_bloco']} na grade \"{$conflito->grade->nome}\" ({$validated['bimestre']}º Bim/{$validated['ano']})."
                        );
                    }
                }

                $grade->update([
                    'nome'     => $turma->nome . ' — ' . $turma->periodo,
                    'curso'    => $validated['curso'],
                    'turma_id' => $validated['turma_id'],
                    'bimestre' => $validated['bimestre'],
                    'ano'      => $validated['ano'],
                ]);

                $grade->horarios()->delete();

                foreach ($horarios as $horarioData) {
                    Horario::create([
                        'grade_id'      => $grade->id,
                        'materia_id'    => $horarioData['materia_id'],
                        'professor_id'  => $horarioData['professor_id'],
                        'dia_semana'    => $horarioData['dia_semana'],
                        'horario_bloco' => $horarioData['horario_bloco'],
                        'sala'          => $horarioData['sala'] ?? null,
                        'classroom_code'=> $horarioData['classroom_code'] ?? null,
                        'tipo_slot'     => $horarioData['tipo_slot'] ?? null,
                    ]);
                }
            });

            return redirect()->route('grades.index')->with('success', 'Grade atualizada com sucesso!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
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