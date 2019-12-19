<?php


namespace Overlu\Rpc;


use Overlu\Rpc\Util\Log;

trait Module
{
    public $request_data = [];
    public static $request_data2 = [];
    public $response_data = [];
    public $class_params = [];
    public static $class_params2 = [];
    public $driver_name;
    public static $driver_name2;

    public function __construct($module = [], $params = [], $driver = '')
    {
        $this->request_data = $module;
        self::$request_data2 = $module;
        $this->class_params = $params;
        self::$class_params2 = $params;
        $this->driver_name = $driver;
        self::$driver_name2 = $driver;
    }

    /**
     * @param $method
     * @param $arguments
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __call($method, $arguments)
    {
        $this->request_data['to']['method'] = $method;
        $this->request_data['to']['params'] = $arguments;
        $this->request_data['to']['type'] = '->';

        return $this->call();
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws \ReflectionException
     */
    public static function __callStatic($method, $arguments)
    {
        self::$request_data2['to']['method'] = $method;
        self::$request_data2['to']['params'] = $arguments;
        self::$request_data2['to']['type'] = '::';
        $instance = static::getQueueInstance(self::$request_data2, self::$class_params2, self::$driver_name2);

        return $instance->call();
    }

    /**
     * @param $request_data
     * @return object
     * @throws \ReflectionException
     */
    public static function getQueueInstance($request_data)
    {
        return (new \ReflectionClass(__CLASS__))->newInstance($request_data);
    }

    public function call()
    {
        return true;
    }

    /**
     * @return array
     */
    public function response()
    {
        $this->syslog();
        return $this->response_data;
    }

    private function syslog()
    {
        if (config('rpc.log_info')) {
            $request = request();
            Log::info('driver: ' . strtolower($this->driver_name) . PHP_EOL, [
                'class params' => $this->class_params,
                'request data' => $this->request_data,
                'response data' => $this->response_data,
                'system data' => [
                    'ip' => $request->ip(),
                    'host' => $request->getHost(),
                    'url' => $request->fullUrl(),
                    'cookie' => $request->cookie()
                ]
            ]);
        }
    }
}
