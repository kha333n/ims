<div x-data="{ detailed: false }" class="max-w-4xl mx-auto pb-12">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-navy-800">User Manual</h1>
            <p class="text-sm text-gray-500">Installment Management System &mdash; Quick Reference Guide</p>
        </div>
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                <input type="checkbox" x-model="detailed" class="rounded border-gray-300 text-navy-600 focus:ring-navy-400">
                Show Details
            </label>
            <a href="{{ route('dashboard') }}" class="px-3 py-1.5 text-sm font-medium text-navy-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                &larr; Back to App
            </a>
        </div>
    </div>

    {{-- Table of Contents --}}
    <div class="bg-white rounded-lg shadow px-5 py-4 mb-6">
        <h2 class="text-sm font-bold text-navy-800 mb-2">Contents</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1 text-sm">
            <a href="#getting-started" class="text-navy-600 hover:underline">1. Getting Started</a>
            <a href="#inventory" class="text-navy-600 hover:underline">2. Inventory</a>
            <a href="#customers" class="text-navy-600 hover:underline">3. Customers</a>
            <a href="#sales" class="text-navy-600 hover:underline">4. Making a Sale</a>
            <a href="#recovery" class="text-navy-600 hover:underline">5. Recovery (Collections)</a>
            <a href="#returns" class="text-navy-600 hover:underline">6. Returns</a>
            <a href="#accounts" class="text-navy-600 hover:underline">7. Account Management</a>
            <a href="#hr" class="text-navy-600 hover:underline">8. Employees & Payroll</a>
            <a href="#expenses" class="text-navy-600 hover:underline">9. Expenses</a>
            <a href="#reports" class="text-navy-600 hover:underline">10. Reports</a>
            <a href="#settings" class="text-navy-600 hover:underline">11. Settings & Backup</a>
            <a href="#shortcuts" class="text-navy-600 hover:underline">12. Keyboard Shortcuts</a>
        </div>
    </div>

    {{-- Sections --}}
    <div class="space-y-4">

        {{-- 1. Getting Started --}}
        <section id="getting-started" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">1. Getting Started</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p>When you first open IMS, the setup wizard guides you through:</p>
                <ol class="list-decimal list-inside space-y-1 ml-2">
                    <li><strong>Company Details</strong> &mdash; Enter your company name, address, and phone (appears on all reports)</li>
                    <li><strong>Owner Account</strong> &mdash; Create your admin username and password</li>
                    <li><strong>Recovery Key</strong> &mdash; Write this down! It's your only way to reset a forgotten password</li>
                    <li><strong>License</strong> &mdash; Enter your license key to activate the app</li>
                </ol>
                <p>After setup, you'll see the <strong>Dashboard</strong> with a summary of your business: active accounts, receivables, today's collections, and stock levels.</p>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Quick Toolbar:</strong> The toolbar below the menu has shortcut buttons for the most common tasks &mdash; New Purchase, New Sale, Recovery Entry, New Customer, and Expense Entry.</p>
                <p><strong>Navigation:</strong> Use the top menu bar to access all modules. Menus are grouped by function: Items, Management, Recovery, Reports, Financial, and Settings.</p>
                <p><strong>Dashboard Widgets:</strong> The dashboard shows active accounts count, total receivables, today's collections, monthly comparison, profit summary, defaulters alert, recent payments/sales, recovery performance, and low stock alerts.</p>
            </div>
        </section>

        {{-- 2. Inventory --}}
        <section id="inventory" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">2. Inventory (Products & Stock)</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p><strong>Products</strong> (<em>Items &rarr; Products</em>): View, add, edit, or delete products. Each product has a name, sale price, purchase price, and current stock quantity.</p>
                <p><strong>Suppliers</strong> (<em>Items &rarr; Suppliers</em>): Manage your suppliers. You can set per-supplier pricing for each product.</p>
                <p><strong>New Purchase</strong> (<em>Items &rarr; New Purchase</em>): Record stock purchases. Select a supplier, add products with quantities and rates, then save. Stock increases automatically.</p>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Adding a Product:</strong></p>
                <ol class="list-decimal list-inside ml-2 space-y-1">
                    <li>Go to Items &rarr; Products</li>
                    <li>Click "Add Product"</li>
                    <li>Fill in: Name (required), Sale Price, Purchase Price, Quantity</li>
                    <li>Optionally add: Supplier, Brand, Model, Color, Category, Image</li>
                    <li>Click Save</li>
                </ol>
                <p><strong>Recording a Purchase:</strong></p>
                <ol class="list-decimal list-inside ml-2 space-y-1">
                    <li>Go to Items &rarr; New Purchase</li>
                    <li>Select the Purchase Date and Supplier</li>
                    <li>Select a Product, enter Rate and Quantity, click "+ Add"</li>
                    <li>Repeat for multiple items</li>
                    <li>Review the items table and total amount</li>
                    <li>Click "Save Purchase"</li>
                </ol>
                <p>The right panel shows current stock and supplier price comparison when you select a product. The "Best" indicator highlights the cheapest supplier.</p>
                <p><strong>Stock is FIFO:</strong> When items are sold, the oldest stock (earliest purchase batch) is reduced first.</p>
            </div>
        </section>

        {{-- 3. Customers --}}
        <section id="customers" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">3. Customers</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p><strong>Add Customer</strong> (<em>Management &rarr; Add Customer</em>): Register a new customer with their name, father's name, mobile number(s), CNIC, address, and reference person.</p>
                <p><strong>Customer List</strong> (<em>Management &rarr; Customers</em>): Search and browse all customers. Click on a customer to view their complete profile and account history.</p>
                <p><strong>Customer Detail:</strong> Shows all customer information, their accounts (active/closed), items purchased, payments made, and remaining balances.</p>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Customer Fields:</strong></p>
                <ul class="list-disc list-inside ml-2 space-y-1">
                    <li><strong>Name</strong> &mdash; Required</li>
                    <li><strong>Father Name</strong> &mdash; Optional</li>
                    <li><strong>Mobile</strong> &mdash; Format: 03XX-XXXXXXX</li>
                    <li><strong>Mobile 2</strong> &mdash; Optional second number</li>
                    <li><strong>CNIC</strong> &mdash; Format: XXXXX-XXXXXXX-X (13 digits)</li>
                    <li><strong>Home Address / Shop Address</strong> &mdash; Two separate address fields</li>
                    <li><strong>Reference</strong> &mdash; Who referred this customer</li>
                </ul>
                <p>A single customer can have <strong>multiple accounts</strong> (multiple items on installment at the same time). Each account tracks its own balance independently.</p>
                <p>In Customer Detail, click on an account row to expand it and see payments, items, and installment plan details.</p>
            </div>
        </section>

        {{-- 4. Making a Sale --}}
        <section id="sales" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">4. Making a Sale</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p>Go to <em>Management &rarr; New Sale</em> (or use the Quick Toolbar).</p>
                <ol class="list-decimal list-inside space-y-1 ml-2">
                    <li><strong>Select Customer</strong> &mdash; Search by ID or name. Click "+ New" to add inline.</li>
                    <li><strong>Add Items</strong> &mdash; Select products and quantities</li>
                    <li><strong>Set Installment Plan</strong> &mdash; Choose Daily, Weekly, or Monthly. Set the amount per period.</li>
                    <li><strong>Assign Staff</strong> &mdash; Select the Sale Man and Recovery Man</li>
                    <li><strong>Enter Advance & Discount</strong> &mdash; Optional upfront payment and discount</li>
                    <li><strong>Click "Create Sale"</strong></li>
                </ol>
                <p>A new Account is created with a unique Account #. The remaining balance = Total - Advance - Discount.</p>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Installment Types:</strong></p>
                <ul class="list-disc list-inside ml-2 space-y-1">
                    <li><strong>Daily</strong> &mdash; Customer pays every day</li>
                    <li><strong>Weekly</strong> &mdash; Customer pays on a specific day of the week (e.g., Monday)</li>
                    <li><strong>Monthly</strong> &mdash; Customer pays on a specific date each month (e.g., 15th)</li>
                </ul>
                <p>The system calculates an estimated completion time based on the remaining balance and installment amount.</p>
                <p><strong>Slip Number:</strong> Optional reference number for your physical receipt/slip.</p>
                <p><strong>Multiple Items:</strong> You can add multiple products to a single sale. Each becomes an Account Item linked to the same account.</p>
            </div>
        </section>

        {{-- 5. Recovery --}}
        <section id="recovery" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">5. Recovery (Collecting Payments)</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p>Go to <em>Recovery &rarr; Recovery Entry</em> (or use the Quick Toolbar).</p>
                <ol class="list-decimal list-inside space-y-1 ml-2">
                    <li>Select <strong>Recovery Man</strong> and <strong>Category</strong> (Daily/Weekly/Monthly)</li>
                    <li>Click <strong>"Load"</strong> to see all assigned accounts</li>
                    <li><strong>Check the box</strong> next to each customer who paid today</li>
                    <li>Adjust the <strong>Amount</strong> if different from the standard installment</li>
                    <li>Click <strong>"Submit Recovery"</strong> to record all payments</li>
                </ol>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Color Codes in Recovery Table:</strong></p>
                <ul class="list-disc list-inside ml-2 space-y-1">
                    <li><span class="text-green-600 font-medium">Green</span> &mdash; Already paid today</li>
                    <li><span class="text-red-600 font-medium">Red</span> &mdash; Overdue payment</li>
                    <li>White &mdash; Due (normal)</li>
                    <li><span class="text-blue-600 font-medium">Blue</span> &mdash; Currently selected row</li>
                </ul>
                <p><strong>Keyboard Navigation:</strong> Use arrow keys (Up/Down) to move between rows, Space or Enter to select, Tab to edit the amount, Esc to go back. This makes data entry very fast without using the mouse.</p>
                <p><strong>Master Checkbox:</strong> Click the checkbox in the header to select all visible accounts at once.</p>
                <p>If a customer has already paid today, a "duplicate" note appears. You can still record an additional payment if needed.</p>
            </div>
        </section>

        {{-- 6. Returns --}}
        <section id="returns" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">6. Processing Returns</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p>Go to <em>Management &rarr; Return Point</em>.</p>
                <ol class="list-decimal list-inside space-y-1 ml-2">
                    <li>Select <strong>Customer</strong> and <strong>Account</strong></li>
                    <li>Select the <strong>Item to Return</strong> from the dropdown</li>
                    <li>The return amount is auto-calculated. You can adjust it.</li>
                    <li>Choose: <strong>Restock</strong> (add back to inventory) or <strong>Scrap</strong> (damaged, don't add back)</li>
                    <li>Click <strong>"Process Return"</strong></li>
                </ol>
                <p>The account balance adjusts automatically. If all items are returned, the account closes.</p>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Restock vs Scrap:</strong></p>
                <ul class="list-disc list-inside ml-2 space-y-1">
                    <li><strong>Restock</strong> &mdash; Item goes back into inventory (stock quantity increases). Use when item is in good condition.</li>
                    <li><strong>Scrap</strong> &mdash; Item is not added back to stock. Use for damaged/broken items.</li>
                </ul>
                <p>The returning amount is deducted from the account's remaining balance. You can enter a lower amount than the item price if the item has depreciated.</p>
            </div>
        </section>

        {{-- 7. Account Management --}}
        <section id="accounts" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">7. Account Management</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p><strong>Close/Activate Account</strong> (<em>Management &rarr; Account Closure</em>):</p>
                <ul class="list-disc list-inside ml-2 space-y-1">
                    <li><strong>Close</strong> &mdash; Select RM &rarr; Customer &rarr; Account. Optionally enter a discount and slip #. Click "Close Account".</li>
                    <li><strong>Activate</strong> &mdash; Switch to "Activate" mode. Select a closed account to reopen it.</li>
                </ul>
                <p><strong>Transfer Account</strong> (<em>Management &rarr; Account Transfer</em>): Move a customer's accounts from one Recovery Man to another.</p>
                <p><strong>Update Installment Plan</strong> (<em>Management &rarr; Installment Update</em>): Change the installment type, amount, or schedule for an existing account.</p>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Closing with Remaining Balance:</strong> You can close an account even with outstanding balance. Enter a discount amount to write off the remaining. This is recorded as a loss in financial reports.</p>
                <p><strong>Installment Update:</strong> When you change the plan, the remaining balance is redistributed. You can set the new amount and the system calculates the number of periods, or set the number of periods and the system calculates the per-period amount.</p>
                <p><strong>Transfer:</strong> All active accounts for the customer are moved to the new Recovery Man at once.</p>
            </div>
        </section>

        {{-- 8. HR & Payroll --}}
        <section id="hr" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">8. Employees & Payroll</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p><strong>Sale Men</strong> (<em>Management &rarr; Sale Men</em>): Add and manage sale staff with their commission percentage and salary.</p>
                <p><strong>Recovery Men</strong> (<em>Management &rarr; Recovery Men</em>): Add and manage recovery agents with their area, rank, commission, and salary.</p>
                <p><strong>Payroll</strong> (<em>Management &rarr; Payroll</em>): View each employee's salary, pending commissions, balance, and make payments.</p>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Commission Tracking:</strong> Commissions are calculated per sale based on the employee's commission %. Each commission record is tracked individually and marked as paid when a payroll payment is made.</p>
                <p><strong>Payroll Payment:</strong></p>
                <ol class="list-decimal list-inside ml-2 space-y-1">
                    <li>Go to Management &rarr; Payroll</li>
                    <li>Click "Accrue Monthly Salaries" at the start of each month to add salary to each employee's balance</li>
                    <li>Click "Pay" next to an employee</li>
                    <li>Enter the payment amount and description</li>
                    <li>Click "Process Payment"</li>
                </ol>
                <p>Payments can be made at any time &mdash; partial, full, or even in advance. The balance tracks what is owed or overpaid.</p>
            </div>
        </section>

        {{-- 9. Expenses --}}
        <section id="expenses" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">9. Daily Expenses</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p>Go to <em>Management &rarr; Expenses</em> (or use the Quick Toolbar).</p>
                <ol class="list-decimal list-inside space-y-1 ml-2">
                    <li>Enter <strong>Amount</strong>, <strong>Description</strong>, <strong>Date</strong>, and <strong>Category</strong></li>
                    <li>Click <strong>"Save Expense"</strong></li>
                </ol>
                <p>Expenses are linked to the logged-in user for tracking. They appear in financial reports but are not deducted from any employee's balance.</p>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p>Expenses can be entered one by one throughout the day, or all at once at the end of the day/month. The category field auto-suggests from previously used categories.</p>
                <p>The right panel shows a filtered list of recent expenses with date range and category filters.</p>
            </div>
        </section>

        {{-- 10. Reports --}}
        <section id="reports" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">10. Reports</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p>All reports follow the same pattern: set date range and optional filters, click <strong>"Generate"</strong>, then view or print.</p>
                <p><strong>Standard Reports</strong> (Reports menu):</p>
                <ul class="list-disc list-inside ml-2 space-y-0.5">
                    <li><strong>Item Sale Report</strong> &mdash; All sales with customer, item, and payment details</li>
                    <li><strong>Daily Recovery</strong> &mdash; Collections by date, filterable by RM and area</li>
                    <li><strong>Monthly Recovery</strong> &mdash; Aggregated monthly collection summary</li>
                    <li><strong>Return Report</strong> &mdash; All item returns with reasons</li>
                    <li><strong>Salesman Report</strong> &mdash; Sales performance by salesman</li>
                    <li><strong>Inventory Report</strong> &mdash; Current stock with batch breakdown</li>
                    <li><strong>Customer Account Report</strong> &mdash; Per-account details across all customers</li>
                    <li><strong>Defaulter Report</strong> &mdash; Overdue accounts past the configured threshold</li>
                </ul>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Financial Reports</strong> (Financial menu):</p>
                <ul class="list-disc list-inside ml-2 space-y-0.5">
                    <li><strong>Cash Book</strong> &mdash; Daily cash inflows and outflows</li>
                    <li><strong>Financial Ledger</strong> &mdash; Complete transaction history</li>
                    <li><strong>Profit & Loss</strong> &mdash; Revenue vs expenses summary</li>
                    <li><strong>Receivables Aging</strong> &mdash; Outstanding amounts grouped by age</li>
                    <li><strong>Collection Performance</strong> &mdash; Recovery man efficiency</li>
                    <li><strong>Commissions</strong> &mdash; Employee commission calculations</li>
                    <li><strong>Inventory Valuation</strong> &mdash; Total stock value</li>
                    <li><strong>Losses & Write-offs</strong> &mdash; Bad debts from closed accounts</li>
                </ul>
                <p><strong>Printing:</strong> All reports are designed for A4 paper. Click the "Print" button or use Ctrl+P. The report header shows your company name, address, and phone from Settings.</p>
                <p><strong>Filters:</strong> Most reports can be filtered by date range, Sale Man, Recovery Man, and status (Active/Closed).</p>
            </div>
        </section>

        {{-- 11. Settings --}}
        <section id="settings" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">11. Settings & Backup</h2>
            <div class="text-sm text-gray-700 space-y-2">
                <p><strong>Company Settings</strong> (<em>Settings &rarr; Company</em>): Update company name, address, phone, and defaulter days threshold.</p>
                <p><strong>Backup & Restore</strong> (<em>Settings &rarr; Backup</em>): Create encrypted backups of your data. Restore from any previous backup point. Auto-backup runs every 12 hours.</p>
                <p><strong>License</strong> (<em>Settings &rarr; License</em>): View license status, activate/deactivate, and verify online.</p>
                <p><strong>User Management</strong> (<em>Settings &rarr; Users</em>): Add/edit app users and assign roles (Owner, Sale Man, Recovery Man).</p>
            </div>
            <div x-show="detailed" x-collapse class="mt-3 text-sm text-gray-600 border-t pt-3 space-y-2">
                <p><strong>Backup:</strong> Backups are encrypted .imsb files stored in your AppData folder. They include the database and all uploaded files (product images). Up to 7 backups are kept automatically.</p>
                <p><strong>Restore:</strong> Restoring replaces your current data with the backup. A confirmation dialog warns you before proceeding. This cannot be undone.</p>
                <p><strong>Defaulter Threshold:</strong> The number of days after which an unpaid account is considered "defaulting". This affects the Defaulter Report and dashboard alert.</p>
                <p><strong>Data Persistence:</strong> Your database, license, and files are stored outside the app folder. They survive app updates and reinstallation.</p>
            </div>
        </section>

        {{-- 12. Keyboard Shortcuts --}}
        <section id="shortcuts" class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">12. Keyboard Shortcuts</h2>
            <div class="text-sm text-gray-700">
                <p class="mb-2">These shortcuts work in the <strong>Recovery Entry</strong> screen for fast data entry:</p>
                <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                    <div class="flex gap-2"><kbd class="px-1.5 py-0.5 bg-gray-100 border rounded text-xs font-mono">&#8593; &#8595;</kbd> <span>Navigate between rows</span></div>
                    <div class="flex gap-2"><kbd class="px-1.5 py-0.5 bg-gray-100 border rounded text-xs font-mono">Space</kbd> <span>Select/deselect row</span></div>
                    <div class="flex gap-2"><kbd class="px-1.5 py-0.5 bg-gray-100 border rounded text-xs font-mono">Enter</kbd> <span>Select row</span></div>
                    <div class="flex gap-2"><kbd class="px-1.5 py-0.5 bg-gray-100 border rounded text-xs font-mono">Tab</kbd> <span>Edit amount field</span></div>
                    <div class="flex gap-2"><kbd class="px-1.5 py-0.5 bg-gray-100 border rounded text-xs font-mono">Esc</kbd> <span>Go back / deselect</span></div>
                    <div class="flex gap-2"><kbd class="px-1.5 py-0.5 bg-gray-100 border rounded text-xs font-mono">Ctrl+P</kbd> <span>Print (in reports)</span></div>
                </div>
            </div>
        </section>

        {{-- Glossary --}}
        <section class="bg-gray-50 rounded-lg shadow px-5 py-4">
            <h2 class="text-base font-bold text-navy-800 mb-2">Glossary</h2>
            <div class="text-sm text-gray-700 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1">
                <div><strong>RM</strong> &mdash; Recovery Man (collects payments)</div>
                <div><strong>SM</strong> &mdash; Sale Man (makes the sale)</div>
                <div><strong>Account</strong> &mdash; One installment plan for a customer</div>
                <div><strong>CID</strong> &mdash; Customer ID number</div>
                <div><strong>CNIC</strong> &mdash; National ID card number</div>
                <div><strong>PKR</strong> &mdash; Pakistani Rupees</div>
                <div><strong>Defaulter</strong> &mdash; Customer with overdue payments</div>
                <div><strong>Advance</strong> &mdash; Upfront payment at time of sale</div>
            </div>
        </section>
    </div>
</div>
