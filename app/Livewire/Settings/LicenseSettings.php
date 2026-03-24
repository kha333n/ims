<?php

namespace App\Livewire\Settings;

use App\Services\LicenseManager;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LicenseSettings extends Component
{
    public string $licenseKey = '';

    public ?array $actionSummary = null;

    public bool $showDeactivateConfirm = false;

    public function activate(): void
    {
        $this->validate([
            'licenseKey' => 'required|string|min:8',
        ]);

        $manager = app(LicenseManager::class);
        $result = $manager->activate($this->licenseKey);

        if ($result['success']) {
            $this->actionSummary = [
                'type' => 'success',
                'title' => 'License Activated',
                'message' => $result['message'],
            ];
            $this->licenseKey = '';
        } else {
            $this->actionSummary = [
                'type' => 'error',
                'title' => 'Activation Failed',
                'message' => $result['message'],
            ];
        }
    }

    public function deactivate(): void
    {
        $manager = app(LicenseManager::class);
        $result = $manager->deactivate();

        $this->showDeactivateConfirm = false;

        if ($result['success']) {
            $this->actionSummary = [
                'type' => 'success',
                'title' => 'License Deactivated',
                'message' => $result['message'],
            ];
        } else {
            $this->actionSummary = [
                'type' => 'error',
                'title' => 'Deactivation Failed',
                'message' => $result['message'],
            ];
        }
    }

    public function refreshOnline(): void
    {
        $manager = app(LicenseManager::class);

        if ($manager->verifyOnline()) {
            $this->actionSummary = [
                'type' => 'success',
                'title' => 'License Verified',
                'message' => 'Online verification successful.',
            ];
        } else {
            $this->actionSummary = [
                'type' => 'error',
                'title' => 'Verification Failed',
                'message' => 'Could not verify license online. Check your internet connection.',
            ];
        }
    }

    public function render()
    {
        $manager = app(LicenseManager::class);

        return view('livewire.settings.license-settings', [
            'status' => $manager->getStatus(),
        ]);
    }
}
