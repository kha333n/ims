# 02 — Database Schema

**Database:** SQLite (file: `storage/app/ims.sqlite`)

All monetary values stored as **integers in paisa** (1 PKR = 100 paisa). Display layer divides by 100.

All tables include: `created_at`, `updated_at`, `deleted_at` (soft delete).

---

## Tables

### `products`

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | Auto-increment |
| `name` | VARCHAR(100) | Required. e.g., "AC DC", "buffer" |
| `price` | INTEGER | Selling price in paisa. Required |
| `quantity` | INTEGER | Current stock. Default 0 |
| `company` | VARCHAR(100) | Supplier/company name. Optional |
| `brand` | VARCHAR(100) | Optional (new field) |
| `model_number` | VARCHAR(100) | Optional (new field) |
| `color` | VARCHAR(50) | Optional (new field) |
| `category` | VARCHAR(100) | Optional (new field) |
| `image_path` | VARCHAR(255) | Relative path to local image. Optional |
| `notes` | TEXT | Optional |
| `deleted_at` | TIMESTAMP | Soft delete |

---

### `suppliers`

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `name` | VARCHAR(100) | Required |
| `contact` | VARCHAR(20) | Optional |
| `address` | TEXT | Optional |
| `notes` | TEXT | Optional |
| `deleted_at` | TIMESTAMP | |

---

### `purchases`

Stock entry (Purchase Point screen).

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `product_id` | FK → products | Required |
| `supplier_id` | FK → suppliers | Optional |
| `purchase_price` | INTEGER | In paisa |
| `quantity` | INTEGER | Units purchased |
| `purchased_at` | DATE | Purchase date |
| `notes` | TEXT | Optional |

---

### `employees`

Covers both Sale Men and Recovery Men.

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `type` | ENUM('sale_man','recovery_man') | Required |
| `name` | VARCHAR(100) | Required |
| `nic` | VARCHAR(20) | CNIC number. Optional |
| `phone` | VARCHAR(20) | Optional |
| `address` | TEXT | Optional |
| `salary` | INTEGER | Monthly salary in paisa. Optional |
| `commission_percent` | DECIMAL(5,2) | Sale Man commission %. Optional |
| `area` | VARCHAR(100) | Recovery Man area. Optional |
| `rank` | VARCHAR(50) | Recovery Man rank. Optional |
| `photo_path` | VARCHAR(255) | Optional |
| `deleted_at` | TIMESTAMP | |

---

### `customers`

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | CID |
| `name` | VARCHAR(100) | Required |
| `father_name` | VARCHAR(100) | Optional |
| `mobile` | VARCHAR(20) | Optional |
| `cnic` | VARCHAR(20) | Pakistani NIC. Optional |
| `home_address` | TEXT | Optional |
| `shop_address` | TEXT | Optional |
| `reference` | VARCHAR(100) | Who referred the customer. Optional |
| `notes` | TEXT | Optional |
| `deleted_at` | TIMESTAMP | |

---

### `accounts`

