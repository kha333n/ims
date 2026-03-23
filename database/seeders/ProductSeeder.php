<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = Supplier::first();

        $products = [
            ['name' => 'AC DC',   'price' => 3000000, 'quantity' => 15, 'category' => 'Electronics'],
            ['name' => 'Buffer',  'price' => 500000,  'quantity' => 30, 'category' => 'Accessories'],
            ['name' => 'JCR',     'price' => 2500000, 'quantity' => 12, 'category' => 'Electronics'],
            ['name' => 'LED',     'price' => 4500000, 'quantity' => 20, 'category' => 'Electronics'],
            ['name' => 'IRN',     'price' => 1500000, 'quantity' => 25, 'category' => 'Appliances'],
            ['name' => 'Mobile',  'price' => 6000000, 'quantity' => 18, 'category' => 'Mobile'],
            ['name' => 'KBL',     'price' => 800000,  'quantity' => 40, 'category' => 'Accessories'],
            ['name' => 'CF',      'price' => 1200000, 'quantity' => 22, 'category' => 'Appliances'],
            ['name' => 'DNS',     'price' => 2000000, 'quantity' => 10, 'category' => 'Electronics'],
            ['name' => 'Blnd',    'price' => 1800000, 'quantity' => 16, 'category' => 'Appliances'],
        ];

        foreach ($products as $data) {
            Product::create(array_merge($data, ['supplier_id' => $supplier?->id]));
        }
    }
}
