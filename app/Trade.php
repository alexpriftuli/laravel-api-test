<?php

namespace App;

use Jenssegers\Mongodb\Relations\BelongsTo;
use Jenssegers\Mongodb\Relations\HasOne;
use Moloquent;

class Trade extends Moloquent
{
    protected $fillable = [
        'trade_id',
        'type',
        'user',
        'shares',
        'stock',
        'created_at',
    ];

}
