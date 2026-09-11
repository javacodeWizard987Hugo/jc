<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = [
        'event_name',
        'template',
        'placeholders',
    ];

    protected function casts(): array
    {
        return [
            'placeholders' => 'array',
        ];
    }
}
