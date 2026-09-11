@extends('layouts.app')

@section('title', 'Installment Agreement Details')

@php
    $banks = [
        'Amana Bank PLC', 'Bank of Ceylon', 'Bank of China Ltd.', 'Cargills Bank PLC', 'Citibank, N.A.',
        'Commercial Bank of Ceylon PLC', 'Deutsche Bank AG (Colombo Branch)', 'DFCC Bank PLC', 'Habib Bank Ltd.',
        'Hatton National Bank PLC', 'Indian Bank', 'Indian Overseas Bank', 'MCB Bank Ltd', 'National Development Bank PLC',
        'Nations Trust Bank PLC', 'Pan Asia Banking Corporation PLC', 'People\'s Bank', 'Public Bank Berhad (Colombo Branch)',
        'Sampath Bank PLC', 'Seylan Bank PLC', 'Standard Chartered Bank', 'State Bank of India (Colombo Branch)',
        'Union Bank of Colombo PLC'
    ];
@endphp

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Installment Agreement Details</h1>
            <p class="mt-2 text-sm text-gray-700">Details for agreement with invoice #{{ $agreement->sale->invoice_number }}.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex gap-2">
            @php
                $rolePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';
            @endphp
            <a href="{{ route($rolePrefix . '.installment-agreement.edit', $agreement->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Edit Agreement
            </a>
             @if(!$agreement->unlocked_at && $agreement->disconnected_at)
                <form action="{{ route('admin.installments.unlock', $agreement) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" 
                        @if($agreement->remaining_fine > 0 || $agreement->balance_amount > 0) 
                            onclick="return confirm('Remaining delay charge of Rs. {{ number_format($agreement->remaining_fine, 2) }} and balance of Rs. {{ number_format($agreement->balance_amount, 2) }} must be fully paid. Continue anyway?')"
                        @endif>Unlock Device</button>
                </form>
            @elseif(!$agreement->disconnected_at && $agreement->delay_days > 0)
                <form action="{{ route('admin.installments.disconnect', $agreement) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">Disconnect Device</button>
                </form>
            @endif
        </div>
    </div>
    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Agreement Information</h3>
            @php
                $statusLabel = $agreement->status_label;
                $statusClass = match($statusLabel) {
                    'Paid Off' => 'bg-blue-100 text-blue-800',
                    'Unlocked' => 'bg-green-100 text-green-800',
                    'Disconnected' => 'bg-red-100 text-red-800',
                    default => 'bg-green-100 text-green-800',
                };
            @endphp
            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                Status: {{ $statusLabel }}
            </span>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
            <dl class="sm:divide-y sm:divide-gray-200">
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Customer</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $agreement->customer->name }}</dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Total Invoice Value</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ number_format($agreement->total_invoice_value, 2) }}</dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Balance Amount</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ number_format($agreement->balance_amount, 2) }}</dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Next Due Date</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ is_string($agreement->next_due_date) ? $agreement->next_due_date : $agreement->next_due_date->format('Y-m-d') }}</dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-yellow-50">
                    <dt class="text-sm font-medium text-gray-900">Total Delay Charge</dt>
                    <dd class="mt-1 text-sm text-red-600 font-bold sm:mt-0 sm:col-span-2">
                        Rs. {{ number_format($agreement->calculated_fine, 2) }} 
                        <span class="text-xs font-normal text-gray-500">({{ $agreement->delay_days }} days delay)</span>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-yellow-50">
                    <dt class="text-sm font-medium text-gray-900">Remaining Delay Charge</dt>
                    <dd class="mt-1 text-sm text-red-700 font-extrabold sm:mt-0 sm:col-span-2">Rs. {{ number_format($agreement->remaining_fine, 2) }}</dd>
                </div>
            </dl>
        </div>
    </div>
   <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
    <div>
        <h2 class="text-lg font-medium text-gray-900">Record Installment Payment</h2>
        <form action="{{ route('admin.installments.payments.store', $agreement) }}" method="POST" class="mt-4 bg-white shadow sm:rounded-lg p-6">
            @csrf
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700">Installment Amount</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0" max="{{ $agreement->balance_amount }}" value="{{ min($agreement->balance_amount, $agreement->monthly_installment_amount) }}" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div class="sm:col-span-3">
                    <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div class="sm:col-span-3">
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <select id="payment_method" name="payment_method" onchange="toggleChequeFields('cheque_details', this)" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                        <option>Cash</option>
                        <option>Card</option>
                        <option>Online</option>
                        <option>Cheque</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="sm:col-span-6" id="cheque_details" style="display: none;">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="cheque_number" class="block text-sm font-medium text-gray-700">Cheque Number</label>
                            <input type="text" name="cheque_number" id="cheque_number" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                            <select id="bank_name" name="bank_name" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                                @foreach($banks as $bank)
                                    <option>{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="cheque_date" class="block text-sm font-medium text-gray-700">Cheque Date</label>
                            <input type="date" name="cheque_date" id="cheque_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-x-3">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Record Payment
                </button>
            </div>
        </form>
    </div>

    <div>
        <h2 class="text-lg font-medium text-gray-900">Record Delay Payment</h2>
        <form action="{{ route('admin.installments.delay-payments.store', $agreement) }}" method="POST" class="mt-4 bg-white shadow sm:rounded-lg p-6">
            @csrf
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="delay_fine_amount" class="block text-sm font-medium text-gray-700">Delay Charge to Pay</label>
                    <input type="number" name="fine_amount" id="delay_fine_amount" step="0.01" min="0.01" value="{{ $agreement->remaining_fine }}" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-yellow-50">
                </div>
                <div class="sm:col-span-3">
                    <label for="delay_payment_date" class="block text-sm font-medium text-gray-700">Payment Date</label>
                    <input type="date" name="payment_date" id="delay_payment_date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div class="sm:col-span-3">
                    <label for="delay_payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <select id="delay_payment_method" name="payment_method" onchange="toggleChequeFields('delay_cheque_details', this)" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                        <option>Cash</option>
                        <option>Card</option>
                        <option>Online</option>
                        <option>Cheque</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="sm:col-span-6" id="delay_cheque_details" style="display: none;">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="delay_cheque_number" class="block text-sm font-medium text-gray-700">Cheque Number</label>
                            <input type="text" name="cheque_number" id="delay_cheque_number" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="delay_bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                            <select id="delay_bank_name" name="bank_name" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                                @foreach($banks as $bank)
                                    <option>{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="delay_cheque_date" class="block text-sm font-medium text-gray-700">Cheque Date</label>
                            <input type="date" name="cheque_date" id="delay_cheque_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-6">
                    <label for="delay_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="delay_notes" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-x-3">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Record Delay Payment
                </button>
            </div>
        </form>
    </div>
</div>

@if($agreement->payments->count() > 0)
<div class="mt-4 flex justify-end gap-x-3">
    @php $lastPayment = $agreement->payments->sortByDesc('created_at')->first(); @endphp
    <a href="{{ route('admin.installments.payments.receipt', $lastPayment) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
        🖨️ Last Receipt (POS)
    </a>
    <a href="{{ route('admin.installments.payments.disconnect-receipt', $lastPayment) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
        🖨️ Last Receipt (Disconnect)
    </a>
</div>
@endif

    {{-- PAYMENT HISTORY --}}
    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Payment History</h3>
        </div>
        <div class="border-t border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Installment</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fine Paid</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($agreement->payments->sortByDesc('payment_date') as $payment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->payment_date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Rs. {{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">Rs. {{ number_format($payment->fine_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">Rs. {{ number_format($payment->amount + $payment->fine_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($payment->payment_method) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->notes ?: '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('admin.installments.payments.receipt', $payment) }}" target="_blank" class="text-red-600 hover:text-red-900" title="Small POS Receipt">Print POS</a>
                                    <a href="{{ route('admin.installments.payments.disconnect-receipt', $payment) }}" target="_blank" class="text-blue-600 hover:text-blue-900" title="Disconnect Receipt">Print Disconnect</a>
                                    <button type="button" class="text-orange-600 hover:text-orange-900 ml-2" title="Delete Payment" onclick="deletePayment({{ $payment->id }})">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">No payments recorded yet.</td>
                        </tr>
                    @endforelse
                    
                    {{-- Summary rows --}}
                    <tr class="bg-gray-50 font-bold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total Payments</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($agreement->payments->sum('amount'), 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">Rs. {{ number_format($agreement->payments->sum('fine_amount'), 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">Rs. {{ number_format($agreement->payments->sum('amount') + $agreement->payments->sum('fine_amount'), 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Down Payment ({{ $agreement->down_payment_date }})</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($agreement->down_payment_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" colspan="2">{{ ucfirst($agreement->down_payment_method) }}</td>
                        <td colspan="3"></td>
                    </tr>
                    <tr class="bg-blue-50 font-extrabold text-blue-900">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">Total Amount Collected</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">Rs. {{ number_format($agreement->payments->sum('amount') + $agreement->down_payment_amount + $agreement->payments->sum('fine_amount'), 2) }}</td>
                        <td colspan="5"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
function toggleChequeFields(divId, selectObj) {
    var paymentMethod = selectObj.value;
    var chequeDetails = document.getElementById(divId);
    if (paymentMethod === 'Cheque') {
        chequeDetails.style.display = 'block';
    } else {
        chequeDetails.style.display = 'none';
    }
}
</script>

@push('scripts')
<script>
function deletePayment(paymentId) {
    const password = prompt('Please enter password to delete payment:');
    if (password === '876') {
        if (confirm('Are you sure you want to delete this payment record?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/installments/payments/${paymentId}`;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            
            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = password;
            
            form.appendChild(methodInput);
            form.appendChild(csrfInput);
            form.appendChild(passwordInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    } else if (password !== null) {
        alert('Incorrect password!');
    }
}
</script>
@endpush

@endsection
