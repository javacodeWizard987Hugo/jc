<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyJobHistory extends Model
{
    use HasFactory;

    protected $table = 'warranty_job_history';

    protected $fillable = [
        'warranty_job_id',
        'old_status',
        'new_status',
        'remarks',
        'updated_by',
    ];

    public function warrantyJob()
    {
        return $this->belongsTo(WarrantyJob::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
