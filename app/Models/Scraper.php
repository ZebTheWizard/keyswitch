<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scraper extends Model
{
    public function switches(): HasMany
    {
        return $this->hasMany(RawKeySwitch::class);
    }
}
