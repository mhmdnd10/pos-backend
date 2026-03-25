<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class Sale extends Model
{
    use BelongsToShop;
    protected $fillable = [
        'customer_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'payment_status',
        'shop_id',
        'sale_date'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
