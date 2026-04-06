<?php

namespace App\Http\Controllers;

use App\Models\Sala; // Importe o modelo Sala
use Illuminate\Http\Request;
use Inertia\Inertia; // Importe o Inertia

class SalaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $salas = Sala::orderBy('nome')->paginate(10);
        return Inertia::render('Salas/Index', [
            'salas' => $salas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Poderíamos passar opções de campus aqui se necessário, mas o enum é fixo por enquanto
        return Inertia::render('Salas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:salas,nome',
            'capacidade' => 'required|integer|min:1',
            'campus' => 'required|in:campus ipolon,campus sede',
        ], [
            'nome.required' => 'Preencha o nome da sala para enviar.',
            'nome.unique' => 'Já existe uma sala com este nome.',
            'capacidade.required' => 'Preencha a capacidade da sala para enviar.',
            'capacidade.min' => 'A capacidade deve ser no mínimo 1.',
            'campus.required' => 'Selecione o campus da sala para enviar.',
            'campus.in' => 'Selecione um campus válido.',
        ]);

        Sala::create($request->all());

        return redirect()->route('salas.index')->with('success', 'Sala criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sala $sala) // Injeção de modelo para buscar a sala automaticamente
    {
        return Inertia::render('Salas/Edit', [
            'sala' => $sala, // Passa a sala encontrada para o componente Vue
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sala $sala) // Injeção de modelo
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:salas,nome,' . $sala->id,
            'capacidade' => 'required|integer|min:1',
            'campus' => 'required|in:campus ipolon,campus sede',
        ], [
            'nome.required' => 'Preencha o nome da sala para enviar.',
            'nome.unique' => 'Já existe uma sala com este nome.',
            'capacidade.required' => 'Preencha a capacidade da sala para enviar.',
            'capacidade.min' => 'A capacidade deve ser no mínimo 1.',
            'campus.required' => 'Selecione o campus da sala para enviar.',
            'campus.in' => 'Selecione um campus válido.',
        ]);

        $sala->update($request->all());

        return redirect()->route('salas.index')->with('success', 'Sala atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sala $sala) // Injeção de modelo
    {
        $sala->delete();

        return redirect()->route('salas.index')->with('success', 'Sala excluída com sucesso!');
    }
    
    public function updateWithPost(Request $request, Sala $sala)
{
    // Reutiliza a mesma lógica do método update original
    return $this->update($request, $sala);
}

/**
 * Remove the specified resource from storage using a POST request.
 */
public function destroyWithPost(Sala $sala)
{
    // Reutiliza a mesma lógica do método destroy original
    return $this->destroy($sala);
}
}