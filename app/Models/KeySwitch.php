<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeySwitch extends Model
{
    protected $casts = [
        'product_images' => 'array',
    ];
}
