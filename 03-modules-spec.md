# 03 — Module Specifications

Each module maps to a set of Livewire components and routes.

---

## Module 1: Inventory Management

### 1.1 Product Management (CRUD)

**Route:** `/inventory/products`

**Screens:**
- **List View** — searchable table: Name, Price, Quantity, Supplier. Actions: Add, Edit, Delete.
- **Add/Edit Form** — inline or modal

**Fields:**
| Field | Required | Notes |
|-------|----------|-------|
| Item Name | ✅ | |
| Item Price | ✅ | Sale price per unit |
| Quantity | ✅ | Current stock (can be 0) |
| Company/Supplier | ❌ | Free text or link to suppliers table |
| Brand | ❌ | New optional field |
| Model Number | ❌ | New optional field |
| Color | ❌ | New optional field |
| Category | ❌ | New optional field |
| Image | ❌ | Upload local file, stored in app storage |
| Notes | ❌ | |

**Rules:**
- Cannot delete a product that has active account items
- Deleting soft-deletes only
- Price stored in paisa (multiply by 100 on save, divide on display)

---

### 1.2 Purchase Point (Stock Entry)

**Route:** `/inventory/purchase`

**Purpose:** Add stock for existing products (no new products here — use Product Management for that).

**Screen:**
- Date picker (defaults to today)
- Product Name dropdown (searchable)
- Rate (purchase price — optional, for records only)
- Quantity input
- "Add" button → adds to line items table
- Line items: Name | Price | Quantity | Total
- Total Amount shown
- "Save" button → inserts purchase record, increments product quantity

**Stock Information panel:** shows current stock of selected product (updates on selection).

**Rules:**
- Rate is optional — if not entered, just log quantity increase
- Quantity must be > 0
- Multiple items can be added in one purchase session

---

## Module 2: Customer Management

### 2.1 Add New Customer

**Route:** `/customers/new` or modal from sale screen

**Fields:**
| Field | Required | Notes |
|-------|----------|-------|
| Customer ID | Auto | Auto-incremented, shown on form |
| Customer Name | ✅ | |
| Father Name | ❌ | |
| Mobile # | ❌ | Format: 0xxx-xxxxxxx |
| CNIC # | ❌ | Format: xxxxx-xxxxxxx-x |
| Reference | ❌ | Who referred them |
| Home Address | ❌ | |
| Shop Address | ❌ | |

**Rules:**
- Customer ID assigned on save (not editable)
- Can be opened as a popup from the New Sale screen

---

### 2.2 Customer List (Clients Management)

**Route:** `/customers`

**Screen:**
- Search by name
- Table: ID | Name | Contact | Address | CNIC | Reference | Balance (remaining)
- Actions per row: View Detail, Edit, Transfer Recovery Man

**Balance** column shows sum of all remaining balances across active accounts.

---

### 2.3 Customer Detail / Payment Recovery View

**Route:** `/customers/{id}`

Shows:
- Customer info
- All accounts with status, balance
- Quick payment entry (Payment Recovery popup): Amount, Transaction Type (Paid/Received), Date, Remarks

---

### 2.4 Account Level Transfer (Recovery Man Reassignment)

**Route:** `/customers/transfer` or accessible from customer detail

**Fields:**
- Customer ID (with name auto-fill)
- Total Amount, Total Remaining (display only)
- From Recovery Man (auto-filled from account)
- To Recovery Man (dropdown)
- "Transfer Account" button

**Rules:**
- All accounts of the customer under the old RM are moved to the new RM
- Logged in `account_transfers` table

---

### 2.5 Account Closure / Activation

**Route:** `/customers/accounts/close` or from account detail

**Modes:** Close Account / Activate Account (radio buttons)

**Close Mode:**
- Recovery Man dropdown
- Customer ID dropdown (filtered by RM)
- Discount Amount (if any remaining balance is waived)
- Discount Slip # 
- Shows: Party Name, Address, Contact, Installment Amount, RM ID, Total Accounts, Total Amount, Total Collection, Balance

**Rules:**
- Closing with balance requires entering discount amount + slip number
- Activating requires account to be in 'closed' status
- Status change logged

---

### 2.6 Party Installment Type Update

**Route:** `/customers/installment-update`

**Fields:**
- Party ID (with name auto-fill, shows current plan)
- New Inst Type (dropdown: Daily/Weekly/Monthly)
- New Day (for weekly/monthly)
- New Installment Amount

**Rules:**
- Change is logged in `installment_plan_changes`
- Remaining balance stays the same
- Previous plan archived, not deleted

---

## Module 3: Sales

### 3.1 New Sale (Sale Point)

**Route:** `/sales/new`

**Layout:** Two-panel — Customer Info (left) + Item Detail (right)

**Customer Info panel:**
- Party ID (dropdown/search — triggers auto-fill)
- Customer Name, Father Name, Mobile, Address (read-only from customer record)
- Reference (read-only)

**Item Detail panel:**
- Item (dropdown/search — shows stock info on right: Item, Rate, Quantity)
- Total Price
- Advance
- Discount (default 0)
- Inst Type (Daily/Weekly/Monthly dropdown)
- Day (for weekly/monthly)
- Rem. Amt (calculated: Total - Advance - Discount)
- Inst. Amt (installment per period)
- Total Insts. (calculated count)
- "Multiple Item Sale" support — add more items

**Sales Information:**
- Slip # (auto or manual)
- Sale Man Name (dropdown)
- Recovery Man (dropdown — filtered by area optionally)
- Area (auto-filled from Recovery Man)
- Sale Date (defaults to today)

**"Proceed" button** → creates account + account_items, decrements stock

**Rules:**
- Customer must exist (or create new from this screen)
- At least one item required
- Total = sum of all item prices × quantities
- Remaining = Total - Advance - Discount
- Installment Amount must be > 0
- Stock must be available (warn if not enough)

