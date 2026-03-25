# 07 — Improvement & New Feature Tasks

Based on audit of existing system, old VB.NET screenshots, and user requirements.

Tasks ordered by dependency. Complete each fully before moving to next.

---

## Phase A: Schema & Data Model Fixes

### TASK-A01: Inventory — FIFO Stock Deduction on Sales

- Add `batch_number` (auto-generated string, e.g. "B-0001") to `purchases` table
- PurchasePoint: auto-assign batch_number on save
- Add `remaining_qty` column to `purchases` — tracks how many units from this batch are still in stock
- On save: `remaining_qty` = `quantity` (full batch available)
- On NewSale: deduct from OLDEST batch first (FIFO — lowest ID with remaining_qty > 0)
  - Loop through purchase batches ordered by purchase_date ASC, deduct until item quantity fulfilled
  - If a batch doesn't have enough, take what's available and move to next batch
- On Return (restock): increment `remaining_qty` on the original batch (or latest batch if original unknown)
- Product.quantity remains the sum of all remaining_qty for that product (or kept in sync)
- **Impact:** PurchasePoint, NewSale, ReturnPoint, InventoryReport
- **Schema:** migration adds `batch_number` (string nullable), `remaining_qty` (integer) to purchases

---

### TASK-A02: Customer — Dual Mobile, CNIC/Mobile Formatting

- Add `mobile_2` column (nullable) to customers
- Customer already has `home_address` and `shop_address` — ensure both visible in forms
- Mobile formatting: Pakistani `03XX-XXXXXXX` — validate regex `^0\d{3}-?\d{7}$` (11 digits)
- CNIC formatting: `XXXXX-XXXXXXX-X` — validate regex `^\d{5}-?\d{7}-?\d$` (13 digits)
- Update forms: AddCustomer, CustomerDetail, NewSale quick-add modal
- Reports: show primary mobile only (keep compact)
- **Schema:** migration adds `mobile_2` to customers

---

### TASK-A03: Employee — Unified Salary + Commission for Both Types

- Both `salary` and `commission_percent` columns already exist on employees table
- Show BOTH fields on BOTH SaleManList and RecoveryManList forms
- Sale Man: default commission > 0, salary = 0 (but editable)
- Recovery Man: default salary > 0, commission = 0 (but editable)
- Ensure CNIC field visible on both forms (column exists)
- **Schema:** none — UI only

---

### TASK-A04: Account Closure — Remove Discount Slip Requirement

- Closing an account with remaining > 0 does NOT require discount_slip
- User can close at any time, discount_slip is always optional
- When activating a closed account, show previous discount info (read-only)
- Remove any required validation on discount_slip — keep it nullable
- **Schema:** none

---

## Phase B: Return Point Improvements

### TASK-B01: Return Point — Improved Flow & Auto-Calculate

- Allow starting from Customer directly (searchable by ID/name), not just RM
- Select account → select item to return
- Auto-populate returning_amount = item unit_price × item quantity
- User can override/reduce the amount
- Show account summary: total, advance, paid so far, remaining
- Keep inventory action choice (restock/scrap)
- On restock: increment product quantity AND purchase batch remaining_qty (FIFO reverse — add to newest batch)
- Functionally complete with old system features + our improvements (confirmation dialogs, loading states, etc.)
- **No schema change**

---

### TASK-B02: Return Point — Auto-Close Account If No Active Items

- After processing return, check if ALL account_items have `returned = true`
- If remaining_amount <= 0: auto-close account silently
- If remaining_amount > 0: prompt "All items returned. Close account and write off remaining balance?"
  - Yes → close + record write-off in financial ledger
  - No → leave account open (user may collect remaining manually)
- **No schema change**

---

## Phase C: Installment Plan Update

### TASK-C01: Dual Calculation Mode

- Two input fields: "Amount Per Period" and "Number of Periods"
- Edit either → other auto-calculates (live)
- amount = ceil(remaining / periods)
- periods = ceil(remaining / amount)
- Last-edited field wins, other adjusts
- **No schema change**

---

## Phase D: Problem Management

### TASK-D01: Problem Entry — Enhanced Fields

- Add `status`: open / in_progress / resolved / escalated (replace `closed` boolean)
- Add `severity`: low / medium / high / critical
- Add `recovery_man_id` FK (link to recovery man instead of string)
- Keep `manager`, `checker`, `branch` as text fields
- Show account details: customer, phone, items, total, remaining, days overdue
- Show problem history timeline per account
- **Accounting note on late payments:** Late/delayed payments are NOT recorded as losses. The account remains as "Accounts Receivable" in the books. Only when an account is forcibly closed with remaining > 0 does it become a loss/write-off. Late payments only affect defaulter/aging reports — no financial ledger entries until actual payment or write-off occurs.
- **Schema:** migration adds `status`, `severity`, `recovery_man_id` to problems. Migrate existing `closed=true` rows to `status='resolved'`

