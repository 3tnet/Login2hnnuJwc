<?php

namespace Ty666\Login2hnnuJwc\Laravel;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\ServiceProvider;
use Ty666\Login2hnnuJwc\Login2hnnuJwc;

/**
 * Created by PhpStorm.
 * User: ty
 * Date: 16-10-10
 * Time: 下午9:15
 */
class LaravelServiceProvider extends ServiceProvider
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

        $this->app->bind(Login2hnnuJwc::class, function ($app) {
            return new Login2hnnuJwc(new \GuzzleHttp\Client([RequestOptions::TIMEOUT => 7]));
        });
    }
}
