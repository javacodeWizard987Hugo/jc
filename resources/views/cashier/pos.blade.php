@extends('layouts.app')

@section('title', 'POS - Point of Sale')

@php
    $routePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';
    $salesCreateRoute = route($routePrefix . '.sales.create');
    $banks = [
        'Amana Bank PLC',
        'Bank of Ceylon',
        'Bank of China Ltd.',
        'Cargills Bank PLC',
        'Citibank, N.A.',
        'Commercial Bank of Ceylon PLC',
        'Deutsche Bank AG (Colombo Branch)',
        'DFCC Bank PLC',
        'Habib Bank Ltd.',
        'Hatton National Bank PLC',
        'Indian Bank',
        'Indian Overseas Bank',
        'MCB Bank Ltd',
        'National Development Bank PLC',
        'Nations Trust Bank PLC',
        'Pan Asia Banking Corporation PLC',
        'People\'s Bank',
        'Public Bank Berhad (Colombo Branch)',
        'Sampath Bank PLC',
        'Seylan Bank PLC',
        'Standard Chartered Bank',
        'State Bank of India (Colombo Branch)',
        'Union Bank of Colombo PLC'
    ];
@endphp

@section('content')
<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<script>
console.log('POS page JavaScript loaded successfully');
console.log('Current URL:', window.location.href);
console.log('Route prefix from server:', '{{ $routePrefix }}');
</script>

<!-- Hidden data for JavaScript -->
<div id="app-data" data-route-prefix="{{ $routePrefix }}"></div>

