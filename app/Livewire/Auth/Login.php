<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    public string $username = '';

    public string $password = '';

    public string $error = '';

    public function login(): void
    {
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $this->username)->first();

        if (! $user) {
            $this->error = 'Invalid username or password.';

            return;
        }

        if (! $user->is_active) {
            $this->error = 'Your account has been deactivated. Contact the administrator.';

            return;
        }

        if (! Auth::attempt(['username' => $this->username, 'password' => $this->password], true)) {
            $this->error = 'Invalid username or password.';

            return;
        }

        $user->update(['last_login_at' => now()]);

        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
