<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 2019/12/3
 * Time: 22:08
 */

namespace Overlu\Rpc\Drivers;


use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Overlu\Rpc\Exceptions\RpcCode;
use Overlu\Rpc\Exceptions\RpcException;
use Overlu\Rpc\Module;
use Overlu\Rpc\Util\Encrypt;

class ApiModule
{
    use Module;
    public $moduleHost;


    /**
     * 传输数据
     * @param array $request_data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function call(array $request_data = [])
    {
        $request_data = empty($request_data) ? $this->request_data : $request_data;
        $request_data['class_params'] = $this->class_params;
        config('rpc.use_nacos') ? $this->getHostByNacos() : $this->getHost();
        $result = (new Client([
            'timeout' => 5.0
        ]))->request('POST', $this->moduleHost, [
            'form_params' => $request_data,
            'headers' => Encrypt::make()
        ]);
        $this->response_data = $result->getBody()->getContents();
        return $this->response();
    }

    /**
     * @param array $data
     * @return mixed
     * @throws RpcException
     * @throws \ReflectionException
     */
    public function watch($data = [])
    {
        $data = $data ?: (request()->input() ?: $this->request_data);
        $data['to']['params'] = $data['to']['params'] ?? [];
        $data['class_params'] = $data['class_params'] ?? [];
        /*if (!class_exists($data['to']['path'])) {
            throw new RpcException(RpcCode::RPC_CLASS_NOT_EXIST);
        }*/
        $instance = (new \ReflectionClass($data['to']['path']))->newInstance(...$data['class_params']);
        $method = $data['to']['method'];
        /*if (!method_exists($instance, $method)) {
            throw new RpcException(RpcCode::RPC_METHOD_NOT_EXIST);
        }*/
        return $data['to']['type'] !== '::'
            ? (isset($data['to']['app_id'])
                ? $instance->$method($data['to']['params'])
                : $instance->$method(...$data['to']['params']))
            : (isset($data['to']['app_id'])
                ? $instance::$method($data['to']['params'])
                : $instance::$method(...$data['to']['params']));
    }

    /**
     * 获取模块的api服务器
     * @return mixed
     */
    public function getHost()
    {
        $module = $this->request_data['to']['module'];
        $moduleHosts = explode(',', config('module.hosts.' . $module, ''));
        $moduleHost = count($moduleHosts) > 1 ? Arr::random($moduleHosts) : $moduleHosts[0];
        $this->moduleHost = 'http://' . $moduleHost . '/overlu/rpc/api';
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
        $this->moduleHost = 'http://' . $moduleHost . '/overlu/rpc/api';
        return $this->moduleHost;
    }
}
