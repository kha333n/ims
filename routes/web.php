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
use App\Livewire\Sales\NewSale;
use App\Livewire\Inventory\PurchasePoint;
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
    Route::get('/new', fn () => view('placeholder', ['title' => 'New Sale']))->name('new');
    Route::get('/return', fn () => view('placeholder', ['title' => 'Return Point']))->name('return');
});

// Recovery
Route::prefix('recovery')->name('recovery.')->group(function () {
    Route::get('/entry', fn () => view('placeholder', ['title' => 'Recovery Entry']))->name('entry');
});

// Reports
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', fn () => view('placeholder', ['title' => 'Reports']))->name('index');
    Route::get('/item-sales', fn () => view('placeholder', ['title' => 'Item Sale Report']))->name('item-sales');
    Route::get('/item-detail', fn () => view('placeholder', ['title' => 'Item Detail Report']))->name('item-detail');
    Route::get('/daily-recovery', fn () => view('placeholder', ['title' => 'Daily Recovery Report']))->name('daily-recovery');
    Route::get('/monthly-recovery', fn () => view('placeholder', ['title' => 'Monthly Recovery Report']))->name('monthly-recovery');
    Route::get('/returns', fn () => view('placeholder', ['title' => 'Return Report']))->name('returns');
    Route::get('/salesman', fn () => view('placeholder', ['title' => 'Salesman Report']))->name('salesman');
    Route::get('/inventory', fn () => view('placeholder', ['title' => 'Inventory Status']))->name('inventory');
    Route::get('/customer', fn () => view('placeholder', ['title' => 'Customer Account Report']))->name('customer');
    Route::get('/defaulters', fn () => view('placeholder', ['title' => 'Defaulter Report']))->name('defaulters');
});

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', fn () => view('placeholder', ['title' => 'Company Settings']))->name('index');
    Route::get('/backup', fn () => view('placeholder', ['title' => 'Backup & Restore']))->name('backup');
    Route::get('/license', fn () => view('placeholder', ['title' => 'License']))->name('license');
});
