<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
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

    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
}
