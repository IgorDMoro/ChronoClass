<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Horario;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PlannerController extends Controller
{
    public function index(Request $request)
    {
        $anosDisponiveis = Grade::whereNotNull('ano')
            ->distinct()
            ->orderByDesc('ano')
            ->pluck('ano');

        $bimestresDisponiveis = Grade::whereNotNull('bimestre')
            ->when($request->ano, fn($q) => $q->where('ano', $request->ano))
            ->distinct()
            ->orderBy('bimestre')
            ->pluck('bimestre');

        // Filtros de turma: ano_entrada e bimestre_entrada
        $anosEntradaDisponiveis = \App\Models\Turma::whereNotNull('ano_entrada')
            ->distinct()
            ->orderByDesc('ano_entrada')
            ->pluck('ano_entrada');

        $bimestresEntradaDisponiveis = ['B1', 'B2', 'B3', 'B4'];

        $grades = null;
        if ($request->filled('ano') && $request->filled('bimestre')) {
            $grades = Grade::with([
                'turma',
                'horarios.materia',
                'horarios.professor',
            ])
            ->where('ano', $request->ano)
            ->where('bimestre', $request->bimestre)
            ->when($request->filled('ano_entrada'), function ($q) use ($request) {
                $q->whereHas('turma', fn($t) => $t->where('ano_entrada', $request->ano_entrada));
            })
            ->when($request->filled('bimestre_entrada'), function ($q) use ($request) {
                $q->whereHas('turma', fn($t) => $t->where('bimestre_entrada', $request->bimestre_entrada));
            })
            ->orderBy('nome')
            ->get();
        }

        return Inertia::render('Grades/Planner', [
            'grades'                       => $grades,
            'ano'                          => $request->ano ? (int) $request->ano : null,
            'bimestre'                     => $request->bimestre ? (int) $request->bimestre : null,
            'anosDisponiveis'              => $anosDisponiveis,
            'bimestresDisponiveis'         => $bimestresDisponiveis,
            'anoEntrada'                   => $request->ano_entrada ? (int) $request->ano_entrada : null,
            'bimestreEntrada'              => $request->bimestre_entrada ?? null,
            'anosEntradaDisponiveis'       => $anosEntradaDisponiveis,
            'bimestresEntradaDisponiveis'  => $bimestresEntradaDisponiveis,
        ]);
    }

    public function salvar(Request $request)
    {
        $validated = $request->validate([
            'alteracoes'                   => 'required|array|min:1',
            'alteracoes.*.horario_id'      => 'required|integer|exists:horarios,id',
            'alteracoes.*.para_grade_id'   => 'required|integer|exists:grades,id',
            'alteracoes.*.dia_semana'      => 'nullable|string',
            'alteracoes.*.horario_bloco'   => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $alteracoes = $validated['alteracoes'];
                $destinoMap = collect($alteracoes)->keyBy('horario_id');

                foreach ($alteracoes as $alt) {
                    $horario   = Horario::findOrFail($alt['horario_id']);
                    $paraGrade = Grade::findOrFail($alt['para_grade_id']);

                    $diaDest   = $alt['dia_semana']    ?? $horario->dia_semana;
                    $blocoDest = $alt['horario_bloco'] ?? $horario->horario_bloco;

                    $conflitantes = Horario::where('grade_id', $paraGrade->id)
                        ->where('dia_semana', $diaDest)
                        ->where('horario_bloco', $blocoDest)
                        ->where('professor_id', $horario->professor_id)
                        ->get();

                    foreach ($conflitantes as $conflitante) {
                        $estaMovendo = $destinoMap->has($conflitante->id) &&
                                       $destinoMap[$conflitante->id]['para_grade_id'] != $paraGrade->id;

                        if (!$estaMovendo) {
                            throw new \Exception(
                                "Conflito: professor já alocado nesse slot em \"{$paraGrade->nome}\"."
                            );
                        }
                    }
                }

                foreach ($alteracoes as $alt) {
                    $updates = ['grade_id' => $alt['para_grade_id']];
                    if (!empty($alt['dia_semana']))    $updates['dia_semana']    = $alt['dia_semana'];
                    if (!empty($alt['horario_bloco'])) $updates['horario_bloco'] = $alt['horario_bloco'];
                    Horario::where('id', $alt['horario_id'])->update($updates);
                }
            });

            return back()->with('success', 'Alterações salvas com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}