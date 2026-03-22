# 04 — UI/UX Design Guidelines

## Design Philosophy

The old system had a dated Windows Forms look (early 2000s style). The new system should feel **modern, clean, and professional** while still being instantly familiar to existing users. Priority: **clarity and speed** over visual flair.

---

## Layout Structure

```
┌─────────────────────────────────────────────────────────┐
│  [Logo]  Installment Management System        [Minimize] │  ← Title Bar (NativePHP)
├──────────────────────────────────────────────────────────┤
│ Items │ Management │ Mobile Employee │ Reports │ Recovery │  ← Top Menu Bar
│ Database Backup │ Settings                                │
├──────────┬───────────────────────────────────────────────┤
│          │                                               │
│ Sidebar  │           Main Content Area                  │
│ (narrow) │                                               │
│          │                                               │
└──────────┴───────────────────────────────────────────────┘
```

The app uses a **top navigation menu bar** (matching old system's mental model) with a content area below. No sidebar required for MVP — all navigation is top-menu.

---

## Color Palette

```css
/* Primary — Deep Blue (matching old system's dark header feel) */
--color-primary:       #1e3a5f;   /* Navy — header, active states */
--color-primary-light: #2563eb;   /* Bright blue — buttons, links */
--color-primary-hover: #1d4ed8;

/* Accent */
--color-accent:        #f59e0b;   /* Amber — warnings, highlights */

/* Status Colors */
--color-success:       #16a34a;   /* Green — paid, active, closed ok */
--color-danger:        #dc2626;   /* Red — defaulters, errors, remaining */
--color-warning:       #d97706;   /* Orange — warnings */
--color-muted:         #6b7280;   /* Grey — secondary text */

/* Background */
--color-bg:            #f8fafc;   /* Very light grey — page bg */
--color-surface:       #ffffff;   /* White — cards, forms */
--color-border:        #e2e8f0;   /* Light border */

/* Table */
--color-table-header:  #1e3a5f;   /* Dark navy — table headers (matches old system) */
--color-table-row-alt: #f1f5f9;   /* Alternate row */
--color-table-hover:   #dbeafe;   /* Row hover */
```

---

## Typography

```css
font-family: "Inter", "Segoe UI", system-ui, sans-serif;

/* Sizes */
--text-xs:   11px;   /* Table cells, secondary info */
--text-sm:   13px;   /* Body text, form labels */
--text-base: 14px;   /* Default */
--text-lg:   16px;   /* Section headings */
--text-xl:   20px;   /* Page titles */
--text-2xl:  24px;   /* Report titles */
```

---

## Component Patterns

### Top Navigation Menu

```
[Items ▾] [Management ▾] [Reports ▾] [Recovery ▾] [Settings ▾]
```

Dropdowns on hover. Active module highlighted. Keyboard navigable.

Quick action toolbar below menu (matching old system's icon toolbar):
```
[New Purchases] [New Sales] [Recovery Entry] [New Customer] [Main Reports]
```
Each is a button with icon + label.

---

### Data Tables

All tables follow this pattern:

```
┌────────────────────────────────────────────────────────┐
│  [Search Box]                    [+ Add New]  [Export] │
├─────┬──────┬────────┬──────────┬──────────┬────────────┤
│ Sr# │ Col1 │  Col2  │  Col3    │  Status  │  Actions   │
│     │ (navy header row, white text, bold)               │
├─────┼──────┼────────┼──────────┼──────────┼────────────┤
│  1  │ ...  │  ...   │  ...     │ [Active] │ [✏] [✕]   │
│  2  │ ...  │  ...   │  ...     │ [Closed] │ [✏] [✕]   │
│  3  │ ...  │  ...   │  ...     │ [Active] │ [✏] [✕]   │
└─────┴──────┴────────┴──────────┴──────────┴────────────┘
│ Showing 1-50 of 342          [← Prev] [1][2][3] [Next →] │
```

- Header row: navy background (`#1e3a5f`), white bold text
- Alternating row colors
- Hover highlight
- Status badges: green (Active), grey (Closed), red (Defaulter)
- Action buttons: small, icon-only with tooltip
- Compact density — show as many rows as possible

---

### Forms

```
┌──────────────────────────────────────────────────────┐
│  ── Customer Information ────────────────────────── │
│                                                      │
│  Customer ID*    [4143          ]                    │
│  Customer Name*  [_______________]                   │
│  Father Name     [_______________]  Reference [____] │
│  Mobile #        [0xxx-xxxxxxx  ]                    │
│  CNIC #          [xxxxx-xxxxxxx-x]                   │
│                                                      │
│  Home Address    [_________________________________] │
│  Shop Address    [_________________________________] │
│                                                      │
│              [✗ Cancel]  [✓ Save]                   │
└──────────────────────────────────────────────────────┘
```

- Section dividers with subtle lines
- Required fields marked with `*`
- Inline validation (red border + message below field)
- Tab order is logical (top-to-bottom, left-to-right)
- Save button: primary blue, always bottom right
- Cancel: secondary/ghost button

---

### Sale Point Layout

Two-panel layout:

```
┌──────────────────┬──────────────────────────────────┐
│ Customer Info    │ Item Detail                      │
│──────────────────│──────────────────────────────────│
│ Party ID: [   ] │ Item: [dropdown        ▾]        │
│ Name: .........  │ Total Price: [        ]          │
│ Father: ........  │ Advance:     [        ]         │
│ Mobile: ........  │ Discount:    [0       ]         │
│ Address: .......  │ Inst Type:   [Daily   ▾]        │
│                  │ Day:         [        ]          │
│                  │ Rem. Amt:    12,500    (calc)    │
│ Sales Info       │ Inst. Amt:   [        ]          │
│──────────────────│ Total Insts: 125       (calc)   │
│ Slip#: [      ] │                                  │
│ Sale Man: [   ▾] │ Stock: Item [AC DC] Qty [223]   │
│ Recov Man:[   ▾] │                                  │
│ Area: .........  │         [+ Add Another Item]     │
│ Date: [today   ] │                                  │
│                  │              [▶ Proceed]         │
└──────────────────┴──────────────────────────────────┘
```

---

### Recovery Checklist

```
Recovery Man: [Kashif Khan ▾]   Category: [Daily ▾]   [↵ Load]

┌──────┬──────┬─────────────┬──────────────┬────────┬─────────┬──────┐
│ RM   │ CID  │ Customer    │ Phone        │ Balance│ Type    │  ✓   │
├──────┼──────┼─────────────┼──────────────┼────────┼─────────┼──────┤
│ KK   │  202 │ Ba Aziz     │ 0313-038...  │ 5,600  │ Daily   │  □   │
│ KK   │  208 │ M Naheed    │ 0341-223...  │ 4,920  │ Daily   │  □   │
│ KK   │  437 │ Bilal       │ 0344-218...  │ 7,100  │ Daily   │  □   │
└──────┴──────┴─────────────┴──────────────┴────────┴─────────┴──────┘
                                        [✓ Update Status]
```

Checking a box visually marks it green. "Update Status" submits all checked items.

---

### Reports

Print view uses a clean white layout:

```
        Utility Store Corporation of Pakistan
        Block A Rehmat Abad Chaklala, Rawalpindi
        
                 ITEM WISE SALES LIST
                 
Item Name: [AC DC ▾]   From: [01/Mar/2025]   To: [16/Apr/2025]   [↵]

┌────┬─────────────┬──────────────┬──────────┬────────┬───────┬─────────┐
│ ID │ Date        │ Customer     │ Item     │ Total  │ Paid  │ Balance │
├────┼─────────────┼──────────────┼──────────┼────────┼───────┼─────────┤
│4233│ 13/Apr/2025 │ Sabzi Shop.. │ AC DC    │ 16,000 │   800 │  15,200 │
│4219│ 09/Apr/2025 │ Kabaka Shop  │ AC DC    │ 16,000 │ 1,100 │  14,900 │
└────┴─────────────┴──────────────┴──────────┴────────┴───────┴─────────┘

                                    Report Time: 22/Mar/2026 10:30 AM
                           Page 1 of 3
```

---

## Interaction Patterns

### Search / Autocomplete
- Customer and Product dropdowns: **type to search**, results filter as you type
- Minimum 2 chars to trigger search
- Show ID + Name in results (e.g., `[4143] Ba Aziz Khan`)

### Keyboard Shortcuts
| Key | Action |
|-----|--------|
| `F1` | New Customer |
| `F2` | New Sale |
| `F3` | Recovery Entry |
| `F5` | Refresh current view |
| `Ctrl+P` | Print current report |
| `Ctrl+S` | Save current form |
| `Escape` | Close modal/cancel |
| `Enter` | Confirm / proceed (in forms) |

### Modals
- Customer add: opens as overlay modal (not new window)
- Account closure: modal
- Recovery Man transfer: modal
- Confirmations (delete, close account): small confirmation dialog

### Notifications / Feedback
- **Success**: Green toast (bottom-right), auto-dismiss 3s
- **Error**: Red toast, stays until dismissed
- **Confirmation**: Modal dialog with explicit Yes/No buttons (never browser confirm())

### Loading States
- Table load: skeleton rows (animated placeholder)
- Form save: button shows spinner + disabled state
- Report generation: spinner overlay on report area

---

## Responsive Consideration

This is a desktop app (min width 1024px). No mobile support needed. Design for:
- **1024 × 768** — minimum (everything must fit, may scroll)
- **1366 × 768** — most common (target)
- **1920 × 1080** — comfortable (tables show more rows)

---

## Print Styles

Reports use a separate print stylesheet:
- Hide navigation, buttons, filters
- Show company header, report title, date
- Black text on white
- Borders on all table cells
- Page numbers in footer
- Font size: 11px for tables (compact but readable)

---

## Accessibility

- All form inputs have associated `<label>`
- Error messages tied to fields via `aria-describedby`
- Focus styles visible (not removed)
- Color is not the only indicator of state (use icon + color)
- Tab order follows visual order
