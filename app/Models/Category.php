<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function features()
    {
        return $this->hasMany(CategoryFeature::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'product_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
