<?php

namespace App\Http\Controllers;

use App\Models\Materia;
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
            'materias' => Materia::paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Materias/Create');
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
        ], [
            'codigo.required' => 'Preencha o código da UC para enviar.',
            'codigo.unique' => 'Já existe uma UC com este código.',
            'nome.required' => 'Preencha o nome da UC para enviar.',
            'carga_horaria.required' => 'Preencha a carga horária da UC para enviar.',
            'modalidade.required' => 'Selecione a modalidade da UC para enviar.',
            'comp_tipo.required' => 'Selecione o tipo Comp. da UC para enviar.',
            'ensw_tipo.required' => 'Selecione o tipo Ensw. da UC para enviar.',
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
        ], [
            'codigo.required' => 'Preencha o código da UC para enviar.',
            'codigo.unique' => 'Já existe uma UC com este código.',
            'nome.required' => 'Preencha o nome da UC para enviar.',
            'carga_horaria.required' => 'Preencha a carga horária da UC para enviar.',
            'modalidade.required' => 'Selecione a modalidade da UC para enviar.',
            'comp_tipo.required' => 'Selecione o tipo Comp. da UC para enviar.',
            'ensw_tipo.required' => 'Selecione o tipo Ensw. da UC para enviar.',
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