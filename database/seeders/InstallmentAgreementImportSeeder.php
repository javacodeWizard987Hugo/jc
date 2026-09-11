<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Item;
use App\Models\SaleItem;
use App\Models\InstallmentAgreement;
use App\Models\InstallmentPayment;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class InstallmentAgreementImportSeeder extends Seeder
{
    public function run(): void
    {
          $branchId = \App\Models\Branch::first()?->id ?? 1;
        $filePath = storage_path('app/installments.xlsx');
        if (!file_exists($filePath)) {
            $this->command->warn('Excel file not found at storage/app/installments.xlsx. Skipping seeder.');
            return;
        }

        $rows = Excel::toArray([], $filePath)[0];

        DB::transaction(function () use ($rows) {

            foreach ($rows as $index => $row) {

                // Skip header row
                if ($index === 0) {
                    continue;
                }

                [
                    $rowNo,           // 0 (serial number - ignore)
                    $saleDate,        // 1
                    $agreementNo,     // 2
                    $customerName,    // 3
                    $emiNumber,       // 4
                    $lockModel,       // 5
                    $contactNo,       // 6
                    $nic,             // 7
                    $dueDate,         // 8
                    $loanMonths,      // 9
                    $monthlyAmount,   // 10
                    $outstanding,     // 11
                    $cost,            // 12
                    $downPayment,     // 13
                    $inst1,           // 14
                    $inst2,           // 15
                    $itemName,        // 16
                    $selling ,         // 17
                      $itemCode// ✅ ADD THIS (adjust index if needed)
                 ] = array_pad($row, 19, null);
              

                // Skip empty agreements
                if (empty($emiNumber)) {
                    continue;
                }

                // Skip header text inside columns
                if (strtolower(trim((string)$dueDate)) === 'due date') {
                    continue;
                }

                // Prevent duplicate agreements by checking the unique EMI number
                if (InstallmentAgreement::where('emi_number', $emiNumber)->exists()) {
                    continue;
                }

                // ---------------- DATE PARSING ----------------
                $saleDateParsed = $this->parseExcelDate($saleDate) ?? now();

                // NIC accidentally in due date column
                if ($this->looksLikeNic($dueDate)) {
                    $this->command->warn("❌ NIC detected in Due Date column, row skipped: {$dueDate}");
                    continue;
                }

                $firstDueDate = $this->parseExcelDate($dueDate);

                if (!$firstDueDate) {
                    $this->command->warn("❌ Invalid due date skipped: {$dueDate}");
                    continue;
                }

                // ---------------- CUSTOMER ----------------
             $nic = strtolower(trim((string)$nic)); // normalize NIC

            $customer = Customer::updateOrCreate(
                ['nic' => $nic], // UNIQUE KEY
                [
                    'name' => trim((string)$customerName),
                    'phone' => trim((string)$contactNo),
                    'is_active' => true,
                ]
            );


                // ---------------- SALE ----------------
                // Use create() instead of firstOrCreate() to ensure a new sale for each row
                // Append the row index to the agreement number to guarantee a unique invoice number
                $sale = Sale::create(
                    [
                        'invoice_number' => 'IMP-' . $agreementNo . '-' . $index,
                        'cashier_id' => 1,
                         'branch_id' => $branchId,
                        'customer_id' => $customer->id,
                        'subtotal' => (float)$selling,
                        'total_amount' => (float)$selling,
                        'payment_method' => 'cash',
                        'status' => 'completed',
                        'created_at' => $saleDateParsed,
                    ]
                );

                // ---------------- ITEM ----------------
               // ---------------- ITEM ----------------
              // ---------------- ITEM ----------------
            // ---------------- ITEM ----------------
                $item = Item::firstOrCreate(
                    ['name' => trim((string)$itemName)],
                    [
                        'selling_price' => (float)$selling,
                        'cost_price' => (float)$cost,
                        'is_active' => true,
                    ]
                );


                // ---------------- SALE ITEM ----------------
                SaleItem::create(
                    [
                        'sale_id' => $sale->id,
                        'item_id' => $item->id,
                        'quantity' => 1,
                        'unit_price' => (float)$selling,
                        'total_price' => (float)$selling,
                    ]
                );

                // ---------------- CALCULATIONS ----------------
                $paidInstallments = (float)$inst1 + (float)$inst2;
                $balance = (float)$selling - (float)$downPayment - $paidInstallments;

                // ---------------- AGREEMENT ----------------
                $agreement = InstallmentAgreement::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $customer->id,
                    'emi_number' => $emiNumber,
                    'emi_lock_mode' => $lockModel,
                    'total_invoice_value' => (float)$selling,
                    'down_payment_amount' => (float)$downPayment,
                    'down_payment_method' => 'Cash',
                    'down_payment_date' => $saleDateParsed,
                    'paid_towards_installment' => $paidInstallments,
                    'balance_amount' => max(0, $balance),
                    'number_of_installments' => (int)$loanMonths,
                    'monthly_installment_amount' => (float)$monthlyAmount,
                    'first_due_date' => $firstDueDate,
                    'due_day_of_month' => $firstDueDate->day,
                    'interest_service_charge' => max(0, (float)$selling - (float)$cost),
                    'status' => $balance <= 0 ? 'paid_off' : 'active',
                    'created_at' => $saleDateParsed,
                ]);

                // ---------------- PAYMENTS ----------------
                if ((float)$inst1 > 0) {
                    InstallmentPayment::create([
                        'installment_agreement_id' => $agreement->id,
                        'amount' => (float)$inst1,
                        'payment_date' => $saleDateParsed,
                        'payment_method' => 'Cash',
                        'status' => 'received',
                        'created_by' => 1,
                        'notes' => 'Imported installment 1',
                    ]);
                }

                if ((float)$inst2 > 0) {
                    InstallmentPayment::create([
                        'installment_agreement_id' => $agreement->id,
                        'amount' => (float)$inst2,
                        'payment_date' => $firstDueDate,
                        'payment_method' => 'Cash',
                        'status' => 'received',
                        'created_by' => 1,
                        'notes' => 'Imported installment 2',
                    ]);
                }

                // ---------------- CUSTOMER BALANCE ----------------
                if ($balance > 0) {
                    $customer->increment('outstanding_balance', $balance);
                }
            }
        });

        $this->command->info('✅ Excel installment data imported successfully');
    }

    private function parseExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = trim((string)$value);

        if (is_numeric($value)) {
            return Carbon::instance(
                ExcelDate::excelToDateTimeObject($value)
            );
        }

        if (preg_match('/^\d{1,2}-[A-Za-z]{3}$/', $value)) {
            return Carbon::createFromFormat('d-M-Y', $value . '-' . now()->year);
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function looksLikeNic($value): bool
    {
        return is_string($value)
            && preg_match('/^\d{9}[vVxX]$/', trim($value));
    }
}
