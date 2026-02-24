<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = ['name', 'address'];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function customerLedgers()
    {
        return $this->hasMany(CustomerLedger::class);
    }
    public function supplierLedgers()
    {
        return $this->hasMany(SupplierLedger::class);
    }
    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class);
    }
    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);  
    }
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
    public function purchaseItems()
    {        return $this->hasMany(PurchaseItem::class);
    }
    


}
