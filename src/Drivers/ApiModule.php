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
        $this->getHost();
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
        $apiHosts = config('module.api');
        $moduleHosts = [];
        foreach ($apiHosts as $host => $modules) {
            if (in_array($module, $modules)) {
                $moduleHosts[] = $host;
            }
        }
        $this->moduleHost = 'http://' . Arr::random($moduleHosts) . '/overlu/rpc/api';
        return $this->moduleHost;
    }
}
