<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $saleMen = Employee::saleMen()->get();
        $recoveryMen = Employee::recoveryMen()->get();
        $suppliers = Supplier::all();

        if ($customers->isEmpty() || $products->isEmpty() || $saleMen->isEmpty() || $recoveryMen->isEmpty()) {
            $this->command->warn('Run SupplierSeeder, ProductSeeder, EmployeeSeeder, CustomerSeeder first.');

            return;
        }

        // ── Supplier Products (price per supplier per product) ─────────────
        foreach ($products as $product) {
            foreach ($suppliers as $supplier) {
                SupplierProduct::create([
                    'supplier_id' => $supplier->id,
                    'product_id' => $product->id,
                    'unit_price' => (int) ($product->purchase_price * (0.85 + rand(0, 30) / 100)),
                    'last_supplied_at' => now()->subDays(rand(5, 90)),
                    'last_quantity' => rand(5, 50),
                ]);
            }
        }

        // ── Purchases (stock history) ──────────────────────────────────────
        foreach ($products as $product) {
            $supplier = $suppliers->random();
            for ($p = 0; $p < rand(2, 4); $p++) {
                Purchase::create([
                    'product_id' => $product->id,
                    'supplier_id' => $supplier->id,
                    'quantity' => rand(5, 20),
                    'unit_cost' => (int) ($product->purchase_price * (0.9 + rand(0, 20) / 100)),
                    'purchase_date' => now()->subDays(rand(10, 120)),
                ]);
            }
        }

        // ── Accounts: ensure all 3 types have good distribution ───────────
        $types = ['daily', 'weekly', 'monthly'];

        for ($i = 0; $i < 60; $i++) {
            $customer = $customers->random();
            $product = $products->random();
            $saleMan = $saleMen->random();
            $recoveryMan = $recoveryMen->random();
            $type = $types[$i % 3]; // rotate evenly: daily, weekly, monthly
            $total = $product->sale_price * rand(1, 2);
            $advance = (int) ($total * (rand(5, 20) / 100));
            $discount = rand(0, 5) === 0 ? (int) ($total * 0.02) : 0;
            $remaining = $total - $advance - $discount;
            $instAmt = max(1000, (int) ($remaining / rand(10, 30)));
            $saleDate = now()->subDays(rand(5, 200));

            // Mix statuses: ~20% closed, rest active
            $status = rand(0, 4) === 0 ? 'closed' : 'active';

            $account = Account::create([
                'customer_id' => $customer->id,
                'sale_man_id' => $saleMan->id,
                'recovery_man_id' => $recoveryMan->id,
                'slip_number' => 'SL-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'sale_date' => $saleDate->toDateString(),
                'total_amount' => $total,
                'advance_amount' => $advance,
                'discount_amount' => $discount,
                'remaining_amount' => $remaining,
                'installment_type' => $type,
                'installment_day' => $type === 'daily' ? null : ($type === 'weekly' ? rand(1, 7) : rand(1, 28)),
                'installment_amount' => $instAmt,
                'status' => $status,
                'closed_at' => $status === 'closed' ? now()->subDays(rand(1, 30)) : null,
            ]);

            // 1-2 items per account
            $itemCount = rand(1, 2);
            for ($j = 0; $j < $itemCount; $j++) {
                $itemProduct = $j === 0 ? $product : $products->random();
                AccountItem::create([
                    'account_id' => $account->id,
                    'product_id' => $itemProduct->id,
                    'quantity' => rand(1, 2),
                    'unit_price' => $itemProduct->sale_price,
                ]);
            }

            // Advance payment
            if ($advance > 0) {
                Payment::create([
                    'account_id' => $account->id,
                    'amount' => $advance,
                    'transaction_type' => 'advance',
                    'payment_date' => $saleDate->toDateString(),
                    'collected_by' => $saleMan->id,
                    'remarks' => 'Advance at sale',
                ]);
            }

            // Installment payments (more for older accounts)
            $daysSinceSale = $saleDate->diffInDays(now());
            $paymentsCount = rand(2, min(15, (int) ($daysSinceSale / 7) + 2));
            $paid = $advance;
            $payDate = $saleDate->copy();

            for ($p = 0; $p < $paymentsCount; $p++) {
                $payAmt = min($instAmt, $remaining - ($paid - $advance));
                if ($payAmt <= 0) {
                    break;
                }

                $payDate = $payDate->addDays(rand(3, 15));
                if ($payDate->gt(now())) {
                    break;
                }

                Payment::create([
                    'account_id' => $account->id,
                    'amount' => $payAmt,
                    'transaction_type' => 'installment',
                    'payment_date' => $payDate->toDateString(),
                    'collected_by' => $recoveryMan->id,
                ]);

                $paid += $payAmt;
            }

            // Update remaining to reflect actual payments
            $actualRemaining = max(0, $total - $paid - $discount);
            $account->update(['remaining_amount' => $actualRemaining]);
        }

        // ── Some returns ───────────────────────────────────────────────────
        $activeAccounts = Account::where('status', 'active')->with('items')->take(5)->get();
        foreach ($activeAccounts as $account) {
            $item = $account->items->first();
            if (! $item || $item->returned) {
                continue;
            }

            ProductReturn::create([
                'account_id' => $account->id,
                'account_item_id' => $item->id,
                'quantity' => 1,
                'returning_amount' => (int) ($item->unit_price * 0.8),
                'return_date' => now()->subDays(rand(1, 30)),
                'reason' => collect(['Defective', 'Wrong item', 'Customer changed mind'])->random(),
                'inventory_action' => rand(0, 1) ? 'restock' : 'scrap',
            ]);

            $item->update(['returned' => true]);
        }

        // ── Some payments for today (so recovery entry shows data) ─────────
        $todayAccounts = Account::where('status', 'active')
            ->where('remaining_amount', '>', 0)
            ->where('installment_type', 'daily')
            ->take(3)
            ->get();

        foreach ($todayAccounts as $account) {
            Payment::create([
                'account_id' => $account->id,
                'amount' => $account->installment_amount,
                'transaction_type' => 'installment',
                'payment_date' => today(),
                'collected_by' => $account->recovery_man_id,
            ]);
        }
    }
}
