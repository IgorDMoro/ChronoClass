<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\GradeHorarioController; 

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
    Route::get('/professores', [ProfessorController::class, 'index'])->name('professores.index');
    Route::get('/professores/create', [ProfessorController::class, 'create'])->name('professores.create');
    Route::post('/professores', [ProfessorController::class, 'store'])->name('professores.store');
    Route::get('/professores/{professor}/edit', [ProfessorController::class, 'edit'])->name('professores.edit');
    Route::put('/professores/{professor}', [ProfessorController::class, 'update'])->name('professores.update');
    Route::patch('/professores/{professor}', [ProfessorController::class, 'update']);
    Route::delete('/professores/{professor}', [ProfessorController::class, 'destroy'])->name('professores.destroy');
    Route::post('/professores/{professor}/delete-post', [ProfessorController::class, 'destroy'])->name('professores.delete-post');
    Route::post('/professores/{professor}/update-post', [ProfessorController::class, 'update'])->name('professores.update-post');

    // Rotas de Recurso para Salas
    Route::resource('salas', SalaController::class);
    Route::post('/salas/{sala}/delete-post', [SalaController::class, 'destroy'])->name('salas.delete-post');

    Route::resource('grade_horarios', GradeHorarioController::class);
    // Route::post('/grade_horarios/{grade_horario}/delete-post', [GradeHorarioController::class, 'destroy'])->name('grade_horarios.delete-post');
    

});

require __DIR__ . '/auth.php';