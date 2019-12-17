<?php


namespace Overlu\Rpc\Drivers;


use Overlu\Rpc\Exceptions\RpcCode;
use Overlu\Rpc\Exceptions\RpcException;

class LocalClassModule
{
    public $module;
    public $path;
    public static $module2;
    public static $path2;
    public $class_params;
    public $driver_name;

    /**
     * LocalClassModule constructor.
     * @param array $module
     * @param array $params
     * @param string $driver
     */
    public function __construct($module = [], $params = [], $driver = '')
    {
        $this->module = $module['to']['module'];
        $this->path = $module['to']['path'];
        self::$module2 = $module['to']['module'];
        self::$path2 = $module['to']['path'];
        $this->class_params = $params;
        $this->driver_name = $driver;
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws RpcException
     */
    public function __call($method, $arguments)
    {
        if (!class_exists($this->path)) {
            throw new RpcException(RpcCode::RPC_CLASS_NOT_EXIST);
        }
        $module = new $this->path(...$this->class_params);
        if (!method_exists($module, $method)) {
            throw new RpcException(RpcCode::RPC_METHOD_NOT_EXIST);
        }
        return $module->$method(...$arguments);
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws RpcException
     */
    public static function __callStatic($method, $arguments)
    {
        if (!class_exists(self::$path2)) {
            throw new RpcException(RpcCode::RPC_CLASS_NOT_EXIST);
        }
        $module = new self::$path2;
        if (!method_exists($module, $method)) {
            throw new RpcException(RpcCode::RPC_METHOD_NOT_EXIST);
        }
        return $module::$method(...$arguments);
    }
}
