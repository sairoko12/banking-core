<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountCredit extends Model
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null;

    protected $table = 'account_credit';

    protected $fillable = [
        'account_id',
        'credit_line',
        'as_delayed'
    ];

    protected $attributes = [
        'as_delayed' => 0
    ];

    public function account()
    {
        return $this->belongsTo('App\UserAccount');
    }
}
