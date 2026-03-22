# 05 — Technical Stack

## Core Stack

| Layer | Technology | Version | Reason |
|-------|-----------|---------|--------|
| Backend Framework | Laravel | 11.x | Mature, rich ecosystem, offline SQLite support |
| Desktop Runtime | NativePHP (Electron) | Latest | Wraps Laravel in a real Windows desktop app |
| Database | SQLite | 3.x | Zero-server, single-file, easy backup |
| Frontend Reactivity | Livewire | 3.x | Full-stack components without writing APIs |
| UI Interactivity | Alpine.js | 3.x | Lightweight JS for modals, dropdowns, toggles |
| CSS Framework | Tailwind CSS | 3.x | Utility-first, fast to build with |
| PDF Generation | Laravel-DomPDF | 2.x | Server-side PDF for reports |
| Icons | Heroicons | 2.x | Free, clean, Tailwind-compatible SVG icons |

---

## NativePHP Setup

NativePHP wraps your Laravel app in Electron, making it a real `.exe` Windows application.

### Installation

```bash
composer require nativephp/laravel
php artisan native:install
npm install
```

### Configuration (`config/nativephp.php`)

```php
return [
    'name' => 'Installment Management System',
    'id' => 'com.ims.desktop',
    'version' => '2.0.0',
    'copyright' => '© 2025 IMS',
    'min_window_width' => 1024,
    'min_window_height' => 768,
    'updater' => [
        'enabled' => false, // Manual updates for now
    ],
];
```

### NativeAppServiceProvider

```php
// app/Providers/NativeAppServiceProvider.php
public function boot(): void
{
    NativeApp::addMenu(Menu::make(
        MenuItem::label('File')->submenu(
            MenuItem::label('New Sale')->action('openSale'),
            MenuItem::separator(),
            MenuItem::label('Backup Database')->action('backup'),
            MenuItem::label('Quit')->quit(),
        ),
        MenuItem::label('Reports')->submenu(
            MenuItem::label('Item Sales')->action('reportItemSales'),
            MenuItem::label('Recovery Report')->action('reportRecovery'),
            // ... etc
        ),
    ));
    
    Window::open()
        ->title('Installment Management System')
        ->width(1366)
        ->height(768)
        ->minWidth(1024)
        ->minHeight(768);
}
```

---

## Database Configuration

### `.env` settings

```env
APP_NAME="Installment Management System"
APP_ENV=production
APP_KEY=base64:...

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/storage/app/ims.sqlite

# Online features only
BACKUP_S3_ENDPOINT=
BACKUP_S3_BUCKET=
BACKUP_S3_KEY=
BACKUP_S3_SECRET=
LICENSE_API_URL=https://your-license-server.com
```

### SQLite in NativePHP

NativePHP maps `storage_path()` to the OS user data directory:
- Windows: `C:\Users\{username}\AppData\Roaming\IMS\storage\`

Set DB path dynamically:

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    config(['database.connections.sqlite.database' => 
        storage_path('app/ims.sqlite')
    ]);
}
```

---

## Laravel Configuration

### Key packages

```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "livewire/livewire": "^3.0",
    "nativephp/laravel": "^0.7",
    "barryvdh/laravel-dompdf": "^2.0",
    "league/flysystem-aws-s3-v3": "^3.0"
  },
  "require-dev": {
    "laravel/pint": "^1.0",
    "pestphp/pest": "^2.0",
    "pestphp/pest-plugin-laravel": "^2.0"
  }
}
```

### Routes (`routes/web.php`)

All routes are web routes (no API in MVP). Grouped by module:

```php
// Inventory
Route::prefix('inventory')->group(function () {
    Route::get('/products', ProductList::class)->name('products.index');
    Route::get('/purchase', PurchasePoint::class)->name('purchase.index');
});

// Customers
Route::prefix('customers')->group(function () {
    Route::get('/', CustomerList::class)->name('customers.index');
    Route::get('/new', AddCustomer::class)->name('customers.create');
    Route::get('/{customer}', CustomerDetail::class)->name('customers.show');
    Route::get('/transfer', AccountTransfer::class)->name('customers.transfer');
    Route::get('/accounts/close', AccountClosure::class)->name('accounts.close');
    Route::get('/installment-update', InstallmentUpdate::class)->name('installment.update');
});

// Sales
Route::prefix('sales')->group(function () {
    Route::get('/new', NewSale::class)->name('sales.create');
    Route::get('/returns', ReturnPoint::class)->name('returns.create');
});

// Recovery
Route::get('/recovery', RecoveryEntry::class)->name('recovery.index');

// Reports
Route::prefix('reports')->group(function () {
    Route::get('/item-sales', ItemSaleReport::class)->name('reports.item-sales');
    Route::get('/item-detail', ItemDetailReport::class)->name('reports.item-detail');
    Route::get('/daily-recovery', DailyRecoveryReport::class)->name('reports.daily-recovery');
    Route::get('/monthly-recovery', MonthlyRecoveryReport::class)->name('reports.monthly-recovery');
    Route::get('/returns', ReturnReport::class)->name('reports.returns');
    Route::get('/salesman-sales', SalesmanReport::class)->name('reports.salesman');
    Route::get('/inventory', InventoryReport::class)->name('reports.inventory');
    Route::get('/customer', CustomerReport::class)->name('reports.customer');
    Route::get('/defaulters', DefaulterReport::class)->name('reports.defaulters');
});

// HR
Route::prefix('hr')->group(function () {
    Route::get('/sale-men', SaleManList::class)->name('hr.salemen');
    Route::get('/recovery-men', RecoveryManList::class)->name('hr.recoveryman');
});

// Settings
Route::prefix('settings')->group(function () {
    Route::get('/', AppSettings::class)->name('settings.index');
    Route::get('/backup', BackupRestore::class)->name('settings.backup');
    Route::get('/license', LicenseSettings::class)->name('settings.license');
});
```

---

## Livewire Component Structure

Each screen is a full Livewire component:

```php
// app/Http/Livewire/Sales/NewSale.php
class NewSale extends Component
{
    // Form state
    public ?int $customerId = null;
    public array $items = [];
    public string $installmentType = 'daily';
    public int $advance = 0;
    public int $discount = 0;
    // ...

    // Computed properties
    public function getTotalProperty(): int { ... }
    public function getRemainingProperty(): int { ... }

    // Actions
    public function addItem(): void { ... }
    public function proceed(): void { ... } // calls SaleService

    public function render(): View
    {
        return view('livewire.sales.new-sale');
    }
}
```

---

## Service Layer

Business logic goes in Services, not Livewire components:

```php
// app/Services/SaleService.php
class SaleService
{
    public function createSale(array $data): Account
    {
        return DB::transaction(function () use ($data) {
            $account = Account::create([...]);
            
            foreach ($data['items'] as $item) {
                AccountItem::create([...]);
                // Decrement stock
                Product::find($item['product_id'])
                    ->decrement('quantity', $item['quantity']);
            }
            
            return $account;
        });
    }
}

// app/Services/RecoveryService.php
class RecoveryService
{
    public function markPayments(array $accountIds, int $recoveryManId): void
    {
        DB::transaction(function () use ($accountIds, $recoveryManId) {
            foreach ($accountIds as $accountId) {
                $account = Account::find($accountId);
                Payment::create([
                    'account_id' => $accountId,
                    'recovery_man_id' => $recoveryManId,
                    'amount' => $account->installment_amount,
                    'payment_date' => today(),
                    'status' => 'paid',
                ]);
            }
        });
    }
}
```

---

## Report Generation

Reports use two modes:
1. **On-screen** — Livewire component with HTML table (filterable, paginated)
2. **Print** — Opens a print-styled Blade view via `window.print()`

```php
// app/Exports/ItemSaleReportExport.php
class ItemSaleReport
{
    public function __construct(
        private Carbon $from,
        private Carbon $to,
        private ?int $recoveryManId = null
    ) {}

    public function data(): Collection
    {
        return Account::query()
            ->with(['customer', 'salеMan', 'recoveryMan', 'items.product'])
            ->when($this->recoveryManId, fn($q) => 
                $q->where('recovery_man_id', $this->recoveryManId))
            ->whereBetween('sale_date', [$this->from, $this->to])
            ->get()
            ->map(fn($account) => [
                'id' => $account->id,
                'date' => $account->sale_date->format('d/M/Y'),
                'sale_man' => $account->saleMan->name,
                'recovery_man' => $account->recoveryMan->name,
                // ...
            ]);
    }
}
```

---

## Backup System

