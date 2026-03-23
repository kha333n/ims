# CLAUDE.md — Installment Management System (IMS)

## Project Identity

This is a **Laravel + NativePHP** desktop Windows application for managing installment-based product sales. It is a full rebuild of an existing VB.NET/Access-based system called "Installment Management System" by Techmiddle Technologies, operated by a business in Rawalpindi, Pakistan.

---

## Core Principles

1. **Offline-first**: The app must work 100% without internet. Only backups and subscription checks require a connection.
2. **Parity before improvement**: Every feature in the old system must exist before new features are added.
3. **Optional enhancements**: New fields (product image, attributes, etc.) are always optional — never break existing workflow.
4. **Desktop, not web**: This runs via NativePHP. Do not design for browsers. Keyboard shortcuts, native dialogs, and local file access matter.
5. **Step-by-step tasks**: Features are built one task at a time. Never mix concerns across tasks.

---

## Tech Stack

- **Framework**: Laravel 11+
- **Desktop Runtime**: NativePHP (Electron driver)
- **Database**: SQLite (local, offline)
- **Frontend**: Livewire 3 + Alpine.js + Tailwind CSS
- **PDF/Printing**: Laravel DomPDF or Browsershot
- **Cloud Backup**: Laravel + S3-compatible storage (online only)
- **Subscription**: Custom license key validation via API (online only)

---

## Domain Language

| Term | Meaning |
|------|---------|
| `Recovery Man (RM)` | Field agent who collects installment payments from customers |
| `Sale Man (SM)` | Employee who makes the sale and registers the customer |
| `Account` | A single installment plan record for a customer (one customer can have multiple accounts) |
| `Installment` | A scheduled payment; can be Daily, Weekly, or Monthly |
| `Area` | Geographic zone assigned to a Recovery Man |
| `Rank` | Seniority/level of a Recovery Man |
| `CID` | Customer ID — auto-incremented unique identifier |
| `Acc#` | Account number — tied to a specific sale/installment plan |
| `Status` | Account state: Active or Closed |
| `Checker` | Supervisor who verifies recovery entries |
| `CNIC` | Pakistani national ID card number |

---

## Module Map

```
IMS
├── Inventory        → Products + Purchase Point (stock entry)
├── Customers        → Add/Edit/View customers, account closure
├── Sales            → New sale, installment plan assignment
├── Recovery         → Daily recovery entry, account status view
├── Returns          → Item returns, account adjustment
├── HR               → Sale Men + Recovery Men CRUD
├── Reports
│   ├── Item Sale Report
│   ├── Item Detail Report (per-day)
│   ├── Daily Recovery Report
│   ├── Recovery Monthly Report
│   ├── Item Return Report
│   ├── SaleMan Sales Report
│   ├── Inventory Status Report
│   ├── Account Holder (Customer) Report
│   └── Defaulter Report
└── Settings
    ├── Company Info
    ├── Installment Types
    └── Backup / Restore
```

---

## Database Overview

- **SQLite** stored at a configurable local path
- All tables use `id` as auto-increment primary key
- Soft deletes (`deleted_at`) on customers, employees, products, accounts
- All monetary values stored as **integers (paisas/fils)** — display divided by 100

---

## Key Business Rules

- A customer can have **multiple active accounts** (multiple items on installment)
- Closing an account requires balance = 0 OR a forced close with discount slip
- When an item is returned, the account balance adjusts; if no remaining items, account closes
- Recovery Man is assigned **per account**, not per customer (can be reassigned)
- Installment plan can be changed mid-way; remaining balance redistributes
- Daily recovery: checker marks received installments — each mark inserts a payment record
- Defaulter = customer with remaining balance older than N days (configurable)

---

## What NOT to Build in MVP

- SMS sending (keep UI stubs, disable feature)
- Expense tracking module (schema only, no UI)
- Script Writer module
- Suggestions & Feedback module
- Advanced commission calculations
- Multi-branch support