<!-- Custom Quantity Modal -->
    <div id="quantityModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40">
    <div class="bg-white rounded-lg shadow-2xl p-8 max-w-md w-full mx-4 border-4 border-black">
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white px-6 py-4 -mx-8 -mt-8 mb-6 rounded-t-lg">
            <h2 class="text-2xl font-bold">🛒 Add Item to Cart</h2>
        </div>
        
        <div id="modalItemName" class="text-xl font-bold text-gray-900 mb-6 text-center"></div>
        
        <div class="space-y-4">
            <!-- Price Input (Alternative to Quantity) -->
            <div>
                <label class="block text-lg font-bold text-gray-800 mb-2">💰 Price (Optional)</label>
                <div class="flex gap-2">
                    <input type="number" id="priceInput" min="0" step="0.01" value=""
                           class="flex-1 px-4 py-3 text-lg border-3 border-green-400 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-green-600 font-semibold"
                           placeholder="Enter price to auto-calculate quantity"
                           oninput="calculateQuantityFromPrice()">
                    <span id="pricePerUnit" class="py-3 px-3 bg-gray-100 border-3 border-gray-300 rounded-lg font-bold text-lg text-gray-800">Rs. /PCS</span>
                </div>
                <div id="priceMessage" class="text-sm text-green-600 font-semibold mt-1"></div>
            </div>

            <!-- Quantity Unit Selection -->
            <div>
                <label class="block text-lg font-bold text-gray-800 mb-2">📦 Quantity Unit</label>
                <div class="grid grid-cols-1 gap-3">
                    <button type="button" id="unitPcs" onclick="selectQuantityUnit('pcs')"
                            class="py-3 px-4 border-3 border-red-600 bg-red-50 rounded-lg font-bold text-lg transition-all text-red-700">
                        📦 PCS (Pieces)
                    </button>
                </div>
            </div>

            <!-- Quantity Input -->
            <div id="quantityInputContainer">
                <!-- For pcs format -->
                <div id="quantity_pcs_input" style="display: block;">
                    <label class="block text-lg font-bold text-gray-800 mb-2">📦 Quantity (PCS)</label>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Pieces</label>
                            <input type="number" id="quantityPcs" min="1" step="1" value="1"
                                   class="w-full px-4 py-3 text-lg border-3 border-red-400 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-red-600 font-semibold"
                                   placeholder="PCS"
                                   oninput="clearPriceCalculation(); updateQuantityFromPcs()">
                        </div>
                    </div>
                </div>
                <div id="stockWarning" class="text-sm text-red-600 font-semibold mt-1"></div>
            </div>
        </div>

        <div id="modalMessage" class="mt-4 p-3 bg-blue-100 border-2 border-blue-400 rounded-lg text-sm font-semibold text-blue-800 hidden"></div>

        <div class="flex gap-3 mt-8">
            <button type="button" onclick="closeQuantityModal()"
                    class="flex-1 bg-gray-400 hover:bg-gray-500 text-white py-3 px-4 rounded-lg font-bold text-lg transition-all transform hover:scale-105">
                ❌ Cancel
            </button>
            <button type="button" onclick="confirmQuantity()"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-black py-3 px-4 rounded-lg font-bold text-lg transition-all transform hover:scale-105">
                ✅ Add to Cart
            </button>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 mx-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 mx-4" role="alert">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center bg-white p-4 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold text-gray-900">💰 Point of Sale</h1>
        <div class="text-base font-semibold text-gray-700 bg-gray-100 px-4 py-2 rounded-lg">
            {{ ucfirst(auth()->user()->role) }}: <span class="text-red-600">{{ auth()->user()->name }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Item Search & Selection -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-gray-200">
                <label class="block text-lg font-bold text-gray-800 mb-3">🔍 Search Items</label>
                <input type="text" id="itemSearch" placeholder="Type item name, code, or scan barcode..." 
                       class="w-full px-5 py-4 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 shadow-sm"
                       autofocus>
                <p class="mt-2 text-sm text-gray-600">💡 Tip: Scan barcode or type item code for quick search</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="mr-2">📦</span> Available Items
                </h2>
                <div id="itemsGrid" class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                    @foreach($items as $item)
                        @php
                            $oneKgPrice = $item->unit_of_measure === 'g' ? $item->selling_price * 1000 : ($item->unit_of_measure === 'kg' ? $item->selling_price : $item->selling_price);
                        @endphp
                        <button onclick="handleItemClick(event, {{ $item->id }}, '{{ $item->name }}', {{ $item->selling_price }}, {{ $item->current_stock }}, '{{ $item->unit_of_measure }}')" 
                            class="p-5 border-2 border-gray-300 rounded-lg hover:border-red-500 hover:bg-red-50 hover:shadow-md transition-all text-left bg-white">
                        <div class="font-bold text-base text-gray-900 mb-1">{{ $item->name }}</div>
                        <div class="text-sm text-gray-600 mb-2">{{ $item->item_code }}</div>
                        <div class="text-sm text-gray-700 mb-1">1 PCS: Rs. {{ number_format($item->selling_price, 2) }}</div>
                        <div class="text-lg font-bold text-red-600 mb-2">Rs. {{ number_format($item->selling_price, 2) }}</div>
                        <div class="text-sm font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded">
                            Stock: {{ App\Models\Item::formatStock($item->current_stock, $item->unit_of_measure) }}
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Cart & Checkout -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-xl p-6 sticky top-4 border-2 border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="mr-2">🛒</span> Shopping Cart
                </h2>
                
                <div id="cartItems" class="space-y-3 mb-6 max-h-64 overflow-y-auto border-2 border-dashed border-gray-200 rounded-lg p-4">
                    <p class="text-base text-gray-500 text-center font-medium">Cart is empty</p>
                </div>

                <div class="border-t-2 border-gray-300 pt-4 space-y-3 bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between text-base font-semibold">
                        <span class="text-gray-700">Subtotal:</span>
                        <span id="subtotal" class="text-gray-900">Rs. 0.00</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold">
                        <span class="text-gray-700">Discount:</span>
                        <span id="discount" class="text-green-600">Rs. 0.00</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold">
                        <span class="text-gray-700">Tax:</span>
                        <span id="tax" class="text-gray-900">Rs. 0.00</span>
                    </div>
                    <div class="flex justify-between font-bold text-2xl border-t-2 border-gray-400 pt-3 mt-3">
                        <span class="text-gray-900">Total:</span>
                        <span id="total" class="text-red-600">Rs. 0.00</span>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <!-- Installment Calculator -->
                   {{-- ================= Installment Calculator ================= --}}
                    
                    {{-- ================= Installment Calculator ================= --}}
                        <div class="bg-green-100 shadow rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold mb-4">📱 Installment Calculator</h3>

                            {{-- INPUTS --}}
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                                {{-- Item Price --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Item Price (Rs.)</label>
                                    <input type="number" id="itemPrice"
                                        class="mt-1 w-full rounded-md border-gray-300"
                                        oninput="calculateInstallment()">
                                </div>
                          {{-- Down Payment Type --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Down Payment Type</label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <button type="button" id="dp_type_percent" onclick="setDownPaymentType('percent')" class="px-3 py-2 bg-blue-600 text-white rounded-l-md">Percent (%)</button>
                                        <button type="button" id="dp_type_fixed" onclick="setDownPaymentType('fixed')" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-r-md">Fixed (Rs)</button>
                                    </div>
                                    <input type="hidden" id="downPaymentType" value="percent">
                                </div>

                                {{-- Down Payment Value --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Down Payment Value</label>
                                    <input type="number" id="downPaymentValue" value="30"
                                        class="mt-1 w-full rounded-md border-gray-300"
                                        oninput="calculateInstallment()">
                                </div>

                                {{-- Annual Interest % --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Interest %</label>
                                    <input type="number" id="annualInterestPercent" value="0"
                                        class="mt-1 w-full rounded-md border-gray-300"
                                        oninput="calculateInstallment()">
                                </div>

                                {{-- Months --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Payment Period (Months)</label>
                                    <input type="number" id="paymentMonths" value="6"
                                        class="mt-1 w-full rounded-md border-gray-300"
                                        oninput="calculateInstallment()">
                                </div>

                                {{-- Other Charges --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Other Charges (Rs.)</label>
                                    <input type="number" id="otherCharges" value="0"
                                        class="mt-1 w-full rounded-md border-gray-300"
                                        oninput="calculateInstallment()">
                                </div>
                            </div>

                            {{-- OUTPUTS --}}
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-6 bg-gray-50 p-4 rounded-lg">

                                <div>
                                    <label class="text-sm">Down Payment Amount</label>
                                    <input type="number" id="calcDownPaymentAmount" readonly
                                        class="w-full bg-gray-100 border rounded p-2">
                                </div>

                                <div>
                                    <label class="text-sm">Loan Amount</label>
                                    <input type="number" id="loanAmount" readonly
                                        class="w-full bg-gray-100 border rounded p-2">
                                </div>

                                <div>
                                    <label class="text-sm">Annual Interest Value</label>
                                    <input type="number" id="annualInterestValue" readonly
                                        class="w-full bg-gray-100 border rounded p-2">
                                </div>

                                <div>
                                    <label class="text-sm">Monthly Interest</label>
                                    <input type="number" id="monthlyInterest" readonly
                                        class="w-full bg-gray-100 border rounded p-2">
                                </div>

                                <div>
                                    <label class="text-sm">Monthly Other Charges</label>
                                    <input type="number" id="monthlyOtherCharges" readonly
                                        class="w-full bg-gray-100 border rounded p-2">
                                </div>

                                <div>
                                    <label class="text-sm font-bold">Monthly Total Payment</label>
                                    <input type="number" id="monthlyTotalPayment" readonly
                                        class="w-full bg-green-100 border-2 border-green-500 rounded p-2 font-bold">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-bold">Total Payable (After Period)</label>
                                <input type="number" id="totalPayable" readonly
                                    class="w-full bg-red-100 border-2 border-red-500 rounded p-3 font-bold">
                            </div>
                        </div>

                  <!-- Customer Selection -->
                    <div class="bg-blue-50 p-5 rounded-xl border-2 border-blue-200">

                            <div class="flex flex-wrap justify-between items-center gap-3 mb-3">
                                <!--<label class="text-lg font-bold text-gray-800">
                                    👤 Customer (Optional)
                                </label>-->

                                <div class="flex gap-3">
                                    <!-- Search Customer History -->
                                    <a href="{{ route('admin.customer-history.index') }}"
                                    class="bg-green-600 text-white px-6 py-3 rounded-lg
                                            text-sm font-bold hover:bg-green-700 transition shadow-md">
                                        🔍 Search Customer History
                                    </a>

                                    <!-- Add New Customer (Bigger Button) -->
                                    <button type="button"
                                        onclick="openAddCustomerModal()"
                                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg
                                            text-sm font-bold hover:bg-blue-700 transition shadow-md">
                                        ➕ Add New Customer
                                    </button>
                    </div>
    </div>

                <!-- Customer Search Input -->
                <input type="text" id="customerSearch"
                    placeholder="Search customer by name, phone..."
                    class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">

                <input type="hidden" id="customerId" name="customer_id">

                <div id="customerResults"
                    class="hidden mt-2 border-2 border-gray-300 rounded-lg bg-white
                        max-h-48 overflow-y-auto shadow-lg"></div>

                <div id="selectedCustomer"
                    class="hidden mt-3 p-3 bg-blue-100 rounded-lg border-2 border-blue-300">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-bold text-blue-900"
                            id="selectedCustomerName"></span>
                        <button type="button"
                            onclick="clearCustomer()"
                            class="text-red-600 text-2xl font-bold hover:text-red-800">
                            ×
                        </button>
                    </div>
                    <div id="customerCreditsInfo"
                        class="mt-2 text-sm font-semibold text-blue-700"></div>
                </div>

            </div>

                    <div>
                        <label class="block text-base font-bold text-gray-800 mb-2">💳 Payment Method</label>
                        <select id="paymentMethod" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 shadow-sm font-semibold" onchange="togglePaymentFields()">
                            <option value="cash">💵 Cash</option>
                            <option value="card">💳 Card</option>
                            <option value="cheque">📝 Cheque</option>
                            <option value="credit">📋 Credit</option>
                            <option value="installment">🏦 Installment</option>
                        </select>
                    </div>
                    
                    <div id="chequeFields" style="display: none;" class="space-y-3 bg-yellow-50 p-4 rounded-lg border-2 border-yellow-300">
                        <label class="block text-base font-bold text-gray-800">📝 Cheque Details</label>
                        <input type="text" id="chequeNumber" placeholder="Cheque Number" 
                               class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm">
                        <select id="bankName" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm">
                            <option value="">Select Bank</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank }}">{{ $bank }}</option>
                            @endforeach
                        </select>
                        <input type="date" id="chequeDate" 
                               class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm">
                    </div>

                    <div id="creditFields" style="display: none;" class="space-y-3 bg-orange-50 p-4 rounded-lg border-2 border-orange-300">
                        <label class="block text-base font-bold text-gray-800">📅 Repay Day (Due Date) *</label>
                        <input type="date" id="creditDueDate" required
                               class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm font-semibold"
                               min="{{ date('Y-m-d') }}">
                        <div id="existingCredits" class="mt-3 p-3 bg-yellow-100 rounded-lg border-2 border-yellow-400">
                            <strong class="text-base font-bold text-gray-800 block mb-2">⚠️ Existing Credits:</strong>
                            <div id="creditsList" class="text-sm"></div>
                        </div>
                    </div>

                 <div id="installmentFields"
                            style="display: none;"
                            class="space-y-3 bg-purple-50 p-4 rounded-lg border-2 border-purple-300">

                            <label class="block text-base font-bold text-gray-800">🏦 Installment Details</label>
                        
                            <!-- Item Price -->
                            <div>
                                <label for="installmentItemPrice" class="text-sm font-medium text-gray-700">Item Price</label>
                                <input type="number" id="installmentItemPrice" name="item_price" readonly class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm bg-gray-100">
                            </div>
                        
                            <!-- Down Payment Amount -->
                            <div>
                                <label for="downPaymentAmount" class="text-sm font-medium text-gray-700">Down Payment Amount</label>
                                <input type="number" id="downPaymentAmount" name="down_payment" readonly class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm bg-gray-100">
                            </div>
                        
                            <!-- Down Payment Method -->
                            <div>
                                <label for="downPaymentMethod" class="text-sm font-medium text-gray-700">Down Payment Method</label>
                                <select id="downPaymentMethod" name="down_payment_method" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm">
                                    <option value="cash">Down Payment by Cash</option>
                                    <option value="card">Down Payment by Card</option>
                                    <option value="cheque">Down Payment by Cheque</option>
                                </select>
                            </div>
                        
                            <!-- Loan Amount -->
                            <div>
                                <label for="installmentLoanAmount" class="text-sm font-medium text-gray-700">Loan Amount</label>
                                <input type="number" id="installmentLoanAmount" name="loan_amount" readonly class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm bg-gray-100">
                            </div>
                        
                            <!-- Number of Installments -->
                            <div>
                                <label for="numberOfInstallments" class="text-sm font-medium text-gray-700">Number of Installments</label>
                                <input type="number" id="numberOfInstallments" name="installment_months" readonly class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm bg-gray-100">
                            </div>
                        
                            <!-- Monthly Installment -->
                            <div>
                                <label for="monthlyInstallmentAmount" class="text-sm font-medium text-gray-700">Monthly Installment</label>
                                <input type="number" id="monthlyInstallmentAmount" name="monthly_installment" readonly class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm bg-gray-100">
                            </div>
                        
                            <!-- Total Payable -->
                            <div>
                                <label for="installmentTotalPayable" class="text-sm font-medium text-gray-700">Total Payable</label>
                                <input type="number" id="installmentTotalPayable" name="total_payable" readonly class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm bg-gray-100">
                            </div>
                        
                            <!-- First Due Date -->
                            <div>
                                <label for="firstDueDate" class="text-sm font-medium text-gray-700">First Due Date</label>
                                <input type="date" id="firstDueDate" name="first_due_date" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm">
                            </div>
                        
                            <!-- Due Day of Month -->
                            <div>
                                <label for="dueDayOfMonth" class="text-sm font-medium text-gray-700">Due Day of Month</label>
                                <input type="number" id="dueDayOfMonth" name="due_day" min="1" max="28" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg shadow-sm" placeholder="Due Day of Month (Default today)">
                            </div>
                        </div>

                    <button onclick="completeSale()" 
                            class="w-full bg-red-600 text-white py-4 px-6 rounded-lg font-bold text-lg hover:bg-red-700 shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        ✅ Complete Sale
                    </button>
                    
                    <button onclick="holdBill()" 
                            class="w-full bg-blue-600 text-white py-4 px-6 rounded-lg font-bold text-lg hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all transform hover:scale-105 border-2 border-blue-700">
                        ⏸️ Hold Bill
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<form id="saleForm" method="POST" action="{{ $salesCreateRoute }}" style="display: none;">
    @csrf
    <input type="hidden" name="items" id="itemsInput">
    <input type="hidden" name="subtotal" id="subtotalInput">
    <input type="hidden" name="discount_amount" id="discountInput">
    <input type="hidden" name="tax_amount" id="taxInput">
    <input type="hidden" name="total_amount" id="totalInput">
    <input type="hidden" name="payment_method" id="paymentMethodInput">
    <input type="hidden" name="customer_id" id="customerIdInput">
    <input type="hidden" name="customer_name" id="customerNameInput">
    <input type="hidden" name="customer_nic" id="customerNicInput">
    <input type="hidden" name="customer_phone" id="customerPhoneInput">
    <input type="hidden" name="customer_address" id="customerAddressInput">
    <input type="hidden" name="credit_due_date" id="creditDueDateInput">
    <input type="hidden" name="cheque_number" id="chequeNumberInput">
    <input type="hidden" name="bank_name" id="bankNameInput">
    <input type="hidden" name="cheque_date" id="chequeDateInput">
    <input type="hidden" name="down_payment_amount" id="downPaymentAmountInput">
    <input type="hidden" name="down_payment_method" id="downPaymentMethodInput">
    <input type="hidden" name="number_of_installments" id="numberOfInstallmentsInput">
    <input type="hidden" name="monthly_installment_amount" id="monthlyInstallmentAmountInput">
    <input type="hidden" name="first_due_date" id="firstDueDateInput">
    <input type="hidden" name="due_day_of_month" id="dueDayOfMonthInput">
</form>





<script>
    function setDownPaymentType(type) {
    document.getElementById('downPaymentType').value = type;
    const percentBtn = document.getElementById('dp_type_percent');
    const fixedBtn = document.getElementById('dp_type_fixed');

    if (type === 'percent') {
        percentBtn.classList.add('bg-blue-600', 'text-white');
        percentBtn.classList.remove('bg-gray-200', 'text-gray-700');
        fixedBtn.classList.add('bg-gray-200', 'text-gray-700');
        fixedBtn.classList.remove('bg-blue-600', 'text-white');
        document.getElementById('downPaymentValue').value = 30; // Default percentage
    } else {
        fixedBtn.classList.add('bg-blue-600', 'text-white');
        fixedBtn.classList.remove('bg-gray-200', 'text-gray-700');
        percentBtn.classList.add('bg-gray-200', 'text-gray-700');
        percentBtn.classList.remove('bg-blue-600', 'text-white');
        document.getElementById('downPaymentValue').value = ''; // Clear for manual input
    }
    calculateInstallment();
}

function roundMoney(value) {
    return Math.round(value * 100) / 100;
}
function calculateInstallment() {
    // Inputs from form
    const itemPrice = parseFloat(document.getElementById('itemPrice').value) || 0;
     const downPaymentType = document.getElementById('downPaymentType').value;
    const downPaymentValue = parseFloat(document.getElementById('downPaymentValue').value) || 30;
    const annualInterestPercent = parseFloat(document.getElementById('annualInterestPercent').value) || 0;
    const months = parseInt(document.getElementById('paymentMonths').value) || 6;
    const otherCharges = parseFloat(document.getElementById('otherCharges').value) || 0; // This is treated as a MONTHLY charge

    /* STEP 1: Down Payment */
      let downPaymentAmount = 0;
    if (downPaymentType === 'percent') {
        downPaymentAmount = itemPrice * (downPaymentValue / 100);
    } else {
        downPaymentAmount = downPaymentValue;
    }

    /* STEP 2: Loan Amount */
    const loanAmount = itemPrice - downPaymentAmount;

    /* STEP 3: Monthly Interest Rate */
    const monthlyInterestRate = (annualInterestPercent / 100) / 12;

    /* STEP 4: Calculate EMI (Equated Monthly Installment for the loan part) */
    let emi = 0;
    if (monthlyInterestRate > 0 && months > 0) {
        const r = monthlyInterestRate;
        const n = months;
        const p = loanAmount;
        emi = p * r * (Math.pow(1 + r, n) / (Math.pow(1 + r, n) - 1));
    } else if (months > 0) {
        emi = loanAmount / months; // 0 interest loan
    }


       /* STEP 5: Total Monthly Payment */
    const monthlyOtherCharges = months > 0 ? otherCharges / months : 0;
    const monthlyTotalPayment = emi + monthlyOtherCharges; // Add monthly other 
    
    /* STEP 6: Total Payable (Total of all payments customer makes) */
    const totalPayable = (monthlyTotalPayment * months) + downPaymentAmount;
    
    /* STEP 7: Total Interest */
    const totalInterest = (emi * months) - loanAmount;

    /* OUTPUTS — ROUND ONLY HERE */
    document.getElementById('calcDownPaymentAmount').value = roundMoney(downPaymentAmount);
    document.getElementById('loanAmount').value = roundMoney(loanAmount);
    document.getElementById('annualInterestValue').value = roundMoney(totalInterest);
    document.getElementById('monthlyInterest').value = roundMoney(emi); // This field now shows the EMI (loan part only)
    document.getElementById('monthlyOtherCharges').value = roundMoney(otherCharges); // This field shows the monthly other charges
    document.getElementById('monthlyTotalPayment').value = Math.ceil(monthlyTotalPayment); // This is the final monthly bill
    document.getElementById('totalPayable').value = Math.ceil(totalPayable);

    /* SYNC TO INSTALLMENT FIELDS in the main checkout form */
    document.getElementById('downPaymentAmount').value = roundMoney(downPaymentAmount);
    document.getElementById('numberOfInstallments').value = months;
    document.getElementById('monthlyInstallmentAmount').value = Math.ceil(monthlyTotalPayment);
    document.getElementById('installmentItemPrice').value = roundMoney(itemPrice);
    document.getElementById('installmentLoanAmount').value = roundMoney(loanAmount);
    document.getElementById('installmentTotalPayable').value = Math.ceil(totalPayable);
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    const today = new Date();

    // first_due_date = today
    const firstDueDateInput = document.getElementById('firstDueDateInput');
    if (firstDueDateInput && !firstDueDateInput.value) {
        firstDueDateInput.value = formatDate(today);
    }

    // due_day_of_month = +1 calendar month (safe)
    const dueDayOfMonthInput = document.getElementById('dueDayOfMonthInput');
    if (dueDayOfMonthInput && !dueDayOfMonthInput.value) {
        const nextDue = new Date(today);
        nextDue.setDate(1);                  // 🔒 prevent overflow
        nextDue.setMonth(nextDue.getMonth() + 1);
        nextDue.setDate(today.getDate());    // restore day safely

        dueDayOfMonthInput.value = formatDate(nextDue);
    }
});
</script>


<script>
let cart = [];
// Persistence logic
const POS_STORAGE_KEY = 'pos_current_cart';
const CUSTOMER_STORAGE_KEY = 'pos_current_customer';

function savePosState() {
    localStorage.setItem(POS_STORAGE_KEY, JSON.stringify(cart));
    if (selectedCustomer) {
        localStorage.setItem(CUSTOMER_STORAGE_KEY, JSON.stringify(selectedCustomer));
    } else {
        localStorage.removeItem(CUSTOMER_STORAGE_KEY);
    }
}

function loadPosState() {
    const savedCart = localStorage.getItem(POS_STORAGE_KEY);
    const savedCustomer = localStorage.getItem(CUSTOMER_STORAGE_KEY);
    
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
            updateCart();
        } catch (e) { console.error('Error loading saved cart', e); }
    }
    
    if (savedCustomer) {
        try {
            const cust = JSON.parse(savedCustomer);
            selectCustomer(cust.id, cust.name, cust.outstanding, cust.nic, cust.phone, cust.address);
        } catch (e) { console.error('Error loading saved customer', e); }
    }
}

function clearPosState() {
    localStorage.removeItem(POS_STORAGE_KEY);
    localStorage.removeItem(CUSTOMER_STORAGE_KEY);
}
const maxDiscount = {{ $maxDiscount }};
const taxRate = {{ $taxRate }};
const roundingRules = '{{ $roundingRules ?? "none" }}';

// Toast notification system
function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️';
    
    toast.innerHTML = `
        <div class="${bgColor} text-white px-6 py-3 rounded-lg shadow-lg font-semibold flex items-center gap-3 animate-slideIn">
            <span>${icon}</span>
            <span>${escapeHtml(message)}</span>
        </div>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, duration);
}

// Add CSS for toast animation
if (!document.getElementById('toastStyles')) {
    const style = document.createElement('style');
    style.id = 'toastStyles';
    style.innerHTML = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
}

// Modal state variables
let currentModalItem = null;
let selectedQuantityUnit = 'pcs';
let quantityPcs = 1;

function openQuantityModal(itemId, name, price, stock, unit) {
    currentModalItem = { itemId, name, price, stock, unit };
    // default selected unit to item's unit if provided
    selectedQuantityUnit = unit || 'pcs';

    document.getElementById('modalItemName').innerHTML = `<span class="text-red-600">${escapeHtml(name)}</span><br><span class="text-base text-gray-600">Price: Rs. ${price.toFixed(2)} | Stock: ${stock}</span>`;

    // Reset quantity input
    document.getElementById('quantityPcs').value = '1';

    document.getElementById('priceInput').value = '';
    document.getElementById('priceMessage').textContent = '';
    document.getElementById('stockWarning').textContent = '';
    document.getElementById('modalMessage').classList.add('hidden');

    // Apply visual selection according to selectedQuantityUnit
    selectQuantityUnit(selectedQuantityUnit);

    // Focus on appropriate input
    document.getElementById('quantityPcs').focus();

    document.getElementById('quantityModal').classList.remove('hidden');

    // Fetch serial numbers
    const serialNumberContainer = document.getElementById('serialNumberContainer');
    const serialNumberSelect = document.getElementById('serialNumberSelect');
    serialNumberSelect.innerHTML = '<option value="">Loading...</option>';

    fetch(`/admin/items/${itemId}/serial-numbers`)
        .then(response => response.json())
        .then(data => {
            serialNumberSelect.innerHTML = '<option value="">Select Serial Number</option>';
            if (data.length > 0) {
                data.forEach(serial => {
                    const option = document.createElement('option');
                    option.value = serial.serial_number;
                    option.textContent = serial.serial_number;
                    serialNumberSelect.appendChild(option);
                });
                serialNumberContainer.style.display = 'block';
            } else {
                serialNumberContainer.style.display = 'none';
            }
        });
}

function closeQuantityModal() {
    document.getElementById('quantityModal').classList.add('hidden');
    currentModalItem = null;
}

function selectQuantityUnit(unit) {
    selectedQuantityUnit = unit;

    // Update button styles
    const unitPcsBtn = document.getElementById('unitPcs');
    const quantityPcsInput = document.getElementById('quantity_pcs_input');

    if (unit === 'pcs') {
        unitPcsBtn.classList.add('border-red-600', 'bg-red-50', 'text-red-700');
        unitPcsBtn.classList.remove('border-gray-300');
        quantityPcsInput.style.display = 'block';
    }
}

function updateQuantityFromPcs() {
    quantityPcs = parseFloat(document.getElementById('quantityPcs').value) || 0;
}

function calculateQuantityFromPrice() {
    if (!currentModalItem) return;

    const priceInput = document.getElementById('priceInput');
    const priceEntered = parseFloat(priceInput.value) || 0;
    const pricePerUnit = parseFloat(currentModalItem.price) || 0;

    // Display price per unit info
    const pricePerUnitSpan = document.getElementById('pricePerUnit');
    pricePerUnitSpan.textContent = `Rs. ${pricePerUnit.toFixed(2)}/PCS`;

    if (priceEntered > 0 && pricePerUnit > 0) {
        // Calculate quantity = price / price_per_pcs
        const calculatedQuantity = priceEntered / pricePerUnit;

        if (selectedQuantityUnit === 'pcs') {
            // Set pcs input
            const wholePcs = Math.round(calculatedQuantity);
            document.getElementById('quantityPcs').value = wholePcs;
            updateQuantityFromPcs();

            // Show message
            const priceMessage = document.getElementById('priceMessage');
            priceMessage.textContent = `✓ Calculated: ${wholePcs} PCS`;
        }
    }
}

function clearPriceCalculation() {
    // When user manually enters quantity, clear the price input
    document.getElementById('priceInput').value = '';
    document.getElementById('priceMessage').textContent = '';
}

function confirmQuantity() {
    if (!currentModalItem) return;

    let quantity = 0;
    let displayQuantity = '';

    if (selectedQuantityUnit === 'pcs') {
        const pcs = parseFloat(document.getElementById('quantityPcs').value) || 0;

        if (pcs <= 0) {
            showToast('Invalid quantity. Please enter a quantity greater than 0.', 'error');
            return;
        }

        quantity = pcs;
        displayQuantity = `${pcs} PCS`;
    }

    // Check stock
    if (quantity > currentModalItem.stock) {
        showToast(`Only ${currentModalItem.stock} available in stock!`, 'warning');
        return;
    }

    // Compute price per pcs based on unit
    const unit = currentModalItem.unit || 'pcs';
    const rawPrice = parseFloat(currentModalItem.price || 0);
    // For pcs, price is already per pcs
    const pricePerPcs = rawPrice;

    const existingItem = cart.find(item => item.item_id === currentModalItem.itemId && item.unit === 'pcs');

    if (existingItem) {
        existingItem.quantity += quantity;
        showToast(`Updated "${currentModalItem.name}"`, 'info');
    } else {
        cart.push({
            item_id: currentModalItem.itemId,
            name: currentModalItem.name,
            price: pricePerPcs,
            quantity: quantity,
            unit: 'pcs', // Always use pcs format
            discount: 0,
            discount_type: 'rupee',
            fromCalculator: false
        });
        showToast(`✨ Added "${currentModalItem.name}" ${displayQuantity} to cart`, 'success');
    }

    updateCart();
    closeQuantityModal();
      savePosState();
}

// Handle Enter key in quantity input
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !document.getElementById('quantityModal').classList.contains('hidden')) {
        e.preventDefault();
        confirmQuantity();
    }
});

// When an item button is clicked, add it directly to the cart
function handleItemClick(e, itemId, name, price, stock, unit) {
    addToCart(itemId, name, price, stock, unit || 'pcs');
}

let currentModalKeepExisting = false;

function addToCart(itemId, name, price, stock, unit = 'pcs') {
    // All items are quantity-based (pcs)
    const pricePerPcs = price; // Price is always per pcs

    const existing = cart.find(item => item.item_id === itemId && item.unit === unit);
    if (existing) {
        // increment by 1
        const inc = 1;
        if (existing.quantity + inc > stock) {
            showToast(`Insufficient stock! Only ${stock} items available.`, 'error');
            return;
        }
        existing.quantity += inc;

        showToast(`Updated "${name}" to ${existing.quantity} PCS`, 'info');
    } else {
        cart.push({
            item_id: itemId,
            name: name,
            price: pricePerPcs,
            quantity: 1, // 1 PCS
            unit: 'pcs', // Always use pcs format
            discount: 0,
            discount_type: 'rupee',
            fromCalculator: false
        });
        showToast(`✨ Added "${name}" 1 PCS to cart`, 'success');
    }
    updateCart();
}

function calculateQuantityInCart() {
    const itemSelect = document.getElementById('calcItemSelect');
    const priceInput = document.getElementById('calcPrice');
    const itemId = itemSelect.value;
    const priceEntered = parseFloat(priceInput.value) || 0;

    if (!itemId) {
        document.getElementById('calcResult').classList.add('hidden');
        return;
    }

    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    const pricePerPcs = parseFloat(selectedOption.dataset.price) || 0;

    if (priceEntered > 0 && pricePerPcs > 0) {
        // Calculate quantity = price / price_per_pcs
        const calculatedQuantity = priceEntered / pricePerPcs;

        // Convert to whole number
        const wholePcs = Math.round(calculatedQuantity);

        // Format display string
        let displayQuantity = wholePcs + ' PCS';

        // Store calculation in window object for later use
        window.lastCalcQuantity = {
            itemId: itemId,
            itemName: selectedOption.dataset.name,
            price: priceEntered,
            pricePerPcs: pricePerPcs,
            quantity: wholePcs,
            unit: 'pcs',  // Always pcs for price calculator
            displayFormat: displayQuantity
        };

        // Display result
        document.getElementById('calcResultQuantity').textContent = displayQuantity;
        document.getElementById('calcResultUnit').textContent = '';
        document.getElementById('calcResult').classList.remove('hidden');
    } else {
        document.getElementById('calcResult').classList.add('hidden');
    }
}

function addCalculatedQuantity() {
    if (!window.lastCalcQuantity) {
        showToast('Please enter a price and select an item first.', 'error');
        return;
    }

    const calc = window.lastCalcQuantity;

    // Helper function to format quantity as "X PCS"
    function formatQuantityDisplay(quantity) {
        return quantity + ' PCS';
    }

    // Find item in cart or add new
    const existingItem = cart.find(item => item.item_id === parseInt(calc.itemId) && item.unit === calc.unit);

    if (existingItem) {
        // Update existing item
        existingItem.quantity += calc.quantity;

        // Mark as calculator-added if not already marked
        if (!existingItem.fromCalculator) {
            existingItem.fromCalculator = true;
        }

        // Update display format
        existingItem.displayQuantity = formatQuantityDisplay(existingItem.quantity);

        // Calculate total quantity in combined format for display
        const totalDisplay = formatQuantityDisplay(existingItem.quantity);
        showToast(`✨ Updated "${calc.itemName}" to cart (Total: ${totalDisplay})`, 'success');
    } else {
        // Add new item - we need to fetch item details from the items array
        const itemElement = document.querySelector(`[onclick*="${calc.itemId}"]`);
        if (!itemElement) {
            showToast('Item not found. Please select from available items.', 'error');
            return;
        }

        cart.push({
            item_id: parseInt(calc.itemId),
            name: calc.itemName,
            price: calc.pricePerPcs,  // Store price per pcs
            quantity: calc.quantity,  // Quantity in pcs
            unit: calc.unit,
            discount: 0,
            discount_type: 'rupee',
            fromCalculator: true,  // Mark as added from calculator
            displayQuantity: calc.displayFormat  // Store the formatted display
        });

        // Show quantity in combined format
        showToast(`✨ Added "${calc.itemName}" ${calc.displayFormat} - Rs. ${calc.price.toFixed(2)} to cart`, 'success');
    }

    // Clear inputs
    document.getElementById('calcItemSelect').value = '';
    document.getElementById('calcPrice').value = '';
    document.getElementById('calcResult').classList.add('hidden');
    window.lastCalcQuantity = null;

    updateCart();
}

function removeFromCart(index) {
    const itemName = cart[index].name;
    cart.splice(index, 1);
    showToast(`Removed "${itemName}" from cart`, 'warning');
    updateCart();
     savePosState();
    savePosState();
}

function updateQuantity(index, change) {
    const item = cart[index];
    item.quantity = Math.max(1, item.quantity + change);

    // Recalculate display quantity for calculator-added items
    if (item.fromCalculator) {
        item.displayQuantity = item.quantity + ' PCS';
    }

    updateCart();
}

function updateDiscount(index, discount) {
    cart[index].discount = parseFloat(discount) || 0;
    updateCart();
}

function updateDiscountType(index, discountType) {
    cart[index].discount_type = discountType;
    updateCart();
}

function updateItemQuantityFromPcs(index) {
    const item = cart[index];
    if (!item) return;

    // Get pcs value from input field
    const pcsInput = document.getElementById(`cart_quantity_pcs_${index}`);

    if (!pcsInput) return;

    const pcs = parseFloat(pcsInput.value) || 0;

    // Update item quantity
    item.quantity = pcs;
    item.unit = 'pcs';

    // Update display quantity for calculator-added items
    if (item.fromCalculator) {
        item.displayQuantity = item.quantity + ' PCS';
    }

    // Update only the price and quantity display for this item without regenerating entire cart
    updateItemPriceDisplay(index);

    // Also update totals
    updateTotals();
    savePosState();
}

// Update only the price and quantity display for a specific cart item
function updateItemPriceDisplay(index) {
    const item = cart[index];
    if (!item) return;

    // Find the cart item container
    const cartItemsDiv = document.getElementById('cartItems');
    const itemContainer = cartItemsDiv.children[index];
    if (!itemContainer) return;

    // Calculate new values
    const itemSubtotal = item.price * item.quantity;
    const discountAmount = item.discount_type === 'percentage'
        ? (itemSubtotal * item.discount / 100)
        : item.discount;
    const itemTotal = itemSubtotal - discountAmount;

    // Format quantity display
    let quantityDisplay = item.quantity + ' PCS';

    // Update quantity display
    const quantitySpan = itemContainer.querySelector('.text-red-600.font-bold');
    if (quantitySpan) {
        quantitySpan.textContent = quantityDisplay;
    }

    // Update price display
    const priceText = itemContainer.querySelector('.text-sm.font-semibold.text-gray-600:last-child');
    if (priceText) {
        priceText.innerHTML = `Price: Rs. ${item.price.toFixed(2)}/PCS | Total: Rs. ${itemSubtotal.toFixed(2)}`;
    }
}

function updateItemQuantity(index, value, unit) {
    // Use the stored unit unless explicit unit passed
    const item = cart[index];
    if (!item) return;
    if (!unit) unit = item.unit || 'pcs';

    let v = parseFloat(value);
    if (isNaN(v) || v < 0) v = 0;

    if (unit === 'pcs') {
        item.quantity = v;
        item.unit = 'pcs';
    }

    // Recalculate display quantity for calculator-added items
    if (item.fromCalculator) {
        item.displayQuantity = item.quantity + ' PCS';
    }

    updateCart();
     savePosState();
}

function updateItemUnit(index, newUnit) {
    const item = cart[index];
    if (!item) return;
    // Keep stored quantity the same; only change displayed unit
    if (newUnit !== 'pcs') {
        newUnit = 'pcs';
    }
    item.unit = newUnit;

    // Recalculate display quantity for calculator-added items
    if (item.fromCalculator) {
        item.displayQuantity = item.quantity + ' PCS';
    }

    updateCart();
}

function updateCart() {
    const cartDiv = document.getElementById('cartItems');
    if (cart.length === 0) {
        cartDiv.innerHTML = '<p class="text-sm text-gray-500 text-center">Cart is empty</p>';
        updateTotals();
        return;
    }

    cartDiv.innerHTML = cart.map((item, index) => {
        const itemSubtotal = item.price * item.quantity;
        const discountAmount = item.discount_type === 'percentage'
            ? (itemSubtotal * item.discount / 100)
            : item.discount;
        const itemTotal = itemSubtotal - discountAmount;
        const unit = item.unit || 'pcs';

        // Helper function to format quantity as "X PCS"
        function formatQuantityDisplay(quantity) {
            return quantity + ' PCS';
        }

        // Use combined format for calculator-added items, otherwise use default format
        let quantityDisplay;
        if (item.fromCalculator) {
            // Update display quantity if quantity changed
            if (!item.displayQuantity) {
                item.displayQuantity = formatQuantityDisplay(item.quantity);
            } else {
                // Recalculate display quantity based on current quantity
                item.displayQuantity = formatQuantityDisplay(item.quantity);
            }
            quantityDisplay = item.displayQuantity;
        } else {
            // Always format as pcs format
            quantityDisplay = `${item.quantity} PCS`;
        }

        // Determine price unit for display
        const priceUnit = 'PCS';

        return `
        <div class="p-4 bg-white border-2 border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow mb-3">
            <div class="flex items-center justify-between mb-3">
                <div class="flex-1">
                    <div class="text-base font-bold text-gray-900 mb-1">${item.name}</div>
                    <div class="text-sm font-semibold text-gray-600 mb-1">
                        📦 Quantity: <span class="text-red-600 font-bold">${quantityDisplay}</span>
                    </div>
                    <div class="text-sm font-semibold text-gray-600">
                        Price: Rs. ${item.price.toFixed(2)}/${priceUnit} | Total: Rs. ${itemSubtotal.toFixed(2)}
                    </div>
                </div>
                <button onclick="removeFromCart(${index})" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold text-lg transition-colors">×</button>
            </div>

            <!-- Quantity Input -->
            <div class="flex items-center gap-2 mb-3 bg-blue-50 p-2 rounded border-2 border-blue-200">
                <label class="text-xs font-bold text-gray-700">📦 Adjust Quantity:</label>
                    <div class="flex gap-2 flex-1">
                        <div class="flex-1">
                            <input type="number" step="1" min="1" id="cart_quantity_pcs_${index}" value="${item.quantity}"
                                   onchange="updateItemQuantityFromPcs(${index})"
                                   onblur="updateItemQuantityFromPcs(${index})"
                                   class="w-full px-2 py-1 text-sm border-2 border-blue-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-semibold"
                                   placeholder="PCS">
                        </div>
                    </div>
            </div>

            <!-- Discount Input -->
            <div class="flex items-center gap-2 bg-green-50 p-2 rounded">
                <span class="text-xs font-bold text-gray-700">Discount:</span>
                <input type="number" 
                       step="0.01" 
                       min="0"
                       value="${item.discount}" 
                       onchange="updateDiscount(${index}, this.value)"
                       placeholder="0.00"
                       class="w-20 px-2 py-1 text-xs border-2 border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <select onchange="updateDiscountType(${index}, this.value)" class="px-2 py-1 text-xs border-2 border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="rupee" ${item.discount_type === 'rupee' ? 'selected' : ''}>Rs.</option>
                    <option value="percentage" ${item.discount_type === 'percentage' ? 'selected' : ''}>%</option>
                </select>
            </div>
        </div>
    `}).join('');
    
    updateTotals();
}

function updateTotals() {
    let subtotal = 0;
    let totalDiscount = 0;

    cart.forEach(item => {
        const itemSubtotal = item.price * item.quantity;
        const discountAmount = item.discount_type === 'percentage' 
            ? (itemSubtotal * item.discount / 100) 
            : item.discount;
        subtotal += itemSubtotal;
        totalDiscount += discountAmount;
    });
    
    const discountPercent = subtotal > 0 ? (totalDiscount / subtotal) * 100 : 0;
    const tax = (subtotal - totalDiscount) * (taxRate / 100);
    let total = subtotal - totalDiscount + tax;
    
    // Apply rounding rules
    if (roundingRules === 'up') {
        total = Math.ceil(total);
    } else if (roundingRules === 'down') {
        total = Math.floor(total);
    } else if (roundingRules === 'nearest') {
        total = Math.round(total);
    }
    // 'none' - no rounding

    document.getElementById('subtotal').textContent = `Rs. ${subtotal.toFixed(2)}`;
    document.getElementById('discount').textContent = `Rs. ${totalDiscount.toFixed(2)}`;
    document.getElementById('tax').textContent = `Rs. ${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `Rs. ${total.toFixed(2)}`;
}

let selectedCustomer = null;
let customerCredits = [];

function togglePaymentFields() {
    const paymentMethod = document.getElementById('paymentMethod').value;
    const chequeFields = document.getElementById('chequeFields');
    const creditFields = document.getElementById('creditFields');
    const installmentFields = document.getElementById('installmentFields');

    chequeFields.style.display = 'none';
    creditFields.style.display = 'none';
    installmentFields.style.display = 'none';

    if (paymentMethod === 'cheque') {
        chequeFields.style.display = 'block';
    } else if (paymentMethod === 'credit') {
        if (!selectedCustomer) {
            alert('Please select a customer first for credit sales.');
            document.getElementById('paymentMethod').value = 'cash';
            return;
        }
        creditFields.style.display = 'block';
        loadCustomerCredits();
    } else if (paymentMethod === 'installment') {
        if (!selectedCustomer) {
            alert('Please select a customer first for installment sales.');
            document.getElementById('paymentMethod').value = 'cash';
            return;
        }
        installmentFields.style.display = 'block';
        calculateInstallment(); // Trigger calculation to populate fields
    }
}

// Customer search
const routePrefix = '{{ $routePrefix }}';
try {
    console.log('Setting up customer search event listener...');
    const customerSearchInput = document.getElementById('customerSearch');
    console.log('Customer search input element:', customerSearchInput);
    console.log('Customer search input value:', customerSearchInput ? customerSearchInput.value : 'null');
    if (customerSearchInput) {
        console.log('Attaching event listener to customer search input');
        let searchTimeout;

        customerSearchInput.addEventListener('input', function(e) {
            console.log('Input event fired on customer search');
            const query = e.target.value.trim();
            const resultsDiv = document.getElementById('customerResults');

            console.log('Customer search triggered, query:', query);
            console.log('Query length:', query.length);
            console.log('Query trimmed:', query);

            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Clear results if query is empty
            if (query.length === 0) {
                resultsDiv.classList.add('hidden');
                resultsDiv.innerHTML = '';
                return;
            }

            // Don't search for queries shorter than 2 characters
            if (query.length < 2) {
                resultsDiv.classList.add('hidden');
                resultsDiv.innerHTML = '';
                return;
            }

            // Show loading state
            resultsDiv.innerHTML = '<div class="p-4 text-base text-gray-600 font-semibold text-center">Searching customers...</div>';
            resultsDiv.classList.remove('hidden');

            // Debounce the search request
            searchTimeout = setTimeout(() => {
                // Build search URL using current origin + role prefix
                const searchUrl = `${window.location.origin}/${routePrefix}/customers/search?q=${encodeURIComponent(query)}`;

                console.log('Customer search URL:', searchUrl);

                fetch(searchUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('Customer search response received, status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                   const customers = data;
                    console.log('Customer search results:', data);
                    console.log('Customers array:', Array.isArray(customers));
                    console.log('Customers length:', customers ? customers.length : 0);

                    if (!Array.isArray(customers) || customers.length === 0) {
                        resultsDiv.innerHTML = '<div class="p-4 text-base text-gray-600 font-semibold text-center">No customers found</div>';
                    } else {
                        resultsDiv.innerHTML = customers.map(customer => {
                            const customerName = escapeHtml(customer.name || 'Unknown');
                            const customerPhone = customer.phone ? escapeHtml(customer.phone) : 'No phone';
                            const outstanding = parseFloat(customer.outstanding_balance || 0).toFixed(2);

                            const customerNic = customer.nic ? customer.nic.replace(/'/g, "\\'") : '';
                            const customerAddress = customer.address ? customer.address.replace(/'/g, "\\'").replace(/\n/g, ' ') : '';
                            const customerPhoneRaw = customer.phone ? customer.phone.replace(/'/g, "\\'") : '';

                            return `
                                <div class="p-4 hover:bg-blue-100 cursor-pointer border-b-2 border-gray-200 transition-colors" 
                                     onclick="selectCustomer(${customer.id}, '${customerName.replace(/'/g, "\\'")}', ${customer.outstanding_balance || 0}, '${customerNic}', '${customerPhoneRaw}', '${customerAddress}')">
                                    <div class="font-bold text-base text-gray-900 mb-1">${customerName}</div>
                                    <div class="text-sm text-gray-700">📱 ${customerPhone} | <span class="font-semibold text-red-600">Outstanding: Rs. ${outstanding}</span></div>
                                </div>
                            `;
                        }).join('');
                    }
                    resultsDiv.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Customer search error:', error);
                    // Only show error for actual network/server errors, not empty results
                    resultsDiv.innerHTML = '<div class="p-4 text-base text-red-600 font-semibold text-center">Error searching customers</div>';
                    resultsDiv.classList.remove('hidden');
                });
            }, 350); // 350ms debounce
        });
    } else {
        console.error('Customer search input element not found!');
    }
} catch (error) {
    console.error('Error setting up customer search:', error);
}

function selectCustomer(id, name, outstanding, nic = '', phone = '', address = '') {
    selectedCustomer = { id, name, outstanding, nic, phone, address };
     savePosState();
    const customerIdInput = document.getElementById('customerId');
    if (customerIdInput) customerIdInput.value = id;
    
    const customerSearchInput = document.getElementById('customerSearch');
    if (customerSearchInput) customerSearchInput.value = name;
    
    const customerResultsDiv = document.getElementById('customerResults');
    if (customerResultsDiv) customerResultsDiv.classList.add('hidden');
    
    const selectedCustomerDiv = document.getElementById('selectedCustomer');
    if (selectedCustomerDiv) selectedCustomerDiv.classList.remove('hidden');
    
    const selectedCustomerNameSpan = document.getElementById('selectedCustomerName');
    if (selectedCustomerNameSpan) selectedCustomerNameSpan.textContent = name;
    
    const customerCreditsInfoDiv = document.getElementById('customerCreditsInfo');
    if (customerCreditsInfoDiv) {
        const outstandingAmount = parseFloat(outstanding) || 0;
        customerCreditsInfoDiv.textContent = `Outstanding: Rs. ${outstandingAmount.toFixed(2)}`;
    }
    
    loadCustomerCredits();
}

function clearCustomer() {
    selectedCustomer = null;
     savePosState();
    document.getElementById('customerId').value = '';
    document.getElementById('customerSearch').value = '';
    document.getElementById('selectedCustomer').classList.add('hidden');
    document.getElementById('customerResults').classList.add('hidden');
    customerCredits = [];
    document.getElementById('creditsList').innerHTML = '';
    
    // Reset payment method if credit was selected
    if (document.getElementById('paymentMethod').value === 'credit') {
        document.getElementById('paymentMethod').value = 'cash';
        togglePaymentFields();
    }
}

function loadCustomerCredits() {
    if (!selectedCustomer) return;
    
    const creditsList = document.getElementById('creditsList');
    if (creditsList) {
        creditsList.innerHTML = '<div class="text-gray-600">Loading credits...</div>';
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    fetch(`${window.location.origin}/${routePrefix}/customers/credits?customer_id=${selectedCustomer.id}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load customer credits');
            return response.json();
        })
        .then(credits => {
            customerCredits = credits;
            if (!creditsList) return;

            if (!Array.isArray(credits) || credits.length === 0) {
                creditsList.innerHTML = '<div class="text-green-600">No outstanding credits</div>';
            } else {
                creditsList.innerHTML = credits.map(credit => {
                    const dueDate = new Date(credit.due_date);
                    const isOverdue = dueDate < new Date();
                    const outstandingAmount = parseFloat(credit.outstanding_amount) || 0;
                    return `
                        <div class="mb-2 p-2 ${isOverdue ? 'bg-red-200 border-2 border-red-400' : 'bg-yellow-200 border-2 border-yellow-400'} rounded-lg">
                            <div class="font-semibold text-sm">Invoice: ${credit.sale?.invoice_number || 'N/A'}</div>
                            <div class="text-sm">Amount: <span class="font-bold">Rs. ${outstandingAmount.toFixed(2)}</span></div>
                            <div class="text-sm">Due: <span class="font-bold">${dueDate.toLocaleDateString()}</span> 
                            ${isOverdue ? '<span class="ml-2 text-red-700 font-bold bg-red-300 px-2 py-1 rounded">OVERDUE</span>' : ''}</div>
                        </div>
                    `;
                }).join('');
            }
        })
        .catch(error => {
            console.error('Error loading credits:', error);
            if (creditsList) {
                creditsList.innerHTML = '<div class="text-red-600">Error loading credits</div>';
            }
        });
}

function completeSale() {
    if (cart.length === 0) {
        showToast('Cart is empty! Please add items to proceed.', 'error');
        return;
    }

    const paymentMethod = document.getElementById('paymentMethod').value;
    
    // Validate payment method is selected
    if (!paymentMethod || paymentMethod.trim() === '') {
        showToast('Please select a payment method before proceeding.', 'error');
        return;
    }
    
    if (paymentMethod === 'credit') {
        if (!selectedCustomer) {
            showToast('Please select a customer for credit sales.', 'error');
            return;
        }
        const creditDueDate = document.getElementById('creditDueDate').value;
        if (!creditDueDate) {
            showToast('Please enter the repay day (due date) for credit sales.', 'error');
            return;
        }
    }
    
    if (paymentMethod === 'cheque') {
        const chequeNumber = document.getElementById('chequeNumber').value;
        const bankName = document.getElementById('bankName').value;
        const chequeDate = document.getElementById('chequeDate').value;
        
        if (!chequeNumber || !bankName || !chequeDate) {
            showToast('Please fill in all cheque details (Cheque Number, Bank Name, and Cheque Date)', 'error');
            return;
        }
    }

    if (paymentMethod === 'installment') {
        const downPaymentAmount = document.getElementById('downPaymentAmount').value;
        const numberOfInstallments = document.getElementById('numberOfInstallments').value;
        const monthlyInstallmentAmount = document.getElementById('monthlyInstallmentAmount').value;
        const firstDueDate = document.getElementById('firstDueDate').value;
        const dueDayOfMonth = document.getElementById('dueDayOfMonth').value;

        if (!downPaymentAmount || !numberOfInstallments || !monthlyInstallmentAmount || !firstDueDate || !dueDayOfMonth) {
            showToast('Please fill in all installment details.', 'error');
            return;
        }
    }

    let subtotal = 0;
    let totalDiscount = 0;

    cart.forEach(item => {
        const itemSubtotal = item.price * item.quantity;
        const discountAmount = item.discount_type === 'percentage' 
            ? (itemSubtotal * item.discount / 100) 
            : item.discount;
        subtotal += itemSubtotal;
        totalDiscount += discountAmount;
    });
    
    const tax = (subtotal - totalDiscount) * (taxRate / 100);
    const total = subtotal - totalDiscount + tax;

    document.getElementById('itemsInput').value = JSON.stringify(cart);
    document.getElementById('subtotalInput').value = subtotal;
    document.getElementById('discountInput').value = totalDiscount;
    document.getElementById('taxInput').value = tax;
    document.getElementById('totalInput').value = total;
    document.getElementById('paymentMethodInput').value = paymentMethod;
    document.getElementById('customerIdInput').value = selectedCustomer ? selectedCustomer.id : '';
    document.getElementById('customerNameInput').value = selectedCustomer ? selectedCustomer.name : '';
    document.getElementById('customerNicInput').value = selectedCustomer ? selectedCustomer.nic : '';
    document.getElementById('customerPhoneInput').value = selectedCustomer ? selectedCustomer.phone : '';
    document.getElementById('customerAddressInput').value = selectedCustomer ? selectedCustomer.address : '';
    document.getElementById('creditDueDateInput').value = paymentMethod === 'credit' ? document.getElementById('creditDueDate').value : '';
    document.getElementById('chequeNumberInput').value = paymentMethod === 'cheque' ? document.getElementById('chequeNumber').value : '';
    document.getElementById('bankNameInput').value = paymentMethod === 'cheque' ? document.getElementById('bankName').value : '';
    document.getElementById('chequeDateInput').value = paymentMethod === 'cheque' ? document.getElementById('chequeDate').value : '';
    
    if (paymentMethod === 'installment') {
        document.getElementById('downPaymentAmountInput').value = document.getElementById('downPaymentAmount').value;
        document.getElementById('downPaymentMethodInput').value = document.getElementById('downPaymentMethod').value;
        document.getElementById('numberOfInstallmentsInput').value = document.getElementById('numberOfInstallments').value;
        document.getElementById('monthlyInstallmentAmountInput').value = document.getElementById('monthlyInstallmentAmount').value;
        document.getElementById('firstDueDateInput').value = document.getElementById('firstDueDate').value;
        document.getElementById('dueDayOfMonthInput').value = document.getElementById('dueDayOfMonth').value;
    }

    // Refresh CSRF token before submission
    const form = document.getElementById('saleForm');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        const tokenInput = form.querySelector('input[name="_token"]');
        if (tokenInput) {
            tokenInput.value = token;
        } else {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = '_token';
            hiddenInput.value = token;
            form.appendChild(hiddenInput);
        }
    }
    
    showToast('Processing sale... 💳', 'info'); 
       clearPosState();

    document.getElementById('saleForm').submit();
}

function holdBill() {
    if (cart.length === 0) {
        showToast('Cart is empty! Add items before holding a bill.', 'error');
        return;
    }

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity) - (item.discount || 0), 0);
    const discount = cart.reduce((sum, item) => sum + (item.discount || 0), 0);
    const tax = (subtotal - discount) * (taxRate / 100);
    const total = subtotal - discount + tax;

    showToast('⏳ Holding bill... Please wait.', 'info');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    fetch('{{ route($routePrefix . ".bills.hold") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            bill_data: cart,
            total_amount: total
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = [];
            updateCart();
               clearPosState();
            showToast('✅ Bill held successfully! You can resume it later.', 'success', 4000);
        } else {
            showToast(`Failed to hold bill: ${data.message || 'Unknown error'}`, 'error', 4000);
        }
    })
    .catch(error => {
        console.error('Error holding bill:', error);
        showToast('❌ Error holding bill. Please try again.', 'error', 4000);
    });
}

// Store initial items HTML for restoring when search is cleared
const itemsGrid = document.getElementById('itemsGrid');
const initialItemsHTML = itemsGrid.innerHTML;

// Helper function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}
// Initialize persistence on page load
document.addEventListener('DOMContentLoaded', () => {
    loadPosState();
    
    // Check if we need to resume a specific held bill from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('resume_held') === '1') {
        openHeldBillsModal();
    }
});

function openHeldBillsModal() {
    // If we have a modal for this, open it. Otherwise, show a list.
    // For now, let's just trigger a click on a "Held Bills" button if it exists,
    // or just fetch and show an alert/prompt.
    showToast('Resuming held bills...', 'info');
    
    fetch(`/${routePrefix}/bills/held`)
        .then(response => response.json())
        .then(bills => {
            if (bills.length === 0) {
                showToast('No held bills found.', 'warning');
                return;
            }
            
            // Create a simple overlay to pick a bill
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            overlay.id = 'heldBillsResumeModal';
            
            let billsHtml = bills.map(bill => `
                <div class="p-4 border-b hover:bg-gray-100 cursor-pointer flex justify-between items-center" onclick="resumeSpecificBill(${bill.id})">
                    <div>
                        <div class="font-bold">Total: Rs. ${parseFloat(bill.total_amount).toFixed(2)}</div>
                        <div class="text-xs text-gray-500">${new Date(bill.created_at).toLocaleString()}</div>
                    </div>
                    <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Resume</button>
                </div>
            `).join('');
            
            overlay.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                    <div class="bg-purple-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                        <h3 class="font-bold">📋 Resume Held Bill</h3>
                        <button onclick="document.getElementById('heldBillsResumeModal').remove()" class="text-2xl">&times;</button>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        ${billsHtml}
                    </div>
                    <div class="p-4 text-center">
                        <button onclick="document.getElementById('heldBillsResumeModal').remove()" class="text-gray-500 font-bold">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        });
}

window.resumeSpecificBill = function(id) {
    showToast('Loading bill...', 'info');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    
    fetch(`/${routePrefix}/bills/${id}/resume`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = data.bill_data;
            updateCart();
            savePosState();
            document.getElementById('heldBillsResumeModal')?.remove();
            showToast('✅ Bill resumed!', 'success');
        }
    });
}

// Helper function to render items
function renderItems(items) {
    const grid = document.getElementById('itemsGrid');
    
    // Ensure items is an array
    if (!Array.isArray(items)) {
        console.error('renderItems: items is not an array', items);
        items = [];
    }
    
    if (items.length === 0) {
        grid.innerHTML = '<div class="col-span-full text-center p-8 text-gray-500 font-semibold text-lg">No items found</div>';
        return;
    }
    
    try {
        grid.innerHTML = items.map(item => {
            // Safely handle all item properties
            const itemId = item.id || 0;
            const itemName = escapeHtml(String(item.name || 'Unknown Item'));
            const itemCode = escapeHtml(String(item.item_code || 'N/A'));
            const sellingPrice = parseFloat(item.selling_price || 0);
            const currentStock = parseFloat(item.current_stock || 0);
            const unitOfMeasure = escapeHtml(String(item.unit_of_measure || 'kg_g'));
            
            // Escape single quotes and double quotes in item name for onclick
            const safeItemName = itemName.replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
            
            const pricePerKg = unitOfMeasure === 'g' ? sellingPrice * 1000 : (unitOfMeasure === 'kg' ? sellingPrice : sellingPrice);
            // Format stock based on unit
            let stockDisplay = '';
            if (unitOfMeasure === 'pcs') {
                stockDisplay = currentStock + ' PCS';
            } else {
                stockDisplay = currentStock + ' ' + unitOfMeasure;
            }

            return `
                <button onclick="handleItemClick(event, ${itemId}, '${safeItemName}', ${sellingPrice}, ${currentStock}, '${unitOfMeasure}')"
                        class="p-5 border-2 border-gray-300 rounded-lg hover:border-red-500 hover:bg-red-50 hover:shadow-md transition-all text-left bg-white">
                    <div class="font-bold text-base text-gray-900 mb-1">${itemName}</div>
                    <div class="text-sm text-gray-600 mb-2">${itemCode}</div>
                    <div class="text-sm text-gray-700 mb-1">1 PCS: Rs. ${sellingPrice.toFixed(2)}</div>
                    <div class="text-lg font-bold text-red-600 mb-2">Rs. ${sellingPrice.toFixed(2)}</div>
                    <div class="text-sm font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded">Stock: ${stockDisplay}</div>
                </button>
            `;
        }).join('');
    } catch (error) {
        console.error('Error rendering items:', error, items);
        grid.innerHTML = '<div class="col-span-full text-center p-8 text-red-500 font-semibold text-lg">Error displaying items. Please refresh the page.</div>';
    }
}

// Barcode scanner support - auto-add item when barcode is scanned (usually ends with Enter)
let barcodeBuffer = '';
let barcodeTimer;
let isTyping = false;
let typingTimer;

document.getElementById('itemSearch').addEventListener('keydown', function(e) {
    // Reset typing flag when user presses a key
    isTyping = true;
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        isTyping = false;
    }, 500);
    
    // If Enter is pressed and we have a barcode-like string (usually 8+ digits, all numeric)
    // Only trigger if user hasn't been typing (barcode scanners send data very fast)
    if (e.key === 'Enter' && barcodeBuffer.length >= 8 && /^\d+$/.test(barcodeBuffer) && !isTyping) {
        e.preventDefault();
        // Search for item by barcode
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const encodedQuery = encodeURIComponent(barcodeBuffer);
        fetch(`{{ route($routePrefix . '.items.search') }}?q=${encodedQuery}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(items => {
            if (items.length > 0) {
                const item = items[0];
                addToCart(item.id, item.name, item.selling_price, item.current_stock, item.unit_of_measure || 'kg');
                this.value = '';
                barcodeBuffer = '';
            } else {
                alert('Item not found with barcode: ' + barcodeBuffer);
                this.value = '';
                barcodeBuffer = '';
            }
        })
        .catch(error => {
            console.error('Error searching for barcode:', error);
            alert('Error searching for item. Please try again.');
        });
        return;
    }
    
    // Track barcode input (fast typing - only for numeric sequences)
    if (e.key.length === 1 && /^\d$/.test(e.key)) {
        barcodeBuffer += e.key;
        clearTimeout(barcodeTimer);
        barcodeTimer = setTimeout(() => {
            barcodeBuffer = '';
        }, 200); // Reset if no input for 200ms
    } else {
        // Reset barcode buffer if non-numeric character is typed
        barcodeBuffer = '';
    }
});

// Search functionality - updates items as user types
let searchTimeout;
const itemSearchInput = document.getElementById('itemSearch');
if (itemSearchInput) {
    itemSearchInput.addEventListener('input', function(e) {
        const query = this.value.trim();
        const grid = document.getElementById('itemsGrid');
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // If query is empty, restore initial items immediately
        if (query.length === 0) {
            if (grid) {
                grid.innerHTML = initialItemsHTML;
            }
            return;
        }
        
        // Show searching state
        if (grid) {
            grid.innerHTML = '<div class="col-span-full text-center p-8 text-gray-500 font-semibold text-lg">Searching...</div>';
        }
        
        // Debounce search to avoid too many requests
        searchTimeout = setTimeout(() => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const searchUrl = `{{ route($routePrefix . '.items.search') }}?q=${encodeURIComponent(query)}`;
            
            fetch(searchUrl, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    return response.text().then(text => {
                        let errorMsg = `HTTP ${response.status}`;
                        try {
                            const json = JSON.parse(text);
                            errorMsg = json.message || json.error || errorMsg;
                        } catch (e) {
                            errorMsg = text.substring(0, 100) || errorMsg;
                        }
                        throw new Error(errorMsg);
                    });
                }
                // Parse JSON response
                return response.json();
            })
            .then(data => {
                // Handle error response from server
                if (data && data.error) {
                    throw new Error(data.message || data.error);
                }
                
                // Ensure data is an array (items)
                let items = Array.isArray(data) ? data : [];
                
                if (grid) {
                    renderItems(items);
                }
            })
            .catch(error => {
                console.error('Error searching items:', error);
                console.error('Search URL:', searchUrl);
                console.error('Query:', query);
                if (grid) {
                    // Show user-friendly error message
                    const errorMsg = error.message || 'Unknown error occurred';
                    grid.innerHTML = '<div class="col-span-full text-center p-8 text-red-500 font-semibold text-lg">Error: ' + escapeHtml(errorMsg.substring(0, 100)) + '</div>';
                }
            });
        }, 200);
    });
}

// Add Customer Modal
function openAddCustomerModal() {
    const modal = document.getElementById('addCustomerModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeAddCustomerModal() {
    const modal = document.getElementById('addCustomerModal');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('addCustomerForm').reset();
    }
}

function submitAddCustomer(event) {
    if (event) event.preventDefault();
    
    const form = document.getElementById('addCustomerForm');
    
    // Check validation
    if (!form.reportValidity()) {
        return;
    }

    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    if (!submitBtn) return;
    const originalText = submitBtn.innerHTML;
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⌛ Processing...';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const storeUrl = window.location.origin + '/' + routePrefix + '/customers';
    
    console.log('Storing customer to:', storeUrl);

    fetch(storeUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData,
        credentials: 'same-origin'
    })
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (e) {
            throw new Error('Server returned an invalid response. Please try again.');
        }

        if (response.ok && data.success) {
            return data;
        } else {
            throw new Error(data.message || 'Error adding customer');
        }
    })
    .then(data => {
        // Close modal
        closeAddCustomerModal();
        // Select the new customer
        if (data.customer) {
            const custId = data.customer.id;
            const custName = data.customer.name;
            const custOutstanding = data.customer.outstanding_balance || 0;
            const custNic = data.customer.nic || '';
            const custPhone = data.customer.phone || '';
            const custAddress = data.customer.address || '';
            selectCustomer(custId, custName, custOutstanding, custNic, custPhone, custAddress);
        }
        showToast('Customer added successfully!', 'success');
    })
    .catch(error => {
        console.error('Error adding customer:', error);
        showToast(error.message || 'Error adding customer', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('addCustomerModal');
    if (modal && event.target === modal) {
        closeAddCustomerModal();
    }
});

</script>

<!-- Add Customer Modal -->
<div id="addCustomerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 max-h-[90vh] flex flex-col">
        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
            <h3 class="text-lg font-bold">➕ Add New Customer</h3>
            <button type="button" onclick="closeAddCustomerModal()" class="text-white text-2xl font-bold hover:text-gray-200">×</button>
        </div>
        
        <form id="addCustomerForm" class="p-6 space-y-4 overflow-y-auto" onsubmit="submitAddCustomer(event)">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Full Name *</label>
                <input type="text" name="name" required class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="John Doe">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number *</label>
                <input type="tel" name="phone" required class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="071 405 6490">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">NIC Number *</label>
                <input type="text" name="nic" required class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="123456789V">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Email (Optional)</label>
                <input type="email" name="email" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="customer@example.com">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Address (Optional)</label>
                <textarea name="address" rows="2" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Street address"></textarea>
            </div>

            <hr class="border-gray-200 my-4">
            <h4 class="font-bold text-blue-800 mb-2">Guarantor Information</h4>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Guarantor Name</label>
                    <input type="text" name="guarantor_name" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Guarantor Name">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Guarantor NIC</label>
                    <input type="text" name="guarantor_nic" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Guarantor NIC">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Guarantor Mobile</label>
                    <input type="tel" name="guarantor_mobile_number" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Guarantor Mobile">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Guarantor Address</label>
                <textarea name="guarantor_address" rows="2" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Guarantor Address"></textarea>
            </div>
            
            <div class="flex gap-3 pt-4 sticky bottom-0 bg-white">
                <button type="button" onclick="closeAddCustomerModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg font-semibold hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Add Customer
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
