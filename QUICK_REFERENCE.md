# Quick Reference - New Features

## 🎯 What's New?

### 1️⃣ Weight Input
- **Where**: Each cart item (blue section)
- **What**: Enter weight value + select unit (kg/g/lbs/oz)
- **Why**: Track item weight for records and invoicing
- **Default**: 0 (optional field)

### 2️⃣ Discount Type Selection
- **Where**: Each cart item (green section)
- **What**: Choose Rupee (Rs.) or Percentage (%)
- **Why**: Flexible discount management
- **Default**: Rupee (Rs.)

### 3️⃣ Store Details in Bill
- **Where**: Invoice header and footer
- **Address**: Kegalu Stores, Kusumpokuna, Diulankadawala
- **Contact**: 071 405 6490
- **Footer**: Aryans kindom POS

---

## 📋 Feature Locations in POS

```
┌─────────────────────────────────────────────┐
│ 🔍 SEARCH & ADD ITEMS                       │
├─────────────────────────────────────────────┤
│                                             │
│ 📦 AVAILABLE ITEMS (Left Section)          │
│ - Click to add items                       │
│                                             │
├────────────────┬──────────────────────────┤
│                │ 🛒 SHOPPING CART        │
│                │ (Right Section)         │
│                │                         │
│                │ Item 1                  │
│                │ [- Qty +] [Remove]      │
│                │ Weight: [___] [kg ▼]    │ ← NEW
│                │ Discount: [__] [Rs ▼]   │ ← NEW
│                │                         │
│                │ Item 2                  │
│                │ [- Qty +] [Remove]      │
│                │ Weight: [___] [kg ▼]    │
│                │ Discount: [__] [% ▼]    │
│                │                         │
│                ├─────────────────────────┤
│                │ Subtotal:    Rs. 0.00   │
│                │ Discount:   -Rs. 0.00   │
│                │ Tax:         Rs. 0.00   │
│                ├─────────────────────────┤
│                │ TOTAL:       Rs. 0.00   │
│                │                         │
│                │ [Complete Sale]         │
│                │ [Hold Bill]             │
│                └─────────────────────────┘
```

---

## 🧮 Calculation Examples

### Rupee Discount
```
Item Price: Rs. 1000
Discount: 100 Rs.
───────────────────
Final: Rs. 900
```

### Percentage Discount
```
Item Price: Rs. 1000
Discount: 10 %
───────────────────
Final: Rs. 900 (10% of 1000 = 100)
```

### Mixed Discounts
```
Item 1: Rs. 600 - 50 Rs.     = Rs. 550
Item 2: Rs. 400 - 10%        = Rs. 360
────────────────────────────────────
Total Discount: Rs. 50 + Rs. 40 = Rs. 90
Total: Rs. 1000 - Rs. 90 = Rs. 910
```

---

## 📄 Invoice Example

```
╔════════════════════════════════╗
║    🍗 RED CHICKEN POS          ║
║  Point of Sale System          ║
║  Kegalu Stores, Kusumpokuna    ║  ← ADDRESS
║  Diulankadawala                ║
║  071 405 6490                  ║  ← CONTACT
╚════════════════════════════════╝

Invoice: INV-2025-12-13-0001
Date: 2025-12-13 14:30:45
Cashier: John Doe
Payment: Cash

┌────────────────────────────────────────┐
│ Item     │ Qty │ Weight  │ Price │ Total│
├────────────────────────────────────────┤
│ Chicken  │  2  │ 2.50 kg │ 600   │ 1200│  ← WEIGHT SHOWN
│ Fish     │  3  │ 1.50 kg │ 400   │ 1200│
└────────────────────────────────────────┘

Subtotal:        Rs. 2400
Discount:         -Rs. 120
Tax:               Rs. 0
─────────────────────────────────
TOTAL:             Rs. 2280

Thank you for your business!
2025-12-13 14:31:00

Aryans kindom POS                         ← FOOTER TEXT
```

---

## ⚡ Quick Tips

| Task | Step |
|------|------|
| **Add Weight** | 1. Click weight field 2. Type value 3. Select unit |
| **Change Discount Type** | 1. Click Rs./% dropdown 2. Select other option |
| **Edit Weight Unit** | Click unit dropdown (kg/g/lbs/oz) |
| **Remove Discount** | Change discount value to 0 |
| **Clear Weight** | Leave field empty or set to 0 |
| **Print Invoice** | Click "Print Invoice" button after sale |

---

## 🔧 Settings & Customization

### Change Store Address
```php
// In Laravel Tinker or Admin Code
\App\Models\SystemSetting::setValue('store_address', 'Your New Address');
```

### Change Contact Number
```php
\App\Models\SystemSetting::setValue('store_contact', 'Your Phone Number');
```

### Change Footer Text
```php
\App\Models\SystemSetting::setValue('store_footer_text', 'Your Text Here');
```

---

## ❓ FAQ

**Q: Do I have to use weight?**
A: No, it's optional. Leave it blank if not needed.

**Q: Which discount type should I use?**
A: Use Rupee (Rs.) for fixed amounts. Use Percentage (%) for promotional discounts.

**Q: Can I change discount type after entering amount?**
A: Yes! Just click the dropdown and switch.

**Q: Will old invoices show weight?**
A: No, only new sales will show weight. Old ones will show "-".

**Q: Can I use multiple weight units for one item?**
A: Yes! Each item can have different units.

**Q: How is percentage discount calculated?**
A: Percentage of the item total. Example: 10% of Rs. 1000 = Rs. 100 discount.

---

## 📊 Data Saved

For each sale item, the system now saves:
- ✅ Weight (amount)
- ✅ Weight Unit (kg/g/lbs/oz)
- ✅ Discount Amount (Rs. value)
- ✅ Discount Type (rupee/percentage)
- ✅ All previous data (quantity, price, etc.)

---

## 🔍 Where to Find Things

| Item | Location |
|------|----------|
| Weight Input | Cart item - blue section |
| Discount Input | Cart item - green section |
| Weight Unit Selector | Next to weight input |
| Discount Type Selector | Next to discount input |
| Store Address | Invoice header |
| Contact Number | Invoice header |
| Footer Text | Invoice bottom |
| Weight in Invoice | Items table column |
| Discount Totals | Below items table |

---

## ✅ Verification Checklist

- [ ] Weight inputs appear in cart
- [ ] Weight unit dropdown works (kg/g/lbs/oz)
- [ ] Discount inputs appear in cart
- [ ] Discount type dropdown works (Rs./%）
- [ ] Calculations are correct
- [ ] Invoice shows store address
- [ ] Invoice shows contact number
- [ ] Invoice shows weight column
- [ ] Invoice shows footer text
- [ ] Print function works properly

---

## 🚀 Getting Started

1. **Login** to POS System
2. **Click** "Point of Sale" from menu
3. **Search** and add items
4. **Enter** weight (optional) and unit
5. **Enter** discount amount and type
6. **Click** "Complete Sale"
7. **Print** invoice (shows all details)

---

**Version**: 1.0
**Date**: December 13, 2025
**Status**: Ready to Use ✅

For detailed documentation, see:
- `USER_GUIDE_NEW_FEATURES.md` - Full user guide
- `TECHNICAL_IMPLEMENTATION.md` - Developer details
- `CUSTOMIZATION_SUMMARY.md` - Complete summary
