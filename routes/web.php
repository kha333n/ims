<?php

use App\Livewire\Auth\FirstRunSetup;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Profile;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Customers\AccountClosure;
use App\Livewire\Customers\AccountTransfer;
use App\Livewire\Customers\AddCustomer;
use App\Livewire\Customers\CustomerDetail;
use App\Livewire\Customers\CustomerList;
use App\Livewire\Customers\InstallmentUpdate;
use App\Livewire\Customers\ProblemEntry;
use App\Livewire\Dashboard\Overview;
use App\Livewire\Expenses\ExpenseEntry;
use App\Livewire\Financial\CollectionReport as FinancialCollectionReport;
use App\Livewire\Financial\CommissionReport;
use App\Livewire\Financial\CreditDebitReport;
use App\Livewire\Financial\DailyCashBook;
use App\Livewire\Financial\InventoryValuationReport;
use App\Livewire\Financial\LedgerReport;
use App\Livewire\Financial\LossReport;
use App\Livewire\Financial\ProfitLossReport;
use App\Livewire\Financial\ReceivablesReport;
use App\Livewire\Financial\SupplierExpenseReport;
use App\Livewire\Help\UserManual;
use App\Livewire\HR\Payroll;
use App\Livewire\HR\RecoveryManList;
use App\Livewire\HR\SaleManList;
use App\Livewire\Inventory\ProductList;
use App\Livewire\Inventory\PurchasePoint;
use App\Livewire\Inventory\SupplierList;
use App\Livewire\Recovery\RecoveryEntry;
use App\Livewire\Reports\CustomerReport;
use App\Livewire\Reports\DailyRecoveryReport;
use App\Livewire\Reports\DefaulterReport;
use App\Livewire\Reports\InventoryReport;
use App\Livewire\Reports\ItemDetailReport;
use App\Livewire\Reports\ItemSaleReport;
use App\Livewire\Reports\MonthlyRecoveryReport;
use App\Livewire\Reports\ReturnReport;
use App\Livewire\Reports\SalesmanReport;
use App\Livewire\Sales\NewSale;
use App\Livewire\Sales\ReturnPoint;
use App\Livewire\Settings\AppSettings;
use App\Livewire\Settings\BackupRestore;
use App\Livewire\Settings\LicenseSettings;
use App\Livewire\Settings\UserManagement;
use Illuminate\Support\Facades\Route;

// ── Auth (no middleware) ────────────────────────────────
Route::get('/setup', FirstRunSetup::class)->name('setup');
Route::get('/login', Login::class)->name('login');
Route::get('/password/reset', ResetPassword::class)->name('password.reset');
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

// License (needs to work before auth for activation)
Route::get('/license', LicenseSettings::class)->name('license');

// ── Authenticated routes ────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', Overview::class)->name('dashboard');
    Route::get('/profile', Profile::class)->name('profile');

    // Inventory
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/products', ProductList::class)->name('products')->middleware('can:products.view');
        Route::get('/suppliers', SupplierList::class)->name('suppliers')->middleware('can:suppliers.manage');
        Route::get('/purchase', PurchasePoint::class)->name('purchase')->middleware('can:purchases.manage');
    });

    // HR
    Route::prefix('hr')->name('hr.')->middleware('can:users.manage')->group(function () {
        Route::get('/sale-men', SaleManList::class)->name('sale-men');
        Route::get('/recovery-men', RecoveryManList::class)->name('recovery-men');
        Route::get('/payroll', Payroll::class)->name('payroll');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->middleware('can:customers.view')->group(function () {
        Route::get('/', CustomerList::class)->name('index');
        Route::get('/new', AddCustomer::class)->name('create')->middleware('can:customers.manage');
        Route::get('/closure', AccountClosure::class)->name('closure')->middleware('can:accounts.close');
        Route::get('/transfer', AccountTransfer::class)->name('transfer')->middleware('can:accounts.transfer');
        Route::get('/installment-update', InstallmentUpdate::class)->name('installment-update')->middleware('can:installments.update');
        Route::get('/problems', ProblemEntry::class)->name('problems')->middleware('can:accounts.close');
        Route::get('/{id}', CustomerDetail::class)->name('show');
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/new', NewSale::class)->name('new')->middleware('can:sales.create');
        Route::get('/return', ReturnPoint::class)->name('return')->middleware('can:returns.manage');
    });

    // Recovery
    Route::prefix('recovery')->name('recovery.')->group(function () {
        Route::get('/entry', RecoveryEntry::class)->name('entry')->middleware('can:recovery.entry');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('can:reports.view')->group(function () {
        Route::get('/', fn () => view('placeholder', ['title' => 'Reports']))->name('index');
        Route::get('/item-sales', ItemSaleReport::class)->name('item-sales');
        Route::get('/item-detail', ItemDetailReport::class)->name('item-detail');
        Route::get('/daily-recovery', DailyRecoveryReport::class)->name('daily-recovery');
        Route::get('/monthly-recovery', MonthlyRecoveryReport::class)->name('monthly-recovery');
        Route::get('/returns', ReturnReport::class)->name('returns');
        Route::get('/salesman', SalesmanReport::class)->name('salesman');
        Route::get('/inventory', InventoryReport::class)->name('inventory');
        Route::get('/customer', CustomerReport::class)->name('customer');
        Route::get('/defaulters', DefaulterReport::class)->name('defaulters');
    });

    // Financial Reports
    Route::prefix('financial')->name('financial.')->middleware('can:financial.view')->group(function () {
        Route::get('/cash-book', DailyCashBook::class)->name('cash-book');
        Route::get('/ledger', LedgerReport::class)->name('ledger');
        Route::get('/profit-loss', ProfitLossReport::class)->name('profit-loss');
        Route::get('/receivables', ReceivablesReport::class)->name('receivables');
        Route::get('/collections', FinancialCollectionReport::class)->name('collections');
        Route::get('/supplier-expenses', SupplierExpenseReport::class)->name('supplier-expenses');
        Route::get('/commissions', CommissionReport::class)->name('commissions');
        Route::get('/inventory-valuation', InventoryValuationReport::class)->name('inventory-valuation');
        Route::get('/losses', LossReport::class)->name('losses');
        Route::get('/credit-debit', CreditDebitReport::class)->name('credit-debit');
    });

    // Expenses
    Route::get('/expenses', ExpenseEntry::class)->name('expenses');

    // Help
    Route::get('/manual', UserManual::class)->name('manual');

    // Settings (owner only)
    Route::prefix('settings')->name('settings.')->middleware('can:settings.manage')->group(function () {
        Route::get('/', AppSettings::class)->name('index');
        Route::get('/backup', BackupRestore::class)->name('backup');
        Route::get('/license', LicenseSettings::class)->name('license');
        Route::get('/users', UserManagement::class)->name('users');
    });
});
