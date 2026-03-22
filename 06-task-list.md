# 06 — Development Task List

Tasks are ordered by dependency. Complete each task fully before moving to the next. Each task is small enough to test independently.

**Estimated total: 55 tasks**

---

## Phase 1: Project Foundation

### TASK-001: Laravel + NativePHP Project Setup

- Create new Laravel 13 or any nativePHP supports project
- Install NativePHP/Laravel, configure Electron driver
- Install Livewire 3, Alpine.js, Tailwind CSS
- Configure SQLite connection pointing to `storage/app/ims.sqlite`
- Verify `php artisan native:serve` opens a desktop window
- **Deliverable:** App window opens with "Hello World" page

---

### TASK-002: App Layout & Navigation Shell

- Create `resources/views/layouts/app.blade.php` — main shell
- Top navigation bar with menus: Items, Management, Reports, Recovery, Settings
- Quick toolbar row with icon buttons: New Purchases, New Sales, Recovery Entry, New Customer, Main Reports
- Active state highlighting on current section
- Color palette implemented in Tailwind config (navy, blues, etc.)
- **Deliverable:** Navigation shell with all menus (links go to placeholder pages)

---

### TASK-003: Base Migrations — Core Tables

- Create migrations for: `products`, `suppliers`, `employees`, `customers`
- Run `php artisan migrate`
- Verify tables created in SQLite
- **Deliverable:** 4 base tables with correct columns

---

### TASK-004: Base Migrations — Transaction Tables

- Create migrations for: `accounts`, `account_items`, `payments`, `purchases`
- Create migrations for: `returns` (use model name `ProductReturn`), `account_transfers`
- Create migrations for: `installment_plan_changes`, `problems`
- Create migration for: `settings` (key-value table)
- Run `php artisan migrate`
- **Deliverable:** All 9 transaction tables created

---

### TASK-005: Eloquent Models

- Create all models: `Product`, `Supplier`, `Employee`, `Customer`, `Account`, `AccountItem`, `Payment`, `Purchase`, `ProductReturn`, `AccountTransfer`, `InstallmentPlanChange`, `Problem`, `Setting`
- Define relationships (hasMany, belongsTo, etc.) per schema doc
- Add soft deletes where specified
- Add money helper: `getFormattedPriceAttribute()` on Product
- **Deliverable:** All models with relationships and casts

---

### TASK-006: Database Seeders — Demo Data

- `ProductSeeder`: 10 products (AC DC, buffer, JCR, LED, IRN, Mobile, KBL, CF, DNS, Blnd)
- `EmployeeSeeder`: 3 sale men, 4 recovery men with areas/ranks
- `CustomerSeeder`: 20 demo customers
- `DemoDataSeeder`: 30 accounts with payments
- **Deliverable:** `php artisan db:seed` populates realistic demo data

---

### TASK-007: Global Helpers & Formatting

- Create `app/Helpers/Money.php` — `formatMoney(int $paisa): string`
- Create `app/Helpers/DateHelper.php` — `formatDate(Carbon): string` → "16/Apr/2025"
- Register in `composer.json` autoload files
- Blade directive `@money($amount)`
- **Deliverable:** Helpers available everywhere, tests pass

---

## Phase 2: Inventory Module

### TASK-008: Product List Screen

- Livewire component: `Inventory\ProductList`
- Table with columns: Sr#, Name, Price, Quantity, Supplier, Actions
- Search by name (real-time filter)
- Pagination (50 per page)
- Empty state message
- **Deliverable:** `/inventory/products` shows filterable product table

---

### TASK-009: Add/Edit Product Form

- Modal form (Livewire modal) for add and edit
- Required fields: Name, Price, Quantity
- Optional fields: Company/Supplier, Brand, Model Number, Color, Category, Notes
- Image upload: file picker, stores in `storage/app/product-images/`, displays thumbnail
- Validation with inline error messages
- Save creates/updates product record
- **Deliverable:** Add and Edit products work with image support

---

### TASK-010: Delete Product

- Soft delete on the product (confirm dialog first)
- Cannot delete if product has active account items (show error message)
- **Deliverable:** Delete works, with proper guard

---

### TASK-011: Purchase Point Screen

