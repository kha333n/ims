<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function createOwner(array $attrs = []): User
    {
        $this->seedRolesIfNeeded();

        $user = User::create(array_merge([
            'name' => 'Test Owner',
            'username' => 'admin',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ], $attrs));

        $user->assignRole('owner');

        return $user;
    }

    protected function actingAsOwner(array $attrs = []): User
    {
        $user = $this->createOwner($attrs);
        $this->actingAs($user);

        return $user;
    }

    private function seedRolesIfNeeded(): void
    {
        if (Role::count() === 0) {
            $permissions = [
                'dashboard.view', 'products.view', 'products.manage', 'suppliers.manage',
                'purchases.manage', 'customers.view', 'customers.manage', 'sales.create',
                'sales.view', 'returns.manage', 'recovery.entry', 'accounts.close',
                'accounts.transfer', 'installments.update', 'reports.view', 'financial.view',
                'settings.manage', 'users.manage', 'backup.manage',
            ];

            foreach ($permissions as $perm) {
                Permission::findOrCreate($perm);
            }

            $owner = Role::findOrCreate('owner');
            $owner->syncPermissions($permissions);

            $saleMan = Role::findOrCreate('sale_man');
            $saleMan->syncPermissions(['dashboard.view', 'products.view', 'customers.view', 'customers.manage', 'sales.create', 'sales.view', 'returns.manage']);

            $recoveryMan = Role::findOrCreate('recovery_man');
            $recoveryMan->syncPermissions(['dashboard.view', 'customers.view', 'recovery.entry']);
        }
    }
}
