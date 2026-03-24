<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            EmployeeSeeder::class,
            CustomerSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
