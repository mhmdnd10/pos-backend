<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierLedger extends Model
{
    protected $fillable = [
        'supplier_id',
        'type',
        'reference_id',
        'refernce_number',
        'debit',
        'credit',
    ];
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

}
