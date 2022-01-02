<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryFeature extends Model

{
    protected $table = "category_features";

    protected $fillable = [
        'field_title', 'field_type', 'category_id ', 'featured',
    ];

    protected $hidden = ["created_at", "updated_at"];
}
