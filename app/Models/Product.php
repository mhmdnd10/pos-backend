<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class Product extends Model
{
    use BelongsToShop;
    protected $fillable = [
        'name',
        'barcode',
        'category_id',
        'description',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'image',
        'is_active',
        'minimum_stock_alert',
        'shop_id',
        'size',
        'color',
        'brand',

    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    
}
