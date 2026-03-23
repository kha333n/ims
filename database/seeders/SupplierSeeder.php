<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Hassan Electronics',  'phone' => '051-2345678', 'address' => 'Raja Bazaar, Rawalpindi'],
            ['name' => 'Malik Traders',        'phone' => '051-4567890', 'address' => 'Saddar, Rawalpindi'],
            ['name' => 'Al-Noor Distributors', 'phone' => '051-3456789', 'address' => 'Committee Chowk, Rawalpindi'],
        ];

        foreach ($suppliers as $data) {
            Supplier::create($data);
        }
    }
}
