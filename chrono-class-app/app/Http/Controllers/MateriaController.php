<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\GrupoMateria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MateriasImport;

class MateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Materias/Index', [
            'materias' => Materia::with('grupo')->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Materias/Create', [
            'grupos' => GrupoMateria::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:255|unique:materias,codigo',
            'nome' => 'required|string|max:255',
            'carga_horaria' => 'required|integer|min:0',
            'modalidade' => ['required', 'string', Rule::in(['Presencial', 'UCD'])],
            'comp_tipo' => ['required', 'string', Rule::in(['Core', 'Flex'])],
            'ensw_tipo' => ['required', 'string', Rule::in(['Core', 'Flex'])],
            'grupo_id' => 'nullable|exists:grupos_materias,id',
        ]);

        Materia::create($validated);

        return redirect()->route('materias.index')
                         ->with('success', 'Matéria criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Materia $materia)
    {
        return Inertia::render('Materias/Show', [
            'materia' => $materia,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Materia $materia)
    {
        return Inertia::render('Materias/Edit', [
            'materia' => $materia,
            'grupos' => GrupoMateria::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Materia $materia)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:255', Rule::unique('materias')->ignore($materia->id)],
            'nome' => 'required|string|max:255',
            'carga_horaria' => 'required|integer|min:0',
            'modalidade' => ['required', 'string', Rule::in(['Presencial', 'UCD'])],
            'comp_tipo' => ['required', 'string', Rule::in(['Core', 'Flex'])],
            'ensw_tipo' => ['required', 'string', Rule::in(['Core', 'Flex'])],
            'grupo_id' => 'nullable|exists:grupos_materias,id',
        ]);

        $materia->update($validated);

        return redirect()->route('materias.index')
                         ->with('success', 'Matéria atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Materia $materia)
    {
        $materia->delete();

        return redirect()->route('materias.index')
                         ->with('success', 'Matéria excluída com sucesso!');
    }

    public function updateWithPost(Request $request, Materia $materia)
    {
        return $this->update($request, $materia);
    }

    public function destroyWithPost(Materia $materia)
    {
        return $this->destroy($materia);
    }

    /**
     * Handle the import of materias from a file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new MateriasImport, $request->file('file'));
            return redirect()->route('materias.index')
                             ->with('success', 'Matérias importadas com sucesso!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Linha " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            return redirect()->back()
                             ->with('error', 'Erro de validação na importação: ' . implode(" | ", $errors));
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Erro ao importar matérias: ' . $e->getMessage());
        }
    }
}