- Livewire component: `Inventory\PurchasePoint`
- Date picker, Product Name dropdown (searchable), Rate, Quantity, Add button
- Line items table (dynamically added): Name | Price | Qty | Total | [Remove]
- Stock Information panel (right): updates on product selection
- Total Amount calculated
- Save: creates `purchases` records, increments `products.quantity`
- **Deliverable:** `/inventory/purchase` adds stock correctly

---

## Phase 3: HR Module (Staff)

### TASK-012: Sale Man List & CRUD

- Livewire component: `HR\SaleManList`
- Table: ID, Name, Phone, NIC, Commission %, Actions
- Add/Edit inline modal form
- Delete (soft delete, warn if has active accounts)
- Search by name
- **Deliverable:** `/hr/sale-men` full CRUD

---

### TASK-013: Recovery Man List & CRUD

- Livewire component: `HR\RecoveryManList`
- Table: ID, Name, Phone, Area, Rank, Actions
- Add/Edit inline modal (extra fields: Salary, Area, Rank)
- Delete guard: cannot delete if has active accounts
- **Deliverable:** `/hr/recovery-men` full CRUD

---

## Phase 4: Customer Module

### TASK-014: Customer List Screen

- Livewire component: `Customers\CustomerList`
- Table: ID, Name, Contact, Address, CNIC, Reference, Balance (remaining across all accounts)
- Search by name (real-time)
- Pagination
- "Add New" button → opens add modal
- Row click → goes to customer detail
- **Deliverable:** `/customers` shows all customers with live balance

---

### TASK-015: Add New Customer

- Livewire component (or modal): `Customers\AddCustomer`
- Fields: Name\*, Father Name, Mobile, CNIC, Reference, Home Address, Shop Address
- Customer ID auto-assigned (shown read-only)
- Accessible from `/customers/new` AND as modal from New Sale screen
- Returns Customer ID on save (for use in New Sale)
- **Deliverable:** New customers can be added from two places

---

### TASK-016: Customer Detail Screen

- Livewire component: `Customers\CustomerDetail`
- Shows full customer info (read-only, with edit button)
- Accounts table: Acc#, Item, Total, Advance, Remaining, Status, RM, Actions
- Quick Payment Entry inline panel: Amount, Transaction Type, Date, Remarks → creates Payment record
- "Edit Customer" updates basic info
- **Deliverable:** `/customers/{id}` shows full detail with payment entry

---

### TASK-017: Account Level Transfer (Recovery Man Reassignment)

- Livewire component: `Customers\AccountTransfer`
- Customer ID picker → shows name, total, remaining, current RM
- From RM (auto-filled), To RM (dropdown)
- "Transfer Account" → updates `recovery_man_id` on all active accounts, logs to `account_transfers`
- Accessible from customer detail AND from menu
- **Deliverable:** RM reassignment works and is logged

---

### TASK-018: Account Closure & Activation

- Livewire component: `Customers\AccountClosure`
- Close mode: RM dropdown → Customer ID dropdown (filtered) → shows account details (Name, Address, Contact, Total, Collection, Balance)
- Discount Amount + Discount Slip # fields
- Activate mode: same selection, shows closed account, "Activate" button
- **Deliverable:** Accounts can be closed and reopened

---

### TASK-019: Party Installment Plan Update

- Livewire component: `Customers\InstallmentUpdate`
- Party ID picker → shows current plan (Type, Amount, Address, Mobile)
- New Inst Type, New Day, New Installment Amount
- Save → updates account, logs to `installment_plan_changes`
- **Deliverable:** Installment plan can be changed mid-way

---

## Phase 5: Sales Module

### TASK-020: New Sale Screen — Customer Panel

- Livewire component: `Sales\NewSale` (build in stages)
- Left panel: Party ID dropdown/search → auto-fills Name, Father, Mobile, Address, Reference
- "+ New Customer" button → opens AddCustomer modal → returns CID to sale form
- Sale date picker (defaults to today)
- Slip # field (optional)
- **Deliverable:** Customer selection panel works with live search

---

### TASK-021: New Sale Screen — Item & Installment Panel

