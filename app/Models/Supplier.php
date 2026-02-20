<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
    
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

}
