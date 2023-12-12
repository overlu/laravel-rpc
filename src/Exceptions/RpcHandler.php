<?php


namespace Overlu\Rpc\Exceptions;


use App\Exceptions\Handler;
use Exception;

class RpcHandler extends Handler
{
    public function render($request, Exception $exception)
    {

        // rpc接口主动抛出的异常
        if ($exception instanceof RpcException) {
            $code = $exception->getCode();
            $code = (!$code || $code < 0) ? RPCStatus::RPC_UNKNOWN_ERROR[0] : $code;
            $msg = $exception->getMessage() ?: RPCStatus::RPC_UNKNOWN_ERROR[1];
            // 异常返回
            return response()->json([
                'error' => true,
                'code' => $code,
                'message' => $msg,
            ]);
        }
        parent::render($request, $exception);

    }

    public function report(Exception $exception)
    {
        parent::report($exception);
    }


}
