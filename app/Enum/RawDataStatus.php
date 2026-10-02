<?php

namespace App\Enum;

enum RawDataStatus: string
{
    case NEEDS_REVIEW = 'needs-review';
    case READY = 'ready';
    case IMPORTED = 'imported';
}
