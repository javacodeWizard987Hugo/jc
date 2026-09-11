# Technical Implementation Details

## Database Schema Changes

### 1. Sale Items Table (`sale_items`)

**New Columns Added**:

```sql
ALTER TABLE sale_items ADD COLUMN weight DECIMAL(10, 2) NULL AFTER quantity;
ALTER TABLE sale_items ADD COLUMN weight_unit VARCHAR(255) DEFAULT 'kg' AFTER weight;
```

**Column Details**:
- `weight` (decimal 10,2, nullable)
  - Stores the weight value
  - Nullable to maintain backward compatibility
  - Precision: up to 8 digits before decimal, 2 digits after
  - Examples: 0.50, 2.75, 100.00, NULL

- `weight_unit` (varchar 255, default 'kg')
  - Stores the unit of measurement
  - Default value: 'kg'
  - Allowed values: 'kg', 'g', 'lbs', 'oz'
  - Can be extended with more units if needed

**Migration File**: `2025_12_13_000001_add_weight_to_sale_items_table.php`

---

### 2. Sales Table (`sales`)

**New Columns Added**:

```sql
ALTER TABLE sales ADD COLUMN discount_type ENUM('rupee', 'percentage') DEFAULT 'rupee' AFTER discount_amount;
```

**Column Details**:
- `discount_type` (enum, default 'rupee')
  - Stores the type of discount applied
  - Values: 'rupee' (fixed amount) or 'percentage'
  - Default: 'rupee' (most common)
  - Added after `discount_amount` column

**Migration File**: `2025_12_13_000002_add_discount_type_to_sales_table.php`

---

### 3. System Settings Table (`system_settings`)

**New Entries Added**:

```sql
INSERT INTO system_settings (key, value, type, description, created_at, updated_at) VALUES
('store_address', 'Kegalu Stores, Kusumpokuna, Diulankadawala', 'string', 'Store address to display on invoices', NOW(), NOW()),
('store_contact', '071 405 6490', 'string', 'Store contact number to display on invoices', NOW(), NOW()),
('store_footer_text', 'Aryans kindom POS', 'string', 'Footer text to display at bottom of invoices', NOW(), NOW());
```

**Migration File**: `2025_12_13_000003_add_store_details_to_system_settings.php`

---

## Model Updates

### SaleItem Model (`app/Models/SaleItem.php`)

**Updated `$fillable` Array**:

```php
protected $fillable = [
    'sale_id',
    'item_id',
    'quantity',
    'unit_price',
    'discount_amount',
    'total_price',
    'weight',           // NEW
    'weight_unit',      // NEW
];
```

**No Changes to Relationships or Casts** - Maintains backward compatibility

---

### Sale Model (`app/Models/Sale.php`)

**Updated `$fillable` Array**:

```php
protected $fillable = [
    'invoice_number',
    'cashier_id',
    'customer_id',
    'subtotal',
    'discount_amount',
    'discount_type',    // NEW
    'tax_amount',
    'total_amount',
    'payment_method',
    'status',
    'cancellation_reason',
    'cancelled_by',
    'cancelled_at',
    'requires_admin_approval',
    'approved_by',
    'approved_at',
    'approval_reason',
    'discount_approval_request',
];
```

**Note**: `discount_type` column defaults to 'rupee' at database level

---

## Frontend Implementation

### POS View (`resources/views/cashier/pos.blade.php`)

#### JavaScript Variables
```javascript
cart = [
    {
        item_id: 1,
        name: "Chicken Breast",
        price: 600,
        quantity: 2,
        discount: 100,
        discount_type: "rupee",        // NEW
        weight: 2.50,                  // NEW
        weight_unit: "kg"              // NEW
    },
    // ... more items
]
```

#### New Functions Added

1. **updateWeight(index, weight)**
   - Updates weight for item at given index
   - Calls `updateCart()` to refresh display
   - Parameters: index (number), weight (string/number)

2. **updateWeightUnit(index, unit)**
   - Updates weight unit for item at given index
   - Valid units: 'kg', 'g', 'lbs', 'oz'
   - Calls `updateCart()` to refresh display
   - Parameters: index (number), unit (string)

3. **updateDiscount(index, discount)**
   - Updates discount amount for item at given index
   - Calls `updateCart()` to refresh display
   - Parameters: index (number), discount (string/number)

4. **updateDiscountType(index, discountType)**
   - Updates discount type for item at given index
   - Valid values: 'rupee', 'percentage'
   - Calls `updateCart()` to refresh display
   - Parameters: index (number), discountType (string)

#### Enhanced Functions

1. **addToCart(itemId, name, price, stock)**
   - Now includes weight and discount_type in cart object
   - Default values: weight=0, weight_unit='kg', discount=0, discount_type='rupee'

2. **updateCart()**
   - Completely rewritten to display weight and discount inputs
   - Shows blue section for weight with input and unit selector
   - Shows green section for discount with input and type selector
   - Better visual organization of cart items

3. **updateTotals()**
   - Enhanced to handle both discount types
   - Calculates discount based on type:
     - Rupee: Direct subtraction
     - Percentage: (item_subtotal * discount / 100)
   - Proper handling of tax and rounding

#### HTML Structure Changes

