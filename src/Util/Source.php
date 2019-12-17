<?php


namespace Overlu\Rpc\Util;


class Source
{
    public static function __callStatic($type, $arguments)
    {
        return (new \Exception())->getTrace()[3][$type];
    }

    public static function from()
    {
        $temp = (new \Exception())->getTrace()[3];
        return [
            'path' => $temp['class'],
            'method' => $temp['function'],
            'params' => $temp['args'],
            'type' => $temp['type'],
            'app_id' => static::app_id(),
            'async' => false,
        ];
    }

    /**
     * @return int
     */
    public static function app_id()
    {
        $app_id = SnowFlake::make(1, 1);
        \Cache::add($app_id, 1, 5);
        return $app_id;
    }

    /**
     * @param $module
     * @return bool
     */
    public static function in_local($module)
    {
        return in_array($module, config('module.registration.local'));
    }
}
