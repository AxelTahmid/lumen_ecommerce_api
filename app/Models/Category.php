<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Helpers;

class Category extends Model
{
    use Helpers;

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

    public static function getCategoryMenuTree($parent_id = null, &$output = [])
    {
        $categories = self::where('parent_id', $parent_id)->get();

        foreach ($categories as $category) {

            $arr = [
                'id' => $category->id,
                'title' => $category->title,
                'path' => ($category->children->count() > 0 ? '#' : '/' . $category->id . '/' . self::slugify($category->title)),
                'children' => []
            ];

            if ($category->children->count() > 0) {
                self::getCategoryMenuTree($category->id, $arr['children']);
            }

            $output[] = $arr;
        }

        return $output;
    }
}
