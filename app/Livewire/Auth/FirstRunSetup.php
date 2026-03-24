<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.auth')]
class FirstRunSetup extends Component
{
    public string $name = '';

    public string $username = 'admin';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $recoveryKey = null;

    public function setup(): void
    {
        if (User::count() > 0) {
            $this->redirect(route('login'));

            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Ensure roles exist
        if (Role::count() === 0) {
            Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder', '--no-interaction' => true]);
        }

        // Generate recovery key
        $recoveryKey = strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4).'-'.Str::random(4).'-'.Str::random(4).'-'.Str::random(4));

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'password' => $this->password,
            'role' => 'owner',
            'is_active' => true,
            'recovery_key' => bcrypt($recoveryKey),
        ]);

        $user->assignRole('owner');

        $this->recoveryKey = $recoveryKey;
    }

    public function continueToApp(): void
    {
        $user = User::where('role', 'owner')->first();
        Auth::login($user, true);
        $user->update(['last_login_at' => now()]);

        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        if (User::count() > 0 && ! $this->recoveryKey) {
            $this->redirect(route('login'));
        }

        return view('livewire.auth.first-run-setup');
    }
}
