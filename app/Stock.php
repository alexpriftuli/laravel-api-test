<?php

namespace App;

use Jenssegers\Mongodb\Relations\BelongsTo;
use Moloquent;

class Stock extends Moloquent
{
    protected $fillable = [
        'symbol',
        'price',
        'created_at',
    ];

}
