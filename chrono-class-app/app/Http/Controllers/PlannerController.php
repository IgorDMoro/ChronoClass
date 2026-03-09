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

        $grades = null;
        if ($request->filled('ano') && $request->filled('bimestre')) {
            $grades = Grade::with([
                'turma',
                'horarios.materia',
                'horarios.professor',
            ])
            ->where('ano', $request->ano)
            ->where('bimestre', $request->bimestre)
            ->orderBy('nome')
            ->get();
        }

        return Inertia::render('Grades/Planner', [
            'grades'               => $grades,
            'ano'                  => $request->ano ? (int) $request->ano : null,
            'bimestre'             => $request->bimestre ? (int) $request->bimestre : null,
            'anosDisponiveis'      => $anosDisponiveis,
            'bimestresDisponiveis' => $bimestresDisponiveis,
        ]);
    }

    public function salvar(Request $request)
    {
        $validated = $request->validate([
            'alteracoes'                 => 'required|array|min:1',
            'alteracoes.*.horario_id'    => 'required|integer|exists:horarios,id',
            'alteracoes.*.para_grade_id' => 'required|integer|exists:grades,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $alteracoes = $validated['alteracoes'];

                // Mapa horario_id → para_grade_id para detectar swaps
                $destinoMap = collect($alteracoes)->keyBy('horario_id');

                // Valida todos antes de mover qualquer um
                foreach ($alteracoes as $alt) {
                    $horario   = Horario::findOrFail($alt['horario_id']);
                    $paraGrade = Grade::findOrFail($alt['para_grade_id']);

                    // Conflito de professor no slot de destino
                    $conflitantes = Horario::where('grade_id', $paraGrade->id)
                        ->where('dia_semana', $horario->dia_semana)
                        ->where('horario_bloco', $horario->horario_bloco)
                        ->where('professor_id', $horario->professor_id)
                        ->get();

                    foreach ($conflitantes as $conflitante) {
                        // Se o conflitante também está sendo movido para fora dessa grade = swap, tudo bem
                        $estaMovendo = $destinoMap->has($conflitante->id) &&
                                       $destinoMap[$conflitante->id]['para_grade_id'] != $paraGrade->id;

                        if (!$estaMovendo) {
                            throw new \Exception(
                                "Conflito: professor já alocado nesse slot em \"{$paraGrade->nome}\"."
                            );
                        }
                    }
                }

                // Aplica todos os movimentos de uma vez
                foreach ($alteracoes as $alt) {
                    Horario::where('id', $alt['horario_id'])
                        ->update(['grade_id' => $alt['para_grade_id']]);
                }
            });

            return back()->with('success', 'Alterações salvas com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}