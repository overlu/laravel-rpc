<?php

namespace Overlu\Rpc\Exceptions;

class RPCStatus
{
    public const RPC_UNKNOWN_ERROR = [0, 'unknown error'];

    public const RPC_OK = [200, 'ok'];
    public const RPC_FORBIDDEN = [403, 'forbidden'];
    public const RPC_NOT_FOUND = [404, 'not found'];
    public const RPC_METHOD_NOT_ALLOWED = [405, 'method not allowed'];
    public const RPC_REQUEST_TIMEOUT = [408, 'request timeout'];
    public const RPC_CLASS_NOT_EXIST = [440, 'class not exist'];
    public const RPC_METHOD_NOT_EXIST = [441, 'method not exist'];

    public const RPC_INTERNAL_SERVER_ERROR = [500, 'internal server error'];
    public const RPC_NOT_IMPLEMENTED = [501, 'not implemented'];
    public const RPC_BAD_GATEWAY = [502, 'bad gateway'];
    public const RPC_SERVICE_UNAVAILABLE = [503, 'service unavailable'];
    public const RPC_GATEWAY_TIMEOUT = [504, 'gateway timeout'];

    public const RPC_LARAVEL_REGET_NOT_EXISTS = [600, "laravel-reget extension not exists. run 'composer require overlu/laravel-reget' first."];


}
