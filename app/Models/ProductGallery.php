<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGallery extends Model
{
    protected $table = "product_gallery";

    protected $fillable = [
        'image', 'product_id ',
    ];
}