- Right panel: Item dropdown (searchable, shows stock qty)
- Total Price, Advance, Discount (default 0)
- Installment Type (Daily/Weekly/Monthly)
- Day field (for Weekly/Monthly)
- Remaining Amount (auto-calculated: Total - Advance - Discount)
- Installment Amount (user enters)
- Total Installments (auto-calculated: Remaining / Inst.Amt)
- **Deliverable:** Installment calculations update live as user types

---

### TASK-022: New Sale Screen — Multi-Item Support

- "Add Another Item" button → add rows to items list
- Each item row: Item dropdown, Price, Quantity, Subtotal, Remove
- Overall Total updates as items are added
- Stock availability warning (if quantity requested > stock)
- **Deliverable:** Multiple items can be added to one sale

---

### TASK-023: New Sale Screen — Staff Assignment & Save

- Sale Man dropdown (all active sale men)
- Recovery Man dropdown (all active recovery men, shows area)
- Area auto-fills from Recovery Man selection
- "Proceed" button → validation → `SaleService::createSale()` → success → redirect to account detail or back to blank form
- Tests: account created, stock decremented, payment records initialized
- **Deliverable:** Complete sale flow saves to database correctly

---

### TASK-024: Return Point Screen

- Livewire component: `Sales\ReturnPoint`
- RM dropdown → Customer ID → Account # (shows item details)
- All account details auto-fill (phone, slip, SM, area, sale date)
- Item detail panel: item, price, qty, total, paid, remaining, new installment
- Return Detail: Returning Amount, Return Date, Reason, Inventory Action (Restock/Scrap)
- "Next" → confirm screen → "Save" → `ReturnService::processReturn()`
- **Deliverable:** Returns adjust balance and restock inventory

---

## Phase 6: Recovery Module

### TASK-025: Recovery Entry Screen (Client Account Status)

- Livewire component: `Recovery\RecoveryEntry`
- RM dropdown, Category dropdown (Daily/Weekly/Monthly), Load button
- Table: RM | CID | Customer Name | Phone | Address | CNIC | Balance | Type | ☐ Checkbox
- Checkboxes mark as received
- "Update Status" → `RecoveryService::markPayments()` → inserts payment records
- Prevent duplicate payments same day (warn, not block)
- **Deliverable:** Recovery checker can mark daily payments

---

## Phase 7: Reports Module

### TASK-026: Reports — Shared Infrastructure

- Create `resources/views/layouts/report.blade.php` — print-ready layout
- Company header (from settings), report title, date/time generated
- Print button (`window.print()`)
- Page numbers in footer
- Print CSS that hides UI chrome
- **Deliverable:** Base report layout used by all reports

---

### TASK-027: Report — Item Sale Report

- Livewire: `Reports\ItemSaleReport`
- Filters: Date From, Date To, Recovery Man (optional)
- Table: TxID, Date, Sales Man, Recovery Man, CID, Customer, Address, Phone, Item, Total, Advance, Paid, Balance
- Totals row at bottom
- Print button
- **Deliverable:** `/reports/item-sales` works with all filters

---

### TASK-028: Report — Item Detail Report (Per-Day)

- Livewire: `Reports\ItemDetailReport`
- Filters: Date From, Date To
- Table: Sr#, Item Name, Quantity, Price, Total, Date
- Groups by item with subtotals
- Print
- **Deliverable:** `/reports/item-detail` per-day item breakdown

---

### TASK-029: Report — Daily Recovery Report

- Livewire: `Reports\DailyRecoveryReport`
- Filters: Date From, Date To, Area (dropdown)
- Header shows: Recovery Man Name, Area
- Table: Sr#, Date, Acc#, Paid Amount, Remaining, Status
- Print
- **Deliverable:** `/reports/daily-recovery`

---

### TASK-030: Report — Recovery Monthly Report

- Livewire: `Reports\MonthlyRecoveryReport`
- Filters: Date range, Area
- Table: Sr#, Total, Advance, Remaining, Previous, R.M.ID, Date, Discount, New Sale, Area
- Print
- **Deliverable:** `/reports/monthly-recovery`

---

### TASK-031: Report — Item Return Report

- Livewire: `Reports\ReturnReport`
- Filters: Date From, Date To
- Table: Sr#, Acc#, Sale Date, RM, SM, Customer, Item, QTY, Total, Received, Remaining, Mobile, Reason
- Print
- **Deliverable:** `/reports/returns`

