<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Profile extends Component
{
    public string $name = '';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public ?array $actionSummary = null;

    public function mount(): void
    {
        $this->name = auth()->user()->name;
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        auth()->user()->update(['name' => $this->name]);
        $this->actionSummary = ['action' => 'Profile Updated', 'detail' => $this->name];
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');

            return;
        }

        auth()->user()->update(['password' => $this->new_password]);
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->actionSummary = ['action' => 'Password Changed', 'detail' => 'Your password has been updated.'];
    }

    public function render()
    {
        return view('livewire.auth.profile');
    }
}
