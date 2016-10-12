<?php

namespace Ty666\Login2hnnuJwc\ServiceProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Created by PhpStorm.
 * User: ty
 * Date: 16-10-10
 * Time: 下午9:15
 */
class ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('login2hnnuJwc', function ($app) {

            $pictureManager = new Login2hnnuJwc();
            return $pictureManager;
        });
    }
}