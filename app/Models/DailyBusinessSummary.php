<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyBusinessSummary extends Model
{

    protected $fillable = [
        'shop_id',
        'business_date',
        'total_sales',
        'total_purchases',
        'customer_payments',
        'supplier_payments',
        'sales_count',
        'purchases_count',
    ];
    protected $casts = [
        'business_date' => 'date:Y-m-d',
        'total_sales' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'customer_payments' => 'decimal:2',
        'supplier_payments' => 'decimal:2',
        'sales_count' => 'integer',
        'purchases_count' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}

