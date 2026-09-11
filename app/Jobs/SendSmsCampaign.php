<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendSmsCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customers;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Support\Collection $customers
     * @param string $message
     */
    public function __construct(Collection $customers, string $message)
    {
        $this->customers = $customers;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\SmsService $smsService
     * @return void
     */
    public function handle(SmsService $smsService): void
    {
        foreach ($this->customers as $customer) {
            if ($customer->phone && $customer->promotional_sms_opt_in) {
                // We don't have specific placeholders for campaigns, so just send the raw message.
                // A more advanced implementation might parse placeholders here.
                $smsService->sendRawSms($customer->phone, $this->message, 'promotion');
            }
        }
    }
}
