<?php

namespace App\Livewire\Auth;

use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.auth')]
class FirstRunSetup extends Component
{
    // Step tracking: 1=Company, 2=Owner Account, 3=Recovery Key, 4=License
    public int $step = 1;

    // Step 1: Company details
    public string $company_name = '';

    public string $company_address = '';

    public string $company_phone = '';

    // Step 2: Owner account
    public string $name = '';

    public string $username = 'admin';

    public string $password = '';

    public string $password_confirmation = '';

    // Step 3: Recovery key display
    public ?string $recoveryKey = null;

    // Step 4: License
    public string $licenseKey = '';

    public ?array $licenseResult = null;

    public function saveCompany(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
        ]);

        Setting::set('company_name', $this->company_name);
        Setting::set('company_address', $this->company_address);
        Setting::set('company_phone', $this->company_phone);

        $this->step = 2;
    }

    public function createOwner(): void
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

        if (Role::count() === 0) {
            Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder', '--force' => true, '--no-interaction' => true]);
        }

        $recoveryKey = strtoupper(
            Str::random(4).'-'.Str::random(4).'-'.Str::random(4).'-'.
            Str::random(4).'-'.Str::random(4).'-'.Str::random(4)
        );

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'password' => $this->password,
            'role' => 'owner',
            'is_active' => true,
            'recovery_key' => bcrypt($recoveryKey),
        ]);

        $user->assignRole('owner');

        // Seed demo data if IMS_DEMO_SEED=true in .env
        // (NativePHP overrides APP_DEBUG at runtime, so we use our own flag)
        if (config('ims.demo_seed')) {
            Artisan::call('db:seed', ['--class' => 'SupplierSeeder', '--force' => true, '--no-interaction' => true]);
            Artisan::call('db:seed', ['--class' => 'ProductSeeder', '--force' => true, '--no-interaction' => true]);
            Artisan::call('db:seed', ['--class' => 'EmployeeSeeder', '--force' => true, '--no-interaction' => true]);
            Artisan::call('db:seed', ['--class' => 'CustomerSeeder', '--force' => true, '--no-interaction' => true]);
            Artisan::call('db:seed', ['--class' => 'DemoDataSeeder', '--force' => true, '--no-interaction' => true]);
        }

        $this->recoveryKey = $recoveryKey;
        $this->step = 3;
    }

    public function proceedToLicense(): void
    {
        $this->step = 4;
    }

    public function activateLicense(): void
    {
        $this->validate([
            'licenseKey' => 'required|string|min:8',
        ]);

        $manager = app(LicenseManager::class);
        $result = $manager->activate($this->licenseKey);

        if ($result['success']) {
            $this->licenseResult = [
                'type' => 'success',
                'message' => $result['message'],
            ];
        } else {
            $this->licenseResult = [
                'type' => 'error',
                'message' => $result['message'],
            ];
        }
    }

    public function continueToApp(): void
    {
        $user = User::where('role', 'owner')->first();

        if ($user) {
            Auth::login($user, true);
            $user->update(['last_login_at' => now()]);
        }

        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        if (User::count() > 0 && $this->step < 3) {
            $manager = app(LicenseManager::class);
            if ($manager->isValid()) {
                $this->redirect(route('dashboard'));
            } else {
                $this->redirect(route('license'));
            }
        }

        return view('livewire.auth.first-run-setup');
    }
}