---

### TASK-032: Report — SaleMan Sales Report

- Livewire: `Reports\SalesmanReport`
- Filters: Sale Man (dropdown), Date From, Date To, Status (Active/Closed/All)
- Table: Sr#, Acc#, Slip#, Date, SM ID, SM Name, Item, Customer, Total, Advance, Remaining, Status + Qty, Phone
- Print
- **Deliverable:** `/reports/salesman-sales`

---

### TASK-033: Report — Inventory Status

- Livewire: `Reports\InventoryReport`
- No filters (shows current state)
- Table: Sr#, Item, Price, Quantity, Company (Supplier)
- Print
- **Deliverable:** `/reports/inventory`

---

### TASK-034: Report — Account Holder (Customer) Report

- Livewire: `Reports\CustomerReport`
- Filter: Account Number
- Shows single account: Account ID, Slip No, Date, Item, Customer Name, Address, Phone, Status
- Print
- **Deliverable:** `/reports/customer`

---

### TASK-035: Report — Defaulter Report

- Livewire: `Reports\DefaulterReport`
- Filters: Recovery Man ID, Days overdue (default 30), By Sale Date toggle
- Table: Sr#, Record#, Date, Days, Name, Address, Phone#, Sale Man, Item, Total, Remaining, Paid, Short Amount, Promise, Status, Action
- Print
- **Deliverable:** `/reports/defaulters`

---

## Phase 8: Problems/Defaulter Actions

### TASK-036: Action Against Problem Screen

- Livewire: `Customers\ProblemEntry`
- Select account → shows customer, item, problem history
- Fields: Manager, Checker, Branch, Recovery Man, Customer Name, CID, Phone, Items, Problem text, Previous Promise Date, New Commitment, Action Against Problem, Close checkbox
- Save → creates/updates `problems` record
- **Deliverable:** Problem tracking works end-to-end

---

## Phase 9: Settings Module

### TASK-037: Company Settings Screen

- Livewire: `Settings\AppSettings`
- Fields: Company Name, Company Address, Company Phone
- Also: Defaulter Days threshold
- Save → updates `settings` table (key-value)
- **Deliverable:** `/settings` saves and loads correctly

---

### TASK-038: Backup & Restore Screen

- Livewire: `Settings\BackupRestore`
- "Create Local Backup" → copies SQLite to `storage/app/backups/`
- Shows list of local backups with date/size
- "Upload to Cloud" → checks internet → uploads via S3
- "Restore from File" → file picker → confirm dialog → replaces database → restart app
- **Deliverable:** `/settings/backup` backup and restore works

---

### TASK-039: License Settings Screen

- Livewire: `Settings\LicenseSettings`
- Shows current license key (masked), status (Valid/Expired/Offline)
- "Validate Now" → calls license API → updates cache
- Enter new license key field
- **Deliverable:** License check works online and gracefully offline

---

## Phase 10: Polish & Quality

### TASK-040: Global Search / Quick Jump

- Search bar in toolbar: type customer name or account number → jump to customer/account
- Keyboard shortcut: `Ctrl+F` focuses search
- **Deliverable:** Quick navigation works

---

### TASK-041: Form Validation Hardening

- Audit all forms for missing validation rules
- Add server-side validation to all Livewire `save()` / `proceed()` methods
- Add inline error messages under each invalid field
- **Deliverable:** No form can be submitted with invalid data

---

### TASK-042: Amount Input Formatting

- Currency inputs auto-format with commas on blur
- Strip formatting on focus (raw number for editing)
- All display amounts use `formatMoney()` helper
- **Deliverable:** All money fields display consistently

---

### TASK-043: Date Input Standardization

- All date pickers use consistent format (dd/Mon/yyyy for display, Y-m-d for storage)
- Default to "today" where appropriate
- Date range pickers validate From ≤ To
- **Deliverable:** Dates consistent throughout app

---

### TASK-044: Loading & Empty States

- Every table has a loading skeleton state (Livewire wire:loading)
- Every table has an empty state with icon and message
- Every save button shows spinner while processing
- **Deliverable:** No blank/broken states during data load

---

