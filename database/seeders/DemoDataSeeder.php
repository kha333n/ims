<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $saleMen = Employee::saleMen()->get();
        $recoveryMen = Employee::recoveryMen()->get();

        if ($customers->isEmpty() || $products->isEmpty() || $saleMen->isEmpty() || $recoveryMen->isEmpty()) {
            $this->command->warn('Run SupplierSeeder, ProductSeeder, EmployeeSeeder, CustomerSeeder first.');

            return;
        }

        $installmentTypes = ['Daily', 'Weekly', 'Monthly'];

        for ($i = 0; $i < 30; $i++) {
            $customer = $customers->random();
            $product = $products->random();
            $saleMan = $saleMen->random();
            $recoveryMan = $recoveryMen->random();
            $type = $installmentTypes[array_rand($installmentTypes)];
            $total = $product->sale_price * rand(1, 2);
            $advance = (int) ($total * 0.1);
            $remaining = $total - $advance;
            $instAmt = (int) ($remaining / rand(10, 24));
            $saleDate = now()->subDays(rand(10, 180));

            $account = Account::create([
                'customer_id' => $customer->id,
                'sale_man_id' => $saleMan->id,
                'recovery_man_id' => $recoveryMan->id,
                'slip_number' => 'SL-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'sale_date' => $saleDate->toDateString(),
                'total_amount' => $total,
                'advance_amount' => $advance,
                'discount_amount' => 0,
                'remaining_amount' => $remaining,
                'installment_type' => $type,
                'installment_day' => $type === 'Daily' ? null : rand(1, 28),
                'installment_amount' => $instAmt,
                'status' => rand(0, 4) === 0 ? 'closed' : 'active',
            ]);

            AccountItem::create([
                'account_id' => $account->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => $product->sale_price,
            ]);

            // Advance payment
            Payment::create([
                'account_id' => $account->id,
                'amount' => $advance,
                'transaction_type' => 'advance',
                'payment_date' => $saleDate->toDateString(),
                'collected_by' => $recoveryMan->id,
            ]);

            // Some installment payments
            $paymentsCount = rand(2, 8);
            $paid = $advance;
            for ($p = 0; $p < $paymentsCount; $p++) {
                $payAmt = min($instAmt, $remaining - $paid + $advance);
                if ($payAmt <= 0) {
                    break;
                }

                Payment::create([
                    'account_id' => $account->id,
                    'amount' => $payAmt,
                    'transaction_type' => 'installment',
                    'payment_date' => $saleDate->addDays(rand(7, 30))->toDateString(),
                    'collected_by' => $recoveryMan->id,
                ]);
                $paid += $payAmt;
            }
        }
    }
}