---

## File Conventions

```
app/
├── Models/          → Eloquent models (Customer, Account, Product, Employee, etc.)
├── Http/Livewire/   → All Livewire components (one per screen)
├── Services/        → Business logic (RecoveryService, SaleService, ReportService)
├── Exports/         → PDF report classes
└── Providers/

resources/views/
├── livewire/        → Livewire blade views
├── layouts/         → App shell layout
└── reports/         → Print-ready report blade templates

database/
├── migrations/      → Ordered migrations
└── seeders/         → Demo data seeders
```

---

## Coding Conventions

- **Models**: PascalCase, singular (`Customer`, `Account`, `Product`)
- **Tables**: snake_case, plural (`customers`, `accounts`, `products`)
- **Livewire**: one component per screen, named by module (`Inventory/PurchasePoint`, `Sales/NewSale`)
- **Services**: injected, not static — all business logic goes here, not in Livewire components
- **No raw SQL** — use Eloquent everywhere
- **Validation**: Laravel Form Requests or inline `$this->validate()` in Livewire
- All amounts displayed with **PKR formatting** (e.g., `12,500`)
- Dates displayed as `dd/Mon/yyyy` (e.g., `16/Apr/2025`) to match old system

---

## UX Conventions

### Action Summaries
- **Every** save/create/update/delete/close/activate/transfer action MUST show a summary banner above the form after completion
- Summary appears as a card with a colored left border (green for success, red for delete/close, orange for returns)
- Summary is dismissible with an × button, stored in a `$actionSummary` (or similar) public property
- The form below the summary resets and is immediately ready for a new entry — no separate "Save & Add New" button needed
- Summary should include key details of what was done: IDs, names, amounts, status changes

### Searchable Select Fields
- All dropdown/select fields that reference another entity (Customer, Product, Employee, Account, Supplier) MUST use the `<x-searchable-select>` component
- Search filters by **ID** (primary) and **name/label** (secondary) — users often search by ID number
- Options format: `[['id' => 1, 'label' => 'Name'], ...]` — passed from the Livewire render method
- Each option displays `#ID` prefix for easy identification

---

## NativePHP Notes

- Window title: `Installment Management System`
- Min window size: `1024 × 768`
- App icon: required (place in `resources/` and configure in `NativeAppServiceProvider`)
- File storage: use `storage_path()` — NativePHP maps this to OS app data folder
- SQLite path: `storage/app/ims.sqlite`
- Printing: open report in a new NativePHP window with `NativePHP::openUrl()` or use system print dialog

---

## Report Printing

- Reports render as Blade views styled for A4 print
- Use `window.print()` triggered from a NativePHP menu item or button
- Each report has a "Print" button that opens a print-formatted view
- Reports include: company name, report title, date/time generated, page numbers

---

## Subscription & Backup (Online Features)

- On startup, check license key validity via `POST /api/v1/validate-license`
- If offline, use cached validation (valid for 7 days)
- Backup: ZIP the SQLite file and upload to S3-compatible endpoint
- Restore: download and replace local SQLite (with confirmation dialog)

---

## When Adding New Tasks

Each task should:
1. Create/modify **one migration** (if schema change needed)
2. Create/update **one Model**
3. Create **one Livewire component**
4. Create **one Blade view**
5. Add **route** in `routes/web.php`
6. Add **navigation entry** if applicable
7. Write **at least 2 feature tests**

Do not combine multiple screens into one task.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `DevForce 141 Standards` — Team-specific conventions for DevForce 141 Laravel projects. Covers the Action Pattern with laravel-actions, centralised Session/Cache, semantic entity naming, data-attribute JS hooks, Pest architecture tests, UTC timestamps, enums over magic strings, and commit discipline. Extends — never replaces — standard Laravel skills.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`, `php artisan tinker --execute "..."`).
- Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `php artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `php artisan config:show [key]`.
- To inspect routes, run `php artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app\Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app\Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
