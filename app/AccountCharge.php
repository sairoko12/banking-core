<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Dictionaries\AccountChargeState;

class AccountCharge extends Model
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null;

    protected $table = 'account_charge';

    protected $fillable = [
        'source_account_id',
        'type_id',
        'operation_date',
        'liquidation_date',
        'description',
        'amount',
        'state'
    ];

    protected $attributes = [
        'state' => AccountChargeState::PENDING
    ];

    public function account()
    {
        return $this->belongsTo('App\UserAccount');
    }

    public function type()
    {
        $this->hasOne('App\LuCharge', 'type_id');
    }
}

