<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class Supplier extends Model
{
    use BelongsToShop;
    protected $fillable = [
        'name',
        'phone',
        'address',
        'shop_id',
    
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    public function ledgers()
    {
        return $this->hasMany(SupplierLedger::class);
    }
    public function getBalanceAttribute()
    
    {
        return $this->ledgers()->selectRaw('SUM(debit - credit) as balance')->value('balance') ?? 0;
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

}
