<?php

namespace App\Models;

use App\Enum\ScrapingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scraper extends Model
{
    protected $fillable = [
        'class',
        'status',
        'error',
    ];

    protected $casts = [
        'status' => ScrapingStatus::class,
    ];

    /**
     * @return HasMany<RawKeySwitch, $this>
     */
    public function switches(): HasMany
    {
        return $this->hasMany(RawKeySwitch::class);
    }
}
