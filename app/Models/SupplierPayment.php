<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class SupplierPayment extends Model
{
    use BelongsToShop;
    protected $fillable = [
        'supplier_id',
        'purchase_id',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'shop_id'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
