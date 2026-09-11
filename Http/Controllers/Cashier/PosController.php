<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warranty;
use App\Models\Payment;
use App\Models\HeldBill;
use App\Models\StockMovement;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\InstallmentAgreement;  // ✅ correct import
use App\Models\CustomerCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id; // Assuming user is associated with a branch
        session(['branch_id' => $branchId]);

        $items = Item::where('is_active', true)
            ->whereHas('stock', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)->where('quantity', '>', 0);
            })
            ->with(['category', 'stock' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            }])
            ->get();
        
        $items->each(function ($item) {
            $stockForBranch = $item->stock->first();
            $item->current_stock = $stockForBranch ? $stockForBranch->quantity : 0;
        });
        
        // Admin can see all held bills, cashier only sees their own
        $heldBillsQuery = HeldBill::query();
        if (auth()->user()->isCashier()) {
            $heldBillsQuery->where('cashier_id', auth()->id());
        }
        $heldBills = $heldBillsQuery->latest()->get();
        
        $maxDiscount = SystemSetting::getValue('max_cashier_discount', 10);
        $taxRate = SystemSetting::getValue('tax_rate', 0);
        $roundingRules = SystemSetting::getValue('rounding_rules', 'none');
        
        // Determine route prefix based on user role
        $routePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';
        
        return view('cashier.pos', compact('items', 'heldBills', 'maxDiscount', 'taxRate', 'roundingRules', 'routePrefix'));
    }

    public function searchItems(Request $request)
    {
        try {
            $query = trim($request->get('q', ''));
            
            // Return empty array if query is empty
            if (empty($query)) {
                return response()->json([]);
            }
            
            // Simple search - SQLite LIKE is case-insensitive for ASCII
            $searchTerm = '%' . $query . '%';
            
            $branchId = session('branch_id');
            $items = Item::where('is_active', true)
                ->whereHas('stock', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)->where('quantity', '>', 0);
                })
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', $searchTerm)
                      ->orWhere('item_code', 'LIKE', $searchTerm)
                      ->orWhere(function($subQ) use ($searchTerm) {
                          $subQ->whereNotNull('barcode')
                               ->where('barcode', 'LIKE', $searchTerm);
                      });
                })
                ->with(['category', 'stock' => function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                }, 'serialNumbers' => function ($query) {
                    $query->where('status', 'in_stock');
                }])
                ->orderBy('name', 'asc')
                ->limit(50)
                ->get();
            
            $items->each(function ($item) {
                $stockForBranch = $item->stock->first();
                $item->current_stock = $stockForBranch ? $stockForBranch->quantity : 0;
            });
            
            // Return JSON response
            return response()->json($items);
            
        } catch (\Exception $e) {
            \Log::error('Search items error: ' . $e->getMessage(), [
                'query' => $request->get('q'),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty array on error to prevent frontend issues
            return response()->json([]);
        }
    }


    public function createSale(Request $request)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        // Handle JSON string items
        $itemsData = $request->input('items');
        if (is_string($itemsData)) {
            $itemsData = json_decode($itemsData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['error' => 'Invalid items data format.']);
            }
        }

        $validated = $request->validate([

            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,cheque,credit,card,installment',
            'customer_id' => 'nullable|exists:customers,id|required_if:payment_method,credit|required_if:payment_method,installment',
            'credit_due_date' => 'nullable|date|required_if:payment_method,credit',
            'cheque_number' => 'nullable|string|required_if:payment_method,cheque',
            'bank_name' => 'nullable|string|required_if:payment_method,cheque',
            'cheque_date' => 'nullable|date|required_if:payment_method,cheque',
            'split_payments' => 'nullable|array',
            'split_payments.*.method' => 'required_with:split_payments|in:cash,cheque,credit,card',
            'split_payments.*.amount' => 'required_with:split_payments|numeric|min:0',
            'split_payments.*.cheque_number' => 'nullable|string|required_if:split_payments.*.method,cheque',
            'split_payments.*.bank_name' => 'nullable|string|required_if:split_payments.*.method,cheque',
            'split_payments.*.cheque_date' => 'nullable|date|required_if:split_payments.*.method,cheque',
            'discount_approval_reason' => 'nullable|string|min:10',
            'down_payment_amount' => 'nullable|numeric|min:0|required_if:payment_method,installment',
            'number_of_installments' => 'nullable|integer|min:1|required_if:payment_method,installment',
            'monthly_installment_amount' => 'nullable|numeric|min:0|required_if:payment_method,installment',
           'first_due_date' => 'required_if:payment_method,installment|date',


               'due_day_of_month' => [
                    'required_if:payment_method,installment',
                    'integer',
                    'min:1',
                    'max:31',
                ],

            'interest_service_charge' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string',
            'customer_nic' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'customer_address' => 'nullable|string',
        ]);

        // Validate items array
        if (!is_array($itemsData) || count($itemsData) < 1) {
            return back()->withErrors(['error' => 'The items field must be an array with at least one item.']);
        }

        foreach ($itemsData as $item) {
            if (!isset($item['item_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                return back()->withErrors(['error' => 'Invalid item data format.']);
            }
        }

        $validated['items'] = $itemsData;

        $requiresApproval = false;

        // Check stock availability and validate quantities
        // Convert cart quantity to item's unit_of_measure for comparison
        foreach ($validated['items'] as $cartItem) {
            $item = Item::find($cartItem['item_id']);
            $quantityFromCart = $cartItem['quantity'];
            $weightUnit = $cartItem['weight_unit'] ?? null;
            
            // Convert quantity to match item's unit_of_measure for stock check
            $itemUnit = $item->unit_of_measure;
            $isWeightBasedSale = !empty($weightUnit) && in_array($weightUnit, ['kg', 'g', 'kg_g']);
            
            if ($isWeightBasedSale) {
                // Weight-based sale: quantity from cart is in kg
                if ($itemUnit === 'g') {
                    $quantity = $quantityFromCart * 1000; // Convert kg to g
                } elseif ($itemUnit === 'kg' || $itemUnit === 'kg_g') {
                    $quantity = $quantityFromCart; // Already in kg
                } else {
                    $quantity = $quantityFromCart;
                }
            } else {
                // Regular sale (no weight): quantity is already in item's unit
                $quantity = $quantityFromCart;
            }
            
            // Check if stock is sufficient (prevent negative stock)
            $branchStock = $item->getBranchStock($branchId);
            if ($branchStock < $quantity) {
                $availableStock = \App\Models\Item::formatStock($branchStock, $item->unit_of_measure);
                $requestedStock = \App\Models\Item::formatStock($quantity, $item->unit_of_measure);
                return back()->withErrors(['error' => "Insufficient stock for {$item->name}. Available: {$availableStock}, Requested: {$requestedStock}"]);
            }
        }

        // Generate invoice number
        $invoiceFormat = SystemSetting::getValue('invoice_format', 'INV-{YYYY}-{MM}-{DD}-{NNNN}');
        $invoiceNumber = $this->generateInvoiceNumber($invoiceFormat);

        // Determine payment method (use first split payment method if split payments exist)
        $paymentMethod = $validated['payment_method'] ?? ($validated['split_payments'][0]['method'] ?? 'cash');

        // Apply rounding rules to total amount
        $totalAmount = $validated['total_amount'];
        $roundingRules = SystemSetting::getValue('rounding_rules', 'none');
        if ($roundingRules === 'up') {
            $totalAmount = ceil($totalAmount);
        } elseif ($roundingRules === 'down') {
            $totalAmount = floor($totalAmount);
        } elseif ($roundingRules === 'nearest') {
            $totalAmount = round($totalAmount);
        }
        
        // Create sale
        $saleData = [
            'invoice_number' => $invoiceNumber,
            'cashier_id' => auth()->id(),
            'branch_id' => $branchId,
            'customer_id' => $validated['customer_id'] ?? null,
            'subtotal' => $validated['subtotal'],
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'discount_type' => 'rupee', // Default discount type - will be determined by items
            'tax_amount' => $validated['tax_amount'] ?? 0,
            'total_amount' => $totalAmount,
            'payment_method' => $paymentMethod,
            'status' => $requiresApproval ? 'pending' : 'completed',
            'requires_admin_approval' => $requiresApproval,
            'discount_approval_request' => $requiresApproval ? $request->input('discount_approval_reason') : null,
        ];
        
        // Create sale
        $sale = Sale::create($saleData);

        // Create installment agreement if payment method is installment
        if ($validated['payment_method'] === 'installment' && $sale->customer_id) {
            $customer = Customer::find($sale->customer_id);
          //  if ($customer->is_overdue) {
               // return back()->withErrors(['error' => 'This customer has overdue payments and cannot make new installment purchases.']);
           // }

           $agreement = \App\Models\InstallmentAgreement::create([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
              'total_invoice_value' => round($sale->total_amount, 2),
                'down_payment_amount' => round($validated['down_payment_amount'], 2),
                'down_payment_date' => now()->toDateString(),
               'balance_amount' => round($sale->total_amount - $validated['down_payment_amount'], 2),
                'number_of_installments' => $validated['number_of_installments'],
            'monthly_installment_amount' => round($validated['monthly_installment_amount'], 2),
                'first_due_date' => $validated['first_due_date'],
                'due_day_of_month' => $validated['due_day_of_month'],
                'interest_service_charge' => $validated['interest_service_charge'] ?? 0,
                'emi_lock_mode' => $customer->emi_lock_mode,
                'emi_number' => $customer->emi_number,
                'guarantor_name' => $customer->guarantor_name,
                'guarantor_nic' => $customer->guarantor_nic,
                'guarantor_address' => $customer->guarantor_address,
                'guarantor_mobile_number' => $customer->guarantor_mobile_number,

                 'customer_age'            => $customer->customer_age,
                'customer_occupation'     => $customer->customer_occupation,
                'customer_institute_name_address' => $customer->customer_institute_name_address,
                'customer_monthly_salary' => $customer->customer_monthly_salary,
                'customer_bank_branch'    => $customer->customer_bank_branch,
                'guarantor_1_occupation'  => $customer->guarantor_1_occupation,
                'guarantor_1_monthly_income' => $customer->guarantor_1_monthly_income,
                'guarantor_1_bank_branch' => $customer->guarantor_1_bank_branch,
                'guarantor_2_name'        => $customer->guarantor_2_name,
                'guarantor_2_nic'         => $customer->guarantor_2_nic,
                'guarantor_2_address'     => $customer->guarantor_2_address,
                'guarantor_2_phone'       => $customer->guarantor_2_phone,
                'guarantor_2_occupation'  => $customer->guarantor_2_occupation,
                'guarantor_2_monthly_income' => $customer->guarantor_2_monthly_income,
                'guarantor_2_bank_branch' => $customer->guarantor_2_bank_branch,

                'purchased_items' => json_encode($validated['items']),
            ]);

            // Send SMS
            app(\App\Services\SmsService::class)->sendSms($agreement->customer->phone, 'installment_down_payment', [
                'CustomerName' => $agreement->customer->name,
                'DownPayment' => number_format($agreement->down_payment_amount, 2),
                'Balance' => number_format($agreement->balance_amount, 2),
            'NextDueDate' => $agreement->getNextDueDate()->toDateString(),


            ]);

            AuditLog::log('create_agreement', "Created installment agreement for bill: {$invoiceNumber}", $agreement);
        }

        // Create sale items and update stock (only if sale is completed)
        foreach ($validated['items'] as $cartItem) {
            $item = Item::find($cartItem['item_id']);
            $unitPrice = $cartItem['price'];
            $quantityFromCart = $cartItem['quantity']; // This is always in kg when weight is used
            $discount = $cartItem['discount'] ?? 0;
            $discountType = $cartItem['discount_type'] ?? 'rupee';
            $weight = $cartItem['weight'] ?? 0;
            $weightUnit = $cartItem['weight_unit'] ?? 'kg';
            
            // Convert quantity to match item's unit_of_measure
            // When weight_unit is present (kg, g, or kg_g), quantity from cart is always in kg
            // When weight_unit is not present or empty, quantity is already in item's unit
            $itemUnit = $item->unit_of_measure;
            $isWeightBasedSale = !empty($weightUnit) && in_array($weightUnit, ['kg', 'g', 'kg_g']);
            
            if ($isWeightBasedSale) {
                // Weight-based sale: quantity from cart is in kg
                // Convert to item's unit_of_measure
                if ($itemUnit === 'g') {
                    $quantity = $quantityFromCart * 1000; // Convert kg to g
                } elseif ($itemUnit === 'kg' || $itemUnit === 'kg_g') {
                    // For kg or kg_g, quantity is already in kg
                    $quantity = $quantityFromCart;
                } else {
                    // Item unit mismatch - use quantity as-is (shouldn't normally happen)
                    $quantity = $quantityFromCart;
                }
            } else {
                // Regular sale (no weight): quantity is already in item's unit
                $quantity = $quantityFromCart;
            }
            
            $totalPrice = ($unitPrice * $quantityFromCart) - $discount; // Use original quantity for price calculation

            $saleItem = SaleItem::create([
                'sale_id' => $sale->id,
                'item_id' => $item->id,
                'quantity' => $quantity, // Store in item's unit_of_measure
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'total_price' => $totalPrice,
                'weight' => $weight,
                'weight_unit' => $weightUnit,
            ]);

            if ($item->requires_serial_number && isset($cartItem['serial_number'])) {
                $serial = \App\Models\SerialNumber::where('serial_number', $cartItem['serial_number'])->first();
                if ($serial && $serial->status === 'available') {
                    $serial->update([
                        'status' => 'sold',
                        'sale_item_id' => $saleItem->id,
                    ]);

                    if ($sale->customer_id) {
                        $duration = $item->warranty_duration_months ?? 12;
                        Warranty::create([
                            'serial_number_id' => $serial->id,
                            'sale_item_id' => $saleItem->id,
                            'customer_id' => $sale->customer_id,
                            'start_date' => now(),
                            'duration' => $duration,
                            'expiry_date' => now()->addMonths($duration),
                        ]);
                    }
                    $saleItem->update(['serial_number_id' => $serial->id]);
                }
            }

            // Only update stock if sale is completed (not pending approval)
            if (!$requiresApproval) {
                // Quantity is now in the item's unit_of_measure format
                $stockReductionQuantity = $quantity;
                
                // Double-check stock availability before reducing (race condition protection)
                $branchStock = \App\Models\BranchStock::where('branch_id', $branchId)->where('item_id', $item->id)->first();

                if (!$branchStock || $branchStock->quantity < $stockReductionQuantity) {
                    // Rollback sale creation - delete sale and all sale items
                    $sale->items()->delete();
                    $sale->delete();
                    $availableStock = \App\Models\Item::formatStock($branchStock ? $branchStock->quantity : 0, $item->unit_of_measure);
                    $requestedStock = \App\Models\Item::formatStock($stockReductionQuantity, $item->unit_of_measure);
                    \Log::error("Stock reduction failed for sale {$invoiceNumber}. Item: {$item->name}, Required: {$requestedStock}, Available: {$availableStock}");
                    return back()->withErrors(['error' => "Insufficient stock for {$item->name}. Available: {$availableStock}, Requested: {$requestedStock}. Sale cancelled."]);
                }
                
                $oldStock = $branchStock->quantity;
                $branchStock->decrement('quantity', $stockReductionQuantity);
                $newStock = $branchStock->fresh()->quantity;
                
                // Format weight display for notes
                $weightDisplay = '';
                if ($weight > 0) {
                    if ($weightUnit === 'kg' || $weightUnit === 'kg_g') {
                        $wholeKg = floor($weight);
                        $remainingGrams = round(($weight - $wholeKg) * 1000);
                        $gFormatted = str_pad($remainingGrams, 3, '0', STR_PAD_LEFT);
                        if ($wholeKg > 0 || $remainingGrams > 0) {
                            $weightDisplay = " (Weight: {$wholeKg}kg {$gFormatted}g)";
                        }
                    } elseif ($weightUnit === 'g') {
                        $wholeKg = floor($weight / 1000);
                        $remainingGrams = round($weight % 1000);
                        $gFormatted = str_pad($remainingGrams, 3, '0', STR_PAD_LEFT);
                        if ($wholeKg > 0 || $remainingGrams > 0) {
                            $weightDisplay = " (Weight: {$wholeKg}kg {$gFormatted}g)";
                        }
                    } else {
                        $weightDisplay = " (Weight: {$weight} {$weightUnit})";
                    }
                }
                
                // Record stock movement
                StockMovement::create([
                    'item_id' => $item->id,
                    'branch_id' => $branchId,
                    'type' => 'sale',
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'quantity' => -$stockReductionQuantity,
                    'balance_after' => $newStock,
                    'notes' => "Sale: {$invoiceNumber}{$weightDisplay}",
                    'created_by' => auth()->id(),
                ]);
            }
        }

        // Create payment record(s) - support split payments
        if (!empty($validated['split_payments'])) {
            // Split payment mode
            $totalSplitAmount = 0;
            foreach ($validated['split_payments'] as $splitPayment) {
                $paymentData = [
                    'sale_id' => $sale->id,
                    'payment_method' => $splitPayment['method'],
                    'amount' => $splitPayment['amount'],
                ];
                
                if ($splitPayment['method'] === 'cheque') {
                    $paymentData['cheque_number'] = $splitPayment['cheque_number'] ?? null;
                    $paymentData['bank_name'] = $splitPayment['bank_name'] ?? null;
                    $paymentData['cheque_date'] = $splitPayment['cheque_date'] ?? null;
                }
                
                Payment::create($paymentData);
                $totalSplitAmount += $splitPayment['amount'];
            }
            
            // Validate split payment total matches sale total
            if (abs($totalSplitAmount - $validated['total_amount']) > 0.01) {
                $sale->delete(); // Rollback sale
                return back()->withErrors(['error' => 'Split payment total does not match sale total.'])->withInput();
            }
        } else {
            // Single payment mode
            $paymentData = [
                'sale_id' => $sale->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['total_amount'],
            ];
            
            // Add cheque details if payment method is cheque
            if ($validated['payment_method'] === 'cheque') {
                $paymentData['cheque_number'] = $request->input('cheque_number');
                $paymentData['bank_name'] = $request->input('bank_name');
                $paymentData['cheque_date'] = $request->input('cheque_date');
            }
            
            Payment::create($paymentData);
        }

        // Create customer credit record if payment method is credit
        if ($paymentMethod === 'credit' && isset($validated['customer_id']) && !$requiresApproval) {
            $customer = \App\Models\Customer::find($validated['customer_id']);
            if ($customer) {
                $creditAmount = $validated['total_amount'];
                $dueDate = $validated['credit_due_date'] ?? now()->addDays(30)->format('Y-m-d');
                
                \App\Models\CustomerCredit::create([
                    'customer_id' => $customer->id,
                    'sale_id' => $sale->id,
                    'amount' => $creditAmount,
                    'paid_amount' => 0,
                    'outstanding_amount' => $creditAmount,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'notes' => "Credit sale - Invoice: {$invoiceNumber}",
                ]);
                
                // Update customer outstanding balance
                $customer->increment('outstanding_balance', $creditAmount);
            }
        }

        // Handle credit in split payments
        if (!empty($validated['split_payments'])) {
            foreach ($validated['split_payments'] as $splitPayment) {
                if ($splitPayment['method'] === 'credit' && isset($validated['customer_id']) && !$requiresApproval) {
                    $customer = \App\Models\Customer::find($validated['customer_id']);
                    if ($customer) {
                        $creditAmount = $splitPayment['amount'];
                        $dueDate = $validated['credit_due_date'] ?? now()->addDays(30)->format('Y-m-d');
                        
                        \App\Models\CustomerCredit::create([
                            'customer_id' => $customer->id,
                            'sale_id' => $sale->id,
                            'amount' => $creditAmount,
                            'paid_amount' => 0,
                            'outstanding_amount' => $creditAmount,
                            'due_date' => $dueDate,
                            'status' => 'pending',
                            'notes' => "Split payment credit - Invoice: {$invoiceNumber}",
                        ]);
                        
                        // Update customer outstanding balance
                        $customer->increment('outstanding_balance', $creditAmount);
                    }
                }
            }
        }

        if ($requiresApproval) {
            AuditLog::log('sale_pending_approval', "Sale pending admin approval: {$invoiceNumber}. Discount: {$totalDiscountPercent}%", $sale);
            
            return redirect()->route('cashier.pos')
                ->with('info', "Sale created but pending admin approval. Invoice: {$invoiceNumber}");
        }
        
        AuditLog::log('create_bill', "Created bill: {$invoiceNumber}", $sale);

        $routePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';

        if ($validated['payment_method'] === 'installment') {
            $customerDetails = [
                'name' => $request->input('customer_name'),
                'nic' => $request->input('customer_nic'),
                'phone' => $request->input('customer_phone'),
                'address' => $request->input('customer_address'),
            ];
          // For admin, use the named route correctly
            $redirectRoute = $routePrefix . '.installment-agreement.edit';
            
            return redirect()->route($redirectRoute, $sale->id)
                ->with('success', "Sale created! Please complete the installment agreement. Invoice: {$invoiceNumber}")
                ->with('customer_details', $customerDetails);
        }
        
        return redirect()->route($routePrefix . '.invoice.print', $sale->id)
            ->with('success', "Sale completed! Invoice: {$invoiceNumber}");
    }

    public function printInvoice(Sale $sale)
    {
        $sale->load(['items.item', 'cashier', 'installmentAgreement']);
        return view('cashier.invoice', compact('sale'));
    }

    public function holdBill(Request $request)
    {
        $validated = $request->validate([
            'bill_data' => 'required|array',
            'total_amount' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        HeldBill::create([
            'cashier_id' => auth()->id(),
            'bill_data' => $validated['bill_data'],
            'total_amount' => $validated['total_amount'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Bill held successfully']);
    }

    public function heldBills()
    {
        // Admin can see all held bills, cashier only sees their own
        $billsQuery = HeldBill::query();
        if (auth()->user()->isCashier()) {
            $billsQuery->where('cashier_id', auth()->id());
        }
        $bills = $billsQuery->latest()->get();
        
        return response()->json($bills);
    }

    public function resumeBill(HeldBill $heldBill)
    {
        return response()->json([
            'success' => true,
            'bill_data' => $heldBill->bill_data,
        ]);
    }

    public function cancelSale(Request $request, Sale $sale)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        // Allow cancellation if:
        // 1. Sale is pending (before payment completion)
        // 2. Sale is completed (after payment, requires reason)
        // 3. Admin can always cancel
        // 4. Cashier can cancel pending sales or completed sales (but may need admin approval for completed)
        if (!in_array($sale->status, ['pending', 'completed'])) {
            return back()->withErrors(['error' => 'This sale cannot be cancelled.']);
        }

        // For cashiers cancelling completed sales, check if admin approval is needed
        $isCashier = auth()->user()->isCashier();
        $isCompleted = $sale->status === 'completed';
        
        if ($isCashier && $isCompleted) {
            // Cashier can cancel completed sales but it's logged
            // Optionally require admin approval - for now, allow with reason
        }

        // Restore stock (only if stock was already deducted, i.e., sale was completed)
        // The quantity field is already in the item's unit_of_measure format
        if ($sale->status === 'completed') {
            foreach ($sale->items as $saleItem) {
                $item = $saleItem->item;
                $stockRestoreQuantity = $saleItem->quantity;
                $weight = $saleItem->weight ?? 0;
                $weightUnit = $saleItem->weight_unit ?? $item->unit_of_measure;
                
                $branchStock = \App\Models\BranchStock::where('branch_id', $branchId)->where('item_id', $item->id)->first();
                if ($branchStock) {
                    $branchStock->increment('quantity', $stockRestoreQuantity);
                }
                $newStock = $item->getBranchStock($branchId);
                
                // Format weight display for notes
                $weightDisplay = '';
                if ($weight > 0) {
                    if ($weightUnit === 'kg' || $weightUnit === 'kg_g') {
                        $wholeKg = floor($weight);
                        $remainingGrams = round(($weight - $wholeKg) * 1000);
                        $gFormatted = str_pad($remainingGrams, 3, '0', STR_PAD_LEFT);
                        if ($wholeKg > 0 || $remainingGrams > 0) {
                            $weightDisplay = " (Weight: {$wholeKg}kg {$gFormatted}g)";
                        }
                    } elseif ($weightUnit === 'g') {
                        $wholeKg = floor($weight / 1000);
                        $remainingGrams = round($weight % 1000);
                        $gFormatted = str_pad($remainingGrams, 3, '0', STR_PAD_LEFT);
                        if ($wholeKg > 0 || $remainingGrams > 0) {
                            $weightDisplay = " (Weight: {$wholeKg}kg {$gFormatted}g)";
                        }
                    } else {
                        $weightDisplay = " (Weight: {$weight} {$weightUnit})";
                    }
                }
                
                StockMovement::create([
                    'item_id' => $item->id,
                    'branch_id' => $branchId,
                    'type' => 'return',
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'quantity' => $stockRestoreQuantity,
                    'balance_after' => $newStock,
                    'notes' => "Sale cancelled: {$sale->invoice_number}. Reason: {$validated['reason']}{$weightDisplay}",
                    'created_by' => auth()->id(),
                ]);
            }
        }

        $sale->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'],
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
        ]);

        AuditLog::log('sale_cancelled', "Sale cancelled: {$sale->invoice_number}. Reason: {$validated['reason']}", $sale);

        // Determine route prefix based on user role
        $routePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';
        
        return redirect()->route($routePrefix . '.pos')
            ->with('success', 'Sale cancelled successfully.');
    }

    public function returnSale(Request $request, Sale $sale)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|string|min:10',
            'refund_method' => 'required|in:cash,card,credit',
        ]);

        // Check cash refund limit (if refund method is cash)
        if ($validated['refund_method'] === 'cash') {
            $cashRefundLimit = SystemSetting::getValue('max_cash_refund', 10000); // Default 10,000
            $totalRefund = 0;
            
            // Calculate total refund amount
            foreach ($validated['items'] as $returnItem) {
                $saleItem = $sale->items()->where('item_id', $returnItem['item_id'])->first();
                if ($saleItem) {
                    $returnQuantity = $returnItem['quantity'];
                    $refundAmount = ($saleItem->unit_price * $returnQuantity) - ($saleItem->discount_amount * ($returnQuantity / $saleItem->quantity));
                    $totalRefund += $refundAmount;
                }
            }
            
            // If cash refund exceeds limit and user is cashier, require admin approval
            if ($totalRefund > $cashRefundLimit && auth()->user()->isCashier()) {
                return back()->withErrors(['error' => "Cash refund amount (Rs. " . number_format($totalRefund, 2) . ") exceeds maximum allowed (Rs. " . number_format($cashRefundLimit, 2) . "). Admin approval required."])->withInput();
            }
        }

        if ($sale->status === 'returned') {
            return back()->withErrors(['error' => 'Sale has already been returned.']);
        }

        // Validate return quantities
        foreach ($validated['items'] as $returnItem) {
            $saleItem = $sale->items()->where('item_id', $returnItem['item_id'])->first();
            if (!$saleItem) {
                return back()->withErrors(['error' => 'Item not found in original sale.']);
            }
            if ($returnItem['quantity'] > $saleItem->quantity) {
                return back()->withErrors(['error' => "Return quantity cannot exceed original quantity for {$saleItem->item->name}."]);
            }
        }

        // Restore stock and create return record
        $totalRefund = 0;
        foreach ($validated['items'] as $returnItem) {
            $saleItem = $sale->items()->where('item_id', $returnItem['item_id'])->first();
            $item = $saleItem->item;
            $returnQuantity = $returnItem['quantity'];
            
            // Calculate proportional quantity to restore
            // The return quantity is in the same unit as the original sale item quantity
            // Calculate the proportional amount based on return quantity vs original quantity
            $proportionalQuantity = ($returnQuantity / $saleItem->quantity) * $saleItem->quantity;
            $stockRestoreQuantity = $returnQuantity; // Return quantity is already in correct unit
            
            $refundAmount = ($saleItem->unit_price * $returnQuantity) - ($saleItem->discount_amount * ($returnQuantity / $saleItem->quantity));
            
            // Restore stock
            $branchStock = \App\Models\BranchStock::where('branch_id', $branchId)->where('item_id', $item->id)->first();
            if ($branchStock) {
                $branchStock->increment('quantity', $stockRestoreQuantity);
            }
            $newStock = $item->getBranchStock($branchId);
            
            // Record stock movement
            StockMovement::create([
                'item_id' => $item->id,
                'branch_id' => $branchId,
                'type' => 'return',
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'quantity' => $stockRestoreQuantity,
                'balance_after' => $newStock,
                'notes' => "Return/Refund: {$sale->invoice_number}. Reason: {$validated['reason']}",
                'created_by' => auth()->id(),
            ]);
            
            $totalRefund += $refundAmount;
        }

        // Update sale status
        $sale->update([
            'status' => 'returned',
            'cancellation_reason' => "Return/Refund: {$validated['reason']}",
        ]);

        AuditLog::log('sale_returned', "Sale returned: {$sale->invoice_number}. Reason: {$validated['reason']}. Refund: Rs. {$totalRefund}", $sale);

        $routePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';
        return redirect()->route($routePrefix . '.pos')
            ->with('success', "Return processed successfully. Refund amount: Rs. " . number_format($totalRefund, 2));
    }

    public function checkStock(Item $item)
    {
        $branchId = session('branch_id');
        $stock = $item->getBranchStock($branchId);
        return response()->json([
            'item_id' => $item->id,
            'name' => $item->name,
            'current_stock' => $stock,
            'unit' => $item->unit_of_measure,
        ]);
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $customers = Customer::where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('nic', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get();

        return response()->json($customers);
    }

    public function getCustomerCredits(Request $request)
    {
        $customerId = $request->get('customer_id');
        
        if (empty($customerId)) {
            return response()->json([]);
        }

        $credits = CustomerCredit::where('customer_id', $customerId)
            ->where('status', 'pending')
            ->with('sale')
            ->get();

        return response()->json($credits);
    }

    private function generateInvoiceNumber($format)
    {
        $replacements = [
            '{YYYY}' => now()->format('Y'),
            '{YY}' => now()->format('y'),
            '{MM}' => now()->format('m'),
            '{DD}' => now()->format('d'),
            '{NNNN}' => str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
        ];

        $invoiceNumber = $format;
        foreach ($replacements as $key => $value) {
            $invoiceNumber = str_replace($key, $value, $invoiceNumber);
        }

        // Ensure uniqueness
        while (Sale::where('invoice_number', $invoiceNumber)->exists()) {
            $replacements['{NNNN}'] = str_pad((int)$replacements['{NNNN}'] + 1, 4, '0', STR_PAD_LEFT);
            $invoiceNumber = $format;
            foreach ($replacements as $key => $value) {
                $invoiceNumber = str_replace($key, $value, $invoiceNumber);
            }
        }

        return $invoiceNumber;
    }
}


