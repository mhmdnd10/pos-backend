<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class CustomerPayment extends Model
{
    use BelongsToShop;
    protected $fillable = [
        'customer_id',
        'sale_id',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'shop_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    
}
