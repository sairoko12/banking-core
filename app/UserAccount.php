<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null;

    protected $table = 'user_account';

    protected $fillable = [
        'user_id',
        'account_type',
        'alias'
    ];

    protected $attributes = [
        'status' => 1
    ];

    protected $appends = [
        'balance'
    ];

    public function getBalanceAttribute(): float
    {
        return app()->make('App\Services\CashMachine\UserAccountService')
            ->getBalance($this->id);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function deposits()
    {
        return $this->hasMany('App\AccountDeposit', 'account_id');
    }

    public function charges()
    {
        return $this->hasMany('App\AccountCharge', 'source_account_id');
    }
}
