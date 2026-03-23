<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FinancialLedger;
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
                $qty = rand(5, 20);
                $cost = (int) ($product->purchase_price * (0.9 + rand(0, 20) / 100));
                $purchaseDate = now()->subDays(rand(10, 120));

                Purchase::create([
                    'product_id' => $product->id,
                    'supplier_id' => $supplier->id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'purchase_date' => $purchaseDate,
                ]);

                FinancialLedger::record('purchase', [
                    'product_id' => $product->id,
                    'credit' => $cost * $qty,
                    'description' => "Stock purchase — {$product->name} x{$qty}",
                    'event_date' => $purchaseDate,
                    'meta' => ['supplier_id' => $supplier->id],
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
            $type = $types[$i % 3];
            $total = $product->sale_price * rand(1, 2);
            $advance = (int) ($total * (rand(5, 20) / 100));
            $discount = rand(0, 5) === 0 ? (int) ($total * 0.02) : 0;
            $remaining = $total - $advance - $discount;
            $instAmt = max(1000, (int) ($remaining / rand(10, 30)));
            $saleDate = now()->subDays(rand(5, 200));

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

            // Ledger: sale
            FinancialLedger::record('sale', [
                'account_id' => $account->id,
                'customer_id' => $customer->id,
                'debit' => $total,
                'balance_after' => $remaining,
                'description' => "New sale Acc#{$account->id} — {$product->name}",
                'event_date' => $saleDate,
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

                FinancialLedger::record('payment', [
                    'account_id' => $account->id,
                    'customer_id' => $customer->id,
                    'employee_id' => $saleMan->id,
                    'debit' => $advance,
                    'balance_after' => $remaining,
                    'description' => "Advance payment Acc#{$account->id}",
                    'event_date' => $saleDate,
                ]);
            }

            // Installment payments
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
                $balanceAfter = max(0, $total - $paid - $discount);

                FinancialLedger::record('recovery', [
                    'account_id' => $account->id,
                    'customer_id' => $customer->id,
                    'employee_id' => $recoveryMan->id,
                    'debit' => $payAmt,
                    'balance_after' => $balanceAfter,
                    'description' => "Recovery collection Acc#{$account->id}",
                    'event_date' => $payDate,
                ]);
            }

            // Update remaining to reflect actual payments
            $actualRemaining = max(0, $total - $paid - $discount);
            $account->update(['remaining_amount' => $actualRemaining]);

            // Ledger: closure + loss if closed
            if ($status === 'closed') {
                $closedAt = $account->closed_at;

                FinancialLedger::record('closure', [
                    'account_id' => $account->id,
                    'customer_id' => $customer->id,
                    'balance_after' => $actualRemaining,
                    'description' => "Account #{$account->id} closed",
                    'event_date' => $closedAt,
                    'meta' => ['remaining_at_close' => $actualRemaining],
                ]);

                if ($actualRemaining > 0) {
                    FinancialLedger::record('loss', [
                        'account_id' => $account->id,
                        'customer_id' => $customer->id,
                        'credit' => $actualRemaining,
                        'balance_after' => $actualRemaining,
                        'description' => "Write-off Acc#{$account->id} — unpaid balance at closure",
                        'event_date' => $closedAt,
                    ]);
                }
            }
        }

        // ── Returns ──────────────────────────────────────────────────────────
        $activeAccounts = Account::where('status', 'active')->with('items.product')->take(5)->get();
        foreach ($activeAccounts as $account) {
            $item = $account->items->first();
            if (! $item || $item->returned) {
                continue;
            }

            $returnAmt = (int) ($item->unit_price * 0.8);
            $returnDate = now()->subDays(rand(1, 30));

            ProductReturn::create([
                'account_id' => $account->id,
                'account_item_id' => $item->id,
                'quantity' => 1,
                'returning_amount' => $returnAmt,
                'return_date' => $returnDate,
                'reason' => collect(['Defective', 'Wrong item', 'Customer changed mind'])->random(),
                'inventory_action' => rand(0, 1) ? 'restock' : 'scrap',
            ]);

            $item->update(['returned' => true]);

            FinancialLedger::record('return', [
                'account_id' => $account->id,
                'customer_id' => $account->customer_id,
                'product_id' => $item->product_id,
                'credit' => $returnAmt,
                'balance_after' => $account->remaining_amount,
                'description' => "Return on Acc#{$account->id} — {$item->product?->name}",
                'event_date' => $returnDate,
            ]);
        }

        // ── Today's payments (recovery entry demo data) ──────────────────────
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

            FinancialLedger::record('recovery', [
                'account_id' => $account->id,
                'customer_id' => $account->customer_id,
                'employee_id' => $account->recovery_man_id,
                'debit' => $account->installment_amount,
                'balance_after' => max(0, $account->remaining_amount - $account->installment_amount),
                'description' => "Recovery collection Acc#{$account->id}",
                'event_date' => today(),
            ]);
        }
    }
}
