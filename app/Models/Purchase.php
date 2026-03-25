<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class Purchase extends Model
{
    use BelongsToShop;
    protected $fillable = [
        'supplier_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'shop_id',
        'purchase_date'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
