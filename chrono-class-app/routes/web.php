<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\GradeHorarioController;
use App\Http\Controllers\MateriaController;

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

    // Rotas para Professores, Salas, Grade Horarios (conforme seu anexo original)
    // Mantê-las como estão se já funcionam.
    Route::get('/professores', [ProfessorController::class, 'index'])->name('professores.index');
    Route::get('/professores/create', [ProfessorController::class, 'create'])->name('professores.create');
    Route::post('/professores', [ProfessorController::class, 'store'])->name('professores.store');
    Route::get('/professores/{professor}/edit', [ProfessorController::class, 'edit'])->name('professores.edit');
    Route::put('/professores/{professor}', [ProfessorController::class, 'update'])->name('professores.update');
    Route::patch('/professores/{professor}', [ProfessorController::class, 'update']);
    Route::delete('/professores/{professor}', [ProfessorController::class, 'destroy'])->name('professores.destroy');
    Route::post('/professores/{professor}/delete-post', [ProfessorController::class, 'destroy'])->name('professores.delete-post');
    Route::post('/professores/{professor}/update-post', [ProfessorController::class, 'update'])->name('professores.update-post');

    Route::resource('salas', SalaController::class);
    Route::post('/salas/{sala}/delete-post', [SalaController::class, 'destroy'])->name('salas.delete-post');

    Route::resource('grade_horarios', GradeHorarioController::class);

    // Rota de recurso para matérias (esta é a rota que define o PUT/DELETE para o Laravel)
    Route::resource('materias', MateriaController::class);

    // Rota para importação (essa já é POST)
    Route::post('/materias/import', [MateriaController::class, 'import'])->name('materias.import');
});

require __DIR__ . '/auth.php';