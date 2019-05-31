<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/user/register', 'UserController@register')->name('user.register');
Route::post('/user/login', 'UserController@login')->name('user.login');

// Routes for the cash machine
Route::group([
    'name' => "cash_machine",
    'prefix' => "/cash-machine",
    'middleware' => "token:api"
], function () {
    Route::post('/pay', 'CashMachineController@pay')->name("payment");

    Route::put('/deposit/{state}', 'CashMachineController@changeDepositState')->name("deposit.state");

    Route::put('/charge/{state}', 'CashMachineController@changeChargeState')->name("charge.state");
});

// Routes for user
Route::middleware('auth:api')->group(function () {
    Route::prefix('/me')->group(function () {
        Route::get('/', 'UserController@details')->name('user.details');

        // Resource of user acount
        Route::resource('/accounts', 'UserAccountsController')->except([
            'create',
            'edit',
            'destroy'
        ]);
    });

    Route::post('/account/deposit/{accountId}', 'CashMachineController@deposit')->name('account.deposit');

    Route::post('/account/withdraw/{accountId}', 'CashMachineController@withdraw')->name('account.withdraw');
});
