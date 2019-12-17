<?php


namespace Overlu\Rpc\Exceptions;


class RpcException extends \Exception
{
    public function __construct($rpcCode, \Throwable $previous = null)
    {
        parent::__construct($rpcCode[1], $rpcCode[0], $previous);
    }

}
