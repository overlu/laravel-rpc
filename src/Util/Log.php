<?php


namespace Overlu\Rpc\Util;

use \Illuminate\Support\Facades\Log as Lg;

class Log
{
    public static function info($message, $content = [])
    {
        Lg::channel('rpc_log_info_channel')->info($message, $content);
    }

    public static function error($message, $content = [])
    {
        Lg::channel('rpc_log_error_channel')->error($message, $content);
    }
}