---

## Phase E: Employee Payroll & Commission System

### TASK-E01: Commission Tracking Schema

- New table: `commission_records`
  - id, employee_id (FK), account_id (FK — which sale generated this), amount (integer paisas), status (pending/paid/cancelled), paid_at (datetime nullable), paid_in_payroll_id (FK nullable), created_at, updated_at
- On NewSale: auto-create commission_record for sale_man (amount = total_amount × commission_percent / 100)
- On Return: IF admin setting `reduce_commission_on_refund` is true → mark related commission_record as cancelled (or create negative adjustment)
- Add setting: `reduce_commission_on_refund` (boolean, default false) — configurable in Settings
- **Schema:** migration creates commission_records, adds setting

---

### TASK-E02: Payroll Schema — Salary & Payment Tracking

- New table: `payroll_entries`
  - id, employee_id (FK), entry_type (salary_accrual/payment/advance/deduction), amount (integer paisas), period_month (string "YYYY-MM" — which month this salary is for), description (text nullable), recorded_by (FK users nullable), created_at, updated_at
- Employee `balance` column (integer, default 0):
  - Increments: salary accrual (monthly), commission records marked paid
  - Decrements: payments made to employee
  - Can go negative (advance paid before month-end)
- Salary accrual: auto-creates payroll_entry (type=salary_accrual) for each employee on month-end (or first visit of new month if not yet accrued). Amount = employee.salary
- **Schema:** migration creates payroll_entries, adds `balance` to employees

---

### TASK-E03: Payroll — Payment Screen

- Livewire component: `HR\Payroll`
- Route: `/hr/payroll`
- Employee table shows:
  - Employee Name, Type, Monthly Salary, Commission %
  - Current Balance (from employees.balance)
  - Pending Commission: sum of commission_records where status='pending' for this employee
  - Salary This Month: accrued or not yet
  - Total Due: balance + pending commission + (salary if not yet accrued this month)
  - Already Paid This Month
- "Pay" action: enter amount → creates payroll_entry (type=payment) → decrements balance
  - Also marks commission_records as paid (oldest first, up to payment amount minus salary portion)
- Payment can happen anytime — not tied to month end
- If paid partially: shows remaining
- If overpaid: balance goes negative, adjusted on next accrual
- Records to financial ledger (event_type: 'payroll', credit = amount)
- **Depends on:** TASK-E01, TASK-E02

---

### TASK-E04: Payroll — History & Reports

- Per-employee history: all payroll_entries + commission_records
- Filter by date range, employee, entry type
- Summary: total salary accrued, total commission earned, total paid, current balance
- Commission detail: which accounts generated commission, amount, status (pending/paid/cancelled)
- Print-ready report layout (A4 portrait)
- **Depends on:** TASK-E01, TASK-E02

---

## Phase F: Daily Expenses Module

### TASK-F01: Expenses Schema

- New table: `expenses`
  - id, amount (integer paisas), description (string), expense_date (date), category (string nullable), recorded_by (FK users nullable), employee_id (FK employees nullable), created_at, updated_at
- **Schema:** migration creates expenses table

---

### TASK-F02: Expense Entry Screen

- Livewire component: `Expenses\ExpenseEntry`
- Route: `/expenses`
- Fields: Amount, Description (required), Date (default today, can backdate), Category (text with datalist from previous entries)
- Auto-links to logged-in user + their employee record
- Shows today's expenses list below form
- Can add expenses one at a time or bulk at day/month end
- Records to financial ledger (event_type: 'expense', credit = amount)
- **Depends on:** TASK-F01

---

### TASK-F03: Expense Reports & Financial Integration

- Add expenses to Daily Cash Book ("Payments Out")
- Add expenses to Profit & Loss ("Operating Expenses" section)
- Add expenses to Credit & Debit summary
- Standalone expense report at `/financial/expenses`
  - Filter: date range, category, employee
  - Columns: Serial, Date, Description, Category, Amount, Recorded By
  - Group by category with subtotals, grand total
  - Print: A4 portrait
- **Depends on:** TASK-F02

---

## Phase G: Report Improvements

