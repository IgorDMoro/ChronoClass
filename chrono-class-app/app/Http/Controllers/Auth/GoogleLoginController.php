<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::updateOrCreate([
                'google_id' => $googleUser->id,
            ], [
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'avatar' => $googleUser->avatar,
                'password' => bcrypt('password') 
            ]);

            Auth::login($user, true);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            // Tratar o erro, talvez redirecionar para a página de login com uma mensagem
            return redirect()->route('login')->with('error', 'Ocorreu um erro ao tentar fazer login com o Google.');
        }
    }
}