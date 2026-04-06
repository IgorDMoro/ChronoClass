<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TurmaController extends Controller
{
    public function index()
    {
        return Inertia::render('Turmas/Index', [
            'turmas' => Turma::orderBy('nome')->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Turmas/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'             => 'required|string|max:255',
            'periodo'          => 'required|string|max:255',
            'ano_entrada'      => 'required|integer|min:2020',
            'bimestre_entrada' => 'required|in:B1,B2,B3,B4',
        ], [
            'nome.required' => 'Preencha o nome da turma para enviar.',
            'periodo.required' => 'Preencha o período da turma para enviar.',
            'ano_entrada.required' => 'Preencha o ano de entrada da turma para enviar.',
            'ano_entrada.min' => 'O ano de entrada deve ser no mínimo 2020.',
            'bimestre_entrada.required' => 'Selecione o bimestre de entrada da turma para enviar.',
            'bimestre_entrada.in' => 'Selecione um bimestre válido.',
        ]);

        Turma::create($request->all());

        return redirect()->route('turmas.index')->with('success', 'Turma criada com sucesso!');
    }

    public function edit(Turma $turma)
    {
        return Inertia::render('Turmas/Edit', [
            'turma' => $turma,
        ]);
    }

    public function update(Request $request, Turma $turma)
    {
        $request->validate([
            'nome'             => 'required|string|max:255',
            'periodo'          => 'required|string|max:255',
            'ano_entrada'      => 'required|integer|min:2020',
            'bimestre_entrada' => 'required|in:B1,B2,B3,B4',
        ], [
            'nome.required' => 'Preencha o nome da turma para enviar.',
            'periodo.required' => 'Preencha o período da turma para enviar.',
            'ano_entrada.required' => 'Preencha o ano de entrada da turma para enviar.',
            'ano_entrada.min' => 'O ano de entrada deve ser no mínimo 2020.',
            'bimestre_entrada.required' => 'Selecione o bimestre de entrada da turma para enviar.',
            'bimestre_entrada.in' => 'Selecione um bimestre válido.',
        ]);

        $turma->update($request->all());

        return redirect()->route('turmas.index')->with('success', 'Turma atualizada com sucesso!');
    }

    public function updateWithPost(Request $request, Turma $turma)
    {
        return $this->update($request, $turma);
    }

    public function destroy(Turma $turma)
    {
        $turma->delete();

        return redirect()->route('turmas.index')->with('success', 'Turma excluída com sucesso!');
    }

    public function destroyWithPost(Turma $turma)
    {
        return $this->destroy($turma);
    }
}