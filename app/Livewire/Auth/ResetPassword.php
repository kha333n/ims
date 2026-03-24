<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\HardwareFingerprint;
use App\Services\LicenseManager;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ResetPassword extends Component
{
    public string $method = ''; // 'recovery_key' or 'support_code'

    public string $recovery_key = '';

    public string $support_code = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public string $error = '';

    public bool $success = false;

    public function resetWithRecoveryKey(): void
    {
        $this->validate([
            'recovery_key' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $owner = User::where('role', 'owner')->first();
        if (! $owner || ! $owner->recovery_key) {
            $this->error = 'No owner account found or recovery key not set.';

            return;
        }

        if (! Hash::check($this->recovery_key, $owner->recovery_key)) {
            $this->error = 'Invalid recovery key.';

            return;
        }

        $owner->update(['password' => $this->new_password]);
        $this->success = true;
    }

    public function resetWithSupportCode(): void
    {
        $this->validate([
            'support_code' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Support code = HMAC(hardware_id | date_hour, license_key)
        // Valid for the current hour only
        $hardwareId = app(HardwareFingerprint::class)->generate();
        $licenseData = app(LicenseManager::class)->getLicenseData();
        $licenseKey = $licenseData['key'] ?? '';

        $valid = false;
        // Check current hour and previous hour (grace period)
        for ($offset = 0; $offset <= 1; $offset++) {
            $dateHour = now()->subHours($offset)->format('Y-m-d-H');
            $expected = strtoupper(substr(hash_hmac('sha256', $hardwareId.'|'.$dateHour, $licenseKey), 0, 12));

            if (strtoupper(trim($this->support_code)) === $expected) {
                $valid = true;
                break;
            }
        }

        if (! $valid) {
            $this->error = 'Invalid or expired support code. Codes are valid for 1 hour.';

            return;
        }

        $owner = User::where('role', 'owner')->first();
        if (! $owner) {
            $this->error = 'No owner account found.';

            return;
        }

        $owner->update(['password' => $this->new_password]);
        $this->success = true;
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
