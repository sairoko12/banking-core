<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    const REPOSITORY_INTERFACE_PATH = 'App\Repositories\Interfaces\\';
    const REPOSITORY_CLASS_PATH = 'App\Repositories\\';

    const REPOSITORIES = [
        // User repository
        [
            'UserRepositoryInterface',
            'UserRepository'
        ],
        // User account repository
        [
            'UserAccountRepositoryInterface',
            'UserAccountRepository'
        ],
        // Account deposit repository
        [
            'AccountDepositRepositoryInterface',
            'AccountDepositRepository'
        ],
        // Account charge repository
        [
            'AccountChargeRepositoryInterface',
            'AccountChargeRepository'
        ],
        // Account credit repository
        [
            'AccountCreditRepositoryInterface',
            'AccountCreditRepository'
        ]
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        collect(self::REPOSITORIES)->eachSpread(function ($interface, $class) {
            $this->app->bind(self::REPOSITORY_INTERFACE_PATH . $interface,
                self::REPOSITORY_CLASS_PATH . $class);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
