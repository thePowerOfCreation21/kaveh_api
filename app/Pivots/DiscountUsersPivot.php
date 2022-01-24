<?php

namespace App\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DiscountUsersPivot extends Pivot
{
    protected $casts = [
        'is_used' => 'boolean'
    ];
}
