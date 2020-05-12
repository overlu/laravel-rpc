<?php


namespace Overlu\Rpc\Drivers;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Overlu\Rpc\Exceptions\RpcCode;
use Overlu\Rpc\Exceptions\RpcException;
use Overlu\Rpc\Module;
use Overlu\Rpc\Util\Source;
use Pheanstalk\Pheanstalk;

class MessageQueueModule
{
    use Module;
    private $server;
    private $tube_prefix;

    /**
     * MessageQueueModule constructor.
     * @param array $module
     * @param array $params
     * @param string $driver
     * @throws RpcException
     */
    public function __construct($module = [], $params = [], $driver = '')
    {
        $this->request_data = $module;
        self::$request_data2 = $module;
        $this->class_params = $params;
        $this->driver_name = $driver;
        $this->tube_prefix = config('rpc.beanstalkd.channel');
        $this->connect();
    }

    /**
     * 传输数据
     * @param array $request_data
     * @return array
     */
    public function call(array $request_data = [])
    {
        $request_data = empty($request_data) ? $this->request_data : $request_data;
        $request_data['class_params'] = $this->class_params;
        $this->server->useTube(isset($request_data['to']['module']) ? $this->tube_prefix . '_' . $request_data['to']['module'] : $this->tube_prefix);
        $this->response_data = $this->server->put(json_encode($request_data, JSON_UNESCAPED_UNICODE));
        return $this->response();
    }

    /**
     * 服务连接
     * @throws RpcException
     */
    private function connect()
    {
        try {
            $this->server = Pheanstalk::create(config('rpc.beanstalkd.host', '127.0.0.1'), config('rpc.beanstalkd.port', '11300'));
        } catch (\Exception $exception) {
            throw new RpcException([
                $exception->getCode(), $exception->getMessage()
            ]);
        }
    }

    /**
     * @throws RpcException
     * @throws \ReflectionException
     */
    public function watch()
    {
        $tubes = $this->tubes();
        foreach ($tubes as $tube) {
            $this->server->watch($tube);
        }
        $job = $this->server->reserve();
        if ($job !== false) {
            $request_data = $job->getData();
            $this->subscribe(json_decode($request_data, true));
            $this->server->delete($job);
            $this->watch();
        } else {
            Log::error('host: ' . config('rpc.beanstalkd.host', '127.0.0.1') . ', reserve false');
        }
    }

    /**
     * 处理服务端接收数据
     * @param array $data
     * @throws RpcException
     * @throws \ReflectionException
     */
    protected function subscribe(array $data)
    {
        if ($this->checkIsLocal($data)) {
            /*if (!class_exists($data['to']['path'])) {
                throw new RpcException(RpcCode::RPC_CLASS_NOT_EXIST);
            }*/
            $instance = (new \ReflectionClass($data['to']['path']))->newInstance(...$data['class_params']);
            $method = $data['to']['method'];
            /*if (!method_exists($instance, $method)) {
                throw new RpcException(RpcCode::RPC_METHOD_NOT_EXIST);
            }*/
            $result = $data['to']['type'] !== '::'
                ? (isset($data['to']['app_id'])
                    ? $instance->$method($data['to']['params'])
                    : $instance->$method(...$data['to']['params']))
                : (isset($data['to']['app_id'])
                    ? $instance::$method($data['to']['params'])
                    : $instance::$method(...$data['to']['params']));
            if ($data['to']['async']) {
                $data['from']['method'] = '_' . $data['from']['method'];
                $data['from']['params'] = $result;
                $returnData = $this->changeStatus($data);
                $this->call($returnData);
            }
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function checkIsLocal(array $data): bool
    {
        if (isset($data['from']) && isset($data['to'])) {
            if (isset($data['to']['module'])) {
                return Source::in_local($data['to']['module']);
            }
            if (isset($data['to']['app_id'])) {
                return Cache::pull($data['to']['app_id']) ? true : false;
            }
        }
        return false;
    }

    /**
     * @param array $data
     * @return array
     */
    private function changeStatus(array $data): array
    {
        $data['temp'] = $data['to'];
        $data['to'] = $data['from'];
        $data['from'] = $data['temp'];
        $data['class_params'] = [];
        unset($data['temp']);
        return $data;
    }

    /**
     * @return array
     */
    public function status(): array
    {
        $tubes = $this->tubes();
        $status = [];
        foreach ($tubes as $tube) {
            $tubeStats = $this->server->statsTube($tube);
            $status[] = [
                $tubeStats['name'], $tubeStats['total-jobs'], $tubeStats['current-using'], $tubeStats['current-waiting'], $tubeStats['current-watching']
            ];
        }
        return $status;
    }

    /**
     * @return int
     */
    public function pid(): int
    {
        $stats = $this->server->stats();
        return $stats['pid'];
    }

    public function tubes()
    {
        $tubes = array_map(function ($tube) {
            return $this->tube_prefix . '_' . $tube;
        }, config('module.registration.local'));
        $tubes[] = $this->tube_prefix;
        return $tubes;
    }
}
