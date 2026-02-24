<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class CustomerLedger extends Model
{
    use BelongsToShop;
    protected $fillable = [
        'customer_id',
        'type',
        'reference_id',
        'reference_number',
        'debit',
        'credit',
        'shop_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
