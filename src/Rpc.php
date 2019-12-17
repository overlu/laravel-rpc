<?php


namespace Overlu\Rpc;


use Illuminate\Support\Str;
use Overlu\Rpc\Exceptions\RpcException;
use Overlu\Rpc\Util\Log;
use Overlu\Rpc\Util\Source;

class Rpc
{
    private $drivers = [
        'api' => 'Api',
        'rpc' => 'Hprose',
        'mq' => 'MessageQueue',
        'local' => 'LocalClass'
    ];

    /**
     * @param string $name
     * @param array $from
     * @param array $params
     * @return mixed
     * @throws \ReflectionException
     */
    public function driver(string $name, array $from, array $params = [])
    {
        if (Str::is('_*', $name)) {
            $name = substr($name, 1);
            $async = true;
        } else {
            $async = false;
        }
        $driver = $this->getDriver($name);
        $driver_name = Str::studly($driver['driver']);
        $driverClass = "\\Overlu\\Rpc\\Drivers\\" . $driver_name;
        return (new \ReflectionClass($driverClass))->newInstance($driver['driver'], $params)->module([
            'to' => [
                'module' => $name,
                'path' => $driver['path'],
                'async' => $async,
            ],
            'from' => $from
        ]);
    }

    /**
     * @param $module
     * @param $arguments
     * @return mixed
     * @throws RpcException
     */
    public function __call($module, $arguments)
    {
        try {
            return $this->driver($module, Source::from(), $arguments);
        } catch (\Exception $exception) {
            if (config('rpc.log_error')) {
                Log::error('error code: ' . $exception->getCode() . ' , error message: ' . $exception->getMessage() . ' , at file: ' . $exception->getFile() . ', on line: ' . $exception->getLine());
            }
            throw new RpcException([
                $exception->getCode(), $exception->getMessage()
            ]);
        }
    }

    /**
     * 获取驱动
     * @param $module
     * @return array
     */
    private function getDriver($module)
    {
        $driver_modules = config('module.registration');
        $driver = '';
        foreach ($driver_modules as $key => $driver_module) {
            if (in_array($module, $driver_module)) {
                $driver = $key;
                break;
            }
        }
        return [
            'driver' => $this->drivers[$driver] ?? 'LocalClass',
            'path' => config('module.mapping.' . $module)
        ];
    }
}
