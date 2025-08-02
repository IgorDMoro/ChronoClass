<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\GradeHorarioController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\TurmaController;

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

    Route::get('/professores', [ProfessorController::class, 'index'])->name('professores.index');
    Route::get('/professores/create', [ProfessorController::class, 'create'])->name('professores.create');
    Route::post('/professores', [ProfessorController::class, 'store'])->name('professores.store');
    Route::get('/professores/{professor}/edit', [ProfessorController::class, 'edit'])->name('professores.edit');

    // Use estas rotas POST para update e destroy
    Route::post('/professores/{professor}/update-post', [ProfessorController::class, 'update'])->name('professores.update-post');
    Route::post('/professores/{professor}/delete-post', [ProfessorController::class, 'destroy'])->name('professores.delete-post');


    // Suas rotas originais de Salas (restauradas)
    Route::resource('salas', SalaController::class);
    Route::post('/salas/{sala}/delete-post', [SalaController::class, 'destroy'])->name('salas.delete-post');

    // Suas rotas originais de Grade Horarios (restauradas)
    Route::resource('grade_horarios', GradeHorarioController::class);

    // Suas rotas originais de Matérias (restauradas)
    Route::resource('materias', MateriaController::class);
    Route::post('/materias/import', [MateriaController::class, 'import'])->name('materias.import');

    // ROTAS DE TURMAS AJUSTADAS CONFORME SOLICITADO
    Route::get('/turmas', [TurmaController::class, 'index'])->name('turmas.index');
    Route::get('/turmas/create', [TurmaController::class, 'create'])->name('turmas.create');
    Route::post('/turmas', [TurmaController::class, 'store'])->name('turmas.store');
    Route::get('/turmas/{turma}/edit', [TurmaController::class, 'edit'])->name('turmas.edit');


    Route::post('/turmas/{turma}', [TurmaController::class, 'update'])->name('turmas.update');


    Route::post('/turmas/{turma}/delete', [TurmaController::class, 'destroy'])->name('turmas.destroy');
});

require __DIR__ . '/auth.php';
