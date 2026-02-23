<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLedger extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'reference_id',
        'reference_number',
        'debit',
        'credit',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
