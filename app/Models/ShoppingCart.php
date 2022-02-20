<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    protected $table = "shopping_cart";

    protected $fillable = [
        'user_id', 'product_id',  'amount',
    ];
    protected $hidden = ["created_at", "updated_at"];
    protected $appends = ["total_price_formatted", "total_price_numeric", "amount_temp"];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->with("gallery");
    }

    public function getTotalPriceFormattedAttribute()
    {
        return number_format($this->amount * $this->product->price_after_discount_numeric, 1);
    }
    public function getTotalPriceNumericAttribute()
    {
        return $this->amount * $this->product->price_after_discount_numeric;
    }
    public function getAmountTempAttribute()
    {
        return $this->amount;
    }
}
