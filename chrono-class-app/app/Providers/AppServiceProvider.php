<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia; // Importe a classe Inertia
use Illuminate\Support\Facades\Auth; // Importe a classe Auth

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3); // Mantenha esta linha se desejar

        // Adicione estas linhas para compartilhar os dados de autenticação com o Inertia
        Inertia::share([
            'auth' => fn () => [
                'user' => Auth::user() ? [
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    // Se você precisar de outras propriedades do usuário no frontend, adicione-as aqui.
                    // Por exemplo: 'is_admin' => Auth::user()->is_admin,
                ] : null, // Se o usuário não estiver logado, 'user' será null
            ],
            // Você pode adicionar outras props globais que queira acessar em todas as páginas
            'flash' => fn () => [
                'message' => session('message'),
                'error' => session('error'),
            ],
        ]);
    }
}