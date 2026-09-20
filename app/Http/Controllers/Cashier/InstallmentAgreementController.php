<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InstallmentAgreement;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\InstallmentPayment;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Services\SmsService;

class InstallmentAgreementController extends Controller
{

    public function addComment(Request $request)
    {
        $request->validate([
            'installment_agreement_id' => 'required|exists:installment_agreements,id',
            'payment_date' => 'required|date',
            'comment' => 'nullable|string|max:255',
        ]);

        $payment = InstallmentPayment::where('installment_agreement_id', $request->installment_agreement_id)
            ->whereDate('payment_date', $request->payment_date)
            ->first();

        if ($payment) {
            $payment->update([
                'notes' => $request->comment,
            ]);
        }

        return back();
    }


    public function show($id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            $agreement = InstallmentAgreement::find($id);
            if ($agreement) {
                $sale = $agreement->sale;
            }
        }

        if (!$sale || $sale->payment_method !== 'installment') {
            abort(404, 'Installment agreement not found.');
        }
        $sale->load('customer', 'items.item', 'installmentAgreement');
        return view('admin.installments.proposal-agreement', compact('sale'));
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|string',
            'subtotal' => 'required|numeric',
            'discount_amount' => 'required|numeric',
            'tax_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'down_payment_amount' => 'required|numeric',
            'number_of_installments' => 'required|integer',
            'monthly_installment_amount' => 'required|numeric',
            'first_due_date' => 'required|date',
            'due_day_of_month' => 'required|integer',
        ]);

        $sale = new Sale([
            'subtotal' => $validated['subtotal'],
            'discount_amount' => $validated['discount_amount'],
            'tax_amount' => $validated['tax_amount'],
            'total_amount' => $validated['total_amount'],
            'payment_method' => 'installment',
            'created_at' => now(),
        ]);

        $customer = Customer::find($validated['customer_id']);
        $sale->setRelation('customer', $customer);

        $itemsData = json_decode($validated['items'], true);
        $saleItems = new EloquentCollection();
        foreach ($itemsData as $itemData) {
            $item = Item::find($itemData['item_id']);
            if ($item) {
                $saleItem = new SaleItem([
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['price'],
                    'total_price' => $itemData['price'] * $itemData['quantity'],
                ]);
                $saleItem->setRelation('item', $item);
                $saleItems->add($saleItem);
            }
        }
        $sale->setRelation('items', $saleItems);

        $agreement = new InstallmentAgreement([
            'total_invoice_value' => $sale->total_amount,
            'down_payment_amount' => $validated['down_payment_amount'],
            'balance_amount' => $sale->total_amount - $validated['down_payment_amount'],
            'number_of_installments' => $validated['number_of_installments'],
            'monthly_installment_amount' => $validated['monthly_installment_amount'],
            'first_due_date' => $validated['first_due_date'],
            'due_day_of_month' => $validated['due_day_of_month'],
        ]);
        $sale->setRelation('installmentAgreement', $agreement);

        return view('admin.installments.proposal-agreement', compact('sale'));
    }

    public function download($id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            $agreement = InstallmentAgreement::find($id);
            if ($agreement) {
                $sale = $agreement->sale;
            }
        }

        if (!$sale || $sale->payment_method !== 'installment') {
            abort(404, 'Installment agreement not found.');
        }

        $sale->load('customer', 'items.item', 'installmentAgreement');

        $pdf = Pdf::loadView('admin.installments.proposal-agreement', compact('sale'));
        
        return $pdf->stream('installment-agreement-' . $sale->invoice_number . '.pdf');
    }

    public function edit($id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            $agreement = InstallmentAgreement::find($id);
            if ($agreement) {
                $sale = $agreement->sale;
            }
        }

        if (!$sale || $sale->payment_method !== 'installment') {
            abort(404, 'Installment agreement not found.');
        }

        $sale->load('customer', 'items.item', 'installmentAgreement');
        return view('cashier.installment_agreement_edit', compact('sale'));
    }

    public function sendOtp(Request $request, $id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            $agreement = InstallmentAgreement::find($id);
            if ($agreement) {
                $sale = $agreement->sale;
            }
        }

        if (!$sale || !$sale->installmentAgreement) {
            return response()->json([
                'success' => false,
                'message' => 'Agreement not found'
            ], 404);
        }

        // 🔐 ALWAYS use customer phone (SECURITY FIX)
        $phone = $sale->customer->phone;
        set_time_limit(120);
        // Generate OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // ✅ FIX: Reset attempts when sending new OTP
        $sale->installmentAgreement->update([
            'otp_code' => $otp,
            'otp_sent_at' => now(),
            'otp_verified_at' => null,
            'otp_attempts' => 0 // 🔥 THIS LINE FIXES YOUR ISSUE
        ]);

        // SMS service
        $smsService = app(\App\Services\SmsService::class);

        $message = "Your OTP for Installment Agreement is: $otp. Valid for 5 minutes.";

        $response = $smsService->sendRawSms(
            $phone,
            $message,
            'otp'
        );

        // Success response
        if (!empty($response['success'])) {
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully'
            ]);
        }

        // Failure response
        return response()->json([
            'success' => false,
            'message' => 'Failed to send OTP',
            'error' => $response['error'] ?? $response['raw'] ?? 'Unknown error'
        ], 500);
    }


    public function verifyOtp(Request $request, $id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            $agreement = InstallmentAgreement::find($id);
            if ($agreement) {
                $sale = $agreement->sale;
            }
        }

        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found'], 404);
        }

      // Trim OTP to prevent whitespace errors
        $request->merge(['otp' => trim($request->otp)]);

        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $agreement = $sale->installmentAgreement;

        if (!$agreement) {
            return response()->json([
                'success' => false,
                'message' => 'Agreement not found'
            ], 404);
        }

        // ⛔ Already verified
        if ($agreement->otp_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'OTP already verified'
            ], 422);
        }

        // ⛔ OTP exists?
        if (!$agreement->otp_code) {
            return response()->json([
                'success' => false,
                'message' => 'OTP not found. Please request again.'
            ], 422);
        }

        // ⛔ OTP expired?
        if (now()->diffInMinutes($agreement->otp_sent_at) > 5) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please request new OTP.'
            ], 422);
        }

        // ⛔ Too many attempts
        if ($agreement->otp_attempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Too many attempts. Request new OTP.'
            ], 429);
        }

        // ❌ Wrong OTP
        if ($agreement->otp_code !== $request->otp) {

            $agreement->increment('otp_attempts');

            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 422);
        }

        // ✅ SUCCESS
        $agreement->update([
            'otp_verified_at' => now(),
            'otp_code' => null,
            'otp_attempts' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully'
        ]);
    }


    public function update(Request $request, $id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            $agreement = InstallmentAgreement::find($id);
            if ($agreement) {
                $sale = $agreement->sale;
            }
        }

        if (!$sale || $sale->payment_method !== 'installment' || !$sale->installmentAgreement) {
            abort(404, 'Installment agreement not found.');
        }

        $validated = $request->validate([
      
            'customer_name'           => 'required|string|max:255',
            'customer_phone'          => 'required|string|max:255',
            'customer_nic'            => 'required|string|max:255',
            'customer_address'         => 'nullable|string',
            'customer_age'            => 'nullable|string',
            'customer_occupation'     => 'nullable|string',
            'customer_institute_name_address' => 'nullable|string',
            'customer_monthly_salary' => 'nullable|string',
            'customer_bank_branch'    => 'nullable|string',

            'guarantor_name'          => 'required|string|max:255',
            'guarantor_nic'           => 'required|string|max:255',
            'guarantor_address'       => 'required|string|max:255',
            'guarantor_mobile_number' => 'required|string|max:255',
            'guarantor_1_occupation'  => 'nullable|string',
            'guarantor_1_monthly_income' => 'nullable|string',
            'guarantor_1_bank_branch' => 'nullable|string',

            // Financial fields
            'total_invoice_value'     => 'required|numeric|min:0',
            'down_payment_amount'     => 'required|numeric|min:0',
            'down_payment_method'     => 'nullable|string',
            'monthly_installment_amount' => 'required|numeric|min:0',
            'number_of_installments'  => 'required|integer|min:1',
            'interest_service_charge' => 'nullable|numeric|min:0',
            'loan_tenor'              => 'nullable|integer|min:1',
            'first_due_date'          => 'required|date',
            'due_day_of_month'        => 'required|integer|min:1|max:31',
            'down_payment_date'       => 'nullable|date',
        ]);
    
        // Update the sale total to match the agreement
        $sale->update([
            'total_amount' => $validated['total_invoice_value'],
        ]);

        // Update the installment agreement with all details
        $sale->installmentAgreement->update([
            
            'customer_age'            => $validated['customer_age'] ?? null,
            'customer_occupation'     => $validated['customer_occupation'] ?? null,
            'customer_institute_name_address' => $validated['customer_institute_name_address'] ?? null,
            'customer_monthly_salary' => $validated['customer_monthly_salary'] ?? null,
            'customer_bank_branch'    => $validated['customer_bank_branch'] ?? null,

            'guarantor_name'          => $validated['guarantor_name'],
            'guarantor_nic'           => $validated['guarantor_nic'],
            'guarantor_address'       => $validated['guarantor_address'],
            'guarantor_mobile_number' => $validated['guarantor_mobile_number'],
            'guarantor_1_occupation'  => $validated['guarantor_1_occupation'] ?? null,
            'guarantor_1_monthly_income' => $validated['guarantor_1_monthly_income'] ?? null,
            'guarantor_1_bank_branch' => $validated['guarantor_1_bank_branch'] ?? null,

         

            // Financial fields
            'total_invoice_value'     => $validated['total_invoice_value'],
            'down_payment_amount'     => $validated['down_payment_amount'],
            'down_payment_method'     => $validated['down_payment_method'] ?? 'cash',
            'monthly_installment_amount' => $validated['monthly_installment_amount'],
            'number_of_installments'  => $validated['number_of_installments'],
            'interest_service_charge' => $validated['interest_service_charge'] ?? 0,
            'loan_tenor'              => $validated['loan_tenor'] ?? $validated['number_of_installments'],
            'first_due_date'          => $validated['first_due_date'],
            'due_day_of_month'        => $validated['due_day_of_month'],
            'down_payment_date'       => $validated['down_payment_date'] ?? $sale->installmentAgreement->down_payment_date,
            'balance_amount'          => round($validated['total_invoice_value'] - $validated['down_payment_amount'] - $sale->installmentAgreement->payments->sum('amount'), 2),
            'is_finalized'            => true,
        ]);
    
        // Update the customer's details and sync everything
        if ($sale->customer) {
            $sale->customer->update([
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
                'nic' => $validated['customer_nic'],
                'address' => $validated['customer_address'] ?? null,

                'customer_age'            => $validated['customer_age'] ?? null,
                'customer_occupation'     => $validated['customer_occupation'] ?? null,
                'customer_institute_name_address' => $validated['customer_institute_name_address'] ?? null,
                'customer_monthly_salary' => $validated['customer_monthly_salary'] ?? null,
                'customer_bank_branch'    => $validated['customer_bank_branch'] ?? null,

                'guarantor_name'          => $validated['guarantor_name'],
                'guarantor_nic'           => $validated['guarantor_nic'],
                'guarantor_mobile_number' => $validated['guarantor_mobile_number'],
                'guarantor_address'       => $validated['guarantor_address'],
                
                'guarantor_1_occupation'  => $validated['guarantor_1_occupation'] ?? null,
                'guarantor_1_monthly_income' => $validated['guarantor_1_monthly_income'] ?? null,
                'guarantor_1_bank_branch' => $validated['guarantor_1_bank_branch'] ?? null,

            ]);
        }

        $routePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';

        \App\Models\AuditLog::log('update_agreement', "Updated installment agreement for sale: {$sale->invoice_number}", $sale->installmentAgreement);

        return redirect()->route($routePrefix . '.installment-agreement.show', $sale->id)
            ->with('success', 'Installment agreement updated successfully.');
    }

    
    // 👇 ADD THIS METHOD
    public function updateNicFiles(Request $request, InstallmentAgreement $agreement)
    {
        $request->validate([
            'customer_nic_front'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'customer_nic_back'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'guarantor_nic_front'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'guarantor_nic_back'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Customer NIC - Front
        if ($request->hasFile('customer_nic_front')) {
            $agreement->customer_nic_front = $request
                ->file('customer_nic_front')
                ->store('nic-files', 'public');
        }

        // Customer NIC - Back
        if ($request->hasFile('customer_nic_back')) {
            $agreement->customer_nic_back = $request
                ->file('customer_nic_back')
                ->store('nic-files', 'public');
        }

        // Guarantor NIC - Front
        if ($request->hasFile('guarantor_nic_front')) {
            $agreement->guarantor_nic_front = $request
                ->file('guarantor_nic_front')
                ->store('nic-files', 'public');
        }

        // Guarantor NIC - Back
        if ($request->hasFile('guarantor_nic_back')) {
            $agreement->guarantor_nic_back = $request
                ->file('guarantor_nic_back')
                ->store('nic-files', 'public');
        }

        $agreement->save();

        return back()->with('success', 'NIC files uploaded successfully.');
    }

    public function printAgreement($id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            $agreement = InstallmentAgreement::find($id);
            if ($agreement) {
                $sale = $agreement->sale;
            }
        }

        if (!$sale || !$sale->installmentAgreement) {
            abort(404, 'Installment agreement not found');
        }

        $sale->load(['customer', 'items.item', 'installmentAgreement']);
        return view('admin.installments.proposal-agreement', compact('sale'));
    }

    public function printApplicationForm($id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            $agreement = InstallmentAgreement::find($id);
            if ($agreement) {
                $sale = $agreement->sale;
            }
        }

        if (!$sale || !$sale->installmentAgreement) {
            abort(404, 'Installment agreement not found');
        }

        $sale->load(['customer', 'items.item', 'installmentAgreement']);
        return view('admin.installments.application-form', compact('sale'));
    }

    public function printGuaranteeBond($id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            $agreement = InstallmentAgreement::find($id);
            if ($agreement) {
                $sale = $agreement->sale;
            }
        }

        if (!$sale) {
            abort(404);
        }

        $sale->load(['customer', 'items.item', 'installmentAgreement']);
        return view('admin.installments.proposal-agreement', compact('sale'));
    }

}
