<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 2019/12/3
 * Time: 22:08
 */

namespace Overlu\Rpc\Drivers;


use Hprose\Client;
use Hprose\Future;
use Hprose\Socket\Server;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Overlu\Rpc\Exceptions\RpcCode;
use Overlu\Rpc\Exceptions\RpcException;
use Overlu\Rpc\Module;

class HproseModule
{
    use Module;
    public $moduleHost;
    private $server;
    private $client;

    /**
     * 传输数据
     * @param array $request_data
     * @return array
     * @throws \Exception
     */
    public function call(array $request_data = [])
    {
        $request_data = $request_data ?: $this->request_data;
        $async = $request_data['to']['async'] ?? false;
        config('rpc.use_nacos') ? $this->getHostByNacos() : $this->getHost();
        $this->client = Client::create($this->moduleHost, $async);
        $this->response_data = $this->client->proxy($request_data, $this->class_params);
        $this->response_data = ($this->response_data instanceof Future)
            ? $this->response_data->done(function ($data) use ($request_data) {
                $method = '_' . $request_data['from']['method'];
                $obj = new $request_data['from']['path'];
                if (method_exists($obj, $method)) {
                    $obj->$method($data);
                }
            }) : $this->response_data;
        return $this->response();
    }

    /**
     * start server
     */
    public function watch(): void
    {
        $this->server = new Server("tcp://0.0.0.0:" . config('rpc.port'));
        $this->server->addMethod('proxy', $this, ['oneway' => false, 'async' => false]);
        $this->server->debug = (app()->environment() !== 'production');
        $this->server->crossDomain = true;
        $this->server->start();
    }

    /**
     * 代理
     * @param array $data
     * @param array $class_params
     * @return mixed
     * @throws RpcException
     * @throws \ReflectionException
     */
    public function proxy($data = [], $class_params = [])
    {
        $data = $data ?: $this->request_data;
        $data['to']['params'] = $data['to']['params'] ?? [];
        /*if (!class_exists($data['to']['path'])) {
            throw new RpcException(RpcCode::RPC_CLASS_NOT_EXIST);
        }*/
        $instance = (new \ReflectionClass($data['to']['path']))->newInstance(...$class_params);
        $method = $data['to']['method'];
        /*if (!method_exists($instance, $method)) {
            throw new RpcException(RpcCode::RPC_METHOD_NOT_EXIST);
        }*/
        return $data['to']['type'] !== '::'
            ? $instance->$method(...$data['to']['params'])
            : $instance::$method(...$data['to']['params']);
    }

    /**
     * 获取模块的rpc服务器
     * @return string
     */
    public function getHost(): string
    {
        $module = $this->request_data['to']['module'];
        $moduleHosts = explode(',', config('module.hosts.' . $module, ''));
        $moduleHost = count($moduleHosts) > 1 ? Arr::random($moduleHosts) : $moduleHosts[0];
        $this->moduleHost = 'tcp://' . (Str::contains($moduleHost, ':') ? $moduleHost : $moduleHost . ':' . config('rpc.port'));
        return $this->moduleHost;
    }

    /**
     * 获取nacos服务地址
     * @return string
     * @throws RpcException
     */
    public function getHostByNacos()
    {
        if (!class_exists('\\Overlu\\Reget\\Reget')) {
            throw new RpcException(RpcCode::RPC_LARAVEL_REGET_NOT_EXISTS);
        }
        $moduleHost = \Overlu\Reget\Reget::getInstance()->service($this->request_data['to']['module']);
        $this->moduleHost = 'tcp://' . (Str::contains($moduleHost, ':') ? $moduleHost : $moduleHost . ':' . config('rpc.port'));
        return $this->moduleHost;
    }
}
