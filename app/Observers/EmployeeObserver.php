<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Role;

class EmployeeObserver
{
    public function created(Employee $employee): void
    {
        // Auto-create linked user account
        $role = $employee->type === 'sale_man' ? 'sale_man' : 'recovery_man';
        $username = ($role === 'sale_man' ? 'sm-' : 'rm-').$employee->id;

        // Use CNIC as default password, or a generated one
        $defaultPassword = $employee->cnic ?: 'ims'.$employee->id;

        $user = User::create([
            'name' => $employee->name,
            'username' => $username,
            'password' => $defaultPassword,
            'role' => $role,
            'employee_id' => $employee->id,
            'is_active' => true,
        ]);

        if (Role::where('name', $role)->exists()) {
            $user->assignRole($role);
        }
    }

    public function updated(Employee $employee): void
    {
        // Sync name to linked user
        $user = User::where('employee_id', $employee->id)->first();
        if ($user) {
            $user->update(['name' => $employee->name]);
        }
    }

    public function deleted(Employee $employee): void
    {
        // Deactivate linked user (soft delete)
        User::where('employee_id', $employee->id)->update(['is_active' => false]);
    }

    public function restored(Employee $employee): void
    {
        User::where('employee_id', $employee->id)->update(['is_active' => true]);
    }
}
