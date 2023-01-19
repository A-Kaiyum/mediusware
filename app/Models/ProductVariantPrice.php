<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $guarded = [];

    protected $with = ['variant_one:id,variant', 'variant_two:id,variant', 'variant_three:id,variant'];
    public function variant_one()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one', 'id');
    }

    public function variant_two()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two', 'id');
    }

    public function variant_three()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three', 'id');
    }
}
