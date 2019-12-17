<?php


namespace Overlu\Rpc;

use Illuminate\Http\Request;
use Overlu\Rpc\Exceptions\RpcCode;
use Overlu\Rpc\Exceptions\RpcException;
use Overlu\Rpc\Util\Encrypt;
use Overlu\Rpc\Util\Signature;

class Base
{
    /**
     * 驱动
     * @var string
     */
    public $driver;

    /**
     * 模型参数
     * @var
     */
    public $params;

    /**
     * 私钥
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $key;

    public function __construct(string $driver, array $params)
    {
        $this->driver = $driver;
        $this->params = $params;

        $this->key = config('rpc.key', '');
        (config('rpc.environment', 'dev') !== 'dev') or $this->check();
        $this->init();
    }

    protected function init()
    {
//        $signature = $this->genSignature();
    }

    /**
     * 检测请求合法性
     * @return bool
     * @throws RpcException
     */
    private function check()
    {
        if ($this->driver === 'localClass') return true;
        $checkMethod = (config('rpc.verify_method') === 'signature')
            ? 'checkSignature'
            : 'checkWhiteIpLists';
        if (!$this->$checkMethod()) {
            throw new RpcException(RpcCode::RPC_FORBIDDEN);
        }
        return true;
    }

    private function checkWhiteIpLists()
    {
        return true;
        //todo 检测白名单
    }

    /**
     * 签名验证
     * @return bool
     */
    public function checkSignature(): bool
    {
        return true;
    }

    public function module(array $module)
    {
        $class = get_class($this) . 'Module';
        return new $class($module, $this->params, $this->driver);
    }
}
