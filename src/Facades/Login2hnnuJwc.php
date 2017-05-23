<?php
namespace Ty666\Login2hnnuJwc\Facades;
use Illuminate\Support\Facades\Facade;

/**
 * Created by PhpStorm.
 * User: ty
 * Date: 16-10-10
 * Time: 下午9:20
 */
class Login2hnnuJwc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Ty666\Login2hnnuJwc\Login2hnnuJwc::class;
    }
}