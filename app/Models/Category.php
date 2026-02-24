<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToShop;

class Category extends Model
{
    use BelongsToShop;
    protected $fillable=[
        'name',
        'shop_id',
    ];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
}