### TASK-045: Toast Notification System

- Alpine.js toast component (bottom-right)
- Success (green), Error (red), Warning (amber)
- Auto-dismiss after 3 seconds for success
- Manual dismiss for errors
- **Deliverable:** Consistent feedback on every action

---

### TASK-046: Confirmation Dialogs

- Delete confirmations use custom modal (not browser `confirm()`)
- Account closure confirmation
- Return confirmation
- Database restore confirmation (most critical — red warning)
- **Deliverable:** All destructive actions have proper confirm dialogs

---

### TASK-047: Keyboard Navigation

- Implement F1, F2, F3, F5, Ctrl+P, Ctrl+S, Escape shortcuts
- Tab order correct in all forms
- Enter key submits forms where appropriate
- **Deliverable:** Power users can navigate without mouse

---

## Phase 11: Testing

### TASK-048: Unit Tests — Services

- `SaleServiceTest` — createSale, validateStock, calculateInstallments
- `RecoveryServiceTest` — markPayments, preventDuplicates
- `ReturnServiceTest` — processReturn, restockLogic, accountClosure
- `BackupServiceTest` — createLocalBackup, isOnline
- **Deliverable:** All services have unit test coverage

---

### TASK-049: Feature Tests — Core Flows

- New sale end-to-end test
- Recovery marking end-to-end test
- Account closure test
- Item return test
- Customer creation + first sale test
- **Deliverable:** Core workflows tested at feature level

---

### TASK-050: Feature Tests — Reports

- Test each report returns correct data for given filters
- Test empty state (no data in range)
- Test date range filtering
- **Deliverable:** All 9 reports have at least 2 tests each

---

## Phase 12: Build & Distribution

### TASK-051: Environment Configuration for Production

- Create `.env.production` template
- Configure SQLite path for production (AppData folder)
- Set `APP_ENV=production`, `APP_DEBUG=false`
- Remove demo seeders from production build
- **Deliverable:** Production build config ready

---

### TASK-052: NativePHP Build Configuration

- App icon (512×512 PNG, convert to .ico for Windows)
- App name, version, copyright in `config/nativephp.php`
- Configure auto-start with Windows (optional setting)
- Window size, min size configured
- **Deliverable:** `php artisan native:build windows` produces clean .exe

---

### TASK-053: First-Run Setup Wizard

- On first launch (no settings record), show setup wizard:
  - Step 1: Enter company name, address, phone
  - Step 2: Set admin password (optional)
  - Step 3: Done — redirect to dashboard
- **Deliverable:** Fresh install guides user through setup

---

### TASK-054: Data Migration Tool (from Old System)

- Script to import data from old Access/CSV export
- Import: Customers, Products, Employees, Accounts (with balances)
- Handle duplicate detection
- Log migration errors
- **Deliverable:** `php artisan ims:import --file=export.csv` works

---

### TASK-055: Final QA Checklist

- Test full sale → recovery → closure flow on a fresh database
- Verify all reports print correctly
- Test backup and restore
- Test offline mode (disconnect internet, verify all local features work)
- Test license expiry (offline cache behavior)
- Verify `php artisan native:build windows` produces working installer
- **Deliverable:** Signed off, ready for client delivery

---

## Task Summary by Phase

| Phase          | Tasks   | Focus                              |
| -------------- | ------- | ---------------------------------- |
| 1 — Foundation | 001–007 | Setup, models, migrations, helpers |
| 2 — Inventory  | 008–011 | Products, stock entry              |
| 3 — HR         | 012–013 | Staff management                   |
| 4 — Customers  | 014–019 | Customer CRUD, account management  |
| 5 — Sales      | 020–024 | Sale point, returns                |
| 6 — Recovery   | 025     | Daily recovery entry               |
| 7 — Reports    | 026–035 | All 9 reports                      |
| 8 — Problems   | 036     | Defaulter action tracking          |
| 9 — Settings   | 037–039 | Config, backup, license            |
| 10 — Polish    | 040–047 | UX, validation, consistency        |
| 11 — Tests     | 048–050 | Automated testing                  |
| 12 — Build     | 051–055 | Production build, migration, QA    |

**Total: 55 tasks** | **Estimated effort: 120–160 hours**