One record per installment sale (one customer can have many).

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | Account number (Acc#) |
| `customer_id` | FK → customers | Required |
| `sale_man_id` | FK → employees | Required (SM) |
| `recovery_man_id` | FK → employees | Required (RM) |
| `area` | VARCHAR(100) | Area at time of sale |
| `sale_date` | DATE | Required |
| `slip_number` | INTEGER | Optional slip# |
| `total_price` | INTEGER | Total sale price in paisa |
| `advance` | INTEGER | Advance payment in paisa. Default 0 |
| `discount` | INTEGER | Discount in paisa. Default 0 |
| `installment_type` | ENUM('daily','weekly','monthly') | Required |
| `installment_day` | TINYINT | Day of week/month for weekly/monthly |
| `installment_amount` | INTEGER | Per-installment amount in paisa |
| `total_installments` | INTEGER | Calculated total installments count |
| `status` | ENUM('active','closed') | Default 'active' |
| `closed_at` | TIMESTAMP | When account was closed |
| `closure_discount` | INTEGER | Discount given on closure. Default 0 |
| `closure_slip` | VARCHAR(50) | Closure slip number |
| `notes` | TEXT | Optional |
| `deleted_at` | TIMESTAMP | |

---

### `account_items`

Items included in a sale (multi-item support).

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `account_id` | FK → accounts | Required |
| `product_id` | FK → products | Required |
| `quantity` | INTEGER | Default 1 |
| `price` | INTEGER | Unit price at time of sale (paisa) |
| `total` | INTEGER | quantity × price |
| `returned` | BOOLEAN | Is this item returned? Default false |
| `returned_at` | TIMESTAMP | |

---

### `payments`

Each installment payment record.

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `account_id` | FK → accounts | Required |
| `recovery_man_id` | FK → employees | Who collected it |
| `amount` | INTEGER | Amount paid in paisa |
| `payment_date` | DATE | Required |
| `status` | ENUM('paid','default') | Default 'paid' |
| `remarks` | TEXT | Optional |
| `entered_by` | VARCHAR(100) | Who entered this record |

---

### `returns`

Product return records.

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `account_id` | FK → accounts | Required |
| `account_item_id` | FK → account_items | Required |
| `recovery_man_id` | FK → employees | RM at time of return |
| `sale_man_id` | FK → employees | SM at time of return |
| `return_date` | DATE | Required |
| `quantity` | INTEGER | Units returned |
| `total_price` | INTEGER | Original price |
| `paid_amount` | INTEGER | Amount already paid |
| `returned_amount` | INTEGER | Amount given back |
| `remaining_amount` | INTEGER | After return adjustment |
| `new_installment_amount` | INTEGER | Recalculated installment |
| `inventory_action` | ENUM('restock','scrap') | What to do with item |
| `reason` | TEXT | Return reason |
| `mobile` | VARCHAR(20) | Customer mobile at time |

---

### `account_transfers`

Log of Recovery Man reassignments.

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `account_id` | FK → accounts | |
| `from_recovery_man_id` | FK → employees | |
| `to_recovery_man_id` | FK → employees | |
| `transferred_at` | TIMESTAMP | |
| `transferred_by` | VARCHAR(100) | Who made the transfer |
| `notes` | TEXT | |

---

### `problems`

Defaulter/problem tracking per account.

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `account_id` | FK → accounts | |
| `recovery_man_id` | FK → employees | |
| `manager` | VARCHAR(100) | Manager who handled |
| `checker` | VARCHAR(100) | Checker |
| `branch` | VARCHAR(100) | Optional |
| `problem` | TEXT | Description |
| `previous_promise_date` | DATE | |
| `new_commitment` | TEXT | Customer's new promise |
| `action_against_problem` | TEXT | What was done |
| `is_closed` | BOOLEAN | Default false |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

---

### `installment_plan_changes`

Log of installment plan updates.

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `account_id` | FK → accounts | |
| `old_type` | VARCHAR(20) | |
| `old_amount` | INTEGER | |
| `new_type` | VARCHAR(20) | |
| `new_amount` | INTEGER | |
| `new_day` | TINYINT | |
| `changed_at` | TIMESTAMP | |
| `changed_by` | VARCHAR(100) | |

---

### `expenses` *(low priority, schema only)*

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `head_id` | INTEGER | Expense category |
| `head_name` | VARCHAR(100) | |
| `amount` | INTEGER | In paisa |
| `description` | TEXT | |
| `bank_name` | VARCHAR(100) | Optional |
| `expense_date` | DATE | |
| `entered_by` | VARCHAR(100) | |

---

### `settings`

Key-value store for app configuration.

| Column | Type | Notes |
|--------|------|-------|
| `key` | VARCHAR(100) PK | |
| `value` | TEXT | |

Default settings:
```
company_name = "Utility Store"
company_address = ""
company_phone = ""
defaulter_days = 30
license_key = ""
license_valid_until = ""
backup_path = ""
```

---

### `users` *(optional — for multi-user)*

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `name` | VARCHAR(100) | |
| `username` | VARCHAR(50) | Unique |
| `password` | VARCHAR(255) | Bcrypt hashed |
| `role` | ENUM('admin','manager','operator') | |
| `deleted_at` | TIMESTAMP | |

---

## Key Relationships

```
customers ──< accounts >── employees (sale_man)
                │          └── employees (recovery_man)
                │
                ├──< account_items >── products
                ├──< payments
                ├──< returns
                ├──< problems
                ├──< account_transfers
                └──< installment_plan_changes

products ──< purchases >── suppliers
```

---

## Computed Values (Not Stored)

These are calculated at query/display time:

- `total_collected` = SUM of `payments.amount` for an account
- `remaining_balance` = `total_price` - `advance` - `discount` - `total_collected`
- `days_overdue` = DAYS since last payment or account creation (for defaulter report)
- `installments_remaining` = `remaining_balance` / `installment_amount`
