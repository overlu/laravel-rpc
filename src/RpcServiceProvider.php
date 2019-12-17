<?php

namespace Overlu\Rpc;

use Illuminate\Support\ServiceProvider;
use Overlu\Rpc\Console\Stop;
use Overlu\Rpc\Console\Reload;
use Overlu\Rpc\Console\Server;
use Overlu\Rpc\Console\Status;

class RpcServiceProvider extends ServiceProvider
{
    protected $defer = true; // 延迟加载服务

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('rpc', function ($app) {
            return new Rpc();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/rpc.php' => config_path('rpc.php'), // 发布配置文件到 laravel 的config 下
            __DIR__ . '/config/module.php' => config_path('module.php'), // 发布配置文件到 laravel 的config 下
        ]);
//        $this->app->offsetUnset(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        /**
         * bind rpc exception handler
         */
        if (config('rpc.exception_driver') === 'rpc' && $this->app->environment() === 'production') {
            $this->app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class,
                \Overlu\Rpc\Exceptions\RpcHandler::class);
        }
        $this->commands([Server::class, Status::class, Stop::class, Reload::class]);
        $this->app['config']["logging.channels.rpc_log_info_channel"] = config('rpc.rpc_log_info_channel');
        $this->app['config']["logging.channels.rpc_log_error_channel"] = config('rpc.rpc_log_error_channel');
        /*$this->app->booted(function () {
            $this->app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class,
//    App\Exceptions\Handler::class,
                \Overlu\Rpc\Exceptions\RpcHandler::class);
        });*/
        /*$this->app->bind(Illuminate\Contracts\Debug\ExceptionHandler::class,function ($service){
            return new \Overlu\Rpc\Exceptions\RpcHandler($service);
        });*/

        $this->loadRoutesFrom(__DIR__ . DIRECTORY_SEPARATOR . 'Routes.php');

    }

    public function provides()
    {
        return ['rpc'];
    }
}
