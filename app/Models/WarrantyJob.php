<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyJob extends Model
{
    use HasFactory;

    const STATUSES = [
        'Received',
        'Sent to Service Center / Supplier',
        'Under Repair',
        'Returned to Branch',
        'Ready for Collection',
        'Collected',
        'Rejected / Not Covered',
        'Replaced'
    ];

    protected $fillable = [
        'job_number',
        'warranty_id',
        'branch_id',
        'claim_date',
        'problem_description',
        'claim_type',
        'status',
        'remarks',
        'created_by',
        'collected_by_name',
        'collected_by_id',
        'collected_at',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'collected_at' => 'datetime',
    ];

    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function history()
    {
        return $this->hasMany(WarrantyJobHistory::class);
    }
}
