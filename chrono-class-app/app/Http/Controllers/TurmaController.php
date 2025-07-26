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
            'turmas' => Turma::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Turmas/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'periodo' => 'required|string|max:255',
            'ano_letivo' => 'required|integer|min:2020',
        ]);

        Turma::create($request->all());

        return redirect()->route('turmas.index');
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
            'nome' => 'required|string|max:255',
            'periodo' => 'required|string|max:255',
            'ano_letivo' => 'required|integer|min:2020',
        ]);

        $turma->update($request->all());

        return redirect()->route('turmas.index');
    }

    public function destroy(Turma $turma)
    {
        $turma->delete();
        return redirect()->route('turmas.index');
    }
}