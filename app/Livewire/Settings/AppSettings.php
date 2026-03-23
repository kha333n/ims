<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AppSettings extends Component
{
    public string $company_name = '';

    public string $company_address = '';

    public string $company_phone = '';

    public int $defaulter_days = 30;

    public ?array $actionSummary = null;

    public function mount(): void
    {
        $this->company_name = Setting::get('company_name', '');
        $this->company_address = Setting::get('company_address', '');
        $this->company_phone = Setting::get('company_phone', '');
        $this->defaulter_days = (int) Setting::get('defaulter_days', 30);
    }

    public function save(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
            'defaulter_days' => 'required|integer|min:1',
        ]);

        Setting::set('company_name', $this->company_name);
        Setting::set('company_address', $this->company_address);
        Setting::set('company_phone', $this->company_phone);
        Setting::set('defaulter_days', $this->defaulter_days);

        $this->actionSummary = ['action' => 'Settings Saved', 'detail' => $this->company_name];
    }

    public function render()
    {
        return view('livewire.settings.app-settings');
    }
}
