<?php

namespace App\Livewire\Auth;

use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email    = '';
    public string $password = '';
    public bool $remember   = false;

    protected $rules = [
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ];

    public function login(): void
    {
        $this->validate();

        if (!Auth::attempt([
            'email'    => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            $this->addError('email', 'Email ou mot de passe incorrect.');
            return;
        }

        // Vérifier si le compte est actif
        if (!Auth::user()->is_active) {
            Auth::logout();
            $this->addError('email', 'Votre compte a été désactivé. Contactez l\'administrateur.');
            return;
        }

        session()->regenerate();

        ActivityLogService::log(
            action: 'login',
            description: 'Connexion — ' . Auth::user()->name,
            model: Auth::user(),
        );

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.guest');
    }
}
