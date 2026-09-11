<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\SmsLog;
use App\Models\SmsTemplate;

class SmsService
{
    public function sendSms($recipient, $eventName, $data)
    {
        $template = SmsTemplate::where('event_name', $eventName)->first();

        if (!$template) {
            return;
        }

        $message = $this->replacePlaceholders($template->template, $data);

        return $this->sendRawSms($recipient, $message, $eventName);
    }

    private function replacePlaceholders($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

 public function sendRawSms($recipient, $message, $type = 'promotional')
{
    if (!config('sms.enabled')) {
        return ['success' => false, 'message' => 'SMS disabled'];
    }

    try {
        $response = Http::withOptions([
            'force_ip_resolve' => 'v4',
            'timeout' => 30,
            'query' => [
                'username' => config('sms.username'),
                'password' => config('sms.password'),
                'apiKey'   => config('sms.api_key'),
            ],
        ])->withHeaders([
            'Accept' => 'application/json',
        ])->post(config('sms.url'), [
            // BODY
            'mask'    => config('sms.sender_id'),
            'number'  => $recipient,
            'content' => $message,
        ]);

        \Log::info('SMS RAW RESPONSE', [
            'url'    => config('sms.url'),
            'recipient' => $recipient,
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        SmsLog::create([
            'recipient_phone_number' => $recipient,
            'message_type' => $type,
            'message' => $message,
            'status' => $response->successful() ? 'sent' : 'failed',
        ]);

        return [
            'success' => $response->successful(),
            'raw' => $response->body(),
        ];

    } catch (\Throwable $e) {
        \Log::error('SMS ERROR', ['error' => $e->getMessage()]);
        return ['success' => false, 'error' => $e->getMessage()];
    }

    
}

/**
 * Send BULK SMS
 * $recipients = array of phone numbers
 */

public function sendBulkSms(array $recipients, $message, $type = 'bulk')
{
    if (!config('sms.enabled')) {
        return ['success' => false, 'message' => 'SMS disabled'];
    }

    $numbers = implode(',', $recipients);

    $query = [
        'username' => config('sms.username'),
        'password' => config('sms.password'),
        'number'   => $numbers,
        'content'  => $message,
        // remove 'mask' for bulk if KD rejects spaces
        // 'mask'   => config('sms.sender_id'),
    ];

    try {
        $response = \Illuminate\Support\Facades\Http::withOptions([
            'force_ip_resolve' => 'v4',
            'timeout' => 45,
        ])->get(config('sms.bulk_url'), $query); // <-- correct way

        \Log::info('BULK SMS RESPONSE', [
            'numbers' => $numbers,
            'body'    => $response->body(),
        ]);

        foreach ($recipients as $num) {
            \App\Models\SmsLog::create([
                'recipient_phone_number' => $num,
                'message_type' => $type,
                'message' => $message,
                'status' => $response->successful() ? 'sent' : 'failed',
            ]);
        }

        return [
            'success' => $response->successful(),
            'raw'     => $response->body(),
        ];

    } catch (\Throwable $e) {
        \Log::error('BULK SMS ERROR', ['error' => $e->getMessage()]);
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


}
