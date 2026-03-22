# 01 — System Overview

## Business Context

The system is used by an installment-sales business operating in Rawalpindi/Islamabad, Pakistan. The business sells consumer products (AC/DC units, electronics, appliances) to customers on daily/weekly/monthly installment plans. Field agents (Recovery Men) collect payments door-to-door each day.

---

## Core Entities

### Product (Item)
A product available for sale on installment.

- Has a name, price, and current stock quantity
- Linked to a supplier/company
- Optional: image, brand, model number, color, category
- Quantity decreases on sale, increases on purchase or return

### Employee
Two types: **Sale Man** and **Recovery Man**. Managed under the same Staff module with different roles.

**Sale Man:**
- Makes the sale and registers the customer
- Linked to every sale record
- Has optional commission percentage

**Recovery Man:**
- Collects daily/weekly/monthly installments from customers
- Has an **Area** (geographic zone) and **Rank** (seniority level)
- Assigned to accounts (can be reassigned/transferred)
- Multiple RM can work the same area

### Customer
A person who buys on installment.

- Has unique auto-incremented Customer ID (CID)
- Full name, father's name, mobile, CNIC, home address, shop address, reference
- One customer can have multiple **Accounts** (e.g., bought AC and a TV separately)

### Account (Register)
The core transaction entity — represents one installment sale.

- Linked to: Customer, Sale Man, Recovery Man, Product
- Contains: Total Price, Advance Paid, Discount, Installment Type, Installment Amount
- Tracks: Total Collected, Remaining Balance
- Status: **Active** or **Closed**
- Multiple items can be on one account (multi-item sale)

### Installment Plan
The payment schedule for an account.

Types:
- **Daily** — customer pays a fixed amount every day
- **Weekly** — customer pays on a specific day each week
- **Monthly** — customer pays once per month

The plan can be changed mid-way; the system recalculates the remaining amount per installment.

### Payment (Recovery Record)
Each time a customer pays an installment, a record is inserted.

- Linked to: Account, Recovery Man, Date
- Amount Paid
- Status: P (Paid) / D (Default)

### Return
When a customer returns a product.

- Linked to: Account, Item
- Return Date, Reason, Amount Returned
- Adjusts account balance; closes account if no remaining items

---

## Business Flow

```
[New Product in Stock]
        │
        ▼
[Customer Walks In / Recovery Man Finds Customer]
        │
        ▼
[Customer Added to System]  ←── If new
        │
        ▼
[Sale Made]
 - Select Customer
 - Select Product(s)
 - Set Advance Payment
 - Set Installment Type (Daily/Weekly/Monthly)
 - Assign Sale Man + Recovery Man
        │
        ▼
[Account Created — Status: Active]
        │
        ▼
[Daily Recovery Loop]
 - Recovery Man visits customer
 - Checker marks payment received in system
 - Installment record inserted
 - Remaining balance reduces
        │
        ▼
[Account Balance = 0]
        │
        ▼
[Account Closed — Status: Closed]
```

---

## Exceptional Flows

### Account Transfer
- Customer's account can be moved from one Recovery Man to another
- Triggered from "Account Level Movement" screen
- Both RM info is logged

### Item Return
- Customer returns item
- System records return, calculates amount to refund
- Item goes back to inventory (or marked as scrap)
- Account balance adjusts; account closes if fully settled

### Account Forced Closure
- Admin/manager can close account with discount
- Discount amount recorded, slip number stored
- Customer still owes nothing after closure

### Installment Plan Change
- Customer's payment frequency can be changed
- Remaining balance stays the same
- New installment amount is recalculated from remaining balance / new schedule

### Defaulter Management
- Customers who haven't paid in N days appear on Defaulter Report
- Recovery Man enters problem record
- Manager logs action against problem (promise date, action taken, status)

---

## User Roles (Simplified for MVP)

The old system had a single-user local setup. The new system should support:

| Role | Access |
|------|--------|
| Admin/Owner | Full access to all modules |
| Manager | All modules except system settings |
| Operator | Sales, Recovery entry, Customers only |

Role management is an optional enhancement — start with a single admin user.

---

## Report Summary

| Report | Filters | Key Columns |
|--------|---------|-------------|
| Item Sale Report | Date range, Recovery Man | Sale Man, RM, Customer, Amount, Paid, Remaining |
| Item Detail Report | Date range | Item Name, Qty, Price, Total, Date |
| Daily Recovery Report | Date range, Area | Date, Acc#, Paid Amount, Remaining, Status |
| Recovery Monthly Report | Date range, Area | Total, Advance, Remaining, Previous, Date, Discount, Area |
| Item Return Report | Date range | Acc#, Sale Date, RM, SM, Customer, Item, QTY, Total, Received, Remaining, Mobile, Reason |
| SaleMan Sales Report | Sale Man, Date range, Status | Acc#, SM, Item, Customer, Total, Advance, Remaining, Status |
| Inventory Status | — | Item, Price, Quantity, Supplier |
| Account Holder Report | Account # | Account ID, Slip#, Date, Item, Name, Address, Phone, Status |
| Defaulter Report | Recovery Man, Days overdue | Date, Days, Customer, SM, Item, Total, Remaining, Paid, Promise, Status, Action |

---

## Data Volume Estimates

Based on the existing system's screenshot data:
- ~4,000+ accounts created
- ~50+ customers active
- ~20+ products in catalog
- ~10 employees (mix of sale men and recovery men)
- Daily recovery entries: ~50–100/day
