<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\GradeController; // Adicionado

// --- Rotas Públicas ---

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/auth/google', [GoogleLoginController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');

// --- Rotas Autenticadas ---

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Professores
    Route::get('/professores', [ProfessorController::class, 'index'])->name('professores.index');
    Route::get('/professores/create', [ProfessorController::class, 'create'])->name('professores.create');
    Route::post('/professores', [ProfessorController::class, 'store'])->name('professores.store');
    Route::get('/professores/{professor}/edit', [ProfessorController::class, 'edit'])->name('professores.edit');
    Route::post('/professores/{professor}/update-post', [ProfessorController::class, 'updateWithPost'])->name('professores.update-post');
    Route::post('/professores/{professor}/delete-post', [ProfessorController::class, 'destroyWithPost'])->name('professores.delete-post');

    // Salas
    Route::get('/salas', [SalaController::class, 'index'])->name('salas.index');
    Route::get('/salas/create', [SalaController::class, 'create'])->name('salas.create');
    Route::post('/salas', [SalaController::class, 'store'])->name('salas.store');
    Route::get('/salas/{sala}/edit', [SalaController::class, 'edit'])->name('salas.edit');
    Route::post('/salas/{sala}/update-post', [SalaController::class, 'updateWithPost'])->name('salas.update-post');
    Route::post('/salas/{sala}/delete-post', [SalaController::class, 'destroyWithPost'])->name('salas.delete-post');

    // Matérias
    Route::get('/materias', [MateriaController::class, 'index'])->name('materias.index');
    Route::get('/materias/create', [MateriaController::class, 'create'])->name('materias.create');
    Route::post('/materias', [MateriaController::class, 'store'])->name('materias.store');
    Route::get('/materias/{materia}/edit', [MateriaController::class, 'edit'])->name('materias.edit');
    Route::post('/materias/import', [MateriaController::class, 'import'])->name('materias.import');
    Route::post('/materias/{materia}/update-post', [MateriaController::class, 'updateWithPost'])->name('materias.update-post');
    Route::post('/materias/{materia}/delete-post', [MateriaController::class, 'destroyWithPost'])->name('materias.delete-post');

    // Turmas
    Route::get('/turmas', [TurmaController::class, 'index'])->name('turmas.index');
    Route::get('/turmas/create', [TurmaController::class, 'create'])->name('turmas.create');
    Route::post('/turmas', [TurmaController::class, 'store'])->name('turmas.store');
    Route::get('/turmas/{turma}/edit', [TurmaController::class, 'edit'])->name('turmas.edit');
    Route::post('/turmas/{turma}/update-post', [TurmaController::class, 'updateWithPost'])->name('turmas.update-post');
    Route::post('/turmas/{turma}/delete-post', [TurmaController::class, 'destroyWithPost'])->name('turmas.delete-post');

    
Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
Route::get('/grades/create', [GradeController::class, 'create'])->name('grades.create');
Route::post('/grades', [GradeController::class, 'store'])->name('grades.store');
Route::get('/grades/{grade}', [GradeController::class, 'show'])->name('grades.show');
Route::get('/grades/{grade}/edit', [GradeController::class, 'edit'])->name('grades.edit');
Route::post('/grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');
Route::post('/grades/{grade}/pin', [GradeController::class, 'pin'])->name('grades.pin');

});

require __DIR__ . '/auth.php';