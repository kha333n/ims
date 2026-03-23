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
            ['name' => 'AC DC',   'sale_price' => 3000000, 'purchase_price' => 2400000, 'quantity' => 15, 'category' => 'Electronics'],
            ['name' => 'Buffer',  'sale_price' => 500000,  'purchase_price' => 350000,  'quantity' => 30, 'category' => 'Accessories'],
            ['name' => 'JCR',     'sale_price' => 2500000, 'purchase_price' => 2000000, 'quantity' => 12, 'category' => 'Electronics'],
            ['name' => 'LED',     'sale_price' => 4500000, 'purchase_price' => 3600000, 'quantity' => 20, 'category' => 'Electronics'],
            ['name' => 'IRN',     'sale_price' => 1500000, 'purchase_price' => 1100000, 'quantity' => 25, 'category' => 'Appliances'],
            ['name' => 'Mobile',  'sale_price' => 6000000, 'purchase_price' => 5000000, 'quantity' => 18, 'category' => 'Mobile'],
            ['name' => 'KBL',     'sale_price' => 800000,  'purchase_price' => 600000,  'quantity' => 40, 'category' => 'Accessories'],
            ['name' => 'CF',      'sale_price' => 1200000, 'purchase_price' => 900000,  'quantity' => 22, 'category' => 'Appliances'],
            ['name' => 'DNS',     'sale_price' => 2000000, 'purchase_price' => 1500000, 'quantity' => 10, 'category' => 'Electronics'],
            ['name' => 'Blnd',    'sale_price' => 1800000, 'purchase_price' => 1300000, 'quantity' => 16, 'category' => 'Appliances'],
        ];

        foreach ($products as $data) {
            Product::create(array_merge($data, ['supplier_id' => $supplier?->id]));
        }
    }
}
