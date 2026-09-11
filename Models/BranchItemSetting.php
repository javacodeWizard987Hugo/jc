<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchItemSetting extends Model
{
    //
      protected $fillable = [
        'branch_id',
        'item_id',
        'reorder_level',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
