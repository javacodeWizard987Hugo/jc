# POS Customization - User Guide

## How to Use the New Features

### 1. Weight Input Feature

**Location**: In the shopping cart (right side of POS screen)

**How to Use**:
1. Add items to your cart as usual
2. For each item in the cart, you'll see a **blue "Weight:" section**
3. Enter the weight value in the input field
4. Select the appropriate weight unit from the dropdown:
   - **kg** (Kilograms) - Most common
   - **g** (Grams) - For smaller items
   - **lbs** (Pounds) - For imperial measurements
   - **oz** (Ounces) - For smaller imperial measurements

**Example**:
```
Item: Chicken Breast
Qty: 2
Weight: 2.50 kg (or 500 g, or 5.51 lbs)
```

**In Invoice**: The weight will appear in a dedicated "Weight" column:
```
Item          | Qty | Weight   | Price    | Total
Chicken       | 2   | 2.50 kg  | Rs. 600  | Rs. 1200
```

---

### 2. Discount Options (Rupee vs Percentage)

**Location**: In the shopping cart (right side of POS screen)

**How to Use**:
1. Add items to your cart
2. For each item in the cart, you'll see a **green "Discount:" section**
3. Enter the discount value in the input field
4. Select the discount type from the dropdown:
   - **Rs.** (Rupee) - Fixed amount discount *(Default)*
   - **%** (Percentage) - Percentage-based discount

**Examples**:

#### Rupee Discount
```
Item Total: Rs. 1000
Discount: 100 Rs.
Final Price: Rs. 900
```

#### Percentage Discount
```
Item Total: Rs. 1000
Discount: 10 %
Final Price: Rs. 900 (10% of 1000 = 100)
```

**Important**: 
- Each item can have its own discount type
- Discounts are calculated individually per item
- Total discount is shown at checkout

---

### 3. Bill Details - Address & Contact

**What's Displayed**:

At the top of every printed invoice:
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    🍗 RED CHICKEN POS
    Point of Sale System
    Kegalu Stores, Kusumpokuna, Diulankadawala
    071 405 6490
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

At the bottom of every printed invoice:
```
Thank you for your business!
2025-12-13 14:30:45

Aryans kindom POS
```

**These Details Are Pre-configured With**:
- Address: Kegalu Stores, Kusumpokuna, Diulankadawala
- Contact: 071 405 6490
- Footer: Aryans kindom POS

---

## Step-by-Step Example Transaction

### Scenario: Selling 2 items with different discounts

1. **Add First Item** (Chicken - Rupee Discount)
   - Item: Chicken Breast
   - Qty: 2
   - Price per unit: Rs. 600
   - Weight: 2.50 kg
   - Discount: 100 Rs. (Rupee)
   - Subtotal: Rs. 1200 - Rs. 100 = Rs. 1100

2. **Add Second Item** (Fish - Percentage Discount)
   - Item: Fish Fillet
   - Qty: 3
   - Price per unit: Rs. 400
   - Weight: 1.50 kg
   - Discount: 10 % (Percentage)
   - Subtotal: Rs. 1200 - Rs. 120 = Rs. 1080

3. **Cart Summary**
   - Subtotal: Rs. 2400
   - Total Discount: Rs. 220 (100 Rs. + 120 Rs. from 10%)
   - Tax (if applicable): Rs. X
   - **Final Total**: Rs. 2200 + Tax

4. **Invoice Shows**:
```
╔══════════════════════════════╗
║    KEGALU STORES POS         ║
║ Kegalu Stores, Kusumpokuna   ║
║ 071 405 6490                 ║
╚══════════════════════════════╝

Invoice: INV-2025-12-13-0001
Date: 2025-12-13 14:30:45

┌─────────────────────────────┐
│ Item      Qty Weight  Price  │
├─────────────────────────────┤
│ Chicken   2   2.50kg  1100   │
│ Fish      3   1.50kg  1080   │
└─────────────────────────────┘

Subtotal:        Rs. 2400
Discount:       -Rs. 220
Tax:              Rs. 0
─────────────────────────────
TOTAL:            Rs. 2180

Thank you for your business!

Aryans kindom POS
```

---

## Important Notes

### Weight Entry
- ✅ Weight is **optional** - leave blank if not needed
- ✅ Supports decimal values (e.g., 2.50, 0.75)
- ✅ Different units per item possible
- ✅ Weight is for tracking only (doesn't affect price)

### Discount Entry
- ✅ Discount is **optional** - leave as 0 if not giving discount
- ✅ **Rupee (Rs.) is the default** - most common choice
- ✅ Can switch to percentage by clicking the dropdown
- ⚠️ Admin approval may be required for large discounts

### Bill Printing
- ✅ Address and contact appear automatically on every invoice
- ✅ "Aryans kindom POS" footer appears automatically
- ✅ Weight information is included in invoice
- ✅ Print using the "Print Invoice" button after completing sale

---

## Common Questions

**Q: Can I change the discount type after entering the amount?**
A: Yes! Simply click the Rs./% dropdown and select the other option. The amount stays in the input field.

**Q: What if I don't want to enter weight?**
A: You don't have to! Leave the weight field empty or at 0. It will show as "-" in the invoice.

**Q: Can different items have different weight units?**
A: Yes! Each item can have its own weight unit. For example, one item in kg and another in lbs.

**Q: Is the discount calculated correctly?**
A: Yes! The system automatically calculates:
- Rupee discount: Exact amount (100 Rs.)
- Percentage discount: Percentage of item total (10% = 10% of total)

**Q: Where do I see the discount type on the invoice?**
A: The invoice shows the final discount amount in Rupees. The type is tracked internally for records.

**Q: Can I edit store address and contact details?**
A: Currently hardcoded for consistency, but can be changed by admin through system settings if needed.

---

## Keyboard Shortcuts & Tips

### Quick Entry Tips
1. **Tab Key**: Move between weight and discount fields
2. **Enter Key**: Confirm entry
3. **Arrow Keys**: Increment/Decrement quantity without clicking buttons
4. **Right-Click**: No special context menu (for future development)

### Best Practices
1. Always enter weight for food items (helps with reconciliation)
2. Use Rupee discount for fixed amounts, Percentage for promotional discounts
3. Print and keep invoices for customer records
4. Review the footer and details match on printed invoices

---

## Troubleshooting

**Issue**: Weight field not appearing in cart
- **Solution**: Refresh the page or clear browser cache (Ctrl+F5)

**Issue**: Discount calculation seems wrong
- **Solution**: Check if you're using the correct discount type (Rs. vs %)

**Issue**: Store details not showing on invoice
- **Solution**: Ensure you're printing the invoice, not just viewing it on screen

**Issue**: Weight not saving to invoice
- **Solution**: Make sure you entered a weight value before completing the sale

---

**Last Updated**: December 13, 2025
**Version**: 1.0
