<?php

namespace App\Models;

use App\Enum\RawDataStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RawKeySwitch extends Model
{
    protected $fillable = ['url'];

    protected $casts = [
        'status' => RawDataStatus::class,
        'raw_data' => 'array',
        'raw_price' => 'float',
    ];

    /**
     * @return HasOne<KeySwitch, $this>
     */
    public function keySwitch(): HasOne
    {
        return $this->hasOne(KeySwitch::class);
    }
}
