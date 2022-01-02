<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $table = "products";

    protected $fillable = [
        'title', 'description', 'price', 'amount', 'discount',
        'discount_start_date', 'discount_end_date', 'created_by', 'category_id',
        'brand_id', 'product_code', 'featured'
    ];



    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function features()
    {
        return $this->hasMany(ProductFeature::class, 'product_id');
    }

    public function gallery()
    {
        return $this->hasMany(ProductGallery::class, 'product_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}
