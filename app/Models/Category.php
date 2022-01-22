<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = "categories";

    protected $fillable = [
        'title', 'description', 'parent_id', 'featured',
    ];

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

    public static function getCategoryLevel($category_id, $level = 0)
    {
        $category = self::find($category_id);
        if (!is_null($category->parent_id)) {
            $level++;
            return self::getCategoryLevel($category->parent_id, $level);
        } else {
            return $level;
        }
    }
}
