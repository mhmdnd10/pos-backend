<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'current_balance',
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
    
}
