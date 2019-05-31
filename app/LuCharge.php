<?php

namespace App\LuCharge;

use Illuminate\Database\Eloquent\Model;

class LuCharge extends Model
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null;

    protected $table = 'lu_charge';

    protected $fillable = [
        'type'
    ];
}
