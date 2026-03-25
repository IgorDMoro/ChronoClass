<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\Turma;
use App\Models\Sala;
use App\Models\Grade;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
{
    return Inertia::render('Dashboard', [
        'grades' => \App\Models\Grade::with(['turma', 'horarios.materia'])->get(),
    ]);
}
}