<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfessorController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rotas Destrinchadas para Professores

    // Lista de todos os professores (GET /professores)
    Route::get('/professores', [ProfessorController::class, 'index'])->name('professores.index');

    // Formulário para criar um novo professor (GET /professores/create)
    Route::get('/professores/create', [ProfessorController::class, 'create'])->name('professores.create');

    // Armazenar um novo professor (POST /professores)
    Route::post('/professores', [ProfessorController::class, 'store'])->name('professores.store');

    // Formulário para editar um professor existente (GET /professores/{professor}/edit)
    Route::get('/professores/{professor}/edit', [ProfessorController::class, 'edit'])->name('professores.edit');

    // ATENÇÃO: Mantemos Route::put e Route::patch.
    // O Inertia/Laravel usará o campo _method='PUT' para direcionar para este método.
    Route::put('/professores/{professor}', [ProfessorController::class, 'update'])->name('professores.update');
    Route::patch('/professores/{professor}', [ProfessorController::class, 'update']); // Alias para update

    // Excluir um professor (DELETE /professores/{professor}) - Mantida por compatibilidade
    Route::delete('/professores/{professor}', [ProfessorController::class, 'destroy'])->name('professores.destroy');

    // Rota para exclusão usando o método POST (do seu ajuste anterior)
    Route::post('/professores/{professor}/delete-post', [ProfessorController::class, 'destroy'])->name('professores.delete-post');

    // NOVA ROTA PARA ATUALIZAÇÃO USANDO O MÉTODO POST
    // Esta rota permite que você envie uma requisição POST para atualizar um professor.
    // No Vue, você usará form.post() para esta URL e incluirá um campo _method com o valor 'put'.
    Route::post('/professores/{professor}/update-post', [ProfessorController::class, 'update'])->name('professores.update-post');
});

require __DIR__ . '/auth.php';

