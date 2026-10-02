<?php

namespace App\Models;

use App\Enum\RawDataStatus;
use Illuminate\Database\Eloquent\Model;

class RawKeySwitch extends Model
{
    protected $fillable = ['url'];

    protected $casts = [
        'status' => RawDataStatus::class,
        'raw_data' => 'array',
        'raw_price' => 'float',
    ];
}
