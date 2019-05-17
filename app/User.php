<?php

namespace App;

use Moloquent;
use Jenssegers\Mongodb\Relations\HasMany;

class User extends Moloquent
{
    protected $fillable = [
        'user_id',
        'name',
    ];

}
