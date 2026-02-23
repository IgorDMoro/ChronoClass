<?php

namespace App\Http\Controllers;

use App\Models\GrupoMateria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class GrupoMateriaController extends Controller
{
    public function index()
    {
        return Inertia::render('GruposMaterias/Index', [
            'grupos' => GrupoMateria::with(['materias', 'professores'])->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('GruposMaterias/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:grupos_materias,nome',
            'descricao' => 'nullable|string',
        ]);

        GrupoMateria::create($validated);

        return Redirect::route('grupos_materias.index')
            ->with('message', 'Grupo de matérias criado com sucesso!');
    }

    public function edit(GrupoMateria $grupoMateria)
    {
        $grupoMateria->load(['materias', 'professores']);
        
        return Inertia::render('GruposMaterias/Edit', [
            'grupo' => $grupoMateria,
        ]);
    }

    public function update(Request $request, GrupoMateria $grupoMateria)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:grupos_materias,nome,' . $grupoMateria->id,
            'descricao' => 'nullable|string',
        ]);

        $grupoMateria->update($validated);

        return Redirect::route('grupos_materias.index')
            ->with('message', 'Grupo de matérias atualizado com sucesso!');
    }

    public function destroy(GrupoMateria $grupoMateria)
    {
        $grupoMateria->delete();

        return Redirect::route('grupos_materias.index')
            ->with('message', 'Grupo de matérias deletado com sucesso!');
    }

    public function updateWithPost(Request $request, GrupoMateria $grupoMateria)
    {
        return $this->update($request, $grupoMateria);
    }

    public function destroyWithPost(GrupoMateria $grupoMateria)
    {
        return $this->destroy($grupoMateria);
    }
}