> **Principles:**
> - Remove redundant/duplicate columns — every column must earn its space
> - Use full proper names — no abbreviations (e.g. "Sale Man" not "SM", "Recovery Man" not "RM", "Account Number" not "Acc#")
> - If space allows after removing waste, add useful context columns
> - All reports must print cleanly on A4 paper (landscape or portrait as noted)
> - UI preview = print output (WYSIWYG)

### TASK-G01: Item Sale Report — Optimized

- Filters: Date Range, Recovery Man (optional), Sale Man (optional), Product (optional), Status (optional)
- Columns (optimized — removed redundant CID since customer name is shown):
  - Serial | Account | Date | Sale Man | Recovery Man | Customer Name | Phone | Item | Quantity | Total Amount | Advance | Paid | Remaining | Status
- Totals row: Total Amount, Advance, Paid, Remaining
- Print: A4 landscape
- **No schema change**

---

### TASK-G02: Daily Recovery Report — Optimized

- Filters: Recovery Man, Date Range, Area (auto-fills from selected Recovery Man)
- Header: Recovery Man name, Area, Date range
- Columns (clean — removed redundant data):
  - Serial | Date | Account | Customer Name | Paid Amount | Remaining | Status
- Group by date with subtotals if multi-day range
- Total: sum of Paid Amount
- Print: A4 portrait
- **No schema change**

---

### TASK-G03: Monthly Recovery Report — Aggregated

- Filters: Month (dropdown), Recovery Man (optional), Area (optional)
- Aggregated per-account view (not individual payments):
  - Serial | Account | Customer Name | Phone | Total Amount | Advance | Collected This Month | Collected All Time | Remaining | Recovery Man | Area
- Summary: total accounts, total collected this month, total outstanding
- Print: A4 landscape
- **No schema change**

---

### TASK-G04: Return Report — Optimized

- Filters: Date Range, Sale Man (optional), Customer (optional), Product (optional)
- Columns (removed redundant — sale date not needed when return date shown):
  - Serial | Account | Return Date | Customer Name | Phone | Recovery Man | Sale Man | Item | Quantity | Total Amount | Received | Remaining | Reason
- Totals: Total Amount, Received count
- Print: A4 landscape
- **No schema change**

---

### TASK-G05: Salesman Sales Report — Optimized

- Filters: Sale Man (required), Date Range, Status (optional)
- Columns (removed redundant SM ID — name is shown, removed slip if not commonly used):
  - Serial | Account | Slip Number | Date | Customer Name | Phone | Item | Quantity | Total Amount | Advance | Remaining | Status
- Footer: Commission summary — Total Sales × Commission % = Commission Earned
- Print: A4 landscape
- **No schema change**

---

### TASK-G06: Sales Detail Report — Comprehensive

- Filters: Date Range, Sale Man (optional), Recovery Man (optional), Product (optional), Status (optional)
- Columns (comprehensive but not redundant):
  - Serial | Account | Date | Customer Name | Phone | Sale Man | Recovery Man | Item | Quantity | Total Amount | Advance | Discount | Paid | Remaining | Installment Type | Status
- This replaces the need for a separate "all sales" view
- Totals for all monetary columns
- Print: A4 landscape
- **No schema change**

---

### TASK-G07: Inventory Report — With Purchase Batch Breakdown

- No filter needed (current snapshot)
- Columns:
  - Serial | Product Name | Batch Number | Purchase Date | Purchase Price | Sale Price | Quantity In Stock | Supplier
- Products with multiple batches show multiple rows
- Summary per product: total quantity
- Grand totals: total stock value at cost, total stock value at sale price
- Depends on TASK-A01
- Print: A4 portrait
- **No schema change beyond TASK-A01**

---

### TASK-G08: Customer Account Report — Per-Account

- Filters: Customer (searchable by ID/name) OR Account Number
- If customer selected: show ALL their accounts as separate entries
- Columns per account:
  - Account Number | Slip Number | Sale Date | Item | Quantity | Customer Name | Address | Phone | Total Amount | Advance | Paid | Remaining | Status
- Expandable: payment history per account (date, amount, type, collected by)
- Customer header: Customer ID, Name, Father Name, Phone, Address, CNIC
- Print: A4 portrait (multi-page if needed)
- **No schema change**

---

### TASK-G09: Defaulter Report — With Problem Sheet Integration

- Filters: Recovery Man (optional), Days Overdue (configurable, default from settings), Sale Man (optional)
- Columns (problem sheet style, optimized):
  - Serial | Account | Sale Date | Days Overdue | Last Payment Date | Days Since Payment | Customer Name | Phone | Address | Sale Man | Item | Total Amount | Paid Amount | Remaining | Short Amount | Promise Date | Status
