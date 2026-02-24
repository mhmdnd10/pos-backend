<?php
namespace App\Traits;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToShop
{
    protected static function bootBelongsToShop()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->shop_id = auth()->user()->shop_id;
            }
        });

        static::addGlobalScope('shop', function ($builder) {
            if (auth()->check()) {
                $builder->where('shop_id', auth()->user()->shop_id);
            }
        });
    }
}