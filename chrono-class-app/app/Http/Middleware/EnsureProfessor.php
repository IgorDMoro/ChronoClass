<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfessor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isProfessor()) {
            return redirect()->route('dashboard')
                ->with('message', 'Acesso restrito a professores.');
        }

        return $next($request);
    }
}
