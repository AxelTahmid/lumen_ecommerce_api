<?php

namespace App\Models;

use App\Traits\Helpers;
use Illuminate\Database\Eloquent\Model;

class ProductGallery extends Model
{
    use Helpers;

    protected $table = "product_gallery";

    protected $fillable = [
        'image', 'product_id ',
    ];

    protected $appends = ["image_url"];

    public function getImageUrlAttribute()
    {
        $urls = [];

        foreach ($this->imagesSizes as $dirName => $imagesSize) {
            $urls[$dirName] = url('/') . '/uploads/' . $this->product_id . '/' . $dirName . '/' . $this->image;
        }

        return $urls;
    }
}
