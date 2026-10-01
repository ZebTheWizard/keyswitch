<?php

namespace App\Enum;

enum ScrapingStatus: string
{
    case READY = 'ready';
    case PENDING = 'pending';
    case FAILED = 'failed';

    public function getColor(): string
    {
        return match ($this) {
            self::READY => 'success',
            self::PENDING => 'gray',
            default => 'danger',
        };
    }
}