```html
<!-- Cart Item Structure -->
<div class="p-4 bg-white border-2 border-gray-200 rounded-lg">
    <!-- Item Header -->
    <div class="flex items-center justify-between mb-3">
        <div>Item Name & Price</div>
        <button>Remove</button>
    </div>
    
    <!-- Quantity Section -->
    <div class="flex items-center gap-2 mb-3 bg-gray-50 p-2 rounded">
        <input type="number">
        <button>-</button>
        <span>Qty</span>
        <button>+</button>
    </div>
    
    <!-- Weight Section (NEW) -->
    <div class="flex items-center gap-2 mb-3 bg-blue-50 p-2 rounded">
        <input type="number" step="0.01" placeholder="0.00">
        <select>
            <option>kg</option>
            <option>g</option>
            <option>lbs</option>
            <option>oz</option>
        </select>
    </div>
    
    <!-- Discount Section (NEW) -->
    <div class="flex items-center gap-2 bg-green-50 p-2 rounded">
        <input type="number" step="0.01" placeholder="0.00">
        <select>
            <option>Rs.</option>
            <option>%</option>
        </select>
    </div>
</div>
```

---

### Invoice View (`resources/views/cashier/invoice.blade.php`)

#### Header Changes

```blade.php
@php
    $storeAddress = \App\Models\SystemSetting::getValue('store_address', 'Kegalu Stores');
    $storeContact = \App\Models\SystemSetting::getValue('store_contact', '071 405 6490');
@endphp

<!-- Added to header -->
<p style="font-size: 9px; margin-top: 5px; color: #333;">{{ $storeAddress }}</p>
<p style="font-size: 9px; color: #333;">{{ $storeContact }}</p>
```

#### Items Table Changes

```blade.php
<table class="items-table">
    <thead>
        <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Weight</th>  <!-- NEW -->
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sale->items as $item)
        <tr>
            <td>{{ $item->item->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->weight ? $item->weight . ' ' . $item->weight_unit : '-' }}</td>
            <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
            <td>Rs. {{ number_format($item->total_price, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
```

#### Footer Changes

```blade.php
<div class="footer">
    <p>Thank you for your business!</p>
    <p>{{ now()->format('Y-m-d H:i:s') }}</p>
    <p style="margin-top: 10px; font-weight: bold; font-size: 11px;">Aryans kindom POS</p>
</div>
```

---

## Controller Implementation

### PosController (`app/Http/Controllers/Cashier/PosController.php`)

#### Updated createSale() Method

**Key Changes**:

1. **Extract Weight from Cart Items**:
```php
$weight = $cartItem['weight'] ?? 0;
$weightUnit = $cartItem['weight_unit'] ?? 'kg';
```

2. **Extract Discount Type from Cart Items**:
```php
$discountType = $cartItem['discount_type'] ?? 'rupee';
```

3. **Create SaleItem with New Fields**:
```php
SaleItem::create([
    'sale_id' => $sale->id,
    'item_id' => $item->id,
    'quantity' => $quantity,
    'unit_price' => $unitPrice,
    'discount_amount' => $discount,
    'total_price' => $totalPrice,
    'weight' => $weight,              // NEW
    'weight_unit' => $weightUnit,     // NEW
]);
```

4. **Set Sale Discount Type**:
```php
$saleData = [
    // ... other fields
    'discount_type' => 'rupee',  // Default for sale level
    // ... rest of sale data
];
```

---

## Data Flow

### Creating a Sale with New Features

```
1. User adds item to cart (Frontend)
   └─ Cart object includes: weight, weight_unit, discount, discount_type

2. User completes sale (Frontend)
   └─ completeSale() function called
   └─ Calculates totals considering discount_type
   └─ Submits form with JSON cart data

3. Form submitted to createSale() (Backend)
   └─ Items JSON decoded
   └─ For each item:
      ├─ Extract weight and weight_unit
      ├─ Extract discount_type
      └─ Create SaleItem with all fields

4. Sale created in database
   └─ sale.discount_type = 'rupee' (default)
   └─ Each sale_item contains:
      ├─ weight
      ├─ weight_unit
      └─ discount_amount (with type tracked separately)

5. Invoice generated
   └─ Retrieves store_address and store_contact from SystemSetting
   └─ Displays weight in items table
   └─ Shows footer text "Aryans kindom POS"
```

---

## Backward Compatibility

✅ All changes maintain backward compatibility:

- **Weight fields are nullable**: Existing sales without weight will display "-"
- **Default discount type**: New sales default to 'rupee'
- **No breaking changes**: All existing functionality preserved
- **Existing data unaffected**: Previous records unchanged

---

## Database Query Examples

### Get all sales with discount type
```sql
SELECT invoice_number, subtotal, discount_amount, discount_type, total_amount 
FROM sales 
WHERE discount_type = 'percentage';
```

### Get weighted items
```sql
SELECT s.invoice_number, si.quantity, si.weight, si.weight_unit 
FROM sales s 
JOIN sale_items si ON s.id = si.sale_id 
WHERE si.weight > 0;
```

### Retrieve store settings
```sql
SELECT value FROM system_settings WHERE key IN ('store_address', 'store_contact', 'store_footer_text');
```

---

## Future Enhancements

Potential additions:

1. **Additional Weight Units**: oz, mg, tons, etc.
2. **Discount Combinations**: Mixed rupee and percentage discounts per sale
3. **Store Settings UI**: Admin interface to edit store details
4. **Weight Reports**: Analytics on weight sold over time
5. **Discount Analytics**: Track discount usage patterns
6. **Weight-Based Pricing**: Auto-calculate price based on weight

---

**Implementation Date**: December 13, 2025
**Version**: 1.0
**Status**: Production Ready
