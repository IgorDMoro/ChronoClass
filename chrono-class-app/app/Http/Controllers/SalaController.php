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
        $salas = Sala::all(); // Busca todas as salas
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
            'capacidade' => 'required|integer|min:1', // Capacidade deve ser no mínimo 1
            'campus' => 'required|in:campus ipolon,campus sede', // Deve ser um dos valores do enum
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
            'nome' => 'required|string|max:255|unique:salas,nome,' . $sala->id, // Ignora o nome da própria sala na validação unique
            'capacidade' => 'required|integer|min:1',
            'campus' => 'required|in:campus ipolon,campus sede',
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
}