---

### 3.2 Return Point

**Route:** `/sales/returns`

**Fields:**
- RM Name (dropdown)
- Customer ID (dropdown — filtered by RM)
- Customer Name (auto-fill)
- Account # (dropdown — shows all accounts for customer)
- Phone, Slip#, Sale Man, Area, Sale Date (auto-fill from account)

**Item Detail (after account selected):**
- Item (from account_items)
- Price, Quantity, Total, Total Price, Advance, Paid Amount, Remaining Amount, New Installment Amount

**Return Detail:**
- Returning Amount (editable)
- Return Date (default today)
- Reason (text)
- Inventory Action (Restock / Scrap — dropdown) ← new improvement

**"Next" → Confirm → Save**

**Rules:**
- Creates `returns` record
- Updates `account_items.returned = true`
- If `inventory_action = restock`: increments `products.quantity`
- Adjusts account balance
- If all items returned and balance = 0, closes account

---

## Module 4: Recovery

### 4.1 Client Account Status (Recovery Entry)

**Route:** `/recovery`

**Purpose:** Recovery Man's daily checklist — mark payments as received.

**Filters:**
- Recovery Man (dropdown)
- Category / Installment Type (Daily/Weekly/Monthly)
- Update Status button

**Table columns:**
- CID | RMID | RM Name | Customer Name | Phone | Address | CNIC | Balance | Category | Checkbox (mark received)

**Checking a customer** inserts a payment record for the installment amount.

**Rules:**
- Cannot mark same customer twice on same date
- Inserts into `payments` table with today's date
- Balance updates immediately

---

## Module 5: Reports

All reports share:
- Date range pickers (From / To)
- Print button (opens print view)
- Company header on print

---

### 5.1 Item Sale Report (Item Wise Sales List)

**Filters:** Date range, Recovery Man (optional)

**Columns:** TxID | Date | Sales Man | Recovery Man | CID | Customer Name | Customer Address | Customer Ph | Item Name | Total Amount | Advance | Paid | Balance

**Also:** Per-day breakdown for each item (Item Detail Report mode)

---

### 5.2 Item Detail Report

**Filters:** Date range

**Columns:** Sr# | Name (item) | Quantity | Price | Total | Date

Grouped by item with subtotals.

---

### 5.3 Daily Recovery Report

**Filters:** Date range, Area

**Header:** Recovery Man name, Area

**Columns:** Sr# | Date | Acc# | Paid Amount | Remaining | Status

---

### 5.4 Recovery Monthly Report

**Filters:** Date range, Area

**Columns:** Sr# | Total | Advance | Remaining | Previous | R.M.ID | Date | Discount | New Sale | Area

---

### 5.5 Item Return Report

**Filters:** Date range

**Columns:** Sr# | Acc# | Sale Date | RM | SM | Customer | Item | QTY | Total | Received | Remaining | Mobile | Reason

---

### 5.6 SaleMan Sales Report

**Filters:** Sale Man ID, Date range, Status (Active/Closed/All)

**Columns:** Sr# | Acc# | Slip# | Date | SM ID | SM Name | Item | Customer | Total | Advance | Remaining | Status

Also shows: Quantity, Customer phone/detail.

---

### 5.7 Inventory Status

No filters.

**Columns:** Sr# | Item | Price | Quantity | Company (Supplier)

---

### 5.8 Account Holder (Customer) Report

**Filter:** Account Number

**Columns:** SR# | Account ID | Slip No | Date | Item | Name | Address | Phone | Status

---

### 5.9 Defaulter Report

**Filters:** Recovery Man ID, Days (overdue threshold), By Sale Date toggle, Sale Date

**Header:** Recovery Man, Senior Recovery Man (checker), Date

**Columns:** Sr# | Record# | Date | Days | Name | Address | Phone# | Sale Man | Item | Total Amount | Remaining Balance | Paid Amount | Short Amount | Promise | Status | Action

---

## Module 6: HR / Staff Management

### 6.1 Sale Man Management

**Route:** `/hr/sale-men`

**Fields:**
| Field | Required |
|-------|----------|
| Employee ID | Auto |
| Employee Name | ✅ |
| Employee Phone # | ❌ |
| Employee Nic # | ❌ |
| Employee Address | ❌ |
| Commission % | ❌ |

**Actions:** Add | Delete | Search | Update | Employee Commission | View All | Employee Salary

---

### 6.2 Recovery Man Management

**Route:** `/hr/recovery-men`

**Additional fields over Sale Man:**
| Field | Required |
|-------|----------|
| Employee Salary | ❌ |
| Employee Area | ❌ |
| Employee Rank | ❌ |

Same actions as Sale Man.

---

## Module 7: Settings

### 7.1 Company Info

- Company Name
- Company Address
- Company Phone

Used in report headers.

### 7.2 App Settings

- Defaulter Days threshold (default: 30)
- Default installment type

### 7.3 Backup & Restore

- **Backup**: Creates ZIP of SQLite file, optionally uploads to cloud
- **Restore**: Download from cloud or select local file, replaces database (with confirmation)
- **Database path**: Shows current SQLite path

### 7.4 License Management

- Enter license key
- Shows license status (valid / expired / offline)
- Last validated timestamp

---

## Removed / Low Priority Features

These exist in old system but are **excluded from MVP**:

| Feature | Status |
|---------|--------|
| SMS Sending | Schema stub only. UI disabled |
| Expense Tracking | Schema only. No UI |
| Script Writer | Removed |
| Suggestions & Feedback | Removed |
| Database direct script | Removed |
| Multi-branch | Not in scope |
| Employee salary history | Post-MVP |
| Advanced commission report | Post-MVP |