```php
// app/Services/BackupService.php
class BackupService
{
    public function createLocalBackup(): string
    {
        $dbPath = config('database.connections.sqlite.database');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = storage_path("app/backups/ims_backup_{$timestamp}.sqlite");
        
        copy($dbPath, $backupPath);
        return $backupPath;
    }

    public function uploadToCloud(string $localPath): void
    {
        // Only if online
        if (!$this->isOnline()) {
            throw new \Exception('No internet connection');
        }
        
        Storage::disk('s3')->put(
            'backups/' . basename($localPath),
            file_get_contents($localPath)
        );
    }

    private function isOnline(): bool
    {
        try {
            $socket = @fsockopen('8.8.8.8', 53, $errno, $errstr, 2);
            if ($socket) { fclose($socket); return true; }
        } catch (\Exception $e) {}
        return false;
    }
}
```

---

## Testing

Use **PestPHP** (Laravel-flavored):

```php
// tests/Feature/Sales/NewSaleTest.php
it('creates an account when sale is submitted', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['quantity' => 10]);
    $saleMan = Employee::factory()->salesman()->create();
    $recoveryMan = Employee::factory()->recoveryman()->create();

    $service = app(SaleService::class);
    $account = $service->createSale([
        'customer_id' => $customer->id,
        'sale_man_id' => $saleMan->id,
        'recovery_man_id' => $recoveryMan->id,
        'sale_date' => today(),
        'advance' => 50000, // 500 PKR in paisa
        'installment_type' => 'daily',
        'installment_amount' => 10000, // 100 PKR
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'price' => $product->price]
        ],
    ]);

    expect($account)->toBeInstanceOf(Account::class)
        ->and($account->status)->toBe('active')
        ->and($product->fresh()->quantity)->toBe(9);
});
```

---

## Build / Distribution

```bash
# Development
php artisan native:serve

# Build for Windows
php artisan native:build windows

# Output: dist/IMS-Setup-2.0.0.exe
```

The built `.exe` is a self-contained Electron app with PHP bundled. No PHP installation required on end-user machine.

---

## Folder Structure (Full)

```
ims/
├── app/
│   ├── Http/
│   │   └── Livewire/
│   │       ├── Inventory/
│   │       │   ├── ProductList.php
│   │       │   └── PurchasePoint.php
│   │       ├── Customers/
│   │       │   ├── CustomerList.php
│   │       │   ├── AddCustomer.php
│   │       │   ├── CustomerDetail.php
│   │       │   ├── AccountTransfer.php
│   │       │   ├── AccountClosure.php
│   │       │   └── InstallmentUpdate.php
│   │       ├── Sales/
│   │       │   ├── NewSale.php
│   │       │   └── ReturnPoint.php
│   │       ├── Recovery/
│   │       │   └── RecoveryEntry.php
│   │       ├── Reports/
│   │       │   ├── ItemSaleReport.php
│   │       │   ├── ItemDetailReport.php
│   │       │   ├── DailyRecoveryReport.php
│   │       │   ├── MonthlyRecoveryReport.php
│   │       │   ├── ReturnReport.php
│   │       │   ├── SalesmanReport.php
│   │       │   ├── InventoryReport.php
│   │       │   ├── CustomerReport.php
│   │       │   └── DefaulterReport.php
│   │       ├── HR/
│   │       │   ├── SaleManList.php
│   │       │   └── RecoveryManList.php
│   │       └── Settings/
│   │           ├── AppSettings.php
│   │           ├── BackupRestore.php
│   │           └── LicenseSettings.php
│   ├── Models/
│   │   ├── Account.php
│   │   ├── AccountItem.php
│   │   ├── AccountTransfer.php
│   │   ├── Customer.php
│   │   ├── Employee.php
│   │   ├── InstallmentPlanChange.php
│   │   ├── Payment.php
│   │   ├── Problem.php
│   │   ├── Product.php
│   │   ├── Purchase.php
│   │   ├── Return.php (ProductReturn)
│   │   ├── Setting.php
│   │   └── Supplier.php
│   ├── Services/
│   │   ├── BackupService.php
│   │   ├── LicenseService.php
│   │   ├── RecoveryService.php
│   │   ├── ReturnService.php
│   │   └── SaleService.php
│   └── Providers/
│       └── NativeAppServiceProvider.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── ProductSeeder.php
│       ├── EmployeeSeeder.php
│       └── DemoDataSeeder.php
├── resources/
│   ├── css/app.css
│   ├── js/app.js
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── livewire/
│       │   ├── inventory/
│       │   ├── customers/
│       │   ├── sales/
│       │   ├── recovery/
│       │   ├── reports/
│       │   ├── hr/
│       │   └── settings/
│       └── reports/
│           └── print/  (print-formatted templates)
├── routes/web.php
├── docs/
├── CLAUDE.md
└── README.md
```
