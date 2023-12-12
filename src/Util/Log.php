<?php

namespace Overlu\Rpc\Util;

use \Illuminate\Support\Facades\Log as Logger;

class Log
{
    public static function info($message, $content = [])
    {
        Logger::channel('rpc_log_info_channel')->info($message, $content);
    }

    public static function error($message, $content = [])
    {
        Logger::channel('rpc_log_error_channel')->error($message, $content);
    }
}
