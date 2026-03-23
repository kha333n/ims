<?php

use App\Livewire\Customers\AccountClosure;
use App\Livewire\Customers\AccountTransfer;
use App\Livewire\Customers\AddCustomer;
use App\Livewire\Customers\CustomerDetail;
use App\Livewire\Customers\CustomerList;
use App\Livewire\Customers\InstallmentUpdate;
use App\Livewire\HR\RecoveryManList;
use App\Livewire\HR\SaleManList;
use App\Livewire\Inventory\ProductList;
use App\Livewire\Inventory\PurchasePoint;
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
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('dashboard'))->name('dashboard');

// Inventory
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/products', ProductList::class)->name('products');
    Route::get('/purchase', PurchasePoint::class)->name('purchase');
});

// HR
Route::prefix('hr')->name('hr.')->group(function () {
    Route::get('/sale-men', SaleManList::class)->name('sale-men');
    Route::get('/recovery-men', RecoveryManList::class)->name('recovery-men');
});

// Customers
Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', CustomerList::class)->name('index');
    Route::get('/new', AddCustomer::class)->name('create');
    Route::get('/closure', AccountClosure::class)->name('closure');
    Route::get('/transfer', AccountTransfer::class)->name('transfer');
    Route::get('/installment-update', InstallmentUpdate::class)->name('installment-update');
    Route::get('/problems', fn () => view('placeholder', ['title' => 'Problem Entry']))->name('problems');
    Route::get('/{id}', CustomerDetail::class)->name('show');
});

// Sales
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/new', NewSale::class)->name('new');
    Route::get('/return', ReturnPoint::class)->name('return');
});

// Recovery
Route::prefix('recovery')->name('recovery.')->group(function () {
    Route::get('/entry', RecoveryEntry::class)->name('entry');
});

// Reports
Route::prefix('reports')->name('reports.')->group(function () {
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

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', fn () => view('placeholder', ['title' => 'Company Settings']))->name('index');
    Route::get('/backup', fn () => view('placeholder', ['title' => 'Backup & Restore']))->name('backup');
    Route::get('/license', fn () => view('placeholder', ['title' => 'License']))->name('license');
});
