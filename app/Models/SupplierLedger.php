<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class SupplierLedger extends Model
{
    use BelongsToShop;
    protected $fillable = [
        'supplier_id',
        'type',
        'reference_id',
        'refernce_number',
        'debit',
        'credit',
    ];
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

}
