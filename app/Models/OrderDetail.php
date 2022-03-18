<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = "order_details";

    protected $fillable = [
        'order_id', 'product_id', 'price', 'amount',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->with("gallery");
    }
}
