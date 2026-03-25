<?php

namespace App\Livewire\Help;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UserManual extends Component
{
    public function render()
    {
        return view('livewire.help.user-manual');
    }
}
