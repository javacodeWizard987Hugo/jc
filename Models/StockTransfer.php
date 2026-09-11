<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_location_id',
        'to_location_id',
        'transfer_date',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    public function fromLocation()
    {
        return $this->belongsTo(Branch::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Branch::class, 'to_location_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
