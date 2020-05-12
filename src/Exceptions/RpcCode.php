<?php


namespace Overlu\Rpc\Exceptions;


class RpcCode
{
    const RPC_UNKNOWN_ERROR = [0, 'unknown error'];

    const RPC_OK = [200, 'ok'];
    const RPC_FORBIDDEN = [403, 'forbidden'];
    const RPC_NOT_FOUND = [404, 'not found'];
    const RPC_METHOD_NOT_ALLOWED = [405, 'method not allowed'];
    const RPC_REQUEST_TIMEOUT = [408, 'request timeout'];
    const RPC_CLASS_NOT_EXIST = [440, 'class not exist'];
    const RPC_METHOD_NOT_EXIST = [441, 'method not exist'];

    const RPC_INTERNAL_SERVER_ERROR = [500, 'internal server error'];
    const RPC_NOT_IMPLEMENTED = [501, 'not implemented'];
    const RPC_BAD_GATEWAY = [502, 'bad gateway'];
    const RPC_SERVICE_UNAVAILABLE = [503, 'service unavailable'];
    const RPC_GATEWAY_TIMEOUT = [504, 'gateway timeout'];

    const RPC_LARAVEL_REGET_NOT_EXISTS = [600, "laravel-reget extension not exists. run 'composer require overlu/laravel-reget' first."];


}
