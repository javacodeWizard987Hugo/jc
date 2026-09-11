@extends('layouts.app')

@section('title', 'Customer Installment History')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Installment History for {{ $customer->name }} (NIC: {{ $customer->nic }})</h3>
        </div>
        <div class="card-body">
            @if($agreements->isEmpty())
                <div class="alert alert-info">
                    No installment agreements found for this customer.
                </div>
            @else
                @foreach($agreements as $agreement)
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                Agreement #{{ $agreement->id }} (Invoice: {{ $agreement->sale->id }}) - 
                                <span class="badge badge-light">{{ ucfirst(str_replace('_', ' ', $agreement->status)) }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Agreement Date:</strong> {{ $agreement->created_at->format('Y-m-d') }}<br>
                                    <strong>Total Amount:</strong> {{ number_format($agreement->total_invoice_value, 2) }}<br>
                                    <strong>Down Payment:</strong> {{ number_format($agreement->down_payment_amount, 2) }}<br>
                                    <strong>Balance:</strong> {{ number_format($agreement->balance_amount, 2) }}<br>
                                    <strong>Installments:</strong> {{ $agreement->number_of_installments }} x {{ number_format($agreement->monthly_installment_amount, 2) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Items:</strong>
                                    <ul>
                                        @foreach($agreement->sale->items as $saleItem)
                                            <li>{{ $saleItem->item->name }} (Qty: {{ $saleItem->quantity }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <hr>

                            <h6>Payment History</h6>
                            @if($agreement->payments->isEmpty())
                                <p>No payments recorded for this agreement.</p>
                            @else
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Payment Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Notes</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $dueDate = \Carbon\Carbon::parse($agreement->first_due_date);
                                        @endphp
                                        @foreach($agreement->payments->sortBy('payment_date') as $payment)
                                            @php
                                                $paymentDate = \Carbon\Carbon::parse($payment->payment_date);
                                                $status = '';
                                                if ($paymentDate->lte($dueDate)) {
                                                    $status = '<span class="badge badge-success">On Time</span>';
                                                } else {
                                                    $status = '<span class="badge badge-danger">Late</span>';
                                                }
                                                if ($payment->amount < $agreement->monthly_installment_amount) {
                                                    $status .= ' <span class="badge badge-warning">Partial</span>';
                                                }
                                                // This is a simplified logic for due date progression
                                                $dueDate->addMonth();
                                            @endphp
                                            <tr>
                                                <td>{{ $payment->payment_date }}</td>
                                                <td>{{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->payment_method }}</td>
                                                <td>{{ $payment->notes }}</td>
                                                <td>{!! $status !!}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-center">
                    {{ $agreements->links() }}
                </div>
            @endif

            <a href="{{ route('admin.customer-history.index') }}" class="btn btn-secondary mt-3">Back to Search</a>
        </div>
    </div>
</div>
@endsection
