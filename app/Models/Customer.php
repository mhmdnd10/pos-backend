<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class Customer extends Model
{
    use BelongsToShop;
    protected $fillable = [
        'name',
        'phone',
        'address',
        'current_balance',
        'shop_id',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    public function ledgers()
    {
        return $this->hasMany(CustomerLedger::class);
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
