<?php

namespace App\Models;

use App\Traits\Helpers;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Helpers;

    protected $table = "products";

    protected $fillable = [
        'title', 'description', 'price', 'amount', 'discount',
        'discount_start_date', 'discount_end_date', 'created_by', 'category_id',
        'brand_id', 'product_code', 'featured'
    ];

    protected $appends = ["slug", "description_short", "title_short", "is_discount_active", "price_after_discount", "price_after_discount_numeric"];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->with('features');
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

    public function getSlugAttribute()
    {
        return self::slugify($this->title);
    }

    public function getTitleShortAttribute()
    {
        return mb_substr($this->title, 0, 73, 'utf-8');
    }

    public function getDescriptionShortAttribute()
    {
        return mb_substr(strip_tags($this->description), 0, 70, 'utf-8');
    }

    public function getIsDiscountActiveAttribute()
    {
        if ($this->discount > 0) {
            if ($this->discount_start_date && $this->discount_end_date) {
                if ($this->discount_start_date <= date("Y-m-d") && $this->discount_end_date >= date("Y-m-d")) {
                    return true;
                }

                return false;
            } else if ($this->discount_start_date && !$this->discount_end_date) {
                if ($this->discount_start_date <= date("Y-m-d")) {
                    return true;
                }

                return false;
            } else if (!$this->discount_start_date && $this->discount_end_date) {
                if ($this->discount_end_date >= date("Y-m-d")) {
                    return true;
                }

                return false;
            }
        }

        return false;
    }

    public function getPriceAfterDiscountAttribute()
    {
        if ($this->getIsDiscountActiveAttribute()) {
            return number_format($this->price - ($this->price * ($this->discount / 100)), 1);
        }

        return number_format($this->price, 1);
    }

    public function getPriceAfterDiscountNumericAttribute()
    {
        if ($this->getIsDiscountActiveAttribute()) {
            return $this->price - ($this->price * ($this->discount / 100));
        }

        return $this->price;
    }

    public function scopeDiscountWithStartAndEndDates($query)
    {
        return $query->whereNotNull('discount_start_date')
            ->whereNotNull('discount_end_date')
            ->whereDate('discount_start_date', '<=', date('Y-m-d'))
            ->whereDate('discount_end_date', '>=', date('Y-m-d'));
    }

    public function scopeDiscountWithStartDate($query)
    {
        return $query->whereNotNull('discount_start_date')
            ->whereNull('discount_end_date')
            ->whereDate('discount_start_date', '<=', date('Y-m-d'));
    }

    public function scopeDiscountWithEndDate($query)
    {
        return $query->whereNotNull('discount_end_date')
            ->whereNull('discount_start_date')
            ->whereDate('discount_end_date', '>=', date('Y-m-d'));
    }
}
