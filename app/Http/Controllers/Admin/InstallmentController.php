<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstallmentAgreement;
use App\Models\InstallmentPayment;
use Illuminate\Http\Request;
use App\Models\Sale;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class InstallmentController extends Controller
{
    
      public function index(Request $request)
    {
        $search = $request->get('search');
        $filter = $request->get('filter');
        $query = InstallmentAgreement::with(['sale.items.item', 'customer', 'payments'])
            ->where('is_finalized', true);

        if ($search) {
            $query->search($search);
        }

        if ($filter === 'repeated') {
            $query->where(function ($q) {
                $q->whereIn('customer_id', function ($sub) {
                    $sub->select('customer_id')
                        ->from('installment_agreements')
                        ->groupBy('customer_id')
                        ->havingRaw('COUNT(*) > 1');
                })->orWhereIn('emi_number', function ($sub) {
                    $sub->select('emi_number')
                        ->from('installment_agreements')
                        ->whereNotNull('emi_number')
                        ->where('emi_number', '!=', '')
                        ->groupBy('emi_number')
                        ->havingRaw('COUNT(*) > 1');
                });
            });
        }

        $agreements = $query->latest()->paginate(20);
        $paidOffCount = InstallmentAgreement::where('status', 'paid_off')->count();

        $today = Carbon::today();

        $downPayments = InstallmentAgreement::whereDate('down_payment_date', $today)
            ->selectRaw('down_payment_method as method, SUM(down_payment_amount) as total')
            ->groupBy('down_payment_method')
            ->get();

        $installmentPayments = InstallmentPayment::whereDate('payment_date', $today)
            ->selectRaw('payment_method as method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        $dailyIncome = $downPayments->concat($installmentPayments)
            ->groupBy('method')
            ->map(function ($items) {
                return $items->sum('total');
            });

        return view('admin.installments.index', compact('agreements', 'dailyIncome', 'paidOffCount'));
    }
    
    public function show(InstallmentAgreement $agreement)
    {
        $agreement->load(['sale.items.item', 'customer', 'payments']);
        return view('admin.installments.show', compact('agreement'));
    }

   public function storePayment(
        Request $request,
        InstallmentAgreement $agreement,
        \App\Services\SmsService $smsService
    ) {
        // 1️⃣ Validate payment
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $agreement->balance_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'cheque_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'cheque_date' => 'nullable|date',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['fine_amount'] = 0; // Explicitly 0 for installment payments

        // 2️⃣ Save installment payment
        $agreement->payments()->create($validated);

        // 3️⃣ Update balance
        $newBalance = $agreement->balance_amount - $validated['amount'];

        $agreement->update([
            'balance_amount' => $newBalance,
            'loan_tenor' => $agreement->loan_tenor ?? $agreement->total_installments,
        ]);

        // 4️⃣ Mark as paid if completed
        if ($newBalance <= 0) {
            $agreement->update([
                'status' => 'paid_off',
                'number_of_installments' => 0,
                'paid_towards_installment' => 0,
            ]);

            $smsService->sendSms(
                $agreement->customer->phone,
                'payment_completed_notification',
                [
                    'CustomerName'  => $agreement->customer->name,
                    'CompletedDate' => now()->format('Y-m-d'),
                ]
            );

            return redirect()
                ->route('admin.installments.show', $agreement)
                ->with('success', 'Payment recorded successfully. Agreement is now fully paid off.');
        }

        // 5️⃣ Update installment tracking
        $totalPaidTowardsInstallment = round(
            (float)$agreement->paid_towards_installment + (float)$validated['amount'], 2
        );

        $monthlyAmount = round((float)$agreement->monthly_installment_amount, 2);

        $fullInstallmentsPaid = floor(round(
            $totalPaidTowardsInstallment / $monthlyAmount, 5
        ));

        if ($fullInstallmentsPaid > 0) {
            $agreement->decrement(
                'number_of_installments',
                (int) $fullInstallmentsPaid
            );

            $agreement->paid_towards_installment = round(
                $totalPaidTowardsInstallment - ($fullInstallmentsPaid * $monthlyAmount), 2
            );

            $agreement->save();
        } else {
            $agreement->paid_towards_installment = $totalPaidTowardsInstallment;
            $agreement->save();
        }

        // 6️⃣ ✅ SEND SMS AUTOMATICALLY
        $smsService->sendSms(
            $agreement->customer->phone,
            'installment_payment_received',
            [
                'CustomerName' => $agreement->customer->name,
                'Amount' => number_format($validated['amount'], 2),
                'Date' => $validated['payment_date'],
                'Balance' => number_format($agreement->balance_amount, 2),
                'NextDueDate' => $agreement->next_due_date instanceof Carbon 
                    ? $agreement->next_due_date->format('Y-m-d') 
                    : $agreement->next_due_date,
            ]
        );

 \App\Models\AuditLog::log('create_installment_payment', "Recorded installment payment of Rs. {$validated['amount']} for agreement: {$agreement->sale->invoice_number}", $agreement);

        return redirect()
            ->route('admin.installments.show', $agreement)
            ->with('success', 'Payment recorded successfully.');
    }


    


    public function printGuaranteeBond($saleId)
        {
            $sale = Sale::with([
                'customer',
                'items.item',
                'installmentAgreement'
            ])->findOrFail($saleId);

         return view('admin.installments.proposal-agreement', compact('sale'));
        }




     public function destroy(InstallmentAgreement $agreement)
    {
        $customer = $agreement->customer;
        $sale = $agreement->sale;
    $invoiceNumber = $sale->invoice_number;
        // Delete related payments first to maintain data integrity
        $agreement->payments()->delete();
        
        // Delete the agreement itself
        $agreement->delete();

        // Delete the sale if it exists
        if ($sale) {
            $sale->items()->delete();
            $sale->delete();
        }

        // Delete customer if they have no other sales or agreements (to remove dummy data)
        if ($customer) {
            $otherAgreements = InstallmentAgreement::where('customer_id', $customer->id)->count();
            $otherSales = Sale::where('customer_id', $customer->id)->count();
            if ($otherAgreements === 0 && $otherSales === 0) {
                $customer->delete();
            }
        }

        return redirect()->back()->with('success', 'Installment agreement, associated sale, and customer (if no other records) have been deleted successfully.');
         \App\Models\AuditLog::log('delete_agreement', "Deleted installment agreement and associated sale: {$invoiceNumber}", null);
    }

    public function sendOverdueReminder(
        InstallmentAgreement $agreement,
        \App\Services\SmsService $smsService
    ) {
        $dueDate = $agreement->next_due_date;
        $dueDateStr = ($dueDate instanceof Carbon) ? $dueDate->format('Y-m-d') : $dueDate;

        $smsService->sendSms(
            $agreement->customer->phone,
            'overdue_installment_reminder',
            [
                'CustomerName' => $agreement->customer->name,
                'OverdueDate' => $dueDateStr,
                'RemainingBalance' => number_format($agreement->balance_amount, 2),
                'MonthlyPayment' => number_format($agreement->monthly_installment_amount, 2),
            ]
        );

        return redirect()
            ->back()
            ->with('success', 'Overdue reminder sent successfully.');
    }

    public function updateCallLog(Request $request, InstallmentAgreement $agreement)
    {
        $validated = $request->validate([
            'call_index' => 'required|integer|min:1|max:5',
            'status' => 'required|string',
            'feedback' => 'nullable|string',
        ]);

        $index = $validated['call_index'];
        $agreement->update([
            "call_{$index}_status" => $validated['status'],
            "call_{$index}_feedback" => $validated['feedback'],
        ]);

        return redirect()->back()->with('success', "Call {$index} log updated.");
    }

   public function sendPaymentCompletedNotification(
    InstallmentAgreement $agreement,
    \App\Services\SmsService $smsService
) {
    // Safety check
    if ($agreement->status !== 'paid_off') {
        return redirect()->back()->with('error', 'Agreement is not fully paid.');
    }

    $completedDate = $agreement->updated_at->format('Y-m-d');

    $smsService->sendSms(
        $agreement->customer->phone,
        'payment_completed_notification',
        [
            'CustomerName'  => $agreement->customer->name,
            'CompletedDate' => $completedDate,
        ]
    );

    return redirect()
        ->back()
        ->with('success', 'Payment completed SMS sent successfully.');
}
public function overdue(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date', now()->toDateString());
        $search = $request->get('search');
        $overdueAgreements = $this->getOverdueAgreementsData($startDate, $endDate, $search);

        return view('admin.installments.overdue', compact('overdueAgreements', 'startDate', 'endDate', 'search'));
    }

    private function getOverdueAgreementsData($startDate = null, $endDate = null, $search = null)
    {
        $query = InstallmentAgreement::where('status', '!=', 'paid_off')
            ->where('balance_amount', '>', 0)
            ->with(['customer', 'sale', 'payments']);

        if ($search) {
            $query->search($search);
        }

        $allAgreements = $query->get();

        return $allAgreements->filter(function ($agreement) use ($startDate, $endDate) {
            $nextDueDate = $agreement->next_due_date;
            
            if (!($nextDueDate instanceof Carbon)) {
                return false;
            }

            $isOverdue = $nextDueDate->lt(now()->startOfDay());

            if (!$isOverdue) {
                return false;
            }

            if ($startDate && $nextDueDate->lt(Carbon::parse($startDate))) {
                return false;
            }

            if ($endDate && $nextDueDate->gt(Carbon::parse($endDate)->endOfDay())) {
                return false;
            }

            return true;
        });
    }

    public function overdueExport(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date', now()->toDateString());
        $search = $request->get('search');
        $overdueAgreements = $this->getOverdueAgreementsData($startDate, $endDate, $search);

        $filename = "overdue_installments_" . now()->format('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($overdueAgreements) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Customer Name', 
                'Phone Number', 
                'Guarantor Name',
                'Guarantor Phone',
                'Invoice Number', 
                'EMI Number', 
                'EMI Lock Mode',
                'Device ID (EMI Number)',
                'Monthly Premium',
                'Quantity',
                'Next Due Date', 
                'Delay Days', 
                'Outstanding Balance'
            ]);

            foreach ($overdueAgreements as $agreement) {
                $nextDueDate = $agreement->next_due_date;
                $daysOverdue = 0;
                if ($nextDueDate instanceof Carbon) {
                    $daysOverdue = abs(now()->startOfDay()->diffInDays($nextDueDate->startOfDay(), false));
                }
                
                $totalPaid = $agreement->payments->sum('amount');
                $emiNumberCount = floor($totalPaid / $agreement->monthly_installment_amount) + 1;
                $totalQty = $agreement->sale ? $agreement->sale->items->sum('quantity') : 0;

                fputcsv($file, [
                    $agreement->customer->name,
                    $agreement->customer->phone ?? 'N/A',
                    $agreement->guarantor_name ?? 'N/A',
                    $agreement->guarantor_mobile_number ?? 'N/A',
                    $agreement->sale->invoice_number,
                    $agreement->combined_emi_numbers,
                    $agreement->emi_lock_mode ?? 'None',
                    $agreement->emi_number ?? 'N/A',
                    number_format($agreement->monthly_installment_amount, 2, '.', ''),
                    $totalQty,
                    $nextDueDate->format('Y-m-d'),
                    $daysOverdue,
                    number_format($agreement->balance_amount, 2, '.', ''),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function overduePrint(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date', now()->toDateString());
        $search = $request->get('search');
        $overdueAgreements = $this->getOverdueAgreementsData($startDate, $endDate, $search);

        if ($request->has('format') && $request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.installments.overdue-print', compact('overdueAgreements', 'startDate', 'endDate', 'search'), ['isPdf' => true]);
            return $pdf->download("overdue_installments_{$endDate}.pdf");
        }

        return view('admin.installments.overdue-print', compact('overdueAgreements', 'startDate', 'endDate', 'search'));
    }
    public function deleteAll()
    {
        // Delete related payments first to maintain data integrity
        InstallmentPayment::query()->delete();
        
        // Delete all agreements
        InstallmentAgreement::query()->delete();

        return redirect()->route('admin.reports.installment-income')->with('success', 'All installment agreements and payments have been deleted successfully.');
    }

  
    public function smsReminder(Request $request)
    {
         $selectedDate = $request->get('reminder_date', now()->addDay()->toDateString());
        $dayOfMonth = $carbonDate->day;

        $rawAgreements = $this->getSmsReminderAgreements($selectedDate);

        $processedAgreements = $rawAgreements->map(function ($agreement) use ($selectedDate, $dayOfMonth) {
            $totalPaid = $agreement->payments->sum('amount');
            $principal = $agreement->total_invoice_value - $agreement->interest_service_charge;
            $interestPaid = max(0, $totalPaid + $agreement->down_payment_amount - $principal);

            $outstandingPrincipal = max(0, $principal - ($totalPaid + $agreement->down_payment_amount));
            $outstandingInterest  = max(0, $agreement->interest_service_charge - $interestPaid);

            // COGS
            $cogs = 0;
            if ($agreement->sale && $agreement->sale->items) {
                $cogs = $agreement->sale->items->sum(
                    fn ($item) => ($item->item->cost_price ?? 0) * $item->quantity
                );
            }

            $profit = $agreement->total_invoice_value - $cogs;
            $profitClass = $profit >= 0 ? 'text-success' : 'text-danger';

            // Overdue status
            $overdueStatus = 'On Track';
            $nextDueDate = $agreement->next_due_date;
            $dueDate = ($nextDueDate instanceof Carbon) ? $nextDueDate : (is_string($nextDueDate) && $nextDueDate !== 'Completed' ? Carbon::parse($nextDueDate) : null);

            if ($agreement->status === 'paid_off') {
                $overdueStatus = 'Paid Off';
            } elseif ($dueDate) {
                if (Carbon::now()->startOfDay()->gt($dueDate->startOfDay())) {
                    $overdueStatus = 'Overdue';
                } elseif (Carbon::now()->startOfDay()->diffInDays($dueDate->startOfDay(), false) <= 7) {
                    $overdueStatus = 'Due Soon';
                }
            }

            // MONTHLY BREAKDOWN (FIFO)
            $monthlyBreakdown = [];
            $totalInstallments = $agreement->total_installments;
            $monthlyInterest = $totalInstallments > 0
                ? $agreement->interest_service_charge / $totalInstallments
                : 0;

            for ($i = 0; $i < $totalInstallments; $i++) {
                $dueD = Carbon::parse($agreement->first_due_date)->addMonths($i);
                $dueD = $agreement->applyDueDayOfMonth($dueD);

                $monthKey = $dueD->format('Y-m');
                $monthlyBreakdown[$monthKey] = [
                    'due_date' => $dueD->toDateString(),
                    'due_amount' => $agreement->monthly_installment_amount,
                    'paid_amount' => 0,
                    'paid_date' => null,
                    'status' => 'on_track',
                    'monthly_interest' => $monthlyInterest,
                    'comment' => '',
                ];
            }

            $remainingPayments = $agreement->payments->sortBy('payment_date')->values();
            $paymentIndex = 0;
            $paymentOffset = 0;

            foreach ($monthlyBreakdown as $monthKey => &$data) {
                $dueForThisMonth = round($data['due_amount'], 2);
                $allocatedForThisMonth = 0;

                while ($dueForThisMonth > 0.009 && $paymentIndex < count($remainingPayments)) {
                    $p = $remainingPayments[$paymentIndex];
                    $availableInPayment = round($p->amount - $paymentOffset, 2);
                    $toTake = min($dueForThisMonth, $availableInPayment);
                    $allocatedForThisMonth = round($allocatedForThisMonth + $toTake, 2);
                    $dueForThisMonth = round($dueForThisMonth - $toTake, 2);
                    $paymentOffset = round($paymentOffset + $toTake, 2);
                    $data['paid_date'] = $p->payment_date;
                    if ($paymentOffset >= round($p->amount, 2) - 0.009) {
                        $paymentIndex++;
                        $paymentOffset = 0;
                    }
                }
                $data['paid_amount'] = $allocatedForThisMonth;
                if ($data['paid_amount'] >= round($data['due_amount'], 2) - 0.009 || $agreement->balance_amount <= 0.009) {
                    $data['status'] = 'paid_off';
                } elseif ($data['paid_amount'] > 0) {
                    $data['status'] = 'partially_paid';
                }
            }

            return (object) [
                'agreement' => $agreement,
                'total_paid' => $totalPaid,
                'principal' => $principal,
                'interest_paid' => $interestPaid,
                'outstanding_principal' => $outstandingPrincipal,
                'outstanding_interest' => $outstandingInterest,
                'profit' => $profit,
                'profit_class' => $profitClass,
                'overdue_status' => $overdueStatus,
                'monthly_breakdown' => $monthlyBreakdown,
                'total_collected' => $totalPaid + $agreement->down_payment_amount,
            ];
        });

        $data = $processedAgreements->groupBy(fn($row) => $row->agreement->customer_id)
            ->map(function ($items) {
                return (object) [
                    'customer' => $items->first()->agreement->customer,
                    'agreements' => $items,
                ];
            });

        return view('admin.installments.sms_reminder', compact('data', 'selectedDate', 'dayOfMonth'));
    }

    public function sendSmsReminders(Request $request, \App\Services\SmsService $smsService)
    {
        $request->validate([
            'agreement_ids' => 'required|array',
            'agreement_ids.*' => 'exists:installment_agreements,id',
        ]);

        $agreements = InstallmentAgreement::whereIn('id', $request->agreement_ids)->with('customer')->get();

        foreach ($agreements as $agreement) {
            $dueDate = $agreement->next_due_date;
            $dueDateStr = ($dueDate instanceof \Carbon\Carbon) ? $dueDate->format('Y-m-d') : $dueDate;

            $smsService->sendSms(
                $agreement->customer->phone,
                'due_date_reminder',
                [
                    'CustomerName' => $agreement->customer->name,
                    'DueDate' => $dueDateStr,
                    'RemainingBalance' => number_format($agreement->balance_amount, 2),
                    'MonthlyPayment' => number_format($agreement->monthly_installment_amount, 2),
                ]
            );
        }

        return redirect()->back()->with('success', 'SMS reminders sent successfully.');
    }

    public function printSmsReminders(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $agreements = $this->getSmsReminderAgreements($date);
        
        if ($request->has('format') && $request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.installments.sms_reminder_print', compact('agreements', 'date'), ['isPdf' => true]);
            return $pdf->download("sms_reminders_{$date}.pdf");
        }
        
        return view('admin.installments.sms_reminder_print', compact('agreements', 'date'));
    }

    private function getSmsReminderAgreements($date)
    {
        $carbonDate = Carbon::parse($date);
        $dayOfMonth = $carbonDate->day;
        $isLastDay = $carbonDate->copy()->endOfMonth()->day === $dayOfMonth;

        $query = InstallmentAgreement::where('status', '!=', 'paid_off')
            ->where('balance_amount', '>', 0)
             ->with(['customer', 'payments', 'sale.items.item.category']);

        if ($isLastDay) {
            $query->where('due_day_of_month', '>=', $dayOfMonth);
        } else {
            $query->where('due_day_of_month', $dayOfMonth);
        }

        return $query->get();
    }

    public function printReceipt(InstallmentPayment $payment)
    {
        $payment->load(['agreement.customer']);
        return view('admin.installments.receipt', compact('payment'));
    }

    public function destroyPayment(Request $request, InstallmentPayment $payment)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if ($request->password !== '876') {
            return back()->withErrors(['error' => 'Invalid password for deleting payment.']);
        }

        $agreement = $payment->agreement;
        $amount = $payment->amount;

        $payment->delete();

        // Recalculate agreement balance
        $agreement->update([
            'balance_amount' => round($agreement->total_invoice_value - $agreement->down_payment_amount - $agreement->payments()->sum('amount'), 2),
            'status' => 'active' // Ensure it's not paid_off if balance is restored
        ]);

        return back()->with('success', 'Payment record deleted successfully.');
    }

    public function printDisconnectReceipt(InstallmentPayment $payment)
    {
        $payment->load(['agreement.customer', 'agreement.sale']);
        return view('admin.installments.disconnect-receipt', compact('payment'));
    }

    public function storeDelayPayment(
        Request $request, 
        InstallmentAgreement $agreement,
        \App\Services\SmsService $smsService
    ) {
        $validated = $request->validate([
            'fine_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'cheque_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'cheque_date' => 'nullable|date',
        ]);

        $agreement->payments()->create([
            'amount' => 0,
            'fine_amount' => $validated['fine_amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'],
            'cheque_number' => $validated['cheque_number'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'cheque_date' => $validated['cheque_date'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // ✅ SEND SMS AUTOMATICALLY FOR DELAY PAYMENT
        $smsService->sendSms(
            $agreement->customer->phone,
            'delay_payment_received',
            [
                'CustomerName' => $agreement->customer->name,
                'Amount' => "0.00",
                'FineAmount' => number_format($validated['fine_amount'], 2),
                'Date' => $validated['payment_date'],
                'Balance' => number_format($agreement->balance_amount, 2),
                'NextDueDate' => $agreement->next_due_date instanceof Carbon 
                    ? $agreement->next_due_date->format('Y-m-d') 
                    : $agreement->next_due_date,
                'LockStatus' => $agreement->status_label,
            ]
        );

        return redirect()
            ->route('admin.installments.show', $agreement)
            ->with('success', 'Delay payment recorded successfully.');
    }

    public function disconnect(InstallmentAgreement $agreement)
    {
        $agreement->update([
            'disconnected_at' => now(),
            'unlocked_at' => null, // Clear unlock date when re-blocked
        ]);

        return redirect()->back()->with('success', 'Device marked as disconnected.');
    }

    public function unlock(InstallmentAgreement $agreement)
    {
        $agreement->update([
            'unlocked_at' => now(),
            // Keep disconnected_at to calculate duration, but the device is now unlocked
        ]);

        return redirect()->back()->with('success', 'Device marked as unlocked.');
    }
    
     public function destroyCustomer(Request $request, \App\Models\Customer $customer)
    {
        if ($request->password !== '876') {
            return back()->withErrors(['error' => 'Invalid password for deleting customer.']);
        }

        \DB::transaction(function () use ($customer) {
            // 1. Installment Agreements & Payments
            foreach ($customer->installmentAgreements as $agreement) {
                $agreement->payments()->delete();
                $agreement->delete();
            }

            // 2. Sales, Items & Payments
            foreach ($customer->sales as $sale) {
                $sale->items()->delete();
                $sale->payments()->delete();
                $sale->delete();
            }

            // 3. Credits
            $customer->credits()->delete();

            // 4. Customer
            $customer->delete();
        });

        return redirect()->route('admin.installments.index')->with('success', "Customer '{$customer->name}' and all associated data deleted successfully.");
    }
}
