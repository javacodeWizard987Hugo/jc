<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number_id',
        'sale_item_id',
        'customer_id',
        'start_date',
        'duration',
        'expiry_date',
        'notes',
    ];

    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warrantyJobs()
    {
        return $this->hasMany(WarrantyJob::class);
    }
}
