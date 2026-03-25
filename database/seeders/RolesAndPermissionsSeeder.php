<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'products.view',
            'products.manage',
            'suppliers.manage',
            'purchases.manage',
            'customers.view',
            'customers.manage',
            'sales.create',
            'sales.view',
            'returns.manage',
            'recovery.entry',
            'accounts.close',
            'accounts.transfer',
            'installments.update',
            'reports.view',
            'financial.view',
            'settings.manage',
            'users.manage',
            'backup.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        // Owner — all permissions
        $owner = Role::findOrCreate('owner');
        $owner->syncPermissions($permissions);

        // Sale Man — sales-related
        $saleMan = Role::findOrCreate('sale_man');
        $saleMan->syncPermissions([
            'dashboard.view',
            'products.view',
            'customers.view',
            'customers.manage',
            'sales.create',
            'sales.view',
            'returns.manage',
        ]);

        // Recovery Man — recovery-related
        $recoveryMan = Role::findOrCreate('recovery_man');
        $recoveryMan->syncPermissions([
            'dashboard.view',
            'customers.view',
            'recovery.entry',
        ]);

        // Admin account is created by the FirstRunSetup wizard, not here.
    }
}
