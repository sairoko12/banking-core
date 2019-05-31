<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Dictionaries\AccountDepositState;
use Webpatser\Uuid\Uuid;

class AccountDeposit extends Model
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null;

    protected $table = 'account_deposit';

    protected $fillable = [
        'account_id',
        'source',
        'operation_date',
        'liquidation_date',
        'tracking_id',
        'description',
        'amount',
        'state',
    ];

    protected $attributes = [
        'state' => AccountDepositState::PENDING
    ];

    public function account()
    {
        return $this->belongsTo('App\UserAccount');
    }


    // Set default tracking_id
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->tracking_id = (string) Uuid::generate();
        });
    }
}
