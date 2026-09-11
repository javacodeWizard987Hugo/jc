<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'recipient_phone_number',
        'message_type',
        'message',
        'status',
    ];
}