- "Short Amount" = installment_amount × estimated missed periods (remaining / installment_amount approximation vs actual payments)
- "Promise Date" = latest new_commitment_date from problems table for this account
- Severity bands: 30-60 days (highlight yellow), 61-90 days (orange), 90+ days (red)
- Summary: count and total outstanding per severity band
- Print: A4 landscape
- **No schema change**

---

### TASK-G10: Print Layout & Company Branding Standardization

- **Company branding header** (similar to old Crystal Reports style, data from Settings):
  - Company Name — centered, bold, larger font
  - Company Address — centered below name
  - Company Phone — centered below address
  - All pulled from `settings` table (company_name, company_address, company_phone)
- **Report section:**
  - Report Title — centered, prominent
  - "Report Time:" with date/time — right aligned
  - Filter summary below title (e.g. "Recovery Man: Junaid | Area: Gulshan Ghazi | Date: 01/Mar/2025 - 17/Apr/2025")
- **Table:** bordered cells, header row with colored background, zebra striping on print
- **Totals:** bold, top-bordered, clearly separated from data rows
- **Footer:** "Copyright [Company Name]" centered + page numbers
- All tables must fit A4 paper (landscape or portrait per report)
- `@media print` CSS: hide nav, toolbars, buttons, show only report content
- Font size: 10-11px for table data, ensure readability
- UI preview matches print output exactly (WYSIWYG)
- Apply to ALL reports (operational + financial) via shared `report.blade.php` layout
- **No schema change, blade/CSS only**

---

## Phase H: Navigation & Integration

### TASK-H01: Expenses Navigation

- Quick toolbar: add "Expense Entry" button
- Management menu: "Daily Expenses" → `/expenses`
- Financial menu: "Expense Report" → `/financial/expenses`
- **Depends on:** TASK-F02

---

### TASK-H02: Payroll Navigation

- Management menu (HR section): "Payroll" → `/hr/payroll`
- Quick "Pay" action on SaleManList and RecoveryManList
- **Depends on:** TASK-E03

---

## Task Summary

| Phase | Tasks | Focus |
|-------|-------|-------|
| A — Schema & Data | A01–A04 (4) | FIFO stock, customer fields, employee fields, closure |
| B — Returns | B01–B02 (2) | Return flow, auto-close |
| C — Installments | C01 (1) | Dual calculation mode |
| D — Problems | D01 (1) | Enhanced problem management |
| E — Payroll | E01–E04 (4) | Commission tracking, salary, payment, reports |
| F — Expenses | F01–F03 (3) | Schema, entry, financial integration |
| G — Reports | G01–G10 (10) | Optimized reports + print standardization |
| H — Navigation | H01–H02 (2) | Menu entries |

**Total: 27 tasks**

### Dependency Graph

```
TASK-A01 (FIFO stock) ──→ TASK-G07 (inventory report)
TASK-A01 ──→ TASK-B01 (return restock uses batches)
TASK-A03 (employee fields) ──→ TASK-E01 (commission schema)
TASK-E01 ──→ TASK-E02 ──→ TASK-E03 ──→ TASK-E04
TASK-E03 ──→ TASK-H02
TASK-F01 ──→ TASK-F02 ──→ TASK-F03
TASK-F02 ──→ TASK-H01

Independent (start anytime):
  A02, A04, B02, C01, D01
  G01–G06, G08–G10
```

### Suggested Order

1. TASK-A02 — Customer dual mobile + formatting (quick)
2. TASK-A03 — Employee unified fields (quick)
3. TASK-A04 — Closure fix (quick)
4. TASK-A01 — FIFO stock + batch tracking
5. TASK-B01, B02 — Return improvements
6. TASK-C01 — Installment dual calc
7. TASK-D01 — Problem management
8. TASK-E01 → E02 → E03 → E04 — Payroll (sequential)
9. TASK-F01 → F02 → F03 — Expenses (sequential)
10. TASK-G10 — Print layout first (foundation for all reports)
11. TASK-G01–G09 — Individual reports
12. TASK-H01, H02 — Navigation last

### Key Accounting Notes

- **Late payments** are NOT losses. Account remains as "Accounts Receivable." Only becomes a loss when account is forcibly closed with remaining > 0 (write-off entry in financial ledger).
- **Commission** is tracked per-sale in `commission_records`. Status: pending → paid (when payroll processes) or cancelled (on refund, if setting enabled).
- **Salary** accrues monthly via `payroll_entries`. Employee `balance` tracks net owed.
- **Expenses** are recorded as credits in financial ledger, reducing net profit.
- **FIFO** ensures oldest purchased stock is sold first — purchase batches track remaining_qty.
