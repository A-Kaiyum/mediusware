<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function product_variant()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function product_variant_price()
    {
        return $this->hasMany(ProductVariantPrice::class);
    }
